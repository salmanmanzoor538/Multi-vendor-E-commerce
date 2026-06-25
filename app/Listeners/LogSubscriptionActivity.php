<?php

namespace App\Listeners;

use App\Events\SubscriptionActivated;
use App\Events\SubscriptionCancelled;
use App\Events\SubscriptionExpired;
use App\Events\SubscriptionRenewed;
use Illuminate\Support\Facades\Log;

class LogSubscriptionActivity
{
    public function handle(SubscriptionActivated|SubscriptionRenewed|SubscriptionCancelled|SubscriptionExpired $event): void
    {
        $subscription = $event->subscription;

        Log::info('Subscription lifecycle event', [
            'event' => class_basename($event),
            'subscription_id' => $subscription->id,
            'vendor_id' => $subscription->vendor_id,
            'plan_id' => $subscription->plan_id,
            'status' => $subscription->status->value,
            'billing_cycle' => $subscription->billing_cycle->value,
            'starts_at' => $subscription->starts_at?->toDateTimeString(),
            'ends_at' => $subscription->ends_at?->toDateTimeString(),
        ]);
    }
}
