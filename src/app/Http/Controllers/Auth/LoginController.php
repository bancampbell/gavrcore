<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\LoginUserAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function __construct(
        protected LoginUserAction $loginAction
    ) {}

    public function login(LoginRequest $request)
    {
        $user = $this->loginAction->execute($request->only('email', 'password'), $request);

        return response()->json([
            'success' => true,
            'data' => [
                'access_token' => $user->createToken('auth_token')->plainTextToken,
                'token_type' => 'Bearer',
                'user' => new UserResource($user),
            ],
        ]);
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        if ($user && method_exists($user->currentAccessToken(), 'delete')) {
            $user->currentAccessToken()->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Выход выполнен успешно',
        ]);
    }
}
