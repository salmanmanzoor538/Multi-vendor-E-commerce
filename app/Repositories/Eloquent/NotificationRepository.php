<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Repositories\Contracts\NotificationRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Notifications\DatabaseNotification;

class NotificationRepository implements NotificationRepositoryInterface
{
    /**
     * @return Collection<int, DatabaseNotification>
     */
    public function latestForUser(User $user, int $limit): Collection
    {
        return $this->baseQueryForUser($user)
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function unreadCountForUser(User $user): int
    {
        return $this->baseQueryForUser($user)
            ->whereNull('read_at')
            ->count();
    }

    public function markAsRead(DatabaseNotification $notification): DatabaseNotification
    {
        if ($notification->read_at === null) {
            $notification->forceFill(['read_at' => now()])->save();
        }

        return $notification->refresh();
    }

    public function markAllAsReadForUser(User $user): int
    {
        return $this->baseQueryForUser($user)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    private function baseQueryForUser(User $user)
    {
        return DatabaseNotification::query()
            ->where('notifiable_type', User::class)
            ->where('notifiable_id', $user->id);
    }
}

