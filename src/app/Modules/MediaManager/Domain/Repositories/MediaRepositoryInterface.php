<?php

namespace Modules\MediaManager\Domain\Repositories;

interface MediaRepositoryInterface
{
    public function getRootFolders(): array;

    public function getContents(string $path): array;

    public function folderExists(string $path): bool;

    public function exists(string $path): bool;

    public function createFolder(string $name, string $path): void;

    public function rename(string $oldPath, string $newName): void;

    public function delete(string $path): void;

    public function copy(string $path): void;

    public function uploadFromPaths(array $filePaths, string $path): array;

    public function getPaginatedContents(string $path, int $page, int $perPage, string $sort, ?string $search): array;
}
