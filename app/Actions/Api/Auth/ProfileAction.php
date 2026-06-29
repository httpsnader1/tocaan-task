<?php

namespace App\Actions\Api\Auth;

use App\Classes\BaseAction;
use App\Http\Resources\Api\UserResource;
use Illuminate\Http\JsonResponse;

class ProfileAction extends BaseAction
{
    public function handle(): JsonResponse
    {
        $data['user'] = UserResource::make(auth('api')->user());

        return $this->apiResponse('Get Profile Data Successfully', $data);
    }
}
