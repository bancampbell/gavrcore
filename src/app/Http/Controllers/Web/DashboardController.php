<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Form;
use App\Models\FormSubmission;
use App\Services\SettingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        $userId = auth()->id();

        $totalTickets = FormSubmission::where('user_id', $userId)->count();
        $inProgressTickets = FormSubmission::where('user_id', $userId)
            ->where('status', 'in_progress')
            ->count();
        $completedTickets = FormSubmission::where('user_id', $userId)
            ->where('status', 'completed')
            ->count();

        return Inertia::render('Dashboard/Index', [
            'user' => auth()->user(),
            'currentTheme' => $this->settingService->getTheme(),
            'totalTickets' => $totalTickets,
            'inProgressTickets' => $inProgressTickets,
            'completedTickets' => $completedTickets,
        ]);
    }

    public function profile(): Response
    {
        return Inertia::render('Dashboard/Profile', [
            'user' => auth()->user(),
            'currentTheme' => $this->settingService->getTheme(),
        ]);
    }

    public function settings(): Response
    {
        return Inertia::render('Dashboard/Settings', [
            'user' => auth()->user(),
            'currentTheme' => $this->settingService->getTheme(),
        ]);
    }

    /**
     * Список заявок пользователя
     */
    public function tickets(): Response
    {
        $submissions = FormSubmission::where('user_id', auth()->id())
            ->with('form')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return Inertia::render('Dashboard/TicketsIndex', [
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
        $forms = Form::where('status', true)->get()->keyBy('id')->toArray();

        return Inertia::render('Dashboard/TicketsCreate', [
            'user' => auth()->user(),
            'currentTheme' => $this->settingService->getTheme(),
            'forms' => $forms,
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
