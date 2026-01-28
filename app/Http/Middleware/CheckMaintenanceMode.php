<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\SystemControlService;
use Illuminate\Support\Facades\Auth;

class CheckMaintenanceMode
{
    public function __construct(protected SystemControlService $systemService) {}

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // If system is locked, strictly control access
        if ($this->systemService->isSystemLocked()) {
            
            // 1. Always allow home page and login
            if ($request->is('/') || $request->is('login') || $request->is('logout')) {
                return $next($request);
            }

            // 2. Allow Admins to access anything
            if (Auth::check() && Auth::user()->role === 'Admin') {
                return $next($request);
            }

            // 3. Prevent infinite loops for the maintenance page itself
            if ($request->is('under-maintenance')) {
                return $next($request);
            }

            // 4. Redirect all unauthorized requests
            return redirect()->route('maintenance.under');
        }

        return $next($request);
    }
}
