<?php

namespace App\Services;

use App\Exceptions\WarningException;
use App\Models\Order;
use App\Models\Product;
use Lorisleiva\Actions\Concerns\AsObject;

class OrderService
{
    use AsObject;

    public function handleProducts($requestProducts): array
    {
        $products = collect();

        foreach ($requestProducts as $product) {
            $productDB = Product::query()->find($product['product_id']);
            $this->checkProductValidity($productDB, $product);
            $products[] = [
                'product_id' => (int)$product['product_id'],
                'quantity' => (int)$product['quantity'],
                'price' => (float)$productDB->price,
                'total' => (float)$productDB->price * $product['quantity'],
            ];
        }

        return [
            'total' => $products->sum('total'),
            'products' => $products,
        ];
    }

    public function checkProductValidity($productDB, $product): void
    {
        throw_if(
            !$productDB,
            new WarningException('Product ( ' . $product['product_id'] . ' ) Not Found')
        );

        throw_if(
            !$productDB->stock,
            new WarningException('Product ( ' . $product['product_id'] . ' ) Stock Is Empty')
        );

        throw_if(
            $product['quantity'] > $productDB->stock,
            new WarningException('Product ( ' . $product['product_id'] . ' ) Quantity Is Greater Than Stock')
        );
    }

    public function decrementProductStock($productID, $quantity): void
    {
        Product::query()
            ->find($productID)
            ->decrement('stock', $quantity);
    }

    public function incrementProductStock($productID, $quantity): void
    {
        Product::query()
            ->find($productID)
            ->increment('stock', $quantity);
    }

    public function checkOrderOwnership(Order $order): void
    {
        throw_if(
            $order->user_id !== auth('api')->id(),
            new WarningException('Sorry , You Are Not Allowed To Access This Order', [], 403)
        );
    }
}
