<?php

namespace App\Models;

use App\Enums\PaymentMethodEnum;
use App\Enums\PaymentStatusEnum;
use App\Traits\BaseModelTrait;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable([
    'method',
    'amount',
    'status',
    'paid_at',
    'transaction_id',
])]
class Payment extends Model
{
    use BaseModelTrait;

    protected function casts(): array
    {
        return [
            'method' => PaymentMethodEnum::class,
            'amount' => 'float',
            'status' => PaymentStatusEnum::class,
            'paid_at' => 'datetime',
        ];
    }

    public function payable(): MorphTo
    {
        return $this->morphTo();
    }
}
