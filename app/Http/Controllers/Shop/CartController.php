<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cart\StoreCartItemRequest;
use App\Http\Requests\Cart\UpdateCartItemQuantityRequest;
use App\Models\Cart;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(
        private readonly CartService $cartService,
    ) {
    }

    public function index(Request $request): View
    {
        if ($request->user()) {
            $this->authorize('manage', Cart::class);
        }

        $cart = $this->cartService->getCart($request->user());

        return view('cart.index', [
            'cart' => $cart->toArray(),
        ]);
    }

    public function summary(Request $request): JsonResponse
    {
        if ($request->user()) {
            $this->authorize('manage', Cart::class);
        }

        $cart = $this->cartService->getCart($request->user());

        return response()->json([
            'cart' => $cart->toArray(),
        ]);
    }

    public function store(StoreCartItemRequest $request): JsonResponse
    {
        if ($request->user()) {
            $this->authorize('manage', Cart::class);
        }

        $cart = $this->cartService->addItem($request->user(), $request->validated());

        return response()->json([
            'message' => 'Item added to cart.',
            'cart' => $cart->toArray(),
        ]);
    }

    public function update(UpdateCartItemQuantityRequest $request, string $lineKey): JsonResponse
    {
        if ($request->user()) {
            $this->authorize('manage', Cart::class);
        }

        $cart = $this->cartService->updateItemQuantity($request->user(), $lineKey, (int) $request->validated('quantity'));

        return response()->json([
            'message' => 'Cart item updated.',
            'cart' => $cart->toArray(),
        ]);
    }

    public function destroy(Request $request, string $lineKey): JsonResponse
    {
        if ($request->user()) {
            $this->authorize('manage', Cart::class);
        }

        $cart = $this->cartService->removeItem($request->user(), $lineKey);

        return response()->json([
            'message' => 'Item removed from cart.',
            'cart' => $cart->toArray(),
        ]);
    }
}
