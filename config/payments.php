<?php

use App\Enums\PaymentMethodEnum;
use App\Services\Payments\ApplePayService;
use App\Services\Payments\CreditCardService;

return [
    'gateways' => [
        PaymentMethodEnum::CREDIT_CARD->value => CreditCardService::class,
        PaymentMethodEnum::APPLE_PAY->value => ApplePayService::class,
    ],

    'credit_card' => [
        // SOON , ADD DATA FOR CREDIT CARD CREDENTIALS
    ],

    'apple_pay' => [
        // SOON , ADD DATA FOR APPLE PAY CREDENTIALS
    ],
];
