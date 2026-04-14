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
        Schema::create('products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('company_id');
            $table->text('name');
            $table->text('name_en')->nullable();
            $table->text('description')->nullable();
            $table->text('unit')->default('งาน');
            $table->decimal('unit_price', 15, 4);
            $table->char('currency', 3)->default('THB');
            $table->decimal('default_wht_rate', 5, 2)->default(3.00);
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
