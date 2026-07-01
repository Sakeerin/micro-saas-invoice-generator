<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Services\OmiseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function omise(Request $request, OmiseService $omise)
    {
        $payload   = $request->getContent();
        $signature = $request->header('Omise-Signature', '');

        if (!$omise->verifyWebhookSignature($payload, $signature)) {
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        $event = $request->json()->all();
        $eventKey = $event['key'] ?? '';

        Log::info('Omise webhook', ['key' => $eventKey]);

        match ($eventKey) {
            'customer.subscription.renewed'  => $this->handleRenewed($event),
            'customer.subscription.expiring' => $this->handleExpiring($event),
            default => null,
        };

        return response()->json(['received' => true]);
    }

    private function handleRenewed(array $event): void
    {
        $data = $event['data'] ?? [];
        $omiseCustomerId = $data['customer'] ?? null;

        if (!$omiseCustomerId) {
            return;
        }

        $subscription = Subscription::where('omise_customer_id', $omiseCustomerId)->first();
        if (!$subscription) {
            return;
        }

        $subscription->update([
            'current_period_end'        => now()->addMonth(),
            'invoice_count_this_month'  => 0,
        ]);
    }

    private function handleExpiring(array $event): void
    {
        $data = $event['data'] ?? [];
        $omiseCustomerId = $data['customer'] ?? null;

        if (!$omiseCustomerId) {
            return;
        }

        $subscription = Subscription::where('omise_customer_id', $omiseCustomerId)->first();
        if ($subscription) {
            Log::info('Subscription expiring for customer', ['customer_id' => $omiseCustomerId]);
        }
    }
}
