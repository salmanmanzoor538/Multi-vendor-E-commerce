<?php

namespace App\Repositories\Contracts;

use App\Models\Cart;

interface CartRepositoryInterface
{
    public function findByUserId(int $userId): ?Cart;

    public function findOrCreateByUserId(int $userId): Cart;
}
