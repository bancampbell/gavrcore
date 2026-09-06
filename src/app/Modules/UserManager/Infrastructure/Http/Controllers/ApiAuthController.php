<?php

namespace App\Modules\UserManager\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\UserManager\Application\DTO\LoginData;
use App\Modules\UserManager\Application\DTO\RegisterData;
use App\Modules\UserManager\Application\UseCases\LoginUserUseCase;
use App\Modules\UserManager\Application\UseCases\RegisterUserUseCase;
use App\Modules\UserManager\Infrastructure\Http\Requests\Auth\LoginRequest;
use App\Modules\UserManager\Infrastructure\Http\Requests\Auth\RegisterRequest;
use App\Modules\UserManager\Infrastructure\Http\Resources\AuthUserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

final class ApiAuthController extends Controller
{
    public function __construct(
        private readonly RegisterUserUseCase $registerUser,
        private readonly LoginUserUseCase $loginUser,
    ) {
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $user = $this->registerUser->execute(RegisterData::fromArray($request->validated()));

        return response()->json([
            'success' => true,
            'data' => [
                'access_token' => $user->createToken('auth_token')->plainTextToken,
                'token_type' => 'Bearer',
                'user' => new AuthUserResource($user),
            ],
        ], 201);
    }

    /**
     * API-логин: в отличие от веб-версии выдаёт Sanctum-токен,
     * а не редиректит на /admin/dashboard.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $user = $this->loginUser->execute(
                LoginData::fromArray($request->only('email', 'password')),
                $request->ip()
            );
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Неверные учетные данные',
                'errors' => $e->errors(),
            ], 422);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'access_token' => $user->createToken('auth_token')->plainTextToken,
                'token_type' => 'Bearer',
                'user' => new AuthUserResource($user),
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json([
            'success' => true,
            'message' => 'Выход выполнен',
        ]);
    }
}
