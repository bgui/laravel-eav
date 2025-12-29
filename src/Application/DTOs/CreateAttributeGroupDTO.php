<?php

namespace Fiachehr\LaravelEav\Application\DTOs;

class CreateAttributeGroupDTO
{
    public function __construct(
        public readonly string $title,
        public readonly string $slug,
        public readonly bool $isActive,
        public readonly string $language,
        public readonly ?int $moduleId = null,
        public readonly array $attributeIds = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            title: $data['title'],
            slug: $data['slug'] ?? \Illuminate\Support\Str::slug($data['title']),
            isActive: $data['is_active'] ?? true,
            language: $data['language'] ?? 'en',
            moduleId: $data['module_id'] ?? null,
            attributeIds: $data['attribute_ids'] ?? [],
        );
    }
}


