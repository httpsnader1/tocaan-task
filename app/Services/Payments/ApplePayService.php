<?php

namespace App\Services\Payments;

use App\Models\Order;
use Lorisleiva\Actions\Concerns\AsObject;

class ApplePayService
{
    use AsObject;

    public function process(Order $order): array
    {
        // LOGIC OF APPLE PAY WILL ADD HERE

        return PaymentService::make()->paymentResponse(
            status: true,
            transactionID: 'APPLE-PAY-' . uniqid('', true),
            data: [
                'orderID' => $order->id,
            ],
        );
    }
}
