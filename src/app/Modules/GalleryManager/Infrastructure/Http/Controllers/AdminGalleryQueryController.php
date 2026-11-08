<?php

declare(strict_types=1);

namespace App\Modules\GalleryManager\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\GalleryManager\Application\DTO\GalleryFiltersData;
use App\Modules\GalleryManager\Application\UseCases\GetGalleryForEditUseCase;
use App\Modules\GalleryManager\Application\UseCases\GetGalleryListUseCase;
use App\Modules\GalleryManager\Domain\Entities\Gallery;
use App\Modules\GalleryManager\Infrastructure\Http\Requests\GalleryIndexRequest;
use Illuminate\Http\JsonResponse;
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
            'user' => auth()->user()->only(['id', 'name', 'email']),
            'title' => 'Галереи',
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('GalleryManager/Create', [
            'user' => auth()->user()->only(['id', 'name', 'email']),
            'title' => 'Создать галерею',
        ]);
    }

    public function list(GalleryIndexRequest $request): JsonResponse
    {
        $filters = GalleryFiltersData::fromRequest($request);
        $paginator = $this->getGalleryListUseCase->execute($filters);

        return response()->json([
            'data' => collect($paginator->items())->map(fn (Gallery $gallery) => [
                'id' => $gallery->id,
                'title' => $gallery->title,
                'type' => $gallery->type->value,
                'status' => $gallery->status->value,
                'images_count' => $gallery->imagesCount ?? 0,
                'created_at' => $gallery->createdAt,
            ])->values(),
            'from' => $paginator->firstItem(),
            'to' => $paginator->lastItem(),
            'total' => $paginator->total(),
            'per_page' => $paginator->perPage(),
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'filters' => $filters->toArray(),
        ]);
    }

    public function edit(int $id): Response
    {
        $gallery = $this->getGalleryForEditUseCase->execute($id);

        if (! $gallery) {
            abort(404);
        }

        return Inertia::render('GalleryManager/Edit', [
            'user' => auth()->user()->only(['id', 'name', 'email']),
            'gallery' => $gallery,
            'title' => "Редактировать галерею: {$gallery['title']}",
        ]);
    }
}
