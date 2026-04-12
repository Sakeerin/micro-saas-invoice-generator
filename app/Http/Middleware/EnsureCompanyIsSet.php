<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureCompanyIsSet
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && !Auth::user()->companies()->exists()) {
            if (!$request->routeIs('company.wizard') && !$request->routeIs('company.store') && !$request->routeIs('logout')) {
                return redirect()->route('company.wizard');
            }
        }

        return $next($request);
    }
}
