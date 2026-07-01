<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        $subscription = $user?->subscription;

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user,
            ],
            'subscription' => $subscription ? [
                'plan'                     => $subscription->plan,
                'invoice_count_this_month' => $subscription->invoice_count_this_month,
                'current_period_end'       => $subscription->current_period_end?->toISOString(),
            ] : ['plan' => 'free', 'invoice_count_this_month' => 0, 'current_period_end' => null],
            'flash' => [
                'success'          => $request->session()->get('success'),
                'error'            => $request->session()->get('error'),
                'share_link'       => $request->session()->get('share_link'),
                'upgrade_required' => $request->session()->get('upgrade_required'),
            ],
        ];
    }
}
