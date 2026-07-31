<?php

namespace Modules\Shop\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Shop\Application\UseCases\Product\GetProductListUseCase;
use Modules\Shop\Application\UseCases\Product\GetProductDetailUseCase;
use Modules\Shop\Application\UseCases\Cart\GetCartUseCase;
use Modules\Shop\Infrastructure\Http\Resources\ProductResource;
use Modules\Shop\Infrastructure\Http\Resources\CartResource;

class ShopController extends Controller
{
    public function index(GetProductListUseCase $useCase): Response
    {
        $filters = request()->only(['page', 'per_page', 'sort', 'category']);
        $perPage = (int) request()->input('per_page', 20);
        $products = $useCase->execute($filters, $perPage);

        return Inertia::render('Shop/Index', [
            'products' => ProductResource::collection($products),
        ]);
    }

    public function show(string $slug, GetProductDetailUseCase $useCase): Response
    {
        $product = $useCase->execute($slug);
        return Inertia::render('Shop/Show', [
            'product' => ProductResource::make($product),
        ]);
    }

    public function cart(GetCartUseCase $useCase): Response
    {
        $cart = $useCase->execute(session()->getId());
        return Inertia::render('Shop/Cart', [
            'cart' => CartResource::make($cart),
        ]);
    }
}
