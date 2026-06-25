<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderManagementService;
use App\Services\ReviewService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(
        private readonly OrderManagementService $orderManagementService,
        private readonly ReviewService $reviewService,
    ) {
        $this->authorizeResource(Order::class, 'order');
    }

    public function index(Request $request): View
    {
        $orders = $this->orderManagementService->paginateForActor($request->user(), 15);

        return view('customer.orders.index', ['orders' => $orders]);
    }

    public function show(Request $request, Order $order): View
    {
        $order = $this->orderManagementService->findForActor($request->user(), $order->id) ?? $order;
        $reviewsByOrderItem = $this->reviewService->reviewsForUserOrderItems($request->user(), $order->items);

        return view('customer.orders.show', [
            'order' => $order,
            'reviewsByOrderItem' => $reviewsByOrderItem,
        ]);
    }
}
