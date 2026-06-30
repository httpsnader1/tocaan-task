<?php

use App\Enums\PaymentMethodEnum;
use App\Services\Payments\ApplePayService;
use App\Services\Payments\CreditCardService;

return [
    /*
    |--------------------------------------------------------------------------
    | Payment Gateways
    |--------------------------------------------------------------------------
    |
    | Each supported payment method is mapped to the gateway class that handles
    | it. Adding a new gateway is just a new entry here (see the README).
    |
    */
    'gateways' => [
        PaymentMethodEnum::CREDIT_CARD->value => CreditCardService::class,
        PaymentMethodEnum::APPLE_PAY->value => ApplePayService::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Gateway Credentials
    |--------------------------------------------------------------------------
    |
    | Each gateway has its own credentials block, pulled from the environment so
    | secrets never live in source control. The keys below are placeholders that
    | document WHERE the real provider's API keys / secrets go — fill the matching
    | values in `.env` once the actual gateway provider is contracted. A gateway
    | reads its block with: config('payments.credit_card').
    |
    */
    'credit_card' => [
        'api_key' => env('CREDIT_CARD_API_KEY'),
        'secret' => env('CREDIT_CARD_SECRET'),
    ],

    'apple_pay' => [
        'merchant_id' => env('APPLE_PAY_MERCHANT_ID'),
        'secret' => env('APPLE_PAY_SECRET'),
    ],
];
