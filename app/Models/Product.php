<?php

namespace App\Models;

use App\Traits\BaseModelTrait;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'name',
    'price',
    'stock',
    'is_active',
])]
class Product extends Model
{
    use BaseModelTrait;

    protected function casts(): array
    {
        return [
            'price' => 'float',
            'stock' => 'float',
            'is_active' => 'boolean',
        ];
    }
}
