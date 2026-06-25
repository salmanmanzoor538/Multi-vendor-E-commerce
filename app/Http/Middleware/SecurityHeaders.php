<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set(
            'Permissions-Policy',
            config(
                'security.permissions_policy',
                'camera=(), microphone=(), geolocation=(), payment=(self)'
            )
        );

        $csp = config('security.csp', []);
        if (($csp['enabled'] ?? true) === true) {
            $headerName = ($csp['report_only'] ?? true) === true
                ? 'Content-Security-Policy-Report-Only'
                : 'Content-Security-Policy';

            $response->headers->set($headerName, $this->buildCspPolicy($csp));
        }

        return $response;
    }

    /**
     * @param  array<string, mixed>  $config
     */
    private function buildCspPolicy(array $config): string
    {
        $directives = $config['directives'] ?? [];
        $parts = [];

        foreach ($directives as $name => $values) {
            if (! is_array($values) || $values === []) {
                continue;
            }

            $parts[] = $name.' '.implode(' ', array_map(static fn ($value): string => (string) $value, $values));
        }

        if (! empty($config['report_uri'])) {
            $parts[] = 'report-uri '.(string) $config['report_uri'];
        }

        return implode('; ', $parts);
    }
}
