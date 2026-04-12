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
        Schema::create('companies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('name');
            $table->text('name_en')->nullable();
            $table->text('address')->nullable();
            $table->text('address_en')->nullable();
            $table->char('tax_id', 13)->nullable();
            $table->text('phone')->nullable();
            $table->text('email')->nullable();
            $table->text('logo_url')->nullable();
            $table->char('brand_color', 7)->default('#1a56db');
            $table->text('bank_name')->nullable();
            $table->text('bank_account')->nullable();
            $table->text('bank_account_name')->nullable();
            $table->text('invoice_prefix')->default('INV');
            $table->integer('invoice_next_number')->default(1);
            $table->decimal('default_vat_rate', 5, 2)->default(7.00);
            $table->char('default_currency', 3)->default('THB');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
