<?php

namespace Fiachehr\LaravelEav\Domain\Entities;

class AttributeValue
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $attributeId,
        public readonly string $attributableType,
        public readonly int $attributableId,
        public readonly mixed $value,
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'attribute_id' => $this->attributeId,
            'attributable_type' => $this->attributableType,
            'attributable_id' => $this->attributableId,
            'value' => $this->value,
        ];
    }
}


