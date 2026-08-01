<?php

namespace Modules\MediaManager\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Modules\MediaManager\Application\UseCases\GetContentsUseCase;
use Modules\MediaManager\Application\UseCases\GetFoldersUseCase;
use Modules\MediaManager\Application\UseCases\CreateFolderUseCase;
use Modules\MediaManager\Application\UseCases\RenameItemUseCase;
use Modules\MediaManager\Application\UseCases\DeleteItemUseCase;
use Modules\MediaManager\Application\UseCases\DeleteItemsUseCase;
use Modules\MediaManager\Application\UseCases\CopyItemUseCase;
use Modules\MediaManager\Application\UseCases\UploadFileUseCase;
use Modules\MediaManager\Application\UseCases\GetPaginatedContentsUseCase;
use Modules\MediaManager\Application\DTO\PaginatedContentsData;
use Modules\MediaManager\Infrastructure\Http\Requests\CreateFolderRequest;
use Modules\MediaManager\Infrastructure\Http\Requests\RenameItemRequest;
use Modules\MediaManager\Infrastructure\Http\Requests\DeleteItemRequest;
use Modules\MediaManager\Infrastructure\Http\Requests\DeleteItemsRequest;
use Modules\MediaManager\Infrastructure\Http\Requests\CopyItemRequest;
use Modules\MediaManager\Infrastructure\Http\Requests\UploadFileRequest;

class MediaController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/MediaManager/Index', [
            'user' => auth()->user(),
            'title' => 'Медиа-менеджер',
        ]);
    }

    public function getContents(Request $request, GetContentsUseCase $useCase)
    {
        $path = (string) $request->get('path', '');
        $contents = $useCase->execute($path);

        return response()->json($contents);
    }

    public function getPaginatedContents(Request $request, GetPaginatedContentsUseCase $useCase)
    {
        $data = new PaginatedContentsData(
            path: (string) $request->get('path', ''),
            page: (int) $request->get('page', 1),
            perPage: (int) $request->get('per_page', 20),
            sort: (string) $request->get('sort', 'name_asc'),
            search: $request->get('search') ? (string) $request->get('search') : null,
        );

        $result = $useCase->execute($data);

        return response()->json($result);
    }

    public function getFolders(Request $request, GetFoldersUseCase $useCase)
    {
        $folders = $useCase->execute();

        return response()->json($folders);
    }

    public function createFolder(CreateFolderRequest $request, CreateFolderUseCase $useCase)
    {
        try {
            $useCase->execute($request->toDTO());
            return response()->json(['success' => true, 'message' => 'Папка создана']);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function renameItem(RenameItemRequest $request, RenameItemUseCase $useCase)
    {
        try {
            $useCase->execute($request->toDTO());
            return response()->json(['success' => true, 'message' => 'Переименовано успешно']);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function deleteItem(DeleteItemRequest $request, DeleteItemUseCase $useCase)
    {
        try {
            $useCase->execute($request->toDTO());
            return response()->json(['success' => true, 'message' => 'Удалено успешно']);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function deleteItems(DeleteItemsRequest $request, DeleteItemsUseCase $useCase)
    {
        try {
            $result = $useCase->execute($request->toDTO());
            return response()->json([
                'success' => true,
                'message' => $result->message,
                'data' => $result->data,
            ]);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function copyItem(CopyItemRequest $request, CopyItemUseCase $useCase)
    {
        try {
            $useCase->execute($request->toDTO());
            return response()->json(['success' => true, 'message' => 'Скопировано успешно']);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function uploadFile(UploadFileRequest $request, UploadFileUseCase $useCase)
    {
        try {
            $result = $useCase->execute($request->toDTO());
            return response()->json([
                'success' => true,
                'message' => $result->message,
                'data' => $result->data,
            ]);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }
}
