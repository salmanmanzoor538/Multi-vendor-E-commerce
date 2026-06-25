<?php

namespace App\Http\Controllers\Customer;

use App\Enums\AddressType;
use App\Enums\PaymentProvider;
use App\Http\Controllers\Controller;
use App\Http\Requests\Checkout\PlaceOrderRequest;
use App\Services\CheckoutService;
use App\Services\OrderPlacementService;
use App\Services\PaymentService;
use App\Services\AddressService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly CheckoutService $checkoutService,
        private readonly AddressService $addressService,
        private readonly OrderPlacementService $orderPlacementService,
        private readonly PaymentService $paymentService,
    ) {
    }

    public function showSummary(Request $request): View
    {
        $addresses = $this->addressService->getForUser($request->user());
        $summary = $this->checkoutService->buildSummary($request->user());

        return view('customer.checkout.summary', [
            'summary' => $summary,
            'shippingAddresses' => $addresses->filter(fn ($address) => $address->type === AddressType::SHIPPING)->values(),
            'billingAddresses' => $addresses->filter(fn ($address) => $address->type === AddressType::BILLING)->values(),
        ]);
    }

    public function place(PlaceOrderRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $user = $request->user();
        $provider = PaymentProvider::from($data['provider']);

        $shippingAddress = $this->addressService->resolveForCheckout($user, (int) $data['shipping_address_id'], AddressType::SHIPPING);
        $billingAddress = $this->addressService->resolveForCheckout($user, (int) $data['billing_address_id'], AddressType::BILLING);

        $placed = $this->orderPlacementService->place($user, $shippingAddress, $billingAddress, $provider);
        $checkoutBatchId = $placed['checkout_batch_id'];

        if ($provider === PaymentProvider::STRIPE) {
            $stripe = $this->paymentService->createStripeCheckout($user, $checkoutBatchId);

            return redirect()->away($stripe['checkout_url']);
        }

        $this->paymentService->createCashOnDelivery($user, $checkoutBatchId);

        return redirect()
            ->route('customer.checkout.success', ['checkout_batch_id' => $checkoutBatchId, 'provider' => PaymentProvider::COD->value])
            ->with('status', 'Order placed with cash on delivery.');
    }

    public function success(Request $request): View
    {
        return view('customer.checkout.success', [
            'checkoutBatchId' => $request->query('checkout_batch_id'),
            'sessionId' => $request->query('session_id'),
            'provider' => $request->query('provider', PaymentProvider::STRIPE->value),
        ]);
    }

    public function cancel(Request $request): View
    {
        return view('customer.checkout.cancel', [
            'checkoutBatchId' => $request->query('checkout_batch_id'),
        ]);
    }
}
