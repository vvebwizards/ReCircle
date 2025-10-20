<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectBasedOnRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // If user is authenticated and trying to access the default dashboard
        if (Auth::check() && $request->routeIs('dashboard')) {
            // Redirect based on role
            if (Auth::user()->role === UserRole::MAKER) {
                return redirect()->route('maker.dashboard');
            } elseif (Auth::user()->role === UserRole::ADMIN) {
                return redirect()->route('admin.dashboard');
            } elseif (Auth::user()->role === UserRole::BUYER) {
                return redirect()->route('buyer.dashboard');
            } elseif (Auth::user()->role === UserRole::COURIER) {
                return redirect()->route('courier.dashboard');
            }
            // Default users continue to the standard dashboard
        }

        return $next($request);
    }
}
