<?php

namespace Tests\Feature;

use App\Enums\OrderStatusEnum;
use App\Enums\PaymentStatusEnum;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    private function authenticate(): User
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'api');

        return $user;
    }

    public function test_listing_orders_requires_authentication(): void
    {
        $this->getJson('/api/orders')->assertUnauthorized();
    }

    public function test_user_can_create_an_order_and_total_is_calculated(): void
    {
        $this->authenticate();
        $first = Product::factory()->create(['price' => 100, 'stock' => 10]);
        $second = Product::factory()->create(['price' => 50, 'stock' => 10]);

        $this->postJson('/api/orders', [
            'products' => [
                ['product_id' => $first->id, 'quantity' => 2],
                ['product_id' => $second->id, 'quantity' => 1],
            ],
        ])->assertOk();

        // 100*2 + 50*1 = 250
        $this->assertDatabaseHas('orders', ['total' => 250]);
        $this->assertDatabaseHas('order_products', [
            'product_id' => $first->id,
            'quantity' => 2,
            'total' => 200,
        ]);
    }

    public function test_creating_an_order_validates_the_payload(): void
    {
        $this->authenticate();

        $this->postJson('/api/orders', [
            'products' => [
                ['quantity' => 1],
            ],
        ])->assertStatus(422)->assertJsonValidationErrors('products.0.product_id');
    }

    public function test_order_cannot_be_created_when_quantity_exceeds_stock(): void
    {
        $this->authenticate();
        $product = Product::factory()->create(['stock' => 1]);

        $this->postJson('/api/orders', [
            'products' => [
                ['product_id' => $product->id, 'quantity' => 5],
            ],
        ])->assertStatus(422);
    }

    public function test_orders_can_be_filtered_by_status(): void
    {
        $user = $this->authenticate();
        Order::factory()->for($user)->create(['status' => OrderStatusEnum::PENDING]);
        Order::factory()->for($user)->create(['status' => OrderStatusEnum::CONFIRMED]);

        $this->getJson('/api/orders?filterStatus=confirmed')
            ->assertOk()
            ->assertJsonPath('data.orders.pagination.total', 1);
    }

    public function test_order_list_is_paginated(): void
    {
        $this->authenticate();

        $this->getJson('/api/orders')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'orders' => [
                        'data',
                        'pagination' => ['current_page', 'per_page', 'total'],
                    ],
                ],
            ]);
    }

    public function test_listing_only_returns_the_authenticated_users_orders(): void
    {
        $user = $this->authenticate();
        $other = User::factory()->create();
        Order::factory()->for($user)->create();
        Order::factory()->count(2)->for($other)->create();

        $this->getJson('/api/orders')
            ->assertOk()
            ->assertJsonPath('data.orders.pagination.total', 1);
    }

    public function test_user_cannot_view_another_users_order(): void
    {
        $this->authenticate();
        $other = User::factory()->create();
        $order = Order::factory()->for($other)->create();

        $this->getJson("/api/orders/{$order->id}")->assertForbidden();
    }

    public function test_user_cannot_delete_another_users_order(): void
    {
        $this->authenticate();
        $other = User::factory()->create();
        $order = Order::factory()->for($other)->create();

        $this->deleteJson("/api/orders/{$order->id}")->assertForbidden();

        $this->assertModelExists($order);
    }

    public function test_user_cannot_update_another_users_order(): void
    {
        $this->authenticate();
        $other = User::factory()->create();
        $order = Order::factory()->for($other)->create();
        $product = Product::factory()->create(['stock' => 10]);

        $this->patchJson("/api/orders/{$order->id}", [
            'products' => [
                ['product_id' => $product->id, 'quantity' => 1],
            ],
        ])->assertForbidden();
    }

    public function test_user_cannot_pay_another_users_order(): void
    {
        $this->authenticate();
        $other = User::factory()->create();
        $order = Order::factory()->for($other)->confirmed()->create();

        $this->postJson("/api/orders/{$order->id}/pay", [
            'payment_method' => 'credit_card',
        ])->assertForbidden();
    }

    public function test_user_can_update_an_order(): void
    {
        $user = $this->authenticate();
        $order = Order::factory()->for($user)->create(['total' => 100]);
        $product = Product::factory()->create(['price' => 30, 'stock' => 10]);

        $this->patchJson("/api/orders/{$order->id}", [
            'products' => [
                ['product_id' => $product->id, 'quantity' => 2],
            ],
        ])->assertOk();

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'total' => 60]);
    }

    public function test_user_can_delete_an_order_without_payments(): void
    {
        $user = $this->authenticate();
        $order = Order::factory()->for($user)->create();

        $this->deleteJson("/api/orders/{$order->id}")->assertOk();

        $this->assertModelMissing($order);
    }

    public function test_order_with_a_successful_payment_cannot_be_deleted(): void
    {
        $user = $this->authenticate();
        $order = Order::factory()->for($user)->confirmed()->create();
        Payment::factory()->create([
            'payable_id' => $order->id,
            'payable_type' => Order::class,
            'status' => PaymentStatusEnum::SUCCESS,
        ]);

        $this->deleteJson("/api/orders/{$order->id}")->assertStatus(422);

        $this->assertModelExists($order);
    }

    public function test_order_with_a_successful_payment_cannot_be_updated(): void
    {
        $user = $this->authenticate();
        $order = Order::factory()->for($user)->confirmed()->create();
        $product = Product::factory()->create(['stock' => 10]);
        Payment::factory()->create([
            'payable_id' => $order->id,
            'payable_type' => Order::class,
            'status' => PaymentStatusEnum::SUCCESS,
        ]);

        $this->patchJson("/api/orders/{$order->id}", [
            'products' => [
                ['product_id' => $product->id, 'quantity' => 1],
            ],
        ])->assertStatus(422);
    }
}
