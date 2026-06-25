<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Notifications\DatabaseNotification;

class NotificationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->exists;
    }

    public function view(User $user, DatabaseNotification $notification): bool
    {
        return $this->belongsToUser($user, $notification);
    }

    public function update(User $user, DatabaseNotification $notification): bool
    {
        return $this->belongsToUser($user, $notification);
    }

    private function belongsToUser(User $user, DatabaseNotification $notification): bool
    {
        return $notification->notifiable_type === User::class
            && (int) $notification->notifiable_id === (int) $user->id;
    }
}

