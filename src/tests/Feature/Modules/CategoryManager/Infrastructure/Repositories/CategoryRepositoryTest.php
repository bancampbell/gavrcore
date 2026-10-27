<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\CategoryManager\Infrastructure\Repositories;

use App\Modules\CategoryManager\Application\DTO\CategoryData;
use App\Modules\CategoryManager\Domain\Repositories\CategoryRepositoryInterface;
use Database\Seeders\CategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * AUDIT-19: slug категории заполняется из name (Str::slug)
 * и при создании через репозиторий, и в сидере.
 */
class CategoryRepositoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_fills_slug_from_name(): void
    {
        $repository = $this->app->make(CategoryRepositoryInterface::class);

        $category = $repository->create(CategoryData::fromArray(['name' => 'Новости компании']));

        $this->assertSame('novosti-kompanii', $category->slug);
    }

    public function test_create_makes_slug_unique_on_collision(): void
    {
        $repository = $this->app->make(CategoryRepositoryInterface::class);

        $first = $repository->create(CategoryData::fromArray(['name' => 'Новости компании']));
        $second = $repository->create(CategoryData::fromArray(['name' => 'Новости компании']));

        $this->assertSame('novosti-kompanii', $first->slug);
        $this->assertSame('novosti-kompanii-1', $second->slug);
    }

    public function test_seeder_fills_slug_for_all_categories(): void
    {
        $this->seed(CategorySeeder::class);

        $categories = \App\Modules\CategoryManager\Infrastructure\Models\CategoryModel::all();

        $this->assertNotEmpty($categories);
        foreach ($categories as $category) {
            $this->assertSame(Str::slug($category->name), $category->slug);
        }
    }
}
