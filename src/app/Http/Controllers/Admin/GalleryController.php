<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Gallery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GalleryController extends Controller
{
    // Список галерей (страница)
    public function index(): Response
    {
        return Inertia::render('Admin/Galleries/Index', [
            'user' => auth()->user(),
            'title' => 'Галереи',
        ]);
    }

    // Список галерей (API для модалки)
    public function list(): JsonResponse
    {
        $galleries = Gallery::withCount('images')
            ->orderBy('created_at', 'desc')
            ->get(['id', 'title', 'type', 'status', 'created_at']);

        return response()->json($galleries);
    }

    // Страница создания
    public function create(): Response
    {
        return Inertia::render('Admin/Galleries/Create', [
            'user' => auth()->user(),
            'title' => 'Создать галерею',
        ]);
    }

    // Сохранение
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:grid,slideshow,slider,switcher',
            'settings' => 'nullable|array',
        ]);

        $gallery = Gallery::create([
            'title' => $validated['title'],
            'type' => $validated['type'],
            'settings' => $validated['settings'] ?? [],
            'status' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Галерея создана',
            'id' => $gallery->id,
        ]);
    }

    // Страница редактирования
    public function edit(Gallery $gallery): Response
    {
        $gallery->load('images');

        return Inertia::render('Admin/Galleries/Edit', [
            'user' => auth()->user(),
            'gallery' => $gallery,
            'title' => "Редактировать галерею: {$gallery->title}",
        ]);
    }

    // Обновление
    public function update(Request $request, Gallery $gallery): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:grid,slideshow,slider,switcher',
            'settings' => 'nullable|array',
            'status' => 'boolean',
        ]);

        $gallery->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Галерея обновлена',
        ]);
    }

    // Обновление изображения
    public function updateImage(Request $request, Gallery $gallery, $imageId): JsonResponse
    {
        $image = $gallery->images()->findOrFail($imageId);

        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'alt_text' => 'nullable|string|max:255',
            'link' => 'nullable|url',
        ]);

        $image->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Изображение обновлено',
        ]);
    }

    // Удаление
    public function destroy(Gallery $gallery): JsonResponse
    {
        $gallery->delete();

        return response()->json([
            'success' => true,
            'message' => 'Галерея удалена',
        ]);
    }

    // Загрузка изображения
    public function uploadImage(Request $request, Gallery $gallery): JsonResponse
    {
        $request->validate([
            'image' => 'required|image|max:10240',
            'title' => 'nullable|string|max:255',
        ]);

        $file = $request->file('image');
        $path = $file->store('galleries/' . $gallery->id, 'public');

        $image = $gallery->images()->create([
            'image_path' => '/storage/' . $path,
            'title' => $request->title ?? $file->getClientOriginalName(),
            'ordering' => $gallery->images()->count() + 1,
            'status' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Изображение загружено',
            'image' => $image,
        ]);
    }

    // Удаление изображения
    public function deleteImage(Gallery $gallery, $imageId): JsonResponse
    {
        $image = $gallery->images()->findOrFail($imageId);

        $path = str_replace('/storage/', '', $image->image_path);
        $fullPath = storage_path('app/public/' . $path);
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }

        $image->delete();

        return response()->json([
            'success' => true,
            'message' => 'Изображение удалено',
        ]);
    }

    // Получение галереи для публичной части
    public function show(Gallery $gallery): JsonResponse
    {
        if (!$gallery->status) {
            return response()->json(['message' => 'Gallery not published'], 404);
        }

        $gallery->load('images');
        return response()->json($gallery);
    }
}
