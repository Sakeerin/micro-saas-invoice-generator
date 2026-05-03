<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Invoice>
 */
class InvoiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'client_id' => Client::factory(),
            'invoice_number' => 'INV-' . date('Y') . '-' . $this->faker->numerify('####'),
            'issue_date' => now(),
            'client_name' => $this->faker->name(),
            'client_address' => $this->faker->address(),
            'subtotal' => 1000,
            'total' => 1070,
            'vat_rate' => 7,
            'vat_amount' => 70,
            'status' => 'draft',
            'template' => 'modern',
            'language' => 'th-en',
            'currency' => 'THB',
        ];
    }
}
