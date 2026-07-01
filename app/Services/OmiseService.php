<?php

namespace App\Services;

use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Facades\Http;

class OmiseService
{
    private string $secretKey;
    private string $publicKey;

    public const PLANS = [
        'free'     => ['price' => 0,   'label' => 'Free',     'invoice_limit' => 5],
        'pro'      => ['price' => 199, 'label' => 'Pro',      'invoice_limit' => null],
        'business' => ['price' => 499, 'label' => 'Business', 'invoice_limit' => null],
    ];

    // Omise plan IDs configured in your Omise dashboard
    public const OMISE_PLAN_IDS = [
        'pro'      => 'pro_monthly',
        'business' => 'business_monthly',
    ];

    public function __construct()
    {
        $this->secretKey = config('services.omise.secret_key', '');
        $this->publicKey = config('services.omise.public_key', '');
    }

    public function createCustomer(User $user, string $cardToken): array
    {
        $response = Http::withBasicAuth($this->secretKey, '')
            ->post('https://api.omise.co/customers', [
                'email'       => $user->email,
                'description' => $user->name,
                'card'        => $cardToken,
            ]);

        if ($response->failed()) {
            throw new \RuntimeException('Omise customer creation failed: ' . $response->body());
        }

        return $response->json();
    }

    public function createSubscription(string $customerId, string $plan): array
    {
        $planId = self::OMISE_PLAN_IDS[$plan] ?? null;
        if (!$planId) {
            throw new \InvalidArgumentException("Unknown plan: {$plan}");
        }

        $response = Http::withBasicAuth($this->secretKey, '')
            ->post('https://api.omise.co/customers/' . $customerId . '/schedules', [
                'every'  => 1,
                'period' => 'month',
                'on'     => ['days_of_month' => [date('j')]],
                'end_date' => date('Y-m-d', strtotime('+10 years')),
                'charge' => [
                    'customer'    => $customerId,
                    'amount'      => self::PLANS[$plan]['price'] * 100,
                    'currency'    => 'thb',
                    'description' => 'Invoice App ' . ucfirst($plan) . ' Plan',
                ],
            ]);

        if ($response->failed()) {
            throw new \RuntimeException('Omise subscription creation failed: ' . $response->body());
        }

        return $response->json();
    }

    public function cancelSubscription(string $subscriptionId): array
    {
        $response = Http::withBasicAuth($this->secretKey, '')
            ->delete('https://api.omise.co/schedules/' . $subscriptionId);

        if ($response->failed()) {
            throw new \RuntimeException('Omise subscription cancellation failed: ' . $response->body());
        }

        return $response->json();
    }

    public function upgradeUser(User $user, string $newPlan, string $cardToken): void
    {
        $subscription = $user->subscription;

        // Create or retrieve Omise customer
        if (!$subscription->omise_customer_id) {
            $customer = $this->createCustomer($user, $cardToken);
            $customerId = $customer['id'];
        } else {
            $customerId = $subscription->omise_customer_id;
            // Add new card to existing customer
            Http::withBasicAuth($this->secretKey, '')
                ->patch('https://api.omise.co/customers/' . $customerId, ['card' => $cardToken]);
        }

        // Cancel existing subscription if any
        if ($subscription->omise_subscription_id) {
            try {
                $this->cancelSubscription($subscription->omise_subscription_id);
            } catch (\Exception) {
                // Proceed even if cancel fails (already cancelled)
            }
        }

        // Create new subscription
        $omiseSub = $this->createSubscription($customerId, $newPlan);

        $subscription->update([
            'plan'                    => $newPlan,
            'omise_customer_id'       => $customerId,
            'omise_subscription_id'   => $omiseSub['id'],
            'current_period_end'      => now()->addMonth(),
        ]);
    }

    public function downgradeToFree(User $user): void
    {
        $subscription = $user->subscription;

        if ($subscription->omise_subscription_id) {
            try {
                $this->cancelSubscription($subscription->omise_subscription_id);
            } catch (\Exception) {
                // Proceed anyway
            }
        }

        $subscription->update([
            'plan'                  => 'free',
            'omise_subscription_id' => null,
        ]);
    }

    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        $secret = config('services.omise.webhook_secret', '');
        if (!$secret) {
            return true; // Skip verification in dev if not configured
        }

        return hash_equals(
            hash_hmac('sha256', $payload, $secret),
            $signature
        );
    }
}
