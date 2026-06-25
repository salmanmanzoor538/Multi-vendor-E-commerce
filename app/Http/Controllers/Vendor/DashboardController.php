<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Services\OrderManagementService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private readonly OrderManagementService $orderManagementService)
    {
    }

    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): View
    {
        return view('vendor.dashboard', [
            'recentOrders' => $this->orderManagementService->paginateForActor($request->user(), 5),
        ]);
    }
}
