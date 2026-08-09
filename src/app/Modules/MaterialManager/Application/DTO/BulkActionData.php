<?php

namespace App\Modules\MaterialManager\Application\DTO;

final readonly class BulkActionData
{
    public function __construct(
        public array $ids,
    ) {
        if (empty($this->ids)) {
            throw new \InvalidArgumentException('IDs cannot be empty');
        }
    }

    public static function fromRequest(array $data): self
    {
        $rawIds = $data['ids'] ?? [];
        $validated = [];

        foreach ($rawIds as $id) {
            $intId = filter_var($id, FILTER_VALIDATE_INT);
            if ($intId === false) {
                throw new \InvalidArgumentException('Invalid ID: ' . $id);
            }
            $validated[] = $intId;
        }

        return new self(ids: $validated);
    }

    public function count(): int
    {
        return count($this->ids);
    }
}
