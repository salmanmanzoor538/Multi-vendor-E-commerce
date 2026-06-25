<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CspReportController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $payload = $request->input('csp-report', $request->all());

        Log::warning('CSP violation report received.', [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'payload' => $payload,
        ]);

        return response()->json(['received' => true]);
    }
}
