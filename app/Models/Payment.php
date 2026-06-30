<?php

namespace App\Models;

use App\Enums\PaymentMethodEnum;
use App\Enums\PaymentStatusEnum;
use App\Models\Builders\PaymentBuilder;
use App\Traits\BaseModelTrait;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @method static PaymentBuilder query()
 */
#[Fillable([
    'method',
    'amount',
    'status',
    'paid_at',
    'transaction_id',
])]
class Payment extends Model
{
    use HasFactory, BaseModelTrait;

    protected function casts(): array
    {
        return [
            'method' => PaymentMethodEnum::class,
            'amount' => 'float',
            'status' => PaymentStatusEnum::class,
            'paid_at' => 'datetime',
        ];
    }

    public function newEloquentBuilder($query): PaymentBuilder
    {
        return new PaymentBuilder($query);
    }

    public function payable(): MorphTo
    {
        return $this->morphTo();
    }
}
