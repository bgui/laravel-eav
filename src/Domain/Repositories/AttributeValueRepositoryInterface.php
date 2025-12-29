<?php

namespace Fiachehr\LaravelEav\Domain\Repositories;

use Fiachehr\LaravelEav\Domain\Entities\AttributeValue;
use Illuminate\Support\Collection;

interface AttributeValueRepositoryInterface
{
    public function findById(int $id): ?AttributeValue;

    public function findByAttributable(string $attributableType, int $attributableId): Collection;

    public function findByAttribute(int $attributeId, string $attributableType, int $attributableId): ?AttributeValue;

    public function create(AttributeValue $value): AttributeValue;

    public function update(AttributeValue $value): AttributeValue;

    public function delete(int $id): bool;

    public function syncForAttributable(string $attributableType, int $attributableId, array $attributeValues): void;

    public function deleteForAttributable(string $attributableType, int $attributableId): void;
}


