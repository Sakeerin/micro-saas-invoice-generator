<?php

namespace App\Services;

use App\Models\Invoice;
use Illuminate\Support\Facades\Storage;
use Spatie\Browsershot\Browsershot;

class InvoicePdfService
{
    private const THAI_FONTS = [
        'Sarabun'       => 'Sarabun-Regular.ttf',
        'Sarabun-Bold'  => 'Sarabun-Bold.ttf',
        'NotoSansThai'  => 'NotoSansThai-Regular.ttf',
    ];

    public function generate(Invoice $invoice): string
    {
        // 1. Render HTML from Blade template
        $html = $this->renderTemplate($invoice);

        // 2. Generate PDF via Puppeteer
        $browsershot = Browsershot::html($html)
            ->noSandbox()
            ->waitUntilNetworkIdle()
            ->margins(15, 15, 15, 15)
            ->format('A4')
            ->showBackground();

        if (env('BROWSERSHOT_NODE_BINARY')) {
            $browsershot->setNodeBinary(env('BROWSERSHOT_NODE_BINARY'));
        } elseif (config('app.env') === 'production') {
            $browsershot->setNodeBinary('/usr/local/bin/node');
        }

        if (env('BROWSERSHOT_NPM_BINARY')) {
            $browsershot->setNpmBinary(env('BROWSERSHOT_NPM_BINARY'));
        } elseif (config('app.env') === 'production') {
            $browsershot->setNpmBinary('/usr/local/bin/npm');
        }
        
        if (env('BROWSERSHOT_CHROME_BINARY')) {
            $browsershot->setChromePath(env('BROWSERSHOT_CHROME_BINARY'));
        }

        $pdf = $browsershot->pdf();

        // 3. Upload to Cloudflare R2 (or fallback to public for dev)
        $filename = "invoices/{$invoice->company_id}/{$invoice->id}.pdf";
        $disk = config('filesystems.default') === 'r2' ? 'r2' : 'public';
        
        Storage::disk($disk)->put($filename, $pdf, [
            'ContentType' => 'application/pdf',
            'CacheControl' => 'max-age=3600',
        ]);

        // 4. Update invoice record with temporary URL and hash
        $pdfUrl = $disk === 'r2' 
            ? Storage::disk($disk)->temporaryUrl($filename, now()->addHours(24))
            : Storage::disk($disk)->url($filename);
            
        $pdfHash = $this->calculateHash($invoice);

        $invoice->update([
            'pdf_url'  => $pdfUrl,
            'pdf_hash' => $pdfHash,
        ]);

        return $pdfUrl;
    }

    private function renderTemplate(Invoice $invoice): string
    {
        $fontCss = $this->buildFontCss();

        return view("pdf.templates.{$invoice->template}", [
            'invoice' => $invoice->load(['items', 'client', 'company']),
            'fontCss' => $fontCss,
        ])->render();
    }

    private function buildFontCss(): string
    {
        $css = '';
        foreach (self::THAI_FONTS as $family => $filename) {
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

    public function isCacheValid(Invoice $invoice): bool
    {
        if (!$invoice->pdf_hash || !$invoice->pdf_url) {
            return false;
        }

        return hash_equals($this->calculateHash($invoice), $invoice->pdf_hash);
    }

    private function calculateHash(Invoice $invoice): string
    {
        return hash('sha256', $invoice->updated_at->timestamp . $invoice->id);
    }
}
