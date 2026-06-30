<?php

namespace App\Actions\Api\Payments;

use App\Classes\BaseAction;
use App\Enums\PaymentMethodEnum;
use App\Enums\PaymentStatusEnum;
use App\Exceptions\WarningException;
use App\Models\Payment;
use App\Services\OrderService;
use App\Services\Payments\PaymentService;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rules\Enum;
use Lorisleiva\Actions\ActionRequest;

class PayAction extends BaseAction
{
    public function handle(Payment $payment, ActionRequest $request): JsonResponse
    {
        PaymentService::make()->checkPaymentOwnership($payment);

        DB::transaction(function () use ($request, $payment): void {

            $paymentService = PaymentService::make()->process($payment->payable, $request->payment_method);

            throw_if(
                $paymentService->status !== PaymentStatusEnum::SUCCESS,
                new WarningException('Sorry , Payment Status Not Success')
            );

            foreach ($payment->payable->products as $product) {
                OrderService::make()->decrementProductStock($product->product_id, $product->quantity);
            }

        });

        $data['payments'] = IndexAction::make()->payments();

        return $this->apiResponse('Payment Pay Successfully', $data);
    }

    public function rules(): array
    {
        return [
            'payment_method' => ['required', new Enum(PaymentMethodEnum::class)],
        ];
    }
}
