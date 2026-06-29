<?php

namespace App\Services\Payments;

use App\Models\Order;
use Lorisleiva\Actions\Concerns\AsObject;

class CreditCardService
{
    use AsObject;

    public function process(Order $order): array
    {
        // LOGIC OF CREDIT CARD WILL ADD HERE

        return PaymentService::make()->paymentResponse(
            status: true,
            transactionID: 'CREDIT-CARD-' . uniqid('', true),
            data: [
                'orderID' => $order->id,
            ],
        );
    }
}
