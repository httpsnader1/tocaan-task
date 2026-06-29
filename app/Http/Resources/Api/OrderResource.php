<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user' => $this->whenLoaded('user', fn($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ]),
            'total' => $this->total,
            'productsCount' => $this->products_count,
            'products' => $this->whenLoaded('products', fn($products) => OrderProductResource::collection($products)),
            'status' => [
                'id' => $this->status,
                'text' => $this->status->text(),
                'color' => $this->status->color(),
            ],
            'payment' => $this->whenLoaded('payment', fn($payment) => PaymentResource::make($payment)),
            'createdAt' => $this->created_at_text,
            'updatedAt' => $this->updated_at_text,
        ];
    }
}
