<?php

namespace App\Actions\Api\Orders;

use App\Classes\BaseAction;
use App\Http\Resources\Api\OrderResource;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class IndexAction extends BaseAction
{
    public function handle(): JsonResponse
    {
        $data['orders'] = $this->orders();

        return $this->apiResponse('Get Orders Successfully', $data);
    }

    public function orders(): AnonymousResourceCollection
    {
        return OrderResource::collection(
            Order::query()
                ->filters()
                ->whereUserId(auth('api')->id())
                ->with('user', 'products.product', 'payment')
                ->withCount('products')
                ->latest()
                ->paginate(10)
        );
    }
}
