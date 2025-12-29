<?php

namespace Fiachehr\LaravelEav\Domain\Entities;

class AttributeGroup
{
    /**
     * @param int|null $id
     * @param string $title
     * @param string $slug
     * @param bool $isActive
     * @param string $language
     * @param int|null $moduleId
     * @param array<int> $attributeIds
     */
    public function __construct(
        public readonly ?int $id,
        public readonly string $title,
        public readonly string $slug,
        public readonly bool $isActive,
        public readonly string $language,
        public readonly ?int $moduleId = null,
        public readonly array $attributeIds = [],
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'is_active' => $this->isActive,
            'language' => $this->language,
            'module_id' => $this->moduleId,
            'attribute_ids' => $this->attributeIds,
        ];
    }
}


