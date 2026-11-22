<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Phase 16 security hardening — stricter headers / CSP where practical.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        if (! (bool) config('sme.security.headers_enabled', true)) {
            return $response;
        }

        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
        $response->headers->set('X-XSS-Protection', '0');

        $csp = (string) config('sme.security.csp', '');
        if ($csp !== '') {
            $header = (bool) config('sme.security.csp_report_only', false)
                ? 'Content-Security-Policy-Report-Only'
                : 'Content-Security-Policy';
            $response->headers->set($header, $csp);
        }

        return $response;
    }
}
