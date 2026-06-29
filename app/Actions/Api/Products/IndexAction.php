<?php

namespace App\Actions\Api\Products;

use App\Classes\BaseAction;
use App\Http\Resources\Api\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class IndexAction extends BaseAction
{
    public function handle(): JsonResponse
    {
        $data['products'] = $this->products();

        return $this->apiResponse('Get Products Successfully', $data);
    }

    public function products(): AnonymousResourceCollection
    {
        return ProductResource::collection(
            Product::query()
                ->latest()
                ->paginate(10)
        );
    }
}
