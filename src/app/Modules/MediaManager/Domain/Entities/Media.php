<?php

namespace Modules\MediaManager\Domain\Entities;

use Modules\MediaManager\Domain\ValueObjects\MediaPath;

class Media implements \JsonSerializable
{
    public function __construct(
        private ?int $id,
        private string $name,
        private MediaPath $path,
        private ?string $type,
        private ?int $size,
        private ?string $mimeType,
        private ?int $parentId,
        private ?int $createdAt,
        private ?int $updatedAt,
    ) {}

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPath(): MediaPath
    {
        return $this->path;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function getParentId(): ?int
    {
        return $this->parentId;
    }

    public function getCreatedAt(): ?int
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?int
    {
        return $this->updatedAt;
    }

    public function isFolder(): bool
    {
        return $this->type === 'folder';
    }

    public function isFile(): bool
    {
        return $this->type === 'file';
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'path' => $this->path->toString(),
            'type' => $this->type,
            'size' => $this->size,
            'mime_type' => $this->mimeType,
            'parent_id' => $this->parentId,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
