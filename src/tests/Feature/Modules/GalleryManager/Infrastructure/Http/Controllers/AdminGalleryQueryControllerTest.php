<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\GalleryManager\Infrastructure\Http\Controllers;

use App\Modules\UserManager\Infrastructure\Http\Middleware\AdminMiddleware;
use App\Modules\UserManager\Infrastructure\Models\UserModel as User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Feature\Modules\GalleryManager\Infrastructure\Models\GalleryImageModelFactory;
use Tests\Feature\Modules\GalleryManager\Infrastructure\Models\GalleryModelFactory;
use Tests\TestCase;

class AdminGalleryQueryControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create();
        Gate::before(fn (User $user) => $user->id === $this->admin->id ? true : null);
    }

    /** @return array<class-string> */
    private function adminMiddleware(): array
    {
        return [AdminMiddleware::class];
    }

    public function test_index_requires_authentication(): void
    {
        $this->get(route('admin.galleries.index'))->assertRedirect('/login');
    }

    public function test_index_returns_inertia_page(): void
    {
        $this->actingAs($this->admin)
            ->withoutMiddleware($this->adminMiddleware())
            ->get(route('admin.galleries.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('GalleryManager/Index', false)
                ->has('user')
                ->where('title', 'Галереи')
            );
    }

    public function test_create_returns_inertia_page(): void
    {
        $this->actingAs($this->admin)
            ->withoutMiddleware($this->adminMiddleware())
            ->get(route('admin.galleries.create'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('GalleryManager/Create', false)
                ->has('user')
                ->where('title', 'Создать галерею')
            );
    }

    public function test_list_returns_paginator_json(): void
    {
        $gallery = (new GalleryModelFactory)->create(['title' => 'Test Gallery']);
        (new GalleryImageModelFactory)->count(2)->create(['gallery_id' => $gallery->id]);

        $this->actingAs($this->admin)
            ->withoutMiddleware($this->adminMiddleware())
            ->withHeader('Accept', 'application/json')
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->get(route('admin.galleries.list'))
            ->assertOk()
            ->assertJsonPath('total', 1)
            ->assertJsonPath('per_page', 10)
            ->assertJsonPath('current_page', 1)
            ->assertJsonPath('last_page', 1)
            ->assertJsonPath('from', 1)
            ->assertJsonPath('to', 1)
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment([
                'title' => 'Test Gallery',
                'type' => 'grid',
                'images_count' => 2,
            ]);
    }

    public function test_list_filters_by_search(): void
    {
        (new GalleryModelFactory)->create(['title' => 'Alpha Gallery']);
        (new GalleryModelFactory)->create(['title' => 'Beta Gallery']);

        $this->actingAs($this->admin)
            ->withoutMiddleware($this->adminMiddleware())
            ->withHeader('Accept', 'application/json')
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->get(route('admin.galleries.list', ['search' => 'Alpha']))
            ->assertOk()
            ->assertJsonPath('total', 1)
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['title' => 'Alpha Gallery'])
            ->assertJsonMissing(['title' => 'Beta Gallery']);
    }

    public function test_list_filters_by_type(): void
    {
        (new GalleryModelFactory)->create(['type' => 'grid']);
        (new GalleryModelFactory)->create(['type' => 'slider']);

        $this->actingAs($this->admin)
            ->withoutMiddleware($this->adminMiddleware())
            ->withHeader('Accept', 'application/json')
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->get(route('admin.galleries.list', ['type' => 'slider']))
            ->assertOk()
            ->assertJsonPath('total', 1)
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['type' => 'slider']);
    }

    public function test_list_filters_by_status(): void
    {
        (new GalleryModelFactory)->create(['status' => true]);
        (new GalleryModelFactory)->create(['status' => false]);

        $this->actingAs($this->admin)
            ->withoutMiddleware($this->adminMiddleware())
            ->withHeader('Accept', 'application/json')
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->get(route('admin.galleries.list', ['status' => 1]))
            ->assertOk()
            ->assertJsonPath('total', 1)
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['status' => 1]);
    }

    public function test_list_paginates_filtered_results(): void
    {
        foreach (range(1, 25) as $i) {
            (new GalleryModelFactory)->create([
                'type' => 'grid',
                'created_at' => now()->subMinutes(100 - $i),
                'updated_at' => now()->subMinutes(100 - $i),
            ]);
        }
        (new GalleryModelFactory)->count(3)->create(['type' => 'slider']);

        $this->actingAs($this->admin)
            ->withoutMiddleware($this->adminMiddleware())
            ->withHeader('Accept', 'application/json')
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->get(route('admin.galleries.list', ['type' => 'grid', 'page' => 2]))
            ->assertOk()
            ->assertJsonPath('total', 25)
            ->assertJsonPath('current_page', 2)
            ->assertJsonPath('last_page', 3)
            ->assertJsonPath('from', 11)
            ->assertJsonPath('to', 20)
            ->assertJsonCount(10, 'data');
    }

    public function test_list_validates_per_page(): void
    {
        $this->actingAs($this->admin)
            ->withoutMiddleware($this->adminMiddleware())
            ->withHeader('Accept', 'application/json')
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->get(route('admin.galleries.list', ['per_page' => 0]))
            ->assertUnprocessable();
    }

    public function test_list_validates_type_enum(): void
    {
        $this->actingAs($this->admin)
            ->withoutMiddleware($this->adminMiddleware())
            ->withHeader('Accept', 'application/json')
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->get(route('admin.galleries.list', ['type' => 'bogus']))
            ->assertUnprocessable();
    }

    public function test_edit_returns_inertia_for_existing_gallery(): void
    {
        $gallery = (new GalleryModelFactory)->create(['title' => 'Editable']);
        (new GalleryImageModelFactory)->count(2)->create(['gallery_id' => $gallery->id]);

        $this->actingAs($this->admin)
            ->withoutMiddleware($this->adminMiddleware())
            ->get(route('admin.galleries.edit', $gallery->id))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('GalleryManager/Edit', false)
                ->has('gallery')
                ->where('gallery.id', $gallery->id)
                ->where('gallery.title', 'Editable')
                ->has('gallery.images', 2)
                ->where('title', 'Редактировать галерею: Editable')
            );
    }

    public function test_edit_returns_not_found_for_non_existing_gallery(): void
    {
        $this->actingAs($this->admin)
            ->withoutMiddleware($this->adminMiddleware())
            ->get(route('admin.galleries.edit', 9999))
            ->assertNotFound()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Site/Errors/NotFound', false)
            );
    }
}
