<?php

namespace App\Actions\Api\Orders;

use App\Classes\BaseAction;
use App\Enums\PaymentStatusEnum;
use App\Http\Requests\Api\OrderRequest;
use App\Models\Order;
use App\Services\OrderService;
use DB;
use Illuminate\Http\JsonResponse;

class CreateAction extends BaseAction
{
    public function handle(OrderRequest $request): JsonResponse
    {
        $products = OrderService::make()->handleProducts($request->products);

        DB::transaction(function () use ($request, $products): void {

            $order = Order::create(
                array_merge(
                    $request->validated(),
                    [
                        'user_id' => auth('api')->id(),
                        'total' => $products['total'],
                    ]
                )
            );

            foreach ($products['products'] as $product) {
                $order->products()->create($product);
            }

        });

        $data['orders'] = IndexAction::make()->orders();

        return $this->apiResponse('Order Created Successfully', $data);
    }
}
