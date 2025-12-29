<?php

namespace Fiachehr\LaravelEav\Application\DTOs;

use Fiachehr\LaravelEav\Domain\Enums\AttributeType;

class CreateAttributeDTO
{
    public function __construct(
        public readonly string $logicalId,
        public readonly string $title,
        public readonly string $slug,
        public readonly AttributeType $type,
        public readonly ?string $description,
        public readonly array $values,
        public readonly array $validations,
        public readonly bool $isActive,
        public readonly string $language,
    ) {}

    public static function fromArray(array $data): self
    {
        // Safely handle type conversion
        $typeValue = $data['type'] ?? 0;
        try {
            $type = AttributeType::from((int)$typeValue);
        } catch (\ValueError $e) {
            // If invalid type, default to TEXT
            \Illuminate\Support\Facades\Log::warning('Invalid AttributeType value: ' . $typeValue . ', defaulting to TEXT', [
                'data' => $data
            ]);
            $type = AttributeType::TEXT;
        }
        
        return new self(
            logicalId: $data['logical_id'] ?? \Illuminate\Support\Str::uuid()->toString(),
            title: $data['title'] ?? '',
            slug: $data['slug'] ?? \Illuminate\Support\Str::slug($data['title'] ?? ''),
            type: $type,
            description: $data['description'] ?? null,
            values: $data['values'] ?? [],
            validations: $data['validations'] ?? [],
            isActive: $data['is_active'] ?? true,
            language: $data['language'] ?? 'en',
        );
    }
}


