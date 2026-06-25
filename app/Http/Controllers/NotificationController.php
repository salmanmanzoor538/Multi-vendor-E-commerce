<?php

namespace App\Http\Controllers;

use App\Http\Requests\Notification\FetchNotificationsRequest;
use App\Http\Requests\Notification\MarkAllNotificationsReadRequest;
use App\Http\Requests\Notification\MarkNotificationReadRequest;
use App\Repositories\Contracts\NotificationRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    public function __construct(private readonly NotificationRepositoryInterface $notificationRepository)
    {
    }

    public function index(FetchNotificationsRequest $request): JsonResponse
    {
        $this->authorize('viewAny', DatabaseNotification::class);

        $limit = (int) ($request->validated('limit') ?? config('notifications.order_updates.feed_limit', 10));
        $limit = max(1, min($limit, 50));

        $notifications = $this->notificationRepository->latestForUser($request->user(), $limit);
        $unreadCount = $this->notificationRepository->unreadCountForUser($request->user());

        return response()->json([
            'unread_count' => $unreadCount,
            'notifications' => $notifications->map(fn (DatabaseNotification $notification): array => [
                'id' => $notification->id,
                'type' => $notification->type,
                'data' => $notification->data,
                'created_at' => $notification->created_at?->toIso8601String(),
                'read_at' => $notification->read_at?->toIso8601String(),
            ])->values(),
        ]);
    }

    public function markAsRead(MarkNotificationReadRequest $request, DatabaseNotification $notification): JsonResponse
    {
        $this->authorize('update', $notification);

        $notification = $this->notificationRepository->markAsRead($notification);
        $unreadCount = $this->notificationRepository->unreadCountForUser($request->user());

        return response()->json([
            'message' => 'Notification marked as read.',
            'notification_id' => $notification->id,
            'read_at' => $notification->read_at?->toIso8601String(),
            'unread_count' => $unreadCount,
        ]);
    }

    public function markAllAsRead(MarkAllNotificationsReadRequest $request): JsonResponse
    {
        $this->authorize('viewAny', DatabaseNotification::class);

        $updatedCount = $this->notificationRepository->markAllAsReadForUser($request->user());

        return response()->json([
            'message' => 'All notifications marked as read.',
            'updated_count' => $updatedCount,
            'unread_count' => 0,
        ]);
    }
}
