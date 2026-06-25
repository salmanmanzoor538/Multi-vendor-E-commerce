<?php

namespace App\Enums;

enum OrderStatus: string
{
    case PENDING = 'pending';
    case CONFIRMED = 'confirmed';
    case PROCESSING = 'processing';
    case SHIPPED = 'shipped';
    case DELIVERED = 'delivered';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::CONFIRMED => 'Confirmed',
            self::PROCESSING => 'Processing',
            self::SHIPPED => 'Shipped',
            self::DELIVERED => 'Delivered',
            self::CANCELLED => 'Cancelled',
        };
    }

    public function canTransitionTo(self $next): bool
    {
        if ($this === $next) {
            return true;
        }

        return match ($this) {
            self::PENDING => in_array($next, [self::CONFIRMED, self::CANCELLED], true),
            self::CONFIRMED => in_array($next, [self::PROCESSING, self::CANCELLED], true),
            self::PROCESSING => in_array($next, [self::SHIPPED, self::CANCELLED], true),
            self::SHIPPED => $next === self::DELIVERED,
            self::DELIVERED, self::CANCELLED => false,
        };
    }
}
