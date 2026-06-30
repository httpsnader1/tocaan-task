<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'transactionID' => $this->transaction_id,
            'method' => $this->method,
            'amount' => $this->amount,
            'status' => [
                'id' => $this->status,
                'text' => $this->status->text(),
                'color' => $this->status->color(),
            ],
            'paidAt' => $this->paid_at_text,
            'payable' => $this->whenLoaded('payable', fn($payable) => [
                'id' => $payable->id,
                'total' => $payable->total,
            ]),
        ];
    }
}
