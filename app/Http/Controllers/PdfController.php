<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateInvoicePdfJob;
use App\Models\Invoice;
use App\Services\InvoicePdfService;
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
        // For Inertia, we might want to return a back() with a flash message
        // or a specific status if it's an API call.
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
}
