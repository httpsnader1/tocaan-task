<?php

namespace App\Actions\Api\Orders;

use App\Classes\BaseAction;
use App\Enums\PaymentStatusEnum;
use App\Models\Order;
use App\Services\OrderService;
use App\Services\Payments\PaymentService;
use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\ActionRequest;

class PayAction extends BaseAction
{
    public function handle(Order $order, ActionRequest $request): JsonResponse
    {
        PaymentService::make()->process($order, $request->payment_method);

        if ($order->payment->status === PaymentStatusEnum::SUCCESS) {
            foreach ($order->products as $product) {
                OrderService::make()->decrementProductStock($product->product_id, $product->quantity);
            }
        }

        $data['orders'] = IndexAction::make()->orders();

        return $this->apiResponse('Order Pay Successfully', $data);
    }

    public function rules(): array
    {
        return [
            'payment_method' => ['required'],
        ];
    }
}
