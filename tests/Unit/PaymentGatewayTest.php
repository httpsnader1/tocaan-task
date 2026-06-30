<?php

namespace Tests\Unit;

use App\Exceptions\WarningException;
use App\Models\Order;
use App\Services\Payments\ApplePayService;
use App\Services\Payments\CreditCardService;
use App\Services\Payments\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentGatewayTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_resolves_a_configured_gateway_from_the_method(): void
    {
        $gateway = PaymentService::make()->resolvePaymentMethod('credit_card');

        $this->assertInstanceOf(CreditCardService::class, $gateway);
    }

    public function test_it_resolves_the_apple_pay_gateway(): void
    {
        $gateway = PaymentService::make()->resolvePaymentMethod('apple_pay');

        $this->assertInstanceOf(ApplePayService::class, $gateway);
    }

    public function test_resolving_an_unsupported_gateway_throws(): void
    {
        $this->expectException(WarningException::class);

        PaymentService::make()->resolvePaymentMethod('bitcoin');
    }

    public function test_credit_card_gateway_returns_a_successful_response(): void
    {
        $order = Order::factory()->create();

        $result = CreditCardService::make()->process($order);

        $this->assertTrue($result['status']);
        $this->assertStringStartsWith('CREDIT-CARD-', $result['transaction_id']);
        $this->assertSame($order->id, $result['data']['orderID']);
    }

    public function test_apple_pay_gateway_returns_a_successful_response(): void
    {
        $order = Order::factory()->create();

        $result = ApplePayService::make()->process($order);

        $this->assertTrue($result['status']);
        $this->assertStringStartsWith('APPLE-PAY-', $result['transaction_id']);
    }
}
