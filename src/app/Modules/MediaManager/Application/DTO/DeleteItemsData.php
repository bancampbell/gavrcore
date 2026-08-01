<?php

namespace Modules\MediaManager\Application\DTO;

class DeleteItemsData
{
    public function __construct(
        public readonly array $paths,
    ) {}
}
