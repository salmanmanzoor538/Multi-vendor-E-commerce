<?php

namespace App\Repositories\Contracts;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Notifications\DatabaseNotification;

interface NotificationRepositoryInterface
{
    /**
     * @return Collection<int, DatabaseNotification>
     */
    public function latestForUser(User $user, int $limit): Collection;

    public function unreadCountForUser(User $user): int;

    public function markAsRead(DatabaseNotification $notification): DatabaseNotification;

    public function markAllAsReadForUser(User $user): int;
}

