<?php

namespace App\Actions\Api\Products;

use App\Classes\BaseAction;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

class DeleteAction extends BaseAction
{
    public function handle(Product $product): JsonResponse
    {
        $product->delete();

        $data['products'] = IndexAction::make()->products();

        return $this->apiResponse('Product Deleted Successfully', $data);
    }
}
