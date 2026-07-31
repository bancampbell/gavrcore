<?php

namespace Modules\Shop\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Shop\Application\UseCases\Cart\AddToCartUseCase;
use Modules\Shop\Application\UseCases\Cart\ClearCartUseCase;
use Modules\Shop\Infrastructure\Http\Requests\CartRequest;

class CartController extends Controller
{
    public function add(CartRequest $request, AddToCartUseCase $useCase)
    {
        $useCase->execute(
            session()->getId(),
            $request->input('product_id'),
            $request->input('quantity')
        );
        return back()->with('success', 'Товар добавлен в корзину');
    }

    public function remove(CartRequest $request, AddToCartUseCase $useCase)
    {
        // Для удаления используем тот же AddToCartUseCase с отрицательным количеством? или отдельный?
        // Проще сделать отдельный UseCase RemoveFromCartUseCase, но пока заглушим:
        // Временно реализуем в контроллере через репозиторий?
        // По ТЗ есть маршрут POST /shop/cart/remove, но UseCase не описан.
        // Добавим позже. Пока оставим заглушку.
        return back()->with('error', 'Метод удаления временно не реализован');
    }

    public function clear(ClearCartUseCase $useCase)
    {
        $useCase->execute(session()->getId());
        return back()->with('success', 'Корзина очищена');
    }
}
