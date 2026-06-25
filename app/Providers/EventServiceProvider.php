<?php

namespace App\Providers;

use App\Events\VendorApplied;
use App\Events\VendorApproved;
use App\Events\VendorRejected;
use App\Events\ReviewSubmitted;
use App\Events\ReviewModerated;
use App\Events\VendorRepliedToReview;
use App\Events\OrdersPlaced;
use App\Events\OrderStatusUpdated;
use App\Events\PaymentConfirmed;
use App\Events\ProductVariantsSynced;
use App\Events\SubscriptionActivated;
use App\Events\SubscriptionCancelled;
use App\Events\SubscriptionExpired;
use App\Events\SubscriptionRenewed;
use App\Listeners\LogOrderActivity;
use App\Listeners\DispatchOrderStatusNotifications;
use App\Listeners\LogProductVariantActivity;
use App\Listeners\LogReviewActivity;
use App\Listeners\LogSubscriptionActivity;
use App\Listeners\LogVendorActivity;
use App\Listeners\MergeGuestCartOnLogin;
use Illuminate\Auth\Events\Login;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        VendorApplied::class => [
            LogVendorActivity::class,
        ],
        VendorApproved::class => [
            LogVendorActivity::class,
        ],
        VendorRejected::class => [
            LogVendorActivity::class,
        ],
        ProductVariantsSynced::class => [
            LogProductVariantActivity::class,
        ],
        SubscriptionActivated::class => [
            LogSubscriptionActivity::class,
        ],
        SubscriptionRenewed::class => [
            LogSubscriptionActivity::class,
        ],
        SubscriptionCancelled::class => [
            LogSubscriptionActivity::class,
        ],
        SubscriptionExpired::class => [
            LogSubscriptionActivity::class,
        ],
        OrdersPlaced::class => [
            LogOrderActivity::class,
        ],
        OrderStatusUpdated::class => [
            LogOrderActivity::class,
            DispatchOrderStatusNotifications::class,
        ],
        PaymentConfirmed::class => [
            LogOrderActivity::class,
        ],
        ReviewSubmitted::class => [
            LogReviewActivity::class,
        ],
        ReviewModerated::class => [
            LogReviewActivity::class,
        ],
        VendorRepliedToReview::class => [
            LogReviewActivity::class,
        ],
        Login::class => [
            MergeGuestCartOnLogin::class,
        ],
    ];
}
