<?php

namespace App\Actions\Api\Products;

use App\Classes\BaseAction;
use App\Http\Requests\Api\ProductRequest;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

class CreateAction extends BaseAction
{
    public function handle(ProductRequest $request): JsonResponse
    {
        Product::create($request->validated());

        $data['products'] = IndexAction::make()->products();

        return $this->apiResponse('Product Created Successfully', $data);
    }
}
