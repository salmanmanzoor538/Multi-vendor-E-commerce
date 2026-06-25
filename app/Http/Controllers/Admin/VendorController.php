<?php

namespace App\Http\Controllers\Admin;

use App\Enums\VendorStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminVendorApprovalRequest;
use App\Models\Vendor;
use App\Services\VendorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VendorController extends Controller
{
    public function __construct(private readonly VendorService $vendorService)
    {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Vendor::class);

        $status = $request->string('status')->toString();

        if (! in_array($status, array_map(fn (VendorStatus $item) => $item->value, VendorStatus::cases()), true)) {
            $status = null;
        }

        return view('admin.vendors.index', [
            'vendors' => $this->vendorService->getAdminVendors($status),
            'activeStatus' => $status,
            'statusLabels' => config('vendor.status_labels'),
        ]);
    }

    public function show(Vendor $vendor): View
    {
        $this->authorize('view', $vendor);

        return view('admin.vendors.show', [
            'vendor' => $vendor->load(['owner', 'approvedBy']),
            'statusLabels' => config('vendor.status_labels'),
        ]);
    }

    public function approve(AdminVendorApprovalRequest $request, Vendor $vendor): RedirectResponse
    {
        $this->authorize('approve', $vendor);

        $this->vendorService->approve($vendor, $request->user());

        return redirect()->route('admin.vendors.show', $vendor)
            ->with('status', 'Vendor approved successfully.');
    }

    public function reject(AdminVendorApprovalRequest $request, Vendor $vendor): RedirectResponse
    {
        $this->authorize('reject', $vendor);

        $validated = $request->validated();

        $this->vendorService->reject($vendor, $request->user(), $validated['rejection_reason'] ?? null);

        return redirect()->route('admin.vendors.show', $vendor)
            ->with('status', 'Vendor rejected successfully.');
    }
}
