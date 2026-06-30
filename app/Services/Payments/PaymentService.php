<?php

namespace App\Services\Payments;

use App\Enums\OrderStatusEnum;
use App\Enums\PaymentStatusEnum;
use App\Exceptions\WarningException;
use App\Models\Order;
use DB;
use Lorisleiva\Actions\Concerns\AsObject;

class PaymentService
{
    use AsObject;

    public function __construct()
    {
    }

    public function process(Order $order, string $method)
    {
        throw_if(
            $order->status !== OrderStatusEnum::CONFIRMED,
            new WarningException('Sorry , Payments Can Only Be Processed For Confirmed Orders')
        );

        throw_if(
            $order->payment && $order->payment?->status === PaymentStatusEnum::SUCCESS,
            new WarningException('Sorry , Payment Already Paid For This Order')
        );

        return DB::transaction(function () use ($order, $method) {

            $paymentMethod = $this->resolvePaymentMethod($method);
            $paymentResult = $paymentMethod->process($order);
            $order->payment()->create([
                'method' => $method,
                'amount' => $order->total,
                'status' => $paymentResult['status'] ? PaymentStatusEnum::SUCCESS : PaymentStatusEnum::FAILED,
                'paid_at' => $paymentResult['status'] ? now() : NULL,
                'transaction_id' => $paymentResult['transaction_id'],
            ]);

            return $order->payment->fresh();

        });
    }

    public function resolvePaymentMethod(string $method)
    {
        $paymentMethod = config('payments.gateways.' . $method);

        throw_if(
            !$paymentMethod,
            new WarningException('Payment Method ( ' . $method . ' ) Unsupported')
        );

        return app($paymentMethod);
    }

    public function paymentResponse(bool $status, string $transactionID, array $data = []): array
    {
        return [
            'status' => $status,
            'transaction_id' => $transactionID,
            'data' => $data,
        ];
    }
}
