<?php

namespace App\Http\Middleware;

use App\Services\OmiseService;
use Closure;
use Illuminate\Http\Request;

class SubscriptionMiddleware
{
    /**
     * Enforce Free plan invoice limit (5/month).
     * Attach plan info to every authenticated request so Vue can read it.
     */
    public function handle(Request $request, Closure $next, string $feature = '')
    {
        $user = $request->user();
        if (!$user) {
            return $next($request);
        }

        $subscription = $user->subscription;

        // Auto-create free subscription if missing (e.g. legacy users)
        if (!$subscription) {
            $subscription = $user->subscription()->create(['plan' => 'free']);
        }

        // Gate AI autofill to Pro+
        if ($feature === 'ai' && $subscription->plan === 'free') {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'AI autofill requires Pro or Business plan',
                    'upgrade_required' => true,
                ], 403);
            }

            return redirect()->route('settings.billing')->with('error', 'Feature นี้ต้องการแผน Pro หรือ Business');
        }

        return $next($request);
    }
}
