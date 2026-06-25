<?php

namespace App\Http\Controllers\Vendor;

use App\Enums\BillingCycle;
use App\Http\Controllers\Controller;
use App\Http\Requests\Vendor\RenewSubscriptionRequest;
use App\Http\Requests\Vendor\StoreSubscriptionRequest;
use App\Models\Subscription;
use App\Services\PlanService;
use App\Services\SubscriptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
    public function __construct(
        private readonly SubscriptionService $subscriptionService,
        private readonly PlanService $planService,
    ) {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Subscription::class);

        $vendorId = (int) ($request->user()->ownedVendor?->id ?? $request->user()->vendor_id);

        return view('vendor.subscription.index', [
            'activeSubscription' => $this->subscriptionService->getActiveForVendor($vendorId),
            'plans' => $this->planService->getActivePlans(),
            'billingCycles' => BillingCycle::cases(),
        ]);
    }

    public function store(StoreSubscriptionRequest $request): RedirectResponse
    {
        $this->authorize('create', Subscription::class);

        $vendorId = (int) ($request->user()->ownedVendor?->id ?? $request->user()->vendor_id);

        $validated = $request->validated();

        $this->subscriptionService->subscribe(
            $vendorId,
            (int) $validated['plan_id'],
            BillingCycle::from($validated['billing_cycle']),
        );

        return redirect()
            ->route('vendor.subscription.index')
            ->with('status', 'Subscription activated successfully.');
    }

    public function renew(RenewSubscriptionRequest $request): RedirectResponse
    {
        $this->authorize('renew', Subscription::class);

        $vendorId = (int) ($request->user()->ownedVendor?->id ?? $request->user()->vendor_id);
        $validated = $request->validated();

        $this->subscriptionService->renew($vendorId, BillingCycle::from($validated['billing_cycle']));

        return redirect()
            ->route('vendor.subscription.index')
            ->with('status', 'Subscription renewed successfully.');
    }

    public function cancel(Request $request): RedirectResponse
    {
        $this->authorize('cancel', Subscription::class);

        $vendorId = (int) ($request->user()->ownedVendor?->id ?? $request->user()->vendor_id);

        $this->subscriptionService->cancel($vendorId);

        return redirect()
            ->route('vendor.subscription.index')
            ->with('status', 'Subscription cancelled successfully.');
    }
}
