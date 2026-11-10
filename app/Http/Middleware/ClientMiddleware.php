<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ClientMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        if (!auth()->user()->is_active) {
            auth()->logout();
            return redirect()->route('login')->withErrors([
                'email' => 'Sua conta está pendente de aprovação ou inativa no momento.',
            ]);
        }

        if (auth()->user()->role === 'admin' && !$request->is('admin*')) {
            // Admin can access client portal or admin portal
            return $next($request);
        }

        return $next($request);
    }
}
