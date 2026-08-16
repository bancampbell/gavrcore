<?php

declare(strict_types=1);

namespace App\Modules\GalleryManager\Infrastructure\Http\Controllers;

use App\Modules\GalleryManager\Application\DTO\CreateGalleryData;
use App\Modules\GalleryManager\Application\DTO\UpdateGalleryData;
use App\Modules\GalleryManager\Application\UseCases\CreateGalleryUseCase;
use App\Modules\GalleryManager\Application\UseCases\DeleteGalleryUseCase;
use App\Modules\GalleryManager\Application\UseCases\DeleteImageUseCase;
use App\Modules\GalleryManager\Application\UseCases\ToggleGalleryStatusUseCase;
use App\Modules\GalleryManager\Application\UseCases\UpdateGalleryUseCase;
use App\Modules\GalleryManager\Application\UseCases\UpdateImageUseCase;
use App\Modules\GalleryManager\Application\UseCases\UploadImageUseCase;
use App\Modules\GalleryManager\Infrastructure\Http\Requests\CreateGalleryRequest;
use App\Modules\GalleryManager\Infrastructure\Http\Requests\UpdateGalleryRequest;
use App\Modules\GalleryManager\Infrastructure\Http\Requests\UpdateImageRequest;
use App\Modules\GalleryManager\Infrastructure\Http\Requests\UploadImageRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class AdminGalleryCommandController extends Controller
{
    public function __construct(
        private readonly CreateGalleryUseCase $createGalleryUseCase,
        private readonly UpdateGalleryUseCase $updateGalleryUseCase,
        private readonly DeleteGalleryUseCase $deleteGalleryUseCase,
        private readonly ToggleGalleryStatusUseCase $toggleGalleryStatusUseCase,
        private readonly UploadImageUseCase $uploadImageUseCase,
        private readonly UpdateImageUseCase $updateImageUseCase,
        private readonly DeleteImageUseCase $deleteImageUseCase,
    ) {}

    public function store(CreateGalleryRequest $request): JsonResponse
    {
        $gallery = $this->createGalleryUseCase->execute(
            CreateGalleryData::fromRequest($request)
        );

        return response()->json([
            'success' => true,
            'message' => 'Галерея создана',
            'id' => $gallery->id,
        ]);
    }

    public function update(UpdateGalleryRequest $request, int $id): JsonResponse
    {
        $this->updateGalleryUseCase->execute($id, UpdateGalleryData::fromRequest($request));

        return response()->json([
            'success' => true,
            'message' => 'Галерея обновлена',
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->deleteGalleryUseCase->execute($id);

        return response()->json([
            'success' => true,
            'message' => 'Галерея удалена',
        ]);
    }

    public function publish(int $id): JsonResponse
    {
        $this->toggleGalleryStatusUseCase->execute($id, true);

        return response()->json([
            'success' => true,
            'message' => 'Галерея опубликована',
        ]);
    }

    public function unpublish(int $id): JsonResponse
    {
        $this->toggleGalleryStatusUseCase->execute($id, false);

        return response()->json([
            'success' => true,
            'message' => 'Галерея снята с публикации',
        ]);
    }

    public function uploadImage(UploadImageRequest $request, int $galleryId): JsonResponse
    {
        $image = $this->uploadImageUseCase->execute(
            galleryId: $galleryId,
            file: $request->file('image'),
            title: $request->input('title'),
        );

        return response()->json([
            'success' => true,
            'message' => 'Изображение загружено',
            'image' => $image,
        ]);
    }

    public function updateImage(UpdateImageRequest $request, int $galleryId, int $imageId): JsonResponse
    {
        $image = $this->updateImageUseCase->execute($galleryId, $imageId, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Изображение обновлено',
            'image' => $image,
        ]);
    }

    public function deleteImage(int $galleryId, int $imageId): JsonResponse
    {
        $this->deleteImageUseCase->execute($galleryId, $imageId);

        return response()->json([
            'success' => true,
            'message' => 'Изображение удалено',
        ]);
    }
}
