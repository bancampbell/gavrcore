<?php

namespace Modules\Shop\Domain\Repositories;

use Modules\Shop\Domain\Entities\Product;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Интерфейс репозитория товаров.
 * Реализации: Eloquent, In-memory и т.д.
 */
interface ProductRepositoryInterface
{
    public function find(string $id): ?Product;
    public function findOrFail(string $id): Product;
    public function findBySlug(string $slug): ?Product;

    /**
     * Получить список товаров с фильтрацией и пагинацией
     *
     * @param array $filters ['category' => uuid, 'search' => string, 'sort' => 'price_asc'|'price_desc'|'name_asc'|'name_desc']
     * @param int $perPage
     * @return LengthAwarePaginator<Product>
     */
    public function getPaginated(array $filters, int $perPage = 20): LengthAwarePaginator;

    public function save(Product $product): void;
    public function delete(string $id): void;
}
