<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Product;
use App\Services\TaxCalculationInput;
use App\Services\TaxEngineService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\InvoiceMail;

class InvoiceController extends Controller
{
    /**
     * Return the next available invoice number for the authenticated company.
     * Used by Create.vue to refresh the number if it may have been taken concurrently.
     */
    public function nextNumber(): \Illuminate\Http\JsonResponse
    {
        $company = auth()->user()->companies()->first();
        $year = now()->year;
        $prefix = $company->invoice_prefix ?? 'INV';
        $nextNumber = str_pad($company->invoice_next_number ?? 1, 4, '0', STR_PAD_LEFT);

        return response()->json(['invoice_number' => "{$prefix}-{$year}-{$nextNumber}"]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $user = auth()->user();
        $company = $user->companies()->first();
        
        $query = Invoice::where('company_id', $company->id)
            ->with('client');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('invoice_number', 'like', '%' . $request->search . '%')
                  ->orWhere('client_name', 'like', '%' . $request->search . '%')
                  ->orWhere('client_name_en', 'like', '%' . $request->search . '%');
            });
        }

        $invoices = $query->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        $clients = Client::where('company_id', $company->id)->get();

        return Inertia::render('Invoices/Index', [
            'invoices' => $invoices,
            'filters' => $request->only(['status', 'client_id', 'search']),
            'clients' => $clients,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        $user = auth()->user();
        $company = $user->companies()->first();
        $clients = Client::where('company_id', $company->id)->get();
        $products = Product::where('company_id', $company->id)->get();

        // Generate default invoice number
        $year = now()->year;
        $prefix = $company->invoice_prefix ?? 'INV';
        $nextNumber = str_pad($company->invoice_next_number ?? 1, 4, '0', STR_PAD_LEFT);
        $defaultInvoiceNumber = "{$prefix}-{$year}-{$nextNumber}";

        return Inertia::render('Invoices/Create', [
            'clients' => $clients,
            'products' => $products,
            'defaultInvoiceNumber' => $defaultInvoiceNumber,
            'company' => $company,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, TaxEngineService $taxEngine)
    {
        $user = auth()->user();
        $company = $user->companies()->first();

        // Enforce Free plan limit (5 invoices/month)
        $subscription = $user->subscription;
        if ($subscription && $subscription->plan === 'free') {
            if ($subscription->invoice_count_this_month >= 5) {
                return back()->withErrors([
                    'plan_limit' => 'คุณใช้ครบ 5 invoice ของแผน Free แล้ว กรุณาอัปเกรดเป็น Pro เพื่อออก invoice ไม่จำกัด',
                ])->with('upgrade_required', true);
            }
        }

        // Calculate totals using TaxEngineService
        $taxInput = new TaxCalculationInput(
            items: $request->items,
            discountType: $request->discount_type ?? 'none',
            discountValue: (float) ($request->discount_value ?? 0),
            vatRate: (float) ($request->vat_rate ?? 0),
            whtRate: (float) ($request->wht_rate ?? 0),
        );

        $taxResult = $taxEngine->calculate($taxInput);

        DB::transaction(function () use ($request, $company, $taxResult) {
            $invoice = Invoice::create([
                'company_id' => $company->id,
                'client_id' => $request->client_id,
                'invoice_number' => $request->invoice_number,
                'reference' => $request->reference,
                'issue_date' => $request->issue_date,
                'due_date' => $request->due_date,
                'currency' => $request->currency ?? $company->default_currency ?? 'THB',
                'language' => $request->language ?? 'th-en',
                'template' => $request->template ?? 'modern',
                'client_name' => $request->client_name,
                'client_name_en' => $request->client_name_en,
                'client_address' => $request->client_address,
                'client_address_en' => $request->client_address_en,
                'client_tax_id' => $request->client_tax_id,
                'discount_type' => $request->discount_type ?? 'none',
                'discount_value' => $request->discount_value ?? 0,
                'discount_amount' => $taxResult->discountAmount,
                'notes' => $request->notes,
                'payment_terms' => $request->payment_terms,
                'subtotal' => $taxResult->subtotal,
                'subtotal_after_discount' => $taxResult->subtotalAfterDiscount,
                'vat_rate' => $taxResult->vatRate,
                'vat_amount' => $taxResult->vatAmount,
                'wht_rate' => $taxResult->whtRate,
                'wht_amount' => $taxResult->whtAmount,
                'total' => $taxResult->total,
                'status' => 'draft',
            ]);

            $invoice->activities()->create([
                'type' => 'created',
                'meta' => ['user_id' => auth()->id()],
            ]);

            // Save items
            foreach ($request->items as $index => $itemData) {
                $lineSubtotal = $itemData['quantity'] * $itemData['unit_price'];
                $lineTotal = $lineSubtotal * (1 - ($itemData['discount_percent'] ?? 0) / 100);

                $invoice->items()->create([
                    'product_id' => $itemData['product_id'] ?? null,
                    'sort_order' => $index,
                    'name' => $itemData['name'],
                    'name_en' => $itemData['name_en'] ?? null,
                    'description' => $itemData['description'] ?? null,
                    'quantity' => $itemData['quantity'] ?? 1,
                    'unit' => $itemData['unit'] ?? 'งาน',
                    'unit_price' => $itemData['unit_price'] ?? 0,
                    'discount_percent' => $itemData['discount_percent'] ?? 0,
                    'line_total' => $lineTotal,
                ]);
            }

            $company->increment('invoice_next_number');
        });

        // Increment monthly invoice counter for Free plan tracking
        if ($subscription && $subscription->plan === 'free') {
            $subscription->increment('invoice_count_this_month');
        }

        return redirect()->route('invoices.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Invoice $invoice)
    {
        $user = auth()->user();
        $company = $user->companies()->first();

        if ($invoice->company_id !== $company->id) {
            abort(403);
        }

        $invoice->delete();

        return redirect()->route('invoices.index');
    }

    /**
     * Duplicate the specified resource.
     */
    public function duplicate(Invoice $invoice)
    {
        $user = auth()->user();
        $company = $user->companies()->first();

        if ($invoice->company_id !== $company->id) {
            abort(403);
        }

        DB::transaction(function () use ($invoice, $company) {
            // Generate next invoice number
            $year = now()->year;
            $prefix = $company->invoice_prefix ?? 'INV';
            $nextNumber = str_pad($company->invoice_next_number ?? 1, 4, '0', STR_PAD_LEFT);
            $newInvoiceNumber = "{$prefix}-{$year}-{$nextNumber}";

            $newInvoice = $invoice->replicate();
            $newInvoice->invoice_number = $newInvoiceNumber;
            $newInvoice->status = 'draft';
            $newInvoice->issue_date = now();
            
            if ($invoice->due_date && $invoice->issue_date) {
                $days = $invoice->issue_date->diffInDays($invoice->due_date);
                $newInvoice->due_date = now()->addDays($days);
            }
            
            $newInvoice->pdf_url = null;
            $newInvoice->pdf_hash = null;
            $newInvoice->share_token = null;
            $newInvoice->first_viewed_at = null;
            $newInvoice->last_viewed_at = null;
            $newInvoice->view_count = 0;
            $newInvoice->email_sent_at = null;
            $newInvoice->email_opened_at = null;
            $newInvoice->paid_at = null;
            
            $newInvoice->save();

            $newInvoice->activities()->create([
                'type' => 'created',
                'meta' => ['source_invoice_id' => $invoice->id, 'user_id' => auth()->id()],
            ]);

            foreach ($invoice->items as $item) {
                $newItem = $item->replicate();
                $newItem->invoice_id = $newInvoice->id;
                $newItem->save();
            }

            $company->increment('invoice_next_number');
        });

        return redirect()->route('invoices.index');
    }

    /**
     * Display the specified resource for public view via token.
     */
    public function showPublic(string $token, \App\Services\InvoicePdfService $pdfService)
    {
        $invoice = Invoice::where('share_token', $token)
            ->with(['company', 'items'])
            ->firstOrFail();

        $now = now();
        $updateData = [
            'view_count' => $invoice->view_count + 1,
            'last_viewed_at' => $now,
        ];

        if (!$invoice->first_viewed_at) {
            $updateData['first_viewed_at'] = $now;
        }

        if ($invoice->status === 'sent') {
            $updateData['status'] = 'viewed';
        }

        $invoice->update($updateData);

        $invoice->activities()->create([
            'type' => 'viewed',
            'meta' => [
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'is_first_view' => !$invoice->first_viewed_at,
            ],
        ]);

        return view("pdf.public_view", [
            'invoice' => $invoice,
        ]);
    }

    /**
     * Generate share link for an invoice.
     */
    public function share(Invoice $invoice)
    {
        $user = auth()->user();
        $company = $user->companies()->first();

        if ($invoice->company_id !== $company->id) {
            abort(403);
        }

        $isFirstSent = !$invoice->share_token;

        if ($isFirstSent) {
            $invoice->update([
                'share_token' => Str::random(64),
                'status' => $invoice->status === 'draft' ? 'sent' : $invoice->status,
                'email_sent_at' => now(),
            ]);

            $invoice->activities()->create([
                'type' => 'sent',
                'meta' => ['method' => 'link', 'user_id' => auth()->id()],
            ]);
        }

        return back()->with('share_link', route('invoices.show_public', $invoice->share_token));
    }

    /**
     * Send invoice to client via email.
     */
    public function sendByEmail(Invoice $invoice, Request $request)
    {
        $user = auth()->user();
        $company = $user->companies()->first();

        if ($invoice->company_id !== $company->id) {
            abort(403);
        }

        $email = $request->email ?? $invoice->client?->contact_email;

        if (!$email) {
            return back()->with('error', 'Client email not found.');
        }

        if (!$invoice->share_token) {
            $invoice->update([
                'share_token' => Str::random(64),
            ]);
        }

        Mail::to($email)->send(new InvoiceMail($invoice->load(['company', 'items', 'client'])));

        $invoice->update([
            'status' => in_array($invoice->status, ['draft', 'sent']) ? 'sent' : $invoice->status,
            'email_sent_at' => now(),
        ]);

        $invoice->activities()->create([
            'type' => 'sent',
            'meta' => ['method' => 'email', 'recipient' => $email, 'user_id' => auth()->id()],
        ]);

        return back()->with('success', 'Invoice has been sent successfully.');
    }

    /**
     * Mark the specified invoice as paid.
     */
    public function markAsPaid(Invoice $invoice)
    {
        $user = auth()->user();
        $company = $user->companies()->first();

        if ($invoice->company_id !== $company->id) {
            abort(403);
        }

        $invoice->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        $invoice->activities()->create([
            'type' => 'paid',
            'meta' => ['user_id' => auth()->id()],
        ]);

        return back()->with('success', 'Invoice marked as paid.');
    }
}
