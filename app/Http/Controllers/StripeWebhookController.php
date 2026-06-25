<?php

namespace App\Http\Controllers;

use App\Services\PaymentService;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StripeWebhookController extends Controller
{
    public function __construct(
        private readonly PaymentService $paymentService,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        try {
            $this->paymentService->handleStripeWebhook(
                $request->getContent(),
                (string) $request->header('Stripe-Signature', '')
            );
        } catch (HttpException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], $exception->getStatusCode());
        }

        return response()->json(['received' => true]);
    }
}
