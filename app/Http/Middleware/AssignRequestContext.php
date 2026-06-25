<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class AssignRequestContext
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $requestId = trim((string) $request->header('X-Request-Id', ''));

        if ($requestId === '' || strlen($requestId) > 100) {
            $requestId = (string) Str::uuid();
        }

        $request->attributes->set('request_id', $requestId);

        Log::withContext([
            'request_id' => $requestId,
            'request_path' => $request->path(),
            'request_method' => $request->method(),
        ]);

        $response = $next($request);
        $response->headers->set('X-Request-Id', $requestId);

        return $response;
    }
}
