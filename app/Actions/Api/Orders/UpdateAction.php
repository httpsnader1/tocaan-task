<?php

namespace App\Actions\Api\Orders;

use App\Classes\BaseAction;
use App\Enums\PaymentStatusEnum;
use App\Exceptions\WarningException;
use App\Http\Requests\Api\OrderRequest;
use App\Models\Order;
use App\Services\OrderService;
use DB;
use Illuminate\Http\JsonResponse;

class UpdateAction extends BaseAction
{
    public function handle(Order $order, OrderRequest $request): JsonResponse
    {
        OrderService::make()->checkOrderOwnership($order);

        throw_if(
            $order->payment?->status === PaymentStatusEnum::SUCCESS,
            new WarningException('Sorry , You Can Not Update Order With Success Payment')
        );

        $products = OrderService::make()->handleProducts($request->products);

        DB::transaction(function () use ($request, $order, $products): void {

            $order->update(
                array_merge(
                    $request->validated(),
                    [
                        'user_id' => auth('api')->id(),
                        'total' => $products['total'],
                    ]
                )
            );

            $productIDs = [];

            foreach ($products['products'] as $product) {
                $productIDs[] = $product['product_id'];
                $order->products()->updateOrCreate([
                    'product_id' => $product['product_id'],
                ], [
                    'quantity' => $product['quantity'],
                    'price' => $product['price'],
                    'total' => $product['total'],
                ]);
            }

            $order->products()->whereNotIn('product_id', $productIDs)->delete();

        });

        $data['orders'] = IndexAction::make()->orders();

        return $this->apiResponse('Order Updated Successfully', $data);
    }
}
