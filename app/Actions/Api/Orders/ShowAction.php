<?php

namespace App\Actions\Api\Orders;

use App\Classes\BaseAction;
use App\Http\Resources\Api\OrderResource;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;

class ShowAction extends BaseAction
{
    public function handle(Order $order): JsonResponse
    {
        OrderService::make()->checkOrderOwnership($order);

        $data['order'] = OrderResource::make($order->load('products.product', 'user')->loadCount('products'));

        return $this->apiResponse('Order Show Successfully', $data);
    }
}
