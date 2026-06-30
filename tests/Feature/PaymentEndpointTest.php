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

class PaymentEndpointTest extends TestCase
{
    use RefreshDatabase;

    private function authenticate(): User
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'api');

        return $user;
    }

    private function paymentForUser(User $user, ?callable $state = null): Payment
    {
        $order = Order::factory()->for($user)->create();

        $factory = Payment::factory();

        return ($state ? $state($factory) : $factory)->create([
            'payable_id' => $order->id,
            'payable_type' => Order::class,
        ]);
    }

    public function test_listing_payments_requires_authentication(): void
    {
        $this->getJson('/api/payments')->assertUnauthorized();
    }

    public function test_listing_only_returns_the_authenticated_users_payments(): void
    {
        $user = $this->authenticate();
        $this->paymentForUser($user);
        $this->paymentForUser(User::factory()->create());

        $this->getJson('/api/payments')
            ->assertOk()
            ->assertJsonPath('data.payments.pagination.total', 1);
    }

    public function test_payments_can_be_filtered_by_status(): void
    {
        $user = $this->authenticate();
        $this->paymentForUser($user); // success (factory default)
        $this->paymentForUser($user, fn($f) => $f->failed());

        $this->getJson('/api/payments?filterStatus=failed')
            ->assertOk()
            ->assertJsonPath('data.payments.pagination.total', 1);
    }

    public function test_user_can_view_their_own_payment(): void
    {
        $user = $this->authenticate();
        $payment = $this->paymentForUser($user);

        $this->getJson("/api/payments/{$payment->id}")->assertOk();
    }

    public function test_user_cannot_view_another_users_payment(): void
    {
        $this->authenticate();
        $payment = $this->paymentForUser(User::factory()->create());

        $this->getJson("/api/payments/{$payment->id}")->assertForbidden();
    }

    public function test_user_can_retry_a_failed_payment_via_the_payment_endpoint(): void
    {
        $user = $this->authenticate();
        $product = Product::factory()->create(['price' => 100, 'stock' => 10]);
        $order = Order::factory()->for($user)->confirmed()->create(['total' => 200]);
        OrderProduct::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'price' => 100,
            'total' => 200,
        ]);
        $payment = Payment::factory()->failed()->create([
            'payable_id' => $order->id,
            'payable_type' => Order::class,
        ]);

        $this->postJson("/api/payments/{$payment->id}/pay", [
            'payment_method' => 'credit_card',
        ])->assertOk();

        // Same payment row, now successful; stock decremented.
        $this->assertSame(1, Payment::query()->where('payable_id', $order->id)->count());
        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => PaymentStatusEnum::SUCCESS->value,
        ]);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 8]);
    }

    public function test_user_cannot_pay_another_users_payment(): void
    {
        $this->authenticate();
        $payment = $this->paymentForUser(User::factory()->create(), fn($f) => $f->failed());

        $this->postJson("/api/payments/{$payment->id}/pay", [
            'payment_method' => 'credit_card',
        ])->assertForbidden();
    }
}
