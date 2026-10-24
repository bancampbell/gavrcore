<?php

namespace App\Modules\UserDashboard\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\SettingManager\Application\Services\SettingService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    protected SettingService $settingService;

    public function __construct(SettingService $settingService)
    {
        $this->settingService = $settingService;
    }

    public function index(): Response
    {
        // TODO: статистика обращений временно отключена. Ранее считалась по
        // App\Models\FormSubmission, которых в монолите нет — данные по заявкам
        // должен отдавать модуль FormBuilder после его миграции.
        return Inertia::render('UserDashboard/Index', [
            'user' => auth()->user(),
            'currentTheme' => $this->settingService->getTheme(),
            'totalTickets' => 0,
            'inProgressTickets' => 0,
            'completedTickets' => 0,
        ]);
    }

    public function profile(): Response
    {
        return Inertia::render('UserDashboard/Profile', [
            'user' => auth()->user(),
            'currentTheme' => $this->settingService->getTheme(),
        ]);
    }

    public function settings(): Response
    {
        return Inertia::render('UserDashboard/Settings', [
            'user' => auth()->user(),
            'currentTheme' => $this->settingService->getTheme(),
        ]);
    }

    /**
     * Список заявок пользователя
     */
    public function tickets(): Response
    {
        // TODO: список обращений временно пуст — выборка зависела от
        // App\Models\FormSubmission (FormBuilder ещё не мигрирован).
        // Заменить пустой paginator на реальную выборку после миграции FormBuilder.
        $submissions = new LengthAwarePaginator([], 0, 15);

        return Inertia::render('UserDashboard/TicketsIndex', [
            'user' => auth()->user(),
            'currentTheme' => $this->settingService->getTheme(),
            'submissions' => $submissions,
        ]);
    }

    /**
     * Страница создания новой заявки
     */
    public function ticketsCreate(): Response
    {
        // TODO: формы отсутствуют (FormBuilder ещё не мигрирован),
        // передаём пустой массив вместо выборки по App\Models\Form.
        return Inertia::render('UserDashboard/TicketsCreate', [
            'user' => auth()->user(),
            'currentTheme' => $this->settingService->getTheme(),
            'forms' => [],
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
        ]);

        $user->update($validated);

        return back()->with('success', 'Профиль обновлён');
    }

    public function updateSettings(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => 'Текущий пароль неверен',
            ]);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Пароль изменён');
    }
}
