<?php

namespace App\Modules\UserManager\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\UserManager\Application\Services\TotpService;
use App\Modules\UserManager\Infrastructure\Models\UserModel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Второй фактор при логине (общий для админки и кабинета).
 *
 * Логин-контроллеры при верном пароле и включённом totp_enabled не выдают
 * сессию, а кладут в сессию метку ожидания 2FA под SESSION_KEY:
 * ['user_id' => int, 'guard' => string, 'remember' => bool, 'redirect' => string].
 * Этот контроллер принимает код (TOTP из приложения-аутентификатора или
 * резервный), по успеху аутентифицирует и ведёт на сохранённый redirect.
 * Сгоревший резервный код второй раз не принимается (burn в TotpService).
 */
final class TwoFactorChallengeController extends Controller
{
    /**
     * Имя сессионного ключа метки ожидания 2FA.
     */
    public const SESSION_KEY = 'two_factor_pending';

    public function __construct(
        private readonly TotpService $totp,
    ) {}

    /**
     * Страница ввода кода второго фактора (компонент фронта — часть 2).
     */
    public function create(Request $request): Response|RedirectResponse
    {
        if (! $request->session()->has(self::SESSION_KEY)) {
            return redirect()->route('login');
        }

        return Inertia::render('UserManager/Auth/TwoFactorChallenge');
    }

    /**
     * Проверка кода второго фактора по сессионной метке.
     */
    public function store(Request $request): RedirectResponse
    {
        $marker = $request->session()->get(self::SESSION_KEY);

        abort_unless(is_array($marker) && isset($marker['user_id']), 403, 'Сессия второго фактора не начата');

        $validated = $request->validate([
            'code' => 'required|string|max:64',
        ], [
            'code.required' => 'Введите код',
        ]);

        $user = UserModel::find($marker['user_id']);

        if ($user === null) {
            $request->session()->forget(self::SESSION_KEY);

            abort(403, 'Сессия второго фактора не начата');
        }

        $code = trim($validated['code']);

        // TOTP: 6 цифр из приложения-аутентификатора (допуск ±1 окно — в TotpService)
        $verified = $user->totp_secret !== null && $this->totp->verify($user->totp_secret, $code);

        // Резервный код: формат XXXX-XXXX-XXXX-XXXX, ввод с дефисами/без,
        // в любом регистре — нормализация внутри TotpService. Проверяем через
        // burn: совпадение = код найден, он же сгорает (персистентность — тут).
        if (! $verified) {
            $remaining = $this->totp->burnRecoveryCode($code, $user->totp_recovery_codes ?? []);

            if ($remaining !== null) {
                // 2FA-поля вне $fillable модели: меняем только здесь, через forceFill
                $user->forceFill(['totp_recovery_codes' => $remaining])->save();
                $verified = true;
            }
        }

        if (! $verified) {
            throw ValidationException::withMessages([
                'code' => 'Неверный код',
            ]);
        }

        $request->session()->forget(self::SESSION_KEY);

        Auth::guard($marker['guard'] ?? 'web')->login($user, (bool) ($marker['remember'] ?? false));

        $request->session()->regenerate();

        return redirect($marker['redirect'] ?? '/dashboard');
    }
}
