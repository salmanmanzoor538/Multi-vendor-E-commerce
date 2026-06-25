<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureApprovedVendor
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, \Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->hasApprovedVendorAccess()) {
            abort(Response::HTTP_FORBIDDEN, 'Your vendor account is not approved yet.');
        }

        return $next($request);
    }
}
