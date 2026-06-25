<?php

namespace App\Http\Controllers;

use App\Http\Requests\Payment\MarkCodPaidRequest;
use App\Repositories\Contracts\PaymentRepositoryInterface;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;

class PaymentController extends Controller
{
    public function __construct(
        private readonly PaymentRepositoryInterface $paymentRepository,
        private readonly PaymentService $paymentService,
    ) {
    }

    public function settleCod(MarkCodPaidRequest $request, string $checkoutBatchId): JsonResponse
    {
        $payment = $this->paymentRepository->findByCheckoutBatchId($checkoutBatchId);
        abort_if($payment === null, 404, 'Payment not found.');

        $this->authorize('settleCod', $payment);

        $payment = $this->paymentService->markCodAsPaid($request->user(), $checkoutBatchId);

        return response()->json([
            'message' => 'Cash on delivery payment marked as paid.',
            'checkout_batch_id' => $checkoutBatchId,
            'payment_status' => $payment->status->value,
        ]);
    }
}
