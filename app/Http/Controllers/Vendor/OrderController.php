<?php

namespace App\Http\Controllers\Vendor;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Order\UpdateOrderStatusRequest;
use App\Models\Order;
use App\Services\OrderManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(private readonly OrderManagementService $orderManagementService)
    {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Order::class);

        return view('vendor.orders.index', [
            'orders' => $this->orderManagementService->paginateForActor($request->user(), 15),
        ]);
    }

    public function show(Request $request, Order $order): View
    {
        $this->authorize('view', $order);

        $resolvedOrder = $this->orderManagementService->findForActor($request->user(), $order->id) ?? $order;

        return view('vendor.orders.show', [
            'order' => $resolvedOrder,
            'statusOptions' => OrderStatus::cases(),
        ]);
    }

    public function updateStatus(UpdateOrderStatusRequest $request, Order $order): RedirectResponse
    {
        $this->authorize('updateStatus', $order);

        $validated = $request->validated();
        $status = OrderStatus::from((string) $validated['status']);

        $this->orderManagementService->updateStatus(
            $order,
            $status,
            $request->user(),
            $validated['note'] ?? null
        );

        return redirect()
            ->route('vendor.orders.show', $order)
            ->with('status', 'Order status updated successfully.');
    }
}
