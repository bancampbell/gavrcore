<?php

namespace Modules\MediaManager\Application\UseCases;

use Illuminate\Support\Facades\Event;
use Modules\MediaManager\Application\DTO\OperationResult;
use Modules\MediaManager\Application\DTO\UploadFileData;
use Modules\MediaManager\Domain\Events\MediaUploaded;
use Modules\MediaManager\Domain\Repositories\MediaRepositoryInterface;

class UploadFileUseCase
{
    public function __construct(
        private MediaRepositoryInterface $repository
    ) {}

    public function execute(UploadFileData $data): OperationResult
    {
        if (! $this->repository->folderExists($data->path)) {
            throw new \RuntimeException('Папка не существует');
        }

        try {
            $uploaded = $this->repository->uploadFromPaths(
                $data->files,
                $data->path
            );

            foreach ($uploaded as $name) {
                Event::dispatch(new MediaUploaded(
                    auth()->id(),
                    $data->path ? $data->path.'/'.$name : $name,
                ));
            }

            return new OperationResult(
                success: true,
                message: 'Загружено файлов: '.count($uploaded),
                data: ['uploaded' => $uploaded],
            );
        } catch (\RuntimeException $e) {
            return new OperationResult(
                success: false,
                message: 'Ошибка загрузки: '.$e->getMessage(),
                errors: [['reason' => $e->getMessage()]],
            );
        }
    }
}
