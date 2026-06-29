<?php

namespace App\Classes;

use App\Traits\JsonResponseTrait;
use Lorisleiva\Actions\Concerns\AsAction;

abstract class BaseAction
{
    use AsAction, JsonResponseTrait;

    public function __construct()
    {
    }
}
