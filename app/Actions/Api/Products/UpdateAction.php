<?php

namespace App\Actions\Api\Products;

use App\Classes\BaseAction;
use App\Http\Requests\Api\ProductRequest;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

class UpdateAction extends BaseAction
{
    public function handle(Product $product, ProductRequest $request): JsonResponse
    {
        $product->update($request->validated());

        $data['products'] = IndexAction::make()->products();

        return $this->apiResponse('Product Updated Successfully', $data);
    }
}
