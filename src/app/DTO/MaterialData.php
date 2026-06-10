<?php

namespace App\DTO;

class MaterialData
{
    public function __construct(
        public readonly string $title,
        public readonly string $alias,
        public readonly ?string $content,
        public readonly int $category_id,
        public readonly int $user_id,
        public readonly string $state,
        public readonly string $access,
        public readonly ?string $published_at,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            title: $data['title'],
            alias: $data['alias'] ?? \Illuminate\Support\Str::slug($data['title']),
            content: $data['content'] ?? null,
            category_id: (int) $data['category_id'],
            user_id: (int) ($data['user_id'] ?? auth()->id()),
            state: $data['state'] ?? 'draft',
            access: $data['access'] ?? 'public',
            published_at: $data['published_at'] ?? null,
        );
    }

    /**
     * @return array<string, string|int|null>
     */
    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'alias' => $this->alias,
            'content' => $this->content,
            'category_id' => $this->category_id,
            'user_id' => $this->user_id,
            'state' => $this->state,
            'access' => $this->access,
            'published_at' => $this->published_at,
        ];
    }
}
