<?php

namespace App\Jobs;

use App\Models\Invoice;
use App\Services\InvoicePdfService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateInvoicePdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Invoice $invoice
    ) {
        $this->onQueue('pdf');
    }

    /**
     * Execute the job.
     */
    public function handle(InvoicePdfService $pdfService): void
    {
        try {
            Log::info("Generating PDF for Invoice: {$this->invoice->invoice_number}");
            $pdfService->generate($this->invoice);
            
            // Broadcast that the PDF is ready
            \App\Events\InvoicePdfReady::dispatch($this->invoice);
            
            Log::info("PDF Generated and broadcasted for Invoice: {$this->invoice->invoice_number}");
        } catch (\Exception $e) {
            Log::error("Failed to generate PDF for Invoice: {$this->invoice->invoice_number}. Error: {$e->getMessage()}");
            throw $e;
        }
    }
}
