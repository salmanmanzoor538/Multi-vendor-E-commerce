<?php

namespace App\Models;

use App\Enums\AddressType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'type',
    'full_name',
    'phone',
    'line_1',
    'line_2',
    'city',
    'state',
    'postal_code',
    'country_code',
    'is_default',
])]
class CustomerAddress extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => AddressType::class,
            'is_default' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
