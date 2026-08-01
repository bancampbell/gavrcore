<?php

namespace Modules\MediaManager\Infrastructure\Repositories;

use Modules\MediaManager\Domain\Entities\Media;
use Modules\MediaManager\Domain\Repositories\MediaRepositoryInterface;
use Modules\MediaManager\Domain\ValueObjects\MediaPath;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\File;
use League\Flysystem\DirectoryAttributes;
use League\Flysystem\FileAttributes;

class MediaRepository implements MediaRepositoryInterface
{
    protected string $disk = 'public';
    protected string $basePath = 'uploads';
    protected string $copySuffix = '_copy';
    protected int $maxDepth = 10;

    public function getRootFolders(): array
    {
        return Cache::remember('media:tree', 300, function () {
            $allFolders = [];
            $this->scanFoldersRecursive($this->basePath, '', $allFolders);

            // Кэшируем DTO (plain arrays), а не Entity-объекты
            return array_map(fn (Media $media) => $media->toArray(), $allFolders);
        });
    }

    private function scanFoldersRecursive(string $path, string $relativePath, array &$result, int $depth = 0): void
    {
        if ($depth >= $this->maxDepth) {
            return;
        }

        $directories = Storage::disk($this->disk)->directories($path);

        foreach ($directories as $dir) {
            $name = basename($dir);
            $currentRelativePath = $relativePath ? $relativePath . '/' . $name : $name;

            $result[] = $this->createMediaFromPath($dir, 'folder');
            $this->scanFoldersRecursive($dir, $currentRelativePath, $result, $depth + 1);
        }
    }

    public function getContents(string $path): array
    {
        $fullPath = $this->resolvePath($path);
        $contents = [];

        $directories = Storage::disk($this->disk)->directories($fullPath);
        $files = Storage::disk($this->disk)->files($fullPath);

        foreach ($directories as $dir) {
            $contents[] = $this->createMediaFromPath($dir, 'folder');
        }

        foreach ($files as $file) {
            $contents[] = $this->createMediaFromPath($file, 'file');
        }

        usort($contents, function ($a, $b) {
            if ($a->getType() === $b->getType()) {
                return strcmp($a->getName(), $b->getName());
            }
            return $a->getType() === 'folder' ? -1 : 1;
        });

        return $contents;
    }

    public function getPaginatedContents(string $path, int $page, int $perPage, string $sort, ?string $search): array
    {
        $fullPath = $this->resolvePath($path);

        $allItems = [];
        $listing = Storage::disk($this->disk)->listContents($fullPath, false);

        foreach ($listing as $item) {
            $type = $item instanceof DirectoryAttributes ? 'folder' : 'file';
            $allItems[] = $this->createMediaFromPath($item->path(), $type);
        }

        if ($search) {
            $allItems = array_filter($allItems, function ($item) use ($search) {
                return stripos($item->getName(), $search) !== false;
            });
        }

        usort($allItems, function ($a, $b) use ($sort) {
            return match($sort) {
                'name_asc' => strcmp($a->getName(), $b->getName()),
                'name_desc' => strcmp($b->getName(), $a->getName()),
                'type_asc' => strcmp($a->getType(), $b->getType()),
                'type_desc' => strcmp($b->getType(), $a->getType()),
                default => strcmp($a->getName(), $b->getName()),
            };
        });

        $total = count($allItems);
        $offset = ($page - 1) * $perPage;
        $paginated = array_slice($allItems, $offset, $perPage);

        return [
            'data' => $paginated,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'last_page' => ceil($total / $perPage),
        ];
    }

    public function folderExists(string $path): bool
    {
        $fullPath = $this->resolvePath($path);
        return Storage::disk($this->disk)->exists($fullPath) || Storage::disk($this->disk)->directoryExists($fullPath);
    }

    public function exists(string $path): bool
    {
        $fullPath = $this->resolvePath($path);
        return Storage::disk($this->disk)->exists($fullPath) || Storage::disk($this->disk)->directoryExists($fullPath . '/');
    }

    public function createFolder(string $name, string $path): void
    {
        $fullPath = $this->resolvePath($path) . '/' . $name;

        if (Storage::disk($this->disk)->exists($fullPath)) {
            throw new \RuntimeException('Folder already exists');
        }

        if (!Storage::disk($this->disk)->makeDirectory($fullPath)) {
            throw new \RuntimeException('Failed to create folder');
        }

        Cache::forget('media:tree');
    }

    public function rename(string $oldPath, string $newName): void
    {
        $fullOldPath = $this->resolvePath($oldPath);
        $dirname = dirname($fullOldPath);
        $newPath = $dirname . '/' . $newName;

        if (!Storage::disk($this->disk)->exists($fullOldPath)) {
            throw new \RuntimeException('File or folder not found');
        }

        if (Storage::disk($this->disk)->exists($newPath)) {
            throw new \RuntimeException('Item with this name already exists');
        }

        if (!Storage::disk($this->disk)->move($fullOldPath, $newPath)) {
            throw new \RuntimeException('Failed to rename');
        }

        Cache::forget('media:tree');
    }

