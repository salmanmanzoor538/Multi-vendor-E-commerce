<?php

namespace App\Listeners;

use App\Services\CartService;
use Illuminate\Auth\Events\Login;

class MergeGuestCartOnLogin
{
    public function __construct(
        private readonly CartService $cartService,
    ) {
    }

    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        $this->cartService->mergeSessionIntoUserCart($event->user);
    }
}
