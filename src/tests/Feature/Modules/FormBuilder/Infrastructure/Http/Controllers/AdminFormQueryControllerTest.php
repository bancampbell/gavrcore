<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\FormBuilder\Infrastructure\Http\Controllers;

use App\Modules\UserManager\Infrastructure\Http\Middleware\AdminMiddleware;
use App\Modules\UserManager\Infrastructure\Models\UserModel as User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminFormQueryControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create();
        Gate::before(fn (User $user) => $user->id === $this->admin->id ? true : null);
    }

    public function test_index_exposes_only_safe_user_fields(): void
    {
        $this->actingAs($this->admin)
            ->withoutMiddleware(AdminMiddleware::class)
            ->get(route('admin.forms.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('FormBuilder/FormIndex', false)
                ->where('user.id', $this->admin->id)
                ->where('user.name', $this->admin->name)
                ->where('user.email', $this->admin->email)
                ->missing('user.password')
                ->missing('user.remember_token')
            );
    }
}
