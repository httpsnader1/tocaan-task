<?php

namespace App\Enums;

enum PaymentStatusEnum: string
{
    case PENDING = 'pending';
    case SUCCESS = 'success';
    case FAILED = 'failed';

    public function text(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::SUCCESS => 'Success',
            self::FAILED => 'Failed',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => '#2563eb',
            self::SUCCESS => '#16a34a',
            self::FAILED => '#dc2626',
        };
    }
}
