<?php

namespace Database\Factories;

use App\Enums\PaymentMethodEnum;
use App\Enums\PaymentStatusEnum;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'payable_id' => Order::factory(),
            'payable_type' => Order::class,
            'method' => PaymentMethodEnum::CREDIT_CARD,
            'amount' => fake()->randomFloat(2, 10, 500),
            'status' => PaymentStatusEnum::SUCCESS,
            'paid_at' => now(),
            'transaction_id' => 'TEST-' . fake()->unique()->uuid(),
        ];
    }

    public function failed(): static
    {
        return $this->state(fn() => [
            'status' => PaymentStatusEnum::FAILED,
            'paid_at' => null,
        ]);
    }
}
