<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\User;
use App\Mail\InvoiceMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\TestCase;

class EmailDeliveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_send_invoice_by_email()
    {
        Mail::fake();

        $user = User::factory()->create();
        $company = Company::create([
            'user_id' => $user->id,
            'name' => 'Test Company',
        ]);

        $client = Client::create([
            'company_id' => $company->id,
            'name' => 'Client Name',
            'contact_email' => 'client@example.com',
        ]);

        $invoice = Invoice::create([
            'company_id' => $company->id,
            'client_id' => $client->id,
            'invoice_number' => 'INV-001',
            'client_name' => 'Client Name',
            'issue_date' => now(),
            'total' => 1000,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($user)
            ->withSession(['_token' => 'testtoken'])
            ->post(route('invoices.send', $invoice), ['_token' => 'testtoken']);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        Mail::assertQueued(InvoiceMail::class, function ($mail) use ($invoice) {
            return $mail->invoice->id === $invoice->id &&
                   $mail->hasTo('client@example.com');
        });

        $invoice->refresh();
        $this->assertEquals('sent', $invoice->status);
        $this->assertNotNull($invoice->share_token);
        $this->assertNotNull($invoice->email_sent_at);
    }

    public function test_it_tracks_email_opening_via_pixel()
    {
        $user = User::factory()->create();
        $company = Company::factory()->create(['user_id' => $user->id]);
        $invoice = Invoice::factory()->create([
            'company_id' => $company->id,
            'share_token' => Str::random(64),
            'email_sent_at' => now(),
        ]);

        $response = $this->get(route('tracking.pixel', $invoice->share_token));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'image/gif');

        $invoice->refresh();
        $this->assertNotNull($invoice->email_opened_at);
    }

    public function test_it_updates_status_to_viewed_when_public_viewed()
    {
        $user = User::factory()->create();
        $company = Company::factory()->create(['user_id' => $user->id]);
        $invoice = Invoice::factory()->create([
            'company_id' => $company->id,
            'share_token' => Str::random(64),
            'status' => 'sent',
        ]);

        $response = $this->get(route('invoices.show_public', $invoice->share_token));

        $response->assertStatus(200);

        $invoice->refresh();
        $this->assertEquals('viewed', $invoice->status);
        $this->assertNotNull($invoice->first_viewed_at);
        $this->assertEquals(1, $invoice->view_count);
    }
}
