<?php

namespace App\Actions\Api\Payments;

use App\Classes\BaseAction;
use App\Http\Resources\Api\PaymentResource;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class IndexAction extends BaseAction
{
    public function handle(): JsonResponse
    {
        $data['payments'] = $this->payments();

        return $this->apiResponse('Get Payments Successfully', $data);
    }

    public function payments(): AnonymousResourceCollection
    {
        return PaymentResource::collection(
            Payment::query()
                ->filters()
                ->whereRelation('payable', 'user_id', auth('api')->id())
                ->with('payable')
                ->latest()
                ->paginate(10)
        );
    }
}
