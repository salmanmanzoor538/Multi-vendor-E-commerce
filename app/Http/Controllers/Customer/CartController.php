<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\StoreCartItemRequest;
use App\Http\Requests\Customer\UpdateCartItemRequest;
use App\Models\CartItem;
use App\Services\CartService;
use App\Services\CheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(
        private readonly CartService $cartService,
        private readonly CheckoutService $checkoutService,
    ) {
        $this->authorizeResource(CartItem::class, 'cartItem');
    }

    public function index(): View
    {
        $summary = $this->checkoutService->buildSummary(auth()->user());

        return view('customer.cart.index', [
            'summary' => $summary,
        ]);
    }

    public function store(StoreCartItemRequest $request): RedirectResponse
    {
        $this->cartService->addItem($request->user(), $request->validated());

        return redirect()->route('customer.cart.index')->with('status', 'Item added to cart.');
    }

    public function update(UpdateCartItemRequest $request, CartItem $cartItem): RedirectResponse
    {
        $this->cartService->updateQuantity($request->user(), $cartItem, (int) $request->validated('quantity'));

        return redirect()->route('customer.cart.index')->with('status', 'Cart updated.');
    }

    public function destroy(CartItem $cartItem): RedirectResponse
    {
        $this->cartService->removeManagedItem(auth()->user(), $cartItem);

        return redirect()->route('customer.cart.index')->with('status', 'Item removed from cart.');
    }
}
