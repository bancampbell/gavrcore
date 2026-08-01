<?php

namespace Modules\MediaManager\Application\UseCases;

use Modules\MediaManager\Application\DTO\CreateFolderData;
use Modules\MediaManager\Domain\Repositories\MediaRepositoryInterface;

class CreateFolderUseCase
{
    public function __construct(
        private MediaRepositoryInterface $repository,
        private int $maxDepth = 10,
    ) {}

    public function execute(CreateFolderData $data): void
    {
        $fullPath = $data->path ? $data->path . '/' . $data->name : $data->name;

        $depth = count(array_filter(explode('/', trim($fullPath, '/'))));
        if ($depth > $this->maxDepth) {
            throw new \RuntimeException('Превышена максимальная глубина вложенности');
        }

        if ($this->repository->folderExists($fullPath)) {
            throw new \RuntimeException('Папка с таким именем уже существует');
        }

        $this->repository->createFolder($data->name, $data->path);
    }
}
