<?php

namespace App\Actions\Api\Orders;

use App\Classes\BaseAction;
use App\Enums\OrderStatusEnum;
use App\Exceptions\WarningException;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;

class ConfirmAction extends BaseAction
{
    public function handle(Order $order): JsonResponse
    {
        OrderService::make()->checkOrderOwnership($order);

        throw_if(
            $order->status === OrderStatusEnum::CONFIRMED,
            new WarningException('Sorry , Order Status Already Confirmed')
        );

        $order->update([
            'status' => OrderStatusEnum::CONFIRMED,
        ]);

        $data['orders'] = IndexAction::make()->orders();

        return $this->apiResponse('Order Confirmed Successfully', $data);
    }
}
