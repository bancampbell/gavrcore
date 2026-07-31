<?php

namespace Modules\Shop\Infrastructure\Repositories;

use Modules\Shop\Domain\Entities\Product as DomainProduct;
use Modules\Shop\Domain\Repositories\ProductRepositoryInterface;
use Modules\Shop\Domain\ValueObjects\Money;
use Modules\Shop\Domain\ValueObjects\Sku;
use Modules\Shop\Domain\ValueObjects\ProductStatus;
use App\Models\Product as EloquentProduct;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class ProductRepository implements ProductRepositoryInterface
{
    public function find(string $id): ?DomainProduct
    {
        $eloquent = EloquentProduct::find($id);
        return $eloquent ? $this->toDomain($eloquent) : null;
    }

    public function findOrFail(string $id): DomainProduct
    {
        $product = $this->find($id);
        if (!$product) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException('Product not found');
        }
        return $product;
    }

    public function findBySlug(string $slug): ?DomainProduct
    {
        // Если нет поля slug, используем name для поиска (можно добавить поле позже)
        $eloquent = EloquentProduct::where('name', $slug)->first();
        return $eloquent ? $this->toDomain($eloquent) : null;
    }

    public function getPaginated(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        $query = EloquentProduct::query();

        if (!empty($filters['category'])) {
            $query->where('category_id', $filters['category']);
        }
        if (!empty($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        $sort = $filters['sort'] ?? 'name_asc';
        match ($sort) {
            'price_asc' => $query->orderBy('price', 'asc'),
            'price_desc' => $query->orderBy('price', 'desc'),
            'name_desc' => $query->orderBy('name', 'desc'),
            default => $query->orderBy('name', 'asc'),
        };

        $paginator = $query->paginate($perPage);
        return $paginator->through(fn($item) => $this->toDomain($item));
    }

    public function save(DomainProduct $product): void
    {
        $eloquent = EloquentProduct::find($product->getId()) ?? new EloquentProduct();
        $eloquent->id = $product->getId();
        $eloquent->name = $product->getName();
        $eloquent->description = $product->getDescription();
        $eloquent->price = $product->getPrice()->getAmount();
        $eloquent->sku = $product->getSku()->getValue();
        $eloquent->status = $product->getStatus()->value;
        $eloquent->stock = $product->getStock();
        $eloquent->category_id = $product->getCategoryId();
        $eloquent->save();
    }

    public function delete(string $id): void
    {
        EloquentProduct::destroy($id);
    }

    private function toDomain(EloquentProduct $eloquent): DomainProduct
    {
        return new DomainProduct(
            $eloquent->id,
            $eloquent->name,
            $eloquent->description,
            new Money($eloquent->price),
            new Sku($eloquent->sku),
            ProductStatus::from($eloquent->status),
            $eloquent->stock,
            $eloquent->category_id
        );
    }
}
