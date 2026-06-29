<?php

namespace App\Http\Resources\Api;

use App\Models\OrderProduct;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product' => $this->whenLoaded('product', fn($product) => [
                'id' => $product->id,
                'name' => $product->name,
            ]),
            'quantity' => $this->quantity,
            'price' => $this->price,
            'total' => $this->total,
        ];
    }
}
