<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        \Illuminate\Support\Carbon::macro('thaiDate', function () {
            return $this->locale('th')->isoFormat('D MMMM BBBB');
        });

        // Public share links: 100 req/min per IP
        RateLimiter::for('share-public', function (Request $request) {
            return Limit::perMinute(100)->by($request->ip());
        });

        // PDF generation: 5 req/min per authenticated user (or IP for guests)
        RateLimiter::for('pdf-generate', function (Request $request) {
            return Limit::perMinute(5)->by($request->user()?->id ?: $request->ip());
        });
    }
}
