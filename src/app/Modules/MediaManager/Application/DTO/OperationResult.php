<?php

namespace Modules\MediaManager\Application\DTO;

readonly class OperationResult
{
    public function __construct(
        public bool $success,
        public string $message,
        public array $data = [],
        public array $errors = [],
    ) {}
}
