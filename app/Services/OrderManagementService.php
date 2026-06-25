<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Events\OrderStatusUpdated;
use App\Models\Order;
use App\Models\User;
use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Repositories\Contracts\OrderStatusHistoryRepositoryInterface;
use App\Support\PerformanceCache;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderManagementService
{
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly OrderStatusHistoryRepositoryInterface $orderStatusHistoryRepository,
    ) {
    }

    public function paginateForActor(User $actor, int $perPage = 15): LengthAwarePaginator
    {
        return match ($actor->role) {
            UserRole::ADMIN => $this->orderRepository->paginateForAdmin($perPage),
            UserRole::VENDOR => $this->orderRepository->paginateForVendor((int) $actor->vendor_id, $perPage),
            default => $this->orderRepository->paginateForUser((int) $actor->id, $perPage),
        };
    }

    public function findForActor(User $actor, int $orderId): ?Order
    {
        return match ($actor->role) {
            UserRole::ADMIN => $this->orderRepository->findForAdmin($orderId),
            UserRole::VENDOR => $this->orderRepository->findForVendor((int) $actor->vendor_id, $orderId),
            default => $this->orderRepository->findForUser((int) $actor->id, $orderId),
        };
    }

    public function updateStatus(Order $order, OrderStatus $toStatus, User $actor, ?string $note = null): Order
    {
        return $this->performTransition($order, $toStatus, $actor, $note, true);
    }

    public function updateStatusFromSystem(Order $order, OrderStatus $toStatus, ?string $note = null): Order
    {
        return $this->performTransition($order, $toStatus, null, $note, false);
    }

    public function createTimelineEntry(Order $order, OrderStatus $toStatus, ?User $actor = null, ?string $note = null): void
    {
        $this->orderStatusHistoryRepository->create([
            'order_id' => $order->id,
            'from_status' => null,
            'to_status' => $toStatus->value,
            'note' => $note,
            'changed_by' => $actor?->id,
        ]);
    }

    private function performTransition(
        Order $order,
        OrderStatus $toStatus,
        ?User $actor,
        ?string $note,
        bool $throwOnInvalidTransition
    ): Order {
        $fromStatus = $order->status;

        if ($fromStatus === $toStatus) {
            return $order;
        }

        if (! $fromStatus->canTransitionTo($toStatus)) {
            if (! $throwOnInvalidTransition) {
                return $order;
            }

            throw ValidationException::withMessages([
                'status' => sprintf(
                    'Order status transition from "%s" to "%s" is not allowed.',
                    $fromStatus->value,
                    $toStatus->value
                ),
            ]);
        }

        $updatedOrder = DB::transaction(function () use ($order, $toStatus, $note, $actor, $fromStatus): Order {
            $updatedOrder = $this->orderRepository->update($order, ['status' => $toStatus->value]);

            $this->orderStatusHistoryRepository->create([
                'order_id' => $order->id,
                'from_status' => $fromStatus->value,
                'to_status' => $toStatus->value,
                'note' => $note,
                'changed_by' => $actor?->id,
            ]);

            return $updatedOrder;
        });

        PerformanceCache::invalidate((string) config('performance.cache.namespaces.analytics', 'analytics'));

        event(new OrderStatusUpdated(
            $updatedOrder,
            $fromStatus,
            $toStatus,
            $actor,
            $note
        ));

        return $updatedOrder;
    }
}
