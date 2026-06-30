<?php

namespace App\Actions\Api\Orders;

use App\Classes\BaseAction;
use App\Enums\PaymentStatusEnum;
use App\Exceptions\WarningException;
use App\Models\Order;
use Illuminate\Http\JsonResponse;

class DeleteAction extends BaseAction
{
    public function handle(Order $order): JsonResponse
    {
        throw_if(
            $order->payment?->status === PaymentStatusEnum::SUCCESS,
            new WarningException('Sorry , You Can Not Delete Order With Success Payment')
        );

        $order->delete();

        $data['orders'] = IndexAction::make()->orders();

        return $this->apiResponse('Order Deleted Successfully', $data);
    }
}
