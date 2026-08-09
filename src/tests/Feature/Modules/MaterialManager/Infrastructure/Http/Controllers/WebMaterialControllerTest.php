<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\MaterialManager\Infrastructure\Http\Controllers;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Feature\Modules\MaterialManager\Infrastructure\Models\MaterialModelFactory;
use Tests\TestCase;

class WebMaterialControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_shows_homepage_material(): void
    {
        $material = (new MaterialModelFactory())->create([
            'state' => 'published',
            'published_at' => now()->subDay(),
            'show_on_homepage' => true,
            'access' => 'public',
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Index', false)
                ->has('homepageMaterial')
                ->where('homepageMaterial.id', $material->id)
            );
    }

    public function test_index_hides_non_public_homepage_for_guests(): void
    {
        (new MaterialModelFactory())->create([
            'state' => 'published',
            'published_at' => now()->subDay(),
            'show_on_homepage' => true,
            'access' => 'registered',
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('homepageMaterial', null)
            );
    }

    public function test_show_displays_public_material(): void
    {
        $material = (new MaterialModelFactory())->create([
            'state' => 'published',
            'published_at' => now()->subDay(),
            'access' => 'public',
        ]);

        $this->get(route('material.show', $material->slug))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Material/Show', false)
                ->where('material.id', $material->id)
            );
    }

    public function test_show_returns_403_for_registered_material_when_guest(): void
    {
        $material = (new MaterialModelFactory())->create([
            'state' => 'published',
            'published_at' => now()->subDay(),
            'access' => 'registered',
        ]);

        $this->get(route('material.show', $material->slug))->assertForbidden();
    }

    public function test_search_returns_results(): void
    {
        (new MaterialModelFactory())->create([
            'title' => 'Unique Search Term',
            'state' => 'published',
            'published_at' => now()->subDay(),
            'access' => 'public',
        ]);

        $this->get(route('search', ['q' => 'Unique Search']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Search/Index', false)
                ->has('materials.data')
            );
    }
}
