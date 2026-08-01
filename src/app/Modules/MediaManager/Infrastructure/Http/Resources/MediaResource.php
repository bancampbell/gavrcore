<?php

namespace Modules\MediaManager\Infrastructure\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MediaResource extends JsonResource
{
    public function toArray($request): array
    {
        $size = $this->resource->getSize();
        $createdAt = $this->resource->getCreatedAt();
        $name = $this->resource->getName();

        return [
            'id' => $this->resource->getId(),
            'name' => $name,
            'path' => $this->resource->getPath()->toString(),
            'type' => $this->resource->getType(),
            'size' => $size,
            'size_formatted' => $this->formatSize($size),
            'mime_type' => $this->resource->getMimeType(),
            'parent_id' => $this->resource->getParentId(),
            'created_at' => $createdAt,
            'date_formatted' => $createdAt ? date('d.m.Y H:i', $createdAt) : null,
            'icon' => $this->getIcon($name),
        ];
    }

    private function formatSize(?int $bytes): string
    {
        if ($bytes === null || $bytes === 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $i = (int) floor(log($bytes, 1024));
        $size = $bytes / pow(1024, $i);

        return round($size, 1) . ' ' . $units[$i];
    }

    private function getIcon(string $filename): string
    {
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        $icons = [
            'pdf' => '📄',
            'jpg' => '🖼️', 'jpeg' => '🖼️', 'png' => '🖼️', 'gif' => '🖼️',
            'webp' => '🖼️', 'svg' => '🖼️',
            'xlsx' => '📊', 'xls' => '📊',
            'doc' => '📃', 'docx' => '📃', 'txt' => '📝',
            'zip' => '🗜️', 'rar' => '🗜️', '7z' => '🗜️',
            'mp4' => '🎬', 'webm' => '🎬', 'mov' => '🎬',
        ];

        return $icons[strtolower($ext)] ?? '📄';
    }
}