    public function delete(string $path): void
    {
        $fullPath = $this->resolvePath($path);

        if (!Storage::disk($this->disk)->exists($fullPath) && !Storage::disk($this->disk)->exists($fullPath . '/')) {
            throw new \RuntimeException('File or folder not found');
        }

        if (Storage::disk($this->disk)->directoryExists($fullPath . '/')) {
            if (!Storage::disk($this->disk)->deleteDirectory($fullPath . '/')) {
                throw new \RuntimeException('Failed to delete folder');
            }
            Cache::forget('media:tree');
            return;
        }

        if (!Storage::disk($this->disk)->delete($fullPath)) {
            throw new \RuntimeException('Failed to delete file');
        }

        Cache::forget('media:tree');
    }

    public function copy(string $path): void
    {
        $fullPath = $this->resolvePath($path);

        if (!Storage::disk($this->disk)->exists($fullPath) && !Storage::disk($this->disk)->exists($fullPath . '/')) {
            throw new \RuntimeException('File or folder not found');
        }

        $dirname = dirname($fullPath);
        $basename = basename($fullPath);
        $extension = pathinfo($basename, PATHINFO_EXTENSION);
        $nameWithoutExt = pathinfo($basename, PATHINFO_FILENAME);

        $counter = 1;
        $newBasename = $basename;
        while (Storage::disk($this->disk)->exists($dirname . '/' . $newBasename)) {
            $newBasename = $nameWithoutExt . $this->copySuffix . '_' . $counter . ($extension ? '.' . $extension : '');
            $counter++;
        }
        $newPath = $dirname . '/' . $newBasename;

        if (Storage::disk($this->disk)->directoryExists($fullPath . '/')) {
            $this->copyDirectory($fullPath . '/', $newPath);
            Cache::forget('media:tree');
            return;
        }

        if (!Storage::disk($this->disk)->copy($fullPath, $newPath)) {
            throw new \RuntimeException('Failed to copy file');
        }

        Cache::forget('media:tree');
    }

    private function copyDirectory(string $source, string $destination): void
    {
        Storage::disk($this->disk)->makeDirectory($destination);

        $items = Storage::disk($this->disk)->allFiles($source);
        foreach ($items as $item) {
            $relativePath = substr($item, strlen($source));
            $destPath = $destination . '/' . $relativePath;
            Storage::disk($this->disk)->makeDirectory(dirname($destPath));
            Storage::disk($this->disk)->copy($item, $destPath);
        }
    }

    public function uploadFromPaths(array $filePaths, string $path): array
    {
        $fullPath = $this->resolvePath($path);
        $uploadedNames = [];

        if (!Storage::disk($this->disk)->exists($fullPath)) {
            Storage::disk($this->disk)->makeDirectory($fullPath);
        }

        foreach ($filePaths as $filePath) {
            $file = new File($filePath);
            $fileName = $file->getFilename();
            $destPath = $fullPath . '/' . $fileName;

            $counter = 1;
            $nameWithoutExt = pathinfo($fileName, PATHINFO_FILENAME);
            $extension = pathinfo($fileName, PATHINFO_EXTENSION);
            while (Storage::disk($this->disk)->exists($destPath)) {
                $newName = $nameWithoutExt . '_' . $counter . ($extension ? '.' . $extension : '');
                $destPath = $fullPath . '/' . $newName;
                $counter++;
            }

            Storage::disk($this->disk)->putFileAs($fullPath, $file, basename($destPath));
            $uploadedNames[] = basename($destPath);
        }

        Cache::forget('media:tree');
        return $uploadedNames;
    }

    protected function resolvePath(string $path): string
    {
        $path = trim($path, '/');
        $fullPath = $this->basePath . ($path ? '/' . $path : '');

        $segments = explode('/', $fullPath);
        $resolved = [];

        foreach ($segments as $segment) {
            if ($segment === '' || $segment === '.') {
                continue;
            }
            if ($segment === '..') {
                array_pop($resolved);
                continue;
            }
            $resolved[] = $segment;
        }

        $resolvedPath = implode('/', $resolved);

        if (!str_starts_with($resolvedPath, $this->basePath)) {
            throw new \RuntimeException('Path traversal detected');
        }

        return $resolvedPath;
    }

    protected function createMediaFromPath(string $path, ?string $forcedType = null): Media
    {
        $relativePath = str_replace($this->basePath . '/', '', $path);
        $name = basename($path);
        $isDir = Storage::disk($this->disk)->directoryExists($path);

        $type = $forcedType ?? ($isDir ? 'folder' : 'file');

        $size = null;
        $mimeType = null;
        $modified = null;

        if ($type === 'file') {
            $size = Storage::disk($this->disk)->size($path);
            $mimeType = Storage::disk($this->disk)->mimeType($path);
            $modified = Storage::disk($this->disk)->lastModified($path);
        }

        return new Media(
            id: null,
            name: $name,
            path: new MediaPath($relativePath),
            type: $type,
            size: $size,
            mimeType: $mimeType,
            parentId: null,
            createdAt: $modified,
            updatedAt: $modified,
        );
    }
}
