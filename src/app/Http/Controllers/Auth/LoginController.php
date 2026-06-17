<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\LoginUserAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

class LoginController extends Controller
{
    public function __construct(
        protected LoginUserAction $loginAction
    ) {
    }

    public function login(LoginRequest $request): JsonResponse
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

    public function logout(Request $request): JsonResponse
    {
        try {
            $token = $request->bearerToken();

            if ($token) {
                $accessToken = PersonalAccessToken::findToken($token);
                if ($accessToken) {
                    $accessToken->delete();
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Выход выполнен успешно',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при выходе: ' . $e->getMessage(),
            ], 500);
        }
    }
}
