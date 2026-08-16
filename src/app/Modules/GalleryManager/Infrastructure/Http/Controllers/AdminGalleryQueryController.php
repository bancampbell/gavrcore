<?php

declare(strict_types=1);

namespace App\Modules\GalleryManager\Infrastructure\Http\Controllers;

use App\Modules\GalleryManager\Application\DTO\GalleryFiltersData;
use App\Modules\GalleryManager\Application\UseCases\GetGalleryForEditUseCase;
use App\Modules\GalleryManager\Application\UseCases\GetGalleryListUseCase;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminGalleryQueryController extends Controller
{
    public function __construct(
        private readonly GetGalleryForEditUseCase $getGalleryForEditUseCase,
        private readonly GetGalleryListUseCase $getGalleryListUseCase,
    ) {}

    public function index(): Response
    {
        return Inertia::render('GalleryManager/Index', [
            'user' => auth()->user(),
            'title' => 'Галереи',
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('GalleryManager/Create', [
            'user' => auth()->user(),
            'title' => 'Создать галерею',
        ]);
    }

    public function list(Request $request): JsonResponse
    {
        $filters = GalleryFiltersData::fromRequest($request);
        $galleries = $this->getGalleryListUseCase->execute($filters);

        return response()->json(
            $galleries->map(fn ($gallery) => [
                'id' => $gallery->id,
                'title' => $gallery->title,
                'type' => $gallery->type->value,
                'status' => $gallery->status->value,
                'images_count' => $gallery->imagesCount ?? 0,
                'created_at' => $gallery->createdAt,
            ])->values()
        );
    }

    public function edit(int $id): Response
    {
        $gallery = $this->getGalleryForEditUseCase->execute($id);

        if (!$gallery) {
            abort(404);
        }

        return Inertia::render('GalleryManager/Edit', [
            'user' => auth()->user(),
            'gallery' => $gallery,
            'title' => "Редактировать галерею: {$gallery['title']}",
        ]);
    }
}
