<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\RegisterUserAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;

class RegisterController extends Controller
{
    public function __construct(
        protected RegisterUserAction $registerAction
    ) {}

    public function register(RegisterRequest $request)
    {
        $user = $this->registerAction->execute($request->validated());

        return response()->json([
            'success' => true,
            'data' => [
                'access_token' => $user->createToken('auth_token')->plainTextToken,
                'token_type' => 'Bearer',
                'user' => new UserResource($user),
            ],
        ], 201);
    }
}
