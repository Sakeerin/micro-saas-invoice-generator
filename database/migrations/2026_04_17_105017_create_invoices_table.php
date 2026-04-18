<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('company_id');
            $table->uuid('client_id')->nullable();
            $table->string('invoice_number');
            $table->string('reference')->nullable();
            $table->string('template')->default('modern');
            $table->string('language')->default('th-en');
            $table->char('currency', 3)->default('THB');
            $table->decimal('exchange_rate', 15, 6)->default(1.0);

            // Client info snapshot
            $table->text('client_name');
            $table->text('client_name_en')->nullable();
            $table->text('client_address')->nullable();
            $table->text('client_address_en')->nullable();
            $table->string('client_tax_id')->nullable();

            // Dates
            $table->date('issue_date');
            $table->date('due_date')->nullable();

            // Amounts
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->enum('discount_type', ['none', 'percent', 'amount'])->default('none');
            $table->decimal('discount_value', 15, 4)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('subtotal_after_discount', 15, 2)->default(0);
            $table->decimal('vat_rate', 5, 2)->default(7.00);
            $table->decimal('vat_amount', 15, 2)->default(0);
            $table->decimal('wht_rate', 5, 2)->default(0);
            $table->decimal('wht_amount', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);

            // Status
            $table->enum('status', ['draft', 'sent', 'viewed', 'paid', 'overdue', 'cancelled'])->default('draft');
            $table->text('notes')->nullable();
            $table->text('payment_terms')->nullable();

            // PDF
            $table->text('pdf_url')->nullable();
            $table->char('pdf_hash', 64)->nullable();

            // Share & Tracking
            $table->char('share_token', 64)->unique()->nullable();
            $table->timestamp('share_expires_at')->nullable();
            $table->timestamp('first_viewed_at')->nullable();
            $table->timestamp('last_viewed_at')->nullable();
            $table->integer('view_count')->default(0);
            $table->timestamp('email_sent_at')->nullable();
            $table->timestamp('email_opened_at')->nullable();

            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'invoice_number']);
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
