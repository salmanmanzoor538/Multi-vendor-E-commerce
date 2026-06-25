<?php

namespace App\Listeners;

use App\Events\OrderStatusUpdated;
use App\Services\OrderNotificationService;

class DispatchOrderStatusNotifications
{
    public function __construct(private readonly OrderNotificationService $orderNotificationService)
    {
    }

    public function handle(OrderStatusUpdated $event): void
    {
        $this->orderNotificationService->notifyOrderStatusUpdated(
            $event->order,
            $event->fromStatus,
            $event->toStatus,
            $event->changedBy,
            $event->note,
        );
    }
}

