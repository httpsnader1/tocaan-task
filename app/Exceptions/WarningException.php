<?php

namespace App\Exceptions;

use App\Traits\JsonResponseTrait;
use Exception;
use Illuminate\Http\JsonResponse;

class WarningException extends Exception
{
    use JsonResponseTrait;

    public function __construct(string $message, protected array $data = [], protected int $statusCode = 422)
    {
        parent::__construct($message);
    }

    public function render(): JsonResponse
    {
        return $this->apiResponse($this->getMessage(), $this->data, $this->statusCode);
    }

    public function report(): bool
    {
        return false;
    }
}
