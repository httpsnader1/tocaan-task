<?php

namespace App\Models;

use App\Enums\OrderStatusEnum;
use App\Models\Builders\OrderBuilder;
use App\Traits\BaseModelTrait;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * @method static OrderBuilder query()
 */
#[Fillable([
    'user_id',
    'total',
    'status',
])]
class Order extends Model
{
    use HasFactory, BaseModelTrait;

    protected function casts(): array
    {
        return [
            'total' => 'float',
            'status' => OrderStatusEnum::class,
        ];
    }

    public function newEloquentBuilder($query): OrderBuilder
    {
        return new OrderBuilder($query);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(OrderProduct::class);
    }

    public function payment(): MorphOne
    {
        return $this->morphOne(Payment::class, 'payable');
    }

    public function getNumberAttribute(): string
    {
        return 'ORDER-' . $this->id;
    }
}
