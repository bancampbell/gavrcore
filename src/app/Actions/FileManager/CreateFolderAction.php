<?php

namespace App\Actions\FileManager;

class CreateFolderAction
{
    protected string $basePath;

    public function __construct()
    {
        $this->basePath = storage_path('app/public/uploads');
    }

    public function execute(string $currentPath, string $folderName): bool
    {
        $folderPath = $this->basePath.($currentPath ? '/'.$currentPath.'/'.$folderName : '/'.$folderName);

        if (is_dir($folderPath)) {
            return false;
        }

        return mkdir($folderPath, 0777, true);
    }
}
