<?php

namespace App\Actions\Api\Auth;

use App\Classes\BaseAction;
use Illuminate\Http\JsonResponse;

class LogoutAction extends BaseAction
{
    public function handle(): JsonResponse
    {
        auth('api')->logout();

        return $this->apiResponse('Logout Successfully');
    }
}
