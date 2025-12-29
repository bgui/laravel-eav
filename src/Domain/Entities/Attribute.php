<?php

namespace Fiachehr\LaravelEav\Domain\Entities;

use Fiachehr\LaravelEav\Domain\Enums\AttributeType;
use Fiachehr\LaravelEav\Domain\ValueObjects\AttributeValidation;

class Attribute
{
    public function __construct(
        public readonly ?int $id,
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

    public function requiresValues(): bool
    {
        return $this->type->requiresValues();
    }

    public function hasValidation(string $validationType): bool
    {
        return in_array($validationType, $this->validations);
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'logical_id' => $this->logicalId,
            'title' => $this->title,
            'slug' => $this->slug,
            'type' => $this->type->value,
            'description' => $this->description,
            'values' => $this->values,
            'validations' => $this->validations,
            'is_active' => $this->isActive,
            'language' => $this->language,
        ];
    }
}


