<?php

namespace Modules\MediaManager\Application\UseCases;

use Modules\MediaManager\Domain\Repositories\MediaRepositoryInterface;

class GetContentsUseCase
{
    public function __construct(
        private MediaRepositoryInterface $repository
    ) {}

    public function execute(string $path): array
    {
        return $this->repository->getContents($path);
    }
}
