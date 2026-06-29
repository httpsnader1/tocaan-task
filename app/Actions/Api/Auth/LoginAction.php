<?php

namespace App\Actions\Api\Auth;

use App\Classes\BaseAction;
use App\Http\Resources\Api\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\ActionRequest;

class LoginAction extends BaseAction
{
    public function handle(ActionRequest $request): JsonResponse
    {
        $data['token'] = auth('api')->attempt($request->validated());

        throw_if(
            !$data['token'],
            ValidationException::withMessages(['email' => [__('auth.failed')]])
        );

        $data['user'] = UserResource::make(auth('api')->user());

        return $this->apiResponse('Login Successfully', $data);
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required'],
        ];
    }
}
