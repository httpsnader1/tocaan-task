<?php

namespace App\Actions\Api\Auth;

use App\Classes\BaseAction;
use App\Http\Resources\Api\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class RegisterAction extends BaseAction
{
    public function handle(ActionRequest $request): JsonResponse
    {
        $user = User::create($request->validated());
        $data['token'] = auth('api')->login($user);
        $data['user'] = UserResource::make($user);

        return $this->apiResponse('Register Successfully', $data);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string'],
            'email' => ['required', 'email', Rule::unique(User::class, 'email')],
            'password' => ['required', 'min:8', 'confirmed'],
        ];
    }
}
