<?php

namespace App\Enums;

enum PaymentMethodEnum: string
{
    case APPLE_PAY = 'apple_pay';
    case CREDIT_CARD = 'credit_card';

    public function text(): string
    {
        return match ($this) {
            self::APPLE_PAY => 'Apple Pay',
            self::CREDIT_CARD => 'Credit Card',
        };
    }
}
