<?php

namespace Modules\MediaManager\Application\UseCases;

use Modules\MediaManager\Domain\Repositories\MediaRepositoryInterface;

class GetFoldersUseCase
{
    public function __construct(
        private MediaRepositoryInterface $repository
    ) {}

    public function execute(): array
    {
        return $this->repository->getRootFolders();
    }
}
