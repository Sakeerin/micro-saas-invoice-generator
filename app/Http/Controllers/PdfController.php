<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateInvoicePdfJob;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Services\InvoicePdfService;
use App\Services\TaxCalculationInput;
use App\Services\TaxEngineService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PdfController extends Controller
{
    public function download(Invoice $invoice, InvoicePdfService $pdfService)
    {
        // Verify ownership
        $user = auth()->user();
        $company = $user->companies()->first();
        if (!$company || $invoice->company_id !== $company->id) {
            abort(403);
        }

        // Check if PDF is already generated and up-to-date
        if ($pdfService->isCacheValid($invoice)) {
            return redirect($invoice->pdf_url);
        }

        // Dispatch job to generate PDF
        GenerateInvoicePdfJob::dispatch($invoice);

        // Return a response indicating that generation has started
        return back()->with('status', 'Generating PDF... Please wait a moment.');
    }

    public function preview(Invoice $invoice, InvoicePdfService $pdfService)
    {
        // Verify ownership
        $user = auth()->user();
        $company = $user->companies()->first();
        if (!$company || $invoice->company_id !== $company->id) {
            abort(403);
        }

        // Just render the HTML for preview
        return view("pdf.templates.{$invoice->template}", [
            'invoice' => $invoice->load(['items', 'client', 'company']),
            'fontCss' => $pdfService->buildFontCss(),
        ]);
    }

    public function previewPublic(string $token, InvoicePdfService $pdfService)
    {
        $invoice = Invoice::where('share_token', $token)
            ->with(['company', 'items'])
            ->firstOrFail();

        // Just render the HTML for preview
        return view("pdf.templates.{$invoice->template}", [
            'invoice' => $invoice,
            'fontCss' => $pdfService->buildFontCss(),
        ]);
    }

    public function previewDraft(Request $request, InvoicePdfService $pdfService, TaxEngineService $taxEngine)
    {
        $user = auth()->user();
        $company = $user->companies()->first();
        if (!$company) {
            abort(403);
        }

        // Calculate totals using TaxEngineService
        $taxInput = new TaxCalculationInput(
            items: $request->items ?? [],
            discountType: $request->discount_type ?? 'none',
            discountValue: (float) ($request->discount_value ?? 0),
            vatRate: (float) ($request->vat_rate ?? 0),
            whtRate: (float) ($request->wht_rate ?? 0),
        );

        $taxResult = $taxEngine->calculate($taxInput);

        // Build a temporary Invoice model
        $invoice = new Invoice([
            'invoice_number' => $request->invoice_number,
            'reference' => $request->reference,
            'issue_date' => $request->issue_date ? \Carbon\Carbon::parse($request->issue_date) : now(),
            'due_date' => $request->due_date ? \Carbon\Carbon::parse($request->due_date) : null,
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
        ]);

        $invoice->setRelation('company', $company);
        
        // Build items collection
        $items = collect($request->items ?? [])->map(function ($itemData, $index) {
            $lineSubtotal = $itemData['quantity'] * $itemData['unit_price'];
            $lineTotal = $lineSubtotal * (1 - ($itemData['discount_percent'] ?? 0) / 100);

            return new InvoiceItem([
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
        });
        
        $invoice->setRelation('items', $items);

        return view("pdf.templates.{$invoice->template}", [
            'invoice' => $invoice,
            'fontCss' => $pdfService->buildFontCss(),
        ]);
    }
}
