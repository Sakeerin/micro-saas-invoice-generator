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
        if ($invoice->company_id !== $company->id) {
            abort(403);
        }

        // Check if PDF is already generated and up-to-date
        if ($pdfService->isCacheValid($invoice)) {
            return redirect($invoice->pdf_url);
        }

        // If not, generate it (synchronously for the first time or use queue and wait)
        // For download button, we might want to generate it synchronously or show a loading state
        // The implementation plan says "dispatch job if no cache" but also mentions redirect to presigned URL
        
        // Let's generate it synchronously here for simplicity of the download button
        // In a production app, we might use a queue and broadcast the result
        $pdfUrl = $pdfService->generate($invoice);

        return redirect($pdfUrl);
    }

    public function preview(Invoice $invoice, InvoicePdfService $pdfService)
    {
        // Verify ownership
        $user = auth()->user();
        $company = $user->companies()->first();
        if ($invoice->company_id !== $company->id) {
            abort(403);
        }

        // Just render the HTML for preview
        return view("pdf.templates.{$invoice->template}", [
            'invoice' => $invoice->load(['items', 'client', 'company']),
            'fontCss' => $this->getFontCss(),
        ]);
    }

    private function getFontCss(): string
    {
        $css = '';
        $fonts = [
            'Sarabun'       => 'Sarabun-Regular.ttf',
            'Sarabun-Bold'  => 'Sarabun-Bold.ttf',
            'NotoSansThai'  => 'NotoSansThai-Regular.ttf',
        ];

        foreach ($fonts as $family => $filename) {
            $path = storage_path("fonts/{$filename}");
            if (file_exists($path)) {
                $base64 = base64_encode(file_get_contents($path));
                $css .= "@font-face {
                    font-family: '{$family}';
                    src: url('data:font/truetype;base64,{$base64}') format('truetype');
                }\n";
            }
        }
        return $css;
    }
}
