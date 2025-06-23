<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RefreshCsrfToken
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Add fresh CSRF token to response headers for AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            $response->headers->set('X-CSRF-TOKEN', csrf_token());
        }

        // For Inertia responses, ensure fresh token is available
        if ($request->header('X-Inertia')) {
            $response->headers->set('X-CSRF-TOKEN', csrf_token());
        }

        return $response;
    }
} 