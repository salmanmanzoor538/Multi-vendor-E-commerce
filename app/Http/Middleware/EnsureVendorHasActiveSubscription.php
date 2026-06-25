<?php

namespace App\Http\Middleware;

use App\Services\SubscriptionLimitService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureVendorHasActiveSubscription
{
    public function __construct(private readonly SubscriptionLimitService $subscriptionLimitService)
    {
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(Response::HTTP_UNAUTHORIZED);
        }

        $vendorId = (int) ($user->ownedVendor?->id ?? $user->vendor_id);

        if ($vendorId <= 0 || ! $this->subscriptionLimitService->hasActiveSubscription($vendorId)) {
            abort(Response::HTTP_FORBIDDEN, config('subscription.messages.no_active_subscription'));
        }

        return $next($request);
    }
}
