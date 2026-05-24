<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use Illuminate\Console\Command;

class CheckOverdueInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoices:check-overdue';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Check for overdue invoices and update their status';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $overdueInvoices = Invoice::where('status', '!=', 'paid')
            ->where('status', '!=', 'cancelled')
            ->where('status', '!=', 'overdue')
            ->whereNotNull('due_date')
            ->where('due_date', '<', now()->startOfDay())
            ->get();

        $count = 0;
        foreach ($overdueInvoices as $invoice) {
            $invoice->update(['status' => 'overdue']);
            
            $invoice->activities()->create([
                'type' => 'overdue',
                'meta' => ['auto_checked' => true],
            ]);
            
            $count++;
        }

        $this->info("Updated {$count} invoices to overdue status.");
    }
}
