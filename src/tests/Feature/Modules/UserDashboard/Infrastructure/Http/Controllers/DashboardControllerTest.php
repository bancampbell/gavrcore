<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\UserDashboard\Infrastructure\Http\Controllers;

use App\Modules\FormBuilder\Domain\Entities\FormSubmission;
use App\Modules\UserManager\Infrastructure\Models\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Feature\Modules\FormBuilder\Infrastructure\Models\FormModelFactory;
use Tests\Feature\Modules\FormBuilder\Infrastructure\Models\FormSubmissionModelFactory;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_dashboard(): void
    {
        $this->get('/dashboard')->assertRedirect(route('login'));
    }

    public function test_index_shows_real_submission_stats_for_current_user(): void
    {
        $user = UserModel::factory()->create();
        $otherUser = UserModel::factory()->create();
        $form = (new FormModelFactory)->create();

        (new FormSubmissionModelFactory)->forUser($user->id)->create(['form_id' => $form->id]);
        (new FormSubmissionModelFactory)->forUser($user->id)->create(['form_id' => $form->id]);
        (new FormSubmissionModelFactory)->forUser($user->id)->withStatus(FormSubmission::STATUS_IN_PROGRESS)->create(['form_id' => $form->id]);
        (new FormSubmissionModelFactory)->forUser($user->id)->withStatus(FormSubmission::STATUS_COMPLETED)->create(['form_id' => $form->id]);
        (new FormSubmissionModelFactory)->forUser($otherUser->id)->withStatus(FormSubmission::STATUS_COMPLETED)->create(['form_id' => $form->id]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('UserDashboard/Index', false)
                ->where('totalTickets', 4)
                ->where('inProgressTickets', 1)
                ->where('completedTickets', 1)
            );
    }

    public function test_tickets_shows_only_current_user_submissions(): void
    {
        $user = UserModel::factory()->create();
        $otherUser = UserModel::factory()->create();
        $form = (new FormModelFactory)->create();

        (new FormSubmissionModelFactory)->forUser($user->id)->count(2)->create(['form_id' => $form->id]);
        (new FormSubmissionModelFactory)->forUser($otherUser->id)->create(['form_id' => $form->id]);

        $response = $this->actingAs($user)->get('/dashboard/tickets');

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('UserDashboard/TicketsIndex', false)
                ->where('submissions.total', 2)
                ->has('submissions.data', 2)
            );
    }

    public function test_tickets_create_provides_only_published_forms_keyed_by_id(): void
    {
        $user = UserModel::factory()->create();
        $published = (new FormModelFactory)->create();
        $draft = (new FormModelFactory)->draft()->create();

        $response = $this->actingAs($user)->get('/dashboard/tickets/new');

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('UserDashboard/TicketsCreate', false)
                ->has("forms.{$published->id}")
                ->missing("forms.{$draft->id}")
            );
    }

    public function test_show_displays_own_submission_and_marks_it_read(): void
    {
        $user = UserModel::factory()->create();
        $form = (new FormModelFactory)->create();
        $submission = (new FormSubmissionModelFactory)->forUser($user->id)->create(['form_id' => $form->id]);

        $response = $this->actingAs($user)->get("/dashboard/tickets/{$submission->id}");

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('UserDashboard/TicketsShow', false)
                ->where('submission.id', $submission->id)
                ->where('formTitle', $form->title)
            );

        $this->assertDatabaseHas('form_submissions', [
            'id' => $submission->id,
        ])->assertNotNull($submission->fresh()->read_at);
    }

    public function test_show_returns_404_for_other_users_submission(): void
    {
        $user = UserModel::factory()->create();
        $otherUser = UserModel::factory()->create();
        $form = (new FormModelFactory)->create();
        $submission = (new FormSubmissionModelFactory)->forUser($otherUser->id)->create(['form_id' => $form->id]);

        $this->actingAs($user)->get("/dashboard/tickets/{$submission->id}")
            ->assertNotFound()
            ->assertInertia(fn (Assert $page) => $page->component('Errors/NotFound', false));
    }

    public function test_show_returns_404_for_missing_submission(): void
    {
        $user = UserModel::factory()->create();

        $this->actingAs($user)->get('/dashboard/tickets/9999')
            ->assertNotFound()
            ->assertInertia(fn (Assert $page) => $page->component('Errors/NotFound', false));
    }
}
