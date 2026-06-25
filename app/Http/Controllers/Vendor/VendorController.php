<?php

namespace App\Http\Controllers\Vendor;

use App\Enums\VendorStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Vendor\StoreVendorApplicationRequest;
use App\Http\Requests\Vendor\UpdateVendorStoreRequest;
use App\Services\VendorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class VendorController extends Controller
{
    public function __construct(private readonly VendorService $vendorService)
    {
    }

    public function create(): View|RedirectResponse
    {
        $user = Auth::user();

        if ($user?->hasApprovedVendorAccess()) {
            return redirect()->route('vendor.dashboard');
        }

        $vendor = $user ? $this->vendorService->getVendorForOwner($user) : null;

        return view('vendor.register', [
            'vendor' => $vendor,
            'statusLabels' => config('vendor.status_labels'),
        ]);
    }

    public function store(StoreVendorApplicationRequest $request): RedirectResponse
    {
        $user = $request->user();

        $existingVendor = $user ? $this->vendorService->getVendorForOwner($user) : null;

        if ($existingVendor) {
            return redirect()->route('vendor.register.create')
                ->with('status', 'You already submitted a vendor application.');
        }

        $vendor = $this->vendorService->createApplication($request->validated(), $user);

        if (! $user) {
            Auth::login($vendor->owner);
        }

        $message = $vendor->status === VendorStatus::PENDING
            ? 'Your vendor application is submitted and pending approval.'
            : 'Vendor account created successfully.';

        return redirect()->route('vendor.register.create')->with('status', $message);
    }

    public function edit(): View
    {
        $vendor = auth()->user()->ownedVendor;

        abort_if(! $vendor, 404);

        $this->authorize('update', $vendor);

        return view('vendor.store.edit', [
            'vendor' => $vendor,
        ]);
    }

    public function update(UpdateVendorStoreRequest $request): RedirectResponse
    {
        $vendor = $request->user()->ownedVendor;

        abort_if(! $vendor, 404);

        $this->authorize('update', $vendor);

        $this->vendorService->updateStore($vendor, $request->validated());

        return redirect()->route('vendor.store.edit')->with('status', 'Store profile updated successfully.');
    }
}
