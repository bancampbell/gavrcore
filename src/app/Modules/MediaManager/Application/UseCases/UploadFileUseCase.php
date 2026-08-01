<?php

namespace Modules\MediaManager\Application\UseCases;

use Modules\MediaManager\Application\DTO\UploadFileData;
use Modules\MediaManager\Application\DTO\OperationResult;
use Modules\MediaManager\Domain\Repositories\MediaRepositoryInterface;

class UploadFileUseCase
{
    public function __construct(
        private MediaRepositoryInterface $repository
    ) {}

    public function execute(UploadFileData $data): OperationResult
    {
        if (!$this->repository->folderExists($data->path)) {
            throw new \RuntimeException('Папка не существует');
        }

        try {
            $uploaded = $this->repository->uploadFromPaths(
                $data->filePaths,
                $data->path
            );

            return new OperationResult(
                success: true,
                message: 'Загружено файлов: ' . count($uploaded),
                data: ['uploaded' => $uploaded],
            );
        } catch (\RuntimeException $e) {
            return new OperationResult(
                success: false,
                message: 'Ошибка загрузки: ' . $e->getMessage(),
                errors: [['reason' => $e->getMessage()]],
            );
        }
    }
}
