<?php

namespace App\Repositories\Eloquent;

use App\Models\Cart;
use App\Repositories\Contracts\CartRepositoryInterface;

class CartRepository implements CartRepositoryInterface
{
    public function findByUserId(int $userId): ?Cart
    {
        return Cart::query()
            ->with(['items.vendor'])
            ->where('user_id', $userId)
            ->first();
    }

    public function findOrCreateByUserId(int $userId): Cart
    {
        return Cart::query()->firstOrCreate([
            'user_id' => $userId,
        ]);
    }
}
