<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MediaController extends Controller
{
    protected string $basePath;

    public function __construct()
    {
        $this->basePath = storage_path('app/public/uploads');
    }

    public function index(): Response
    {
        return Inertia::render('Admin/MediaManager/Index', [
            'user' => auth()->user(),
            'title' => 'Медиа-менеджер',
        ]);
    }

    public function getContents(Request $request): JsonResponse
    {
        $path = $request->get('path', '');
        $fullPath = $this->basePath.($path ? '/'.$path : '');

        if (! is_dir($fullPath)) {
            return response()->json([]);
        }

        $items = scandir($fullPath);
        $contents = [];

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $itemPath = $fullPath.'/'.$item;
            $isDir = is_dir($itemPath);

            $contents[] = [
                'name' => $item,
                'path' => $path ? $path.'/'.$item : $item,
                'type' => $isDir ? 'folder' : 'file',
                'size' => $isDir ? null : filesize($itemPath),
                'mime_type' => $isDir ? null : mime_content_type($itemPath),
                'modified' => filemtime($itemPath),
            ];
        }

        usort($contents, function ($a, $b) {
            if ($a['type'] === $b['type']) {
                return strcmp($a['name'], $b['name']);
            }

            return $a['type'] === 'folder' ? -1 : 1;
        });

        return response()->json($contents);
    }

    public function createFolder(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255|regex:/^[a-zA-Zа-яА-Я0-9_\-]+$/u',
            'path' => 'nullable|string',
        ]);

        $path = $request->get('path', '');
        $folderPath = $this->basePath.($path ? '/'.$path.'/'.$request->name : '/'.$request->name);

        if (is_dir($folderPath)) {
            return response()->json(['message' => 'Папка уже существует'], 400);
        }

        if (! mkdir($folderPath, 0777, true)) {
            return response()->json(['message' => 'Не удалось создать папку'], 500);
        }

        return response()->json(['message' => 'Папка создана', 'success' => true]);
    }

    public function getFolders(Request $request): JsonResponse
    {
        $folders = $this->scanFoldersRecursive($this->basePath);

        return response()->json($folders);
    }

    public function renameItem(Request $request): JsonResponse
    {
        $request->validate([
            'old_path' => 'required|string',
            'new_name' => 'required|string|max:255|regex:/^[a-zA-Zа-яА-Я0-9_\-\.]+$/u',
        ]);

        $oldPath = $request->old_path;
        $newName = $request->new_name;

        $fullOldPath = $this->basePath.'/'.$oldPath;
        $dirname = dirname($fullOldPath);
        $newPath = $dirname.'/'.$newName;

        if (! file_exists($fullOldPath)) {
            return response()->json(['message' => 'Файл или папка не найдены'], 404);
        }

        if (file_exists($newPath)) {
            return response()->json(['message' => 'Элемент с таким именем уже существует'], 400);
        }

        if (! rename($fullOldPath, $newPath)) {
            return response()->json(['message' => 'Не удалось переименовать'], 500);
        }

        return response()->json(['message' => 'Переименовано успешно', 'success' => true]);
    }

    public function deleteItem(Request $request): JsonResponse
    {
        $request->validate([
            'path' => 'required|string',
        ]);

        $path = $request->path;
        $fullPath = $this->basePath.'/'.$path;

        if (! file_exists($fullPath)) {
            return response()->json(['message' => 'Файл или папка не найдены'], 404);
        }

        if (is_dir($fullPath)) {
            $this->deleteDirectory($fullPath);
        } else {
            unlink($fullPath);
        }

        return response()->json(['message' => 'Удалено успешно', 'success' => true]);
    }

    public function deleteMultiple(Request $request): JsonResponse
    {
        $request->validate([
            'paths' => 'required|array',
            'paths.*' => 'required|string',
        ]);

        $paths = $request->input('paths');
        $deleted = [];

        foreach ($paths as $path) {
            $fullPath = $this->basePath.'/'.$path;

            if (! file_exists($fullPath)) {
                continue;
            }

            if (is_dir($fullPath)) {
                $this->deleteDirectory($fullPath);
            } else {
                unlink($fullPath);
            }
            $deleted[] = $path;
        }

        return response()->json([
            'message' => 'Удалено элементов: '.count($deleted),
            'success' => true,
            'deleted' => $deleted
        ]);
    }

    public function copyItem(Request $request): JsonResponse
    {
        $request->validate([
            'path' => 'required|string',
        ]);

        $path = $request->path;
        $fullPath = $this->basePath.'/'.$path;

        if (! file_exists($fullPath)) {
            return response()->json(['message' => 'Файл или папка не найдены'], 404);
        }

        $dirname = dirname($fullPath);
        $basename = basename($fullPath);
        $extension = pathinfo($basename, PATHINFO_EXTENSION);
        $nameWithoutExt = pathinfo($basename, PATHINFO_FILENAME);

        $counter = 1;
        $newBasename = $basename;
        while (file_exists($dirname.'/'.$newBasename)) {
            $newBasename = $nameWithoutExt.'_копия_'.$counter.($extension ? '.'.$extension : '');
            $counter++;
        }
        $newPath = $dirname.'/'.$newBasename;

        if (is_dir($fullPath)) {
            $this->copyDirectory($fullPath, $newPath);
        } else {
            copy($fullPath, $newPath);
        }

        return response()->json(['message' => 'Скопировано успешно', 'success' => true]);
    }

    public function uploadFile(Request $request): JsonResponse
    {
        $request->validate([
            'files.*' => 'required|file|max:102400',
            'path' => 'nullable|string',
        ]);

        $path = $request->get('path', '');
        $uploadPath = $this->basePath.($path ? '/'.$path : '');

        if (! is_dir($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }

        $uploaded = [];
        foreach ($request->file('files') as $file) {
            $fileName = $file->getClientOriginalName();
            $file->move($uploadPath, $fileName);
            $uploaded[] = $fileName;
        }

        return response()->json(['message' => 'Загружено файлов: '.count($uploaded), 'files' => $uploaded]);
    }

    private function scanFoldersRecursive(string $path, string $relativePath = ''): array
    {
        $folders = [];
        $items = scandir($path);

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $fullPath = $path.'/'.$item;
            if (is_dir($fullPath)) {
                $currentPath = $relativePath ? $relativePath.'/'.$item : $item;
                $folders[] = [
                    'name' => $item,
                    'path' => $currentPath,
                ];

                $subFolders = $this->scanFoldersRecursive($fullPath, $currentPath);
                $folders = array_merge($folders, $subFolders);
            }
        }

        return $folders;
    }

    private function deleteDirectory(string $dir): void
    {
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir.'/'.$file;
            is_dir($path) ? $this->deleteDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }

    private function copyDirectory(string $source, string $dest): void
    {
        if (! is_dir($dest)) {
            mkdir($dest, 0777, true);
        }

        $files = array_diff(scandir($source), ['.', '..']);
        foreach ($files as $file) {
            $srcPath = $source.'/'.$file;
            $destPath = $dest.'/'.$file;

            if (is_dir($srcPath)) {
                $this->copyDirectory($srcPath, $destPath);
            } else {
                copy($srcPath, $destPath);
            }
        }
    }
}
