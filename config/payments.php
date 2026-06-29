<?php

return [
    'gateways' => [
        'credit_card' => \App\Services\Payments\CreditCardService::class,
        'apple_pay' => \App\Services\Payments\ApplePayService::class,
    ],

    'credit_card' => [
        // SOON , ADD DATA FOR CREDIT CARD CREDENTIALS
    ],

    'apple_pay' => [
        // SOON , ADD DATA FOR APPLE PAY CREDENTIALS
    ],
];
