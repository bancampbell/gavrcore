<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MediaController extends Controller
{
    protected string $basePath;

    public function __construct()
    {
        $this->basePath = storage_path('app/public/uploads');
    }

    public function index()
    {
        return Inertia::render('Admin/MediaManager/Index', [
            'user' => auth()->user(),
        ]);
    }

    public function getContents(Request $request)
    {
        $path = $request->get('path', '');
        $fullPath = $this->basePath . ($path ? '/' . $path : '');

        if (!is_dir($fullPath)) {
            return response()->json([]);
        }

        $items = scandir($fullPath);
        $contents = [];

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $itemPath = $fullPath . '/' . $item;
            $isDir = is_dir($itemPath);

            $contents[] = [
                'name' => $item,
                'path' => $path ? $path . '/' . $item : $item,
                'type' => $isDir ? 'folder' : 'file',
                'size' => $isDir ? null : filesize($itemPath),
                'mime_type' => $isDir ? null : mime_content_type($itemPath),
            ];
        }

        // Сортируем: папки первые, потом файлы
        usort($contents, function($a, $b) {
            if ($a['type'] === $b['type']) {
                return strcmp($a['name'], $b['name']);
            }
            return $a['type'] === 'folder' ? -1 : 1;
        });

        return response()->json($contents);
    }

    public function createFolder(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|regex:/^[a-zA-Zа-яА-Я0-9_\-]+$/u',
            'path' => 'nullable|string'
        ]);

        $path = $request->get('path', '');
        $folderPath = $this->basePath . ($path ? '/' . $path . '/' . $request->name : '/' . $request->name);

        if (is_dir($folderPath)) {
            return response()->json(['message' => 'Папка уже существует'], 400);
        }

        if (!mkdir($folderPath, 0777, true)) {
            return response()->json(['message' => 'Не удалось создать папку'], 500);
        }

        return response()->json(['message' => 'Папка создана', 'success' => true]);
    }

    public function getFolders(Request $request)
    {
        $folders = $this->scanFoldersRecursive($this->basePath);
        return response()->json($folders);
    }

    private function scanFoldersRecursive(string $path, string $relativePath = ''): array
    {
        $folders = [];
        $items = scandir($path);

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $fullPath = $path . '/' . $item;
            if (is_dir($fullPath)) {
                $currentPath = $relativePath ? $relativePath . '/' . $item : $item;
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



}
