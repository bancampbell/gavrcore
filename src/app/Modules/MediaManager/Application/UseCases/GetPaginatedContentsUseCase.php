<?php

namespace Modules\MediaManager\Application\UseCases;

use Modules\MediaManager\Application\DTO\PaginatedContentsData;
use Modules\MediaManager\Domain\Repositories\MediaRepositoryInterface;

class GetPaginatedContentsUseCase
{
    public function __construct(
        private MediaRepositoryInterface $repository
    ) {}

    public function execute(PaginatedContentsData $data): array
    {
        return $this->repository->getPaginatedContents(
            $data->path,
            $data->page,
            $data->perPage,
            $data->sort,
            $data->search
        );
    }
}
