<?php

namespace App\Modules\UserDashboard\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\FormBuilder\Application\DTO\SubmissionFiltersData;
use App\Modules\FormBuilder\Application\UseCases\GetActiveFormsUseCase;
use App\Modules\FormBuilder\Application\UseCases\GetFormByIdUseCase;
use App\Modules\FormBuilder\Application\UseCases\GetSubmissionsUseCase;
use App\Modules\FormBuilder\Application\UseCases\GetUserSubmissionStatsUseCase;
use App\Modules\FormBuilder\Application\UseCases\ShowSubmissionUseCase;
use App\Modules\FormBuilder\Infrastructure\Http\Resources\SubmissionResource;
use App\Modules\SettingManager\Application\Services\SettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        protected SettingService $settingService,
        protected GetUserSubmissionStatsUseCase $getUserSubmissionStatsUseCase,
        protected GetSubmissionsUseCase $getSubmissionsUseCase,
        protected GetActiveFormsUseCase $getActiveFormsUseCase,
        protected ShowSubmissionUseCase $showSubmissionUseCase,
        protected GetFormByIdUseCase $getFormByIdUseCase,
    ) {}

    public function index(): Response
    {
        $stats = $this->getUserSubmissionStatsUseCase->execute((int) auth()->id());

        return Inertia::render('UserDashboard/Index', [
            'user' => auth()->user(),
            'currentTheme' => $this->settingService->getTheme(),
            'totalTickets' => $stats['total'],
            'inProgressTickets' => $stats['in_progress'],
            'completedTickets' => $stats['completed'],
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
        $submissions = $this->getSubmissionsUseCase->execute(
            new SubmissionFiltersData(userId: (int) auth()->id()),
            15
        );

        return Inertia::render('UserDashboard/TicketsIndex', [
            'user' => auth()->user(),
            'currentTheme' => $this->settingService->getTheme(),
            'submissions' => $submissions,
        ]);
    }

    /**
     * Карточка заявки пользователя
     */
    public function show(int $id): Response
    {
        $submission = $this->showSubmissionUseCase->execute($id);

        abort_unless($submission !== null && $submission->userId === (int) auth()->id(), 404);

        $form = $this->getFormByIdUseCase->execute($submission->formId);

        return Inertia::render('UserDashboard/TicketsShow', [
            'user' => auth()->user(),
            'currentTheme' => $this->settingService->getTheme(),
            'submission' => (new SubmissionResource($submission))->toArray(request()),
            'formTitle' => $form?->title,
        ]);
    }

    /**
     * Страница создания новой заявки
     */
    public function ticketsCreate(): Response
    {
        return Inertia::render('UserDashboard/TicketsCreate', [
            'user' => auth()->user(),
            'currentTheme' => $this->settingService->getTheme(),
            'forms' => $this->getActiveFormsUseCase->execute(),
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,'.$user->id,
        ]);

        $user->update($validated);

        return back()->with('success', 'Профиль обновлён');
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if (! Hash::check($request->current_password, $user->password)) {
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
