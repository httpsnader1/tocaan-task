<?php

namespace App\Actions\Api\Payments;

use App\Classes\BaseAction;
use App\Http\Resources\Api\PaymentResource;
use App\Models\Payment;
use App\Services\Payments\PaymentService;
use Illuminate\Http\JsonResponse;

class ShowAction extends BaseAction
{
    public function handle(Payment $payment): JsonResponse
    {
        PaymentService::make()->checkPaymentOwnership($payment);

        $data['payment'] = PaymentResource::make($payment->load('payable'));

        return $this->apiResponse('Payment Show Successfully', $data);
    }
}
