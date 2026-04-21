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

class InvoiceController extends Controller
{
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
        // Validation will be added in a later step
        // For now, focus on UI and saving basic info
        
        $user = auth()->user();
        $company = $user->companies()->first();

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

            // Save items
            foreach ($request->items as $index => $itemData) {
                // Calculate line total for safety (though frontend should have it)
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

            // Increment company next invoice number if this one was used
            $company->increment('invoice_next_number');
        });

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
            
            // Re-calculate due date based on duration
            if ($invoice->due_date && $invoice->issue_date) {
                $days = $invoice->issue_date->diffInDays($invoice->due_date);
                $newInvoice->due_date = now()->addDays($days);
            }
            
            // Clear tracking/PDF info
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

            // Duplicate items
            foreach ($invoice->items as $item) {
                $newItem = $item->replicate();
                $newItem->invoice_id = $newInvoice->id;
                $newItem->save();
            }

            $company->increment('invoice_next_number');
        });

        return redirect()->route('invoices.index');
    }
}
