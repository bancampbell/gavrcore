<?php

namespace Modules\Shop\Infrastructure\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Modules\Shop\Application\UseCases\Product\GetProductListUseCase;
use Modules\Shop\Application\UseCases\Product\CreateProductUseCase;
use Modules\Shop\Application\UseCases\Product\DeleteProductUseCase;
use Modules\Shop\Application\DTO\ProductData;
use Modules\Shop\Infrastructure\Http\Requests\ProductRequest;
use Modules\Shop\Infrastructure\Http\Resources\ProductResource;

class ProductController extends Controller
{
    public function index(GetProductListUseCase $useCase)
    {
        $products = $useCase->execute(request()->only(['page', 'per_page', 'sort', 'category']));
        return inertia('Admin/Shop/Products', [
            'products' => ProductResource::collection($products),
        ]);
    }

    public function store(ProductRequest $request, CreateProductUseCase $useCase)
    {
        $data = new ProductData(
            name: $request->input('name'),
            description: $request->input('description'),
            price: $request->input('price'),
            sku: $request->input('sku'),
            status: $request->input('status', 'draft'),
            stock: $request->input('stock', 0),
            categoryId: $request->input('category_id'),
        );
        $useCase->execute($data);
        return redirect()->route('admin.shop.products')->with('success', 'Товар создан');
    }

    public function update(ProductRequest $request, string $id, UpdateProductUseCase $useCase)
    {
        // нужен UpdateProductUseCase — пока не реализован, заглушка
        return back()->with('error', 'Обновление временно не реализовано');
    }

    public function destroy(string $id, DeleteProductUseCase $useCase)
    {
        $useCase->execute($id);
        return back()->with('success', 'Товар удалён');
    }
}
