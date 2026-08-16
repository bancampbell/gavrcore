<?php

declare(strict_types=1);

namespace App\Modules\GalleryManager\Infrastructure\Http\Controllers;

use App\Modules\GalleryManager\Application\UseCases\GetGalleryForPublicUseCase;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class WebGalleryController extends Controller
{
    public function __construct(
        private readonly GetGalleryForPublicUseCase $getGalleryForPublicUseCase,
    ) {}

    public function show(int $id): JsonResponse
    {
        $gallery = $this->getGalleryForPublicUseCase->execute($id);

        if (!$gallery) {
            return response()->json(['message' => 'Gallery not found or not published'], 404);
        }

        return response()->json($gallery);
    }
}
