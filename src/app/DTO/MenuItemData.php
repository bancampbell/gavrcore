<?php

namespace App\DTO;

class MenuItemData
{
    public function __construct(
        public ?int $id,
        public int $menu_type_id,
        public ?int $parent_id,
        public string $title,
        public string $alias,
        public string $link_type,
        public ?string $link_value,
        public string $target,
        public int $ordering,
        public bool $status,
        public string $access,
        public string $language,
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            menu_type_id: $data['menu_type_id'],
            parent_id: $data['parent_id'] ?? null,
            title: $data['title'],
            alias: $data['alias'],
            link_type: $data['link_type'] ?? 'url',
            link_value: $data['link_value'] ?? null,
            target: $data['target'] ?? '_self',
            ordering: $data['ordering'] ?? 0,
            status: $data['status'] ?? true,
            access: $data['access'] ?? 'all',
            language: $data['language'] ?? 'all',
        );
    }

    /**
     * @return array<string, int|string|bool|null>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'menu_type_id' => $this->menu_type_id,
            'parent_id' => $this->parent_id,
            'title' => $this->title,
            'alias' => $this->alias,
            'link_type' => $this->link_type,
            'link_value' => $this->link_value,
            'target' => $this->target,
            'ordering' => $this->ordering,
            'status' => $this->status,
            'access' => $this->access,
            'language' => $this->language,
        ];
    }
}
