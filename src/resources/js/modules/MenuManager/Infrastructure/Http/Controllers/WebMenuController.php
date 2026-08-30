<?php

namespace App\Modules\MenuManager\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\MenuManager\Application\UseCases\GetMenuTreeUseCase;
use Illuminate\Http\JsonResponse;

class WebMenuController extends Controller
{
    public function __construct(private GetMenuTreeUseCase $getMenuTreeUseCase) {}

    public function tree(string $alias): JsonResponse
    {
        return response()->json($this->getMenuTreeUseCase->execute($alias));
    }
}