<?php

namespace App\Actions\Api\Products;

use App\Classes\BaseAction;
use App\Http\Resources\Api\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

class ShowAction extends BaseAction
{
    public function handle(Product $product): JsonResponse
    {
        $data['product'] = ProductResource::make($product);

        return $this->apiResponse('Product Show Successfully', $data);
    }
}
