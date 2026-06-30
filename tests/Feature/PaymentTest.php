<?php

namespace Tests\Feature;

use App\Enums\PaymentStatusEnum;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    private function authenticate(): User
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'api');

        return $user;
    }

    private function confirmedOrderWith(User $user, Product $product, int $quantity = 1): Order
    {
        $order = Order::factory()->for($user)->confirmed()->create([
            'total' => $product->price * $quantity,
        ]);

        OrderProduct::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => $quantity,
            'price' => $product->price,
            'total' => $product->price * $quantity,
        ]);

        return $order;
    }

    public function test_payment_succeeds_for_a_confirmed_order(): void
    {
        $user = $this->authenticate();
        $product = Product::factory()->create(['price' => 100, 'stock' => 10]);
        $order = $this->confirmedOrderWith($user, $product, 2);

        $this->postJson("/api/orders/{$order->id}/pay", [
            'payment_method' => 'credit_card',
        ])->assertOk();

        $this->assertDatabaseHas('payments', [
            'payable_id' => $order->id,
            'payable_type' => Order::class,
            'method' => 'credit_card',
            'status' => PaymentStatusEnum::SUCCESS->value,
        ]);
    }

    public function test_successful_payment_decrements_product_stock(): void
    {
        $user = $this->authenticate();
        $product = Product::factory()->create(['price' => 100, 'stock' => 10]);
        $order = $this->confirmedOrderWith($user, $product, 3);

        $this->postJson("/api/orders/{$order->id}/pay", [
            'payment_method' => 'credit_card',
        ])->assertOk();

        // 10 - 3 = 7
        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 7]);
    }

    public function test_payment_is_blocked_for_a_non_confirmed_order(): void
    {
        $user = $this->authenticate();
        $product = Product::factory()->create(['price' => 100, 'stock' => 10]);
        // Pending order (factory default status).
        $order = Order::factory()->for($user)->create();
        OrderProduct::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => $product->price,
            'total' => $product->price,
        ]);

        $this->postJson("/api/orders/{$order->id}/pay", [
            'payment_method' => 'credit_card',
        ])->assertStatus(422);

        $this->assertDatabaseMissing('payments', ['payable_id' => $order->id]);
    }

    public function test_payment_with_an_unsupported_method_fails(): void
    {
        $user = $this->authenticate();
        $product = Product::factory()->create(['price' => 100, 'stock' => 10]);
        $order = $this->confirmedOrderWith($user, $product);

        $this->postJson("/api/orders/{$order->id}/pay", [
            'payment_method' => 'bitcoin',
        ])->assertStatus(422);

        $this->assertDatabaseMissing('payments', ['payable_id' => $order->id]);
    }

    public function test_an_order_cannot_be_paid_twice(): void
    {
        $user = $this->authenticate();
        $product = Product::factory()->create(['price' => 100, 'stock' => 10]);
        $order = $this->confirmedOrderWith($user, $product);
        Payment::factory()->create([
            'payable_id' => $order->id,
            'payable_type' => Order::class,
            'status' => PaymentStatusEnum::SUCCESS,
        ]);

        $this->postJson("/api/orders/{$order->id}/pay", [
            'payment_method' => 'credit_card',
        ])->assertStatus(422);
    }

    public function test_apple_pay_gateway_processes_a_payment(): void
    {
        $user = $this->authenticate();
        $product = Product::factory()->create(['price' => 100, 'stock' => 10]);
        $order = $this->confirmedOrderWith($user, $product);

        $this->postJson("/api/orders/{$order->id}/pay", [
            'payment_method' => 'apple_pay',
        ])->assertOk();

        $payment = Payment::query()->where('payable_id', $order->id)->first();
        $this->assertNotNull($payment);
        $this->assertStringStartsWith('APPLE-PAY-', $payment->transaction_id);
    }

    public function test_paying_after_a_failed_attempt_updates_the_same_payment(): void
    {
        $user = $this->authenticate();
        $product = Product::factory()->create(['price' => 100, 'stock' => 10]);
        $order = $this->confirmedOrderWith($user, $product);

        // A previous, non-successful attempt already left a payment row.
        Payment::factory()->failed()->create([
            'payable_id' => $order->id,
            'payable_type' => Order::class,
        ]);

        $this->postJson("/api/orders/{$order->id}/pay", [
            'payment_method' => 'credit_card',
        ])->assertOk();

        // Still exactly one payment for the order, now marked successful.
        $this->assertSame(1, Payment::query()->where('payable_id', $order->id)->count());
        $this->assertDatabaseHas('payments', [
            'payable_id' => $order->id,
            'status' => PaymentStatusEnum::SUCCESS->value,
        ]);
    }
}
