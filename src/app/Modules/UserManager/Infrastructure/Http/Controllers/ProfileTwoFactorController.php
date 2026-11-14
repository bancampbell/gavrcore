<?php

namespace App\Modules\UserManager\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\SettingManager\Application\Services\SettingService;
use App\Modules\UserManager\Application\Services\TotpService;
use App\Modules\UserManager\Infrastructure\Http\Requests\Profile\ConfirmTwoFactorRequest;
use App\Modules\UserManager\Infrastructure\Http\Requests\Profile\DisableTwoFactorRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * Управление TOTP-2FA текущего пользователя (только на себя).
 * Общие для админки и кабинета эндпоинты: админ и пользователь кабинета
 * аутентифицируются одной сессионной гвардой (web), а auth:sanctum
 * дополнительно принимает bearer-токен.
 */
final class ProfileTwoFactorController extends Controller
{
    public function __construct(
        private readonly TotpService $totp,
        private readonly SettingService $settings,
    ) {}

    /**
     * Шаг 1 включения: генерирует pending-секрет и отдаёт otpauth-URL.
     * Флаг totp_enabled не взводится — до подтверждения кодом (confirm).
     */
    public function enable(Request $request): JsonResponse
    {
        $user = $request->user();

        abort_if($user->totp_enabled, 409, 'Двухфакторная аутентификация уже включена');

        $secret = $this->totp->generateSecret();

        // 2FA-поля вне $fillable модели: меняем только здесь, через forceFill.
        $user->forceFill(['totp_secret' => $secret])->save();

        return response()->json([
            'success' => true,
            'data' => [
                'otpauth_url' => $this->totp->buildOtpAuthUrl($secret, $user->email, $this->issuer()),
            ],
        ]);
    }

    /**
     * Шаг 2 включения: проверяет код по pending-секрету, взводит флаг
     * и возвращает резервные коды (в открытом виде — только в этом ответе).
     */
    public function confirm(ConfirmTwoFactorRequest $request): JsonResponse
    {
        $user = $request->user();

        abort_if($user->totp_enabled, 409, 'Двухфакторная аутентификация уже включена');

        $code = $request->string('code')->toString();

        if ($user->totp_secret === null || ! $this->totp->verify($user->totp_secret, $code)) {
            throw ValidationException::withMessages([
                'code' => 'Неверный код подтверждения',
            ]);
        }

        $recoveryCodes = $this->totp->generateRecoveryCodes();

        $user->forceFill([
            'totp_enabled' => true,
            'totp_recovery_codes' => array_map(
                fn (string $plain) => $this->totp->hashRecoveryCode($plain),
                $recoveryCodes,
            ),
        ])->save();

        return response()->json([
            'success' => true,
            'data' => [
                'recovery_codes' => $recoveryCodes,
            ],
        ]);
    }

    /**
     * Отключение 2FA по текущему паролю: сбрасывает флаг, секрет и коды.
     */
    public function disable(DisableTwoFactorRequest $request): JsonResponse
    {
        $user = $request->user();

        abort_if(! $user->totp_enabled, 409, 'Двухфакторная аутентификация не включена');

        if (! Hash::check($request->string('current_password')->toString(), $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => 'Текущий пароль неверен',
            ]);
        }

        $user->forceFill([
            'totp_enabled' => false,
            'totp_secret' => null,
            'totp_recovery_codes' => null,
        ])->save();

        return response()->json([
            'success' => true,
            'message' => 'Двухфакторная аутентификация отключена',
        ]);
    }

    /**
     * Issuer для otpauth-URL: название сайта из настроек, fallback — APP_NAME
     * (по аналогии с CategorySeoProvider).
     */
    private function issuer(): string
    {
        $settings = $this->settings->getAllSettings();

        return $settings['site_name'] ?? config('app.name');
    }
}
