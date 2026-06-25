<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;
use App\Notifications\OrderStatusUpdatedNotification;
use Illuminate\Support\Collection;

class OrderNotificationService
{
    public function notifyOrderStatusUpdated(
        Order $order,
        OrderStatus $fromStatus,
        OrderStatus $toStatus,
        ?User $changedBy = null,
        ?string $note = null,
    ): void {
        $recipients = $this->resolveRecipients($order);
        if ($recipients->isEmpty()) {
            return;
        }

        $notification = new OrderStatusUpdatedNotification(
            orderId: (int) $order->id,
            orderNumber: (string) $order->order_number,
            fromStatus: $fromStatus,
            toStatus: $toStatus,
            changedById: $changedBy?->id,
            changedByName: $changedBy?->name,
            note: $note,
            occurredAtIso: now()->toIso8601String(),
        );

        $recipients->each(fn (User $user) => $user->notify($notification));
    }

    /**
     * @return Collection<int, User>
     */
    private function resolveRecipients(Order $order): Collection
    {
        $customer = User::query()->find($order->user_id);
        $vendorUsers = $order->vendor_id === null
            ? collect()
            : User::query()
                ->where('vendor_id', $order->vendor_id)
                ->get();

        return collect([$customer])
            ->filter()
            ->merge($vendorUsers)
            ->unique('id')
            ->values();
    }
}
