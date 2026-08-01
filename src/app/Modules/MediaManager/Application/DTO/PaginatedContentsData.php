<?php

namespace Modules\MediaManager\Application\DTO;

class PaginatedContentsData
{
    public function __construct(
        public readonly string $path,
        public readonly int $page = 1,
        public readonly int $perPage = 20,
        public readonly string $sort = 'name_asc',
        public readonly ?string $search = null,
    ) {}
}
