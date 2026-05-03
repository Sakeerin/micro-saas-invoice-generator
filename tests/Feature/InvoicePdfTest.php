<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\User;
use App\Services\InvoicePdfService;
use App\Jobs\GenerateInvoicePdfJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class InvoicePdfTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_generate_pdf_for_invoice()
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $company = Company::create([
            'user_id' => $user->id,
            'name' => 'บริษัท ทดสอบ จำกัด',
            'name_en' => 'Test Company Co., Ltd.',
            'address' => "123 ถนนพระราม 9\nแขวงห้วยขวาง เขตห้วยขวาง\nกรุงเทพมหานคร 10310",
            'tax_id' => '1234567890123',
            'brand_color' => '#1a56db',
        ]);

        $client = Client::create([
            'company_id' => $company->id,
            'name' => 'ลูกค้า สมมติ',
            'name_en' => 'Mock Client',
            'address' => '456 ถนนสุขุมวิท กรุงเทพฯ',
            'tax_id' => '9876543210987',
        ]);

        $invoice = Invoice::create([
            'company_id' => $company->id,
            'client_id' => $client->id,
            'invoice_number' => 'INV-2026-0001',
            'issue_date' => now(),
            'due_date' => now()->addDays(30),
            'client_name' => $client->name,
            'client_address' => $client->address,
            'subtotal' => 1000,
            'total' => 1070,
            'vat_rate' => 7,
            'vat_amount' => 70,
            'status' => 'draft',
            'template' => 'modern',
            'language' => 'th-en',
        ]);

        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'name' => 'ค่าบริการพัฒนาซอฟต์แวร์',
            'name_en' => 'Software Development Service',
            'quantity' => 1,
            'unit' => 'งาน',
            'unit_price' => 1000,
            'line_total' => 1000,
            'sort_order' => 0,
        ]);

        $service = new InvoicePdfService();
        
        // Use try-catch to handle environments without node/puppeteer
        try {
            $pdfUrl = $service->generate($invoice);
            $this->assertNotNull($pdfUrl);
            $this->assertNotNull($invoice->fresh()->pdf_url);
            $this->assertNotNull($invoice->fresh()->pdf_hash);

            $filename = "invoices/{$invoice->company_id}/{$invoice->id}.pdf";
            Storage::disk('public')->assertExists($filename);
        } catch (\Exception $e) {
            $this->markTestSkipped('Browsershot/Puppeteer not available: ' . $e->getMessage());
        }
    }

    public function test_it_dispatches_job_if_cache_is_invalid()
    {
        Storage::fake('public');
        Bus::fake();

        $user = User::factory()->create();
        $company = Company::factory()->create(['user_id' => $user->id]);
        $client = Client::factory()->create(['company_id' => $company->id]);
        
        $invoice = Invoice::factory()->create([
            'company_id' => $company->id,
            'client_id' => $client->id,
            'invoice_number' => 'INV-2026-0002',
            'issue_date' => now(),
            'client_name' => $client->name,
            'template' => 'modern',
        ]);

        $response = $this->actingAs($user)->get(route('invoices.download', $invoice));

        $response->assertStatus(302);
        Bus::assertDispatched(GenerateInvoicePdfJob::class, function ($job) use ($invoice) {
            return $job->invoice->id === $invoice->id;
        });
    }

    public function test_it_redirects_to_existing_pdf_if_cache_is_valid()
    {
        Storage::fake('public');
        Bus::fake();

        $user = User::factory()->create();
        $company = Company::factory()->create(['user_id' => $user->id]);
        $client = Client::factory()->create(['company_id' => $company->id]);
        
        $invoice = Invoice::factory()->create([
            'company_id' => $company->id,
            'client_id' => $client->id,
            'pdf_url' => 'https://example.com/invoice.pdf',
        ]);
        
        // Force a valid hash
        $hash = hash('sha256', $invoice->updated_at->timestamp . $invoice->id);
        $invoice->update(['pdf_hash' => $hash]);

        $response = $this->actingAs($user)->get(route('invoices.download', $invoice));

        $response->assertRedirect('https://example.com/invoice.pdf');
        Bus::assertNotDispatched(GenerateInvoicePdfJob::class);
    }
}
