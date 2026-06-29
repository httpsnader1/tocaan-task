<?php

namespace App\Actions\Api\Orders;

use App\Classes\BaseAction;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\ActionRequest;

class StockAction extends BaseAction
{
    public function handle(Product $product, ActionRequest $request): JsonResponse
    {
        $product->increment('stock', $request->stock);

        $data['products'] = IndexAction::make()->products();

        return $this->apiResponse('Product Stock Updated Successfully', $data);
    }

    public function rules(): array
    {
        return [
            'stock' => ['required', 'integer', 'min:1'],
        ];
    }
}
