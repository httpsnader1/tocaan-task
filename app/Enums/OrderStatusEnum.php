<?php

namespace App\Enums;

enum OrderStatusEnum: string
{
    case PENDING = 'pending';
    case CONFIRMED = 'confirmed';
    case CANCELLED = 'cancelled';

    public function text(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::CONFIRMED => 'Confirmed',
            self::CANCELLED => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => '#2563eb',
            self::CONFIRMED => '#16a34a',
            self::CANCELLED => '#dc2626',
        };
    }
}
