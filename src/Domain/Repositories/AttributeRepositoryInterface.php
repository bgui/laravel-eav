<?php

namespace Fiachehr\LaravelEav\Domain\Repositories;

use Fiachehr\LaravelEav\Domain\Entities\Attribute;
use Illuminate\Support\Collection;

interface AttributeRepositoryInterface
{
    public function findById(int $id): ?Attribute;

    public function findBySlug(string $slug): ?Attribute;

    public function findByLogicalId(string $logicalId): ?Attribute;

    public function findAll(): Collection;

    public function findActive(): Collection;

    public function findByLanguage(string $language): Collection;

    public function findByCurrentLanguage(): Collection;

    public function findByLanguages(array $languages): Collection;

    public function create(Attribute $attribute): Attribute;

    public function update(Attribute $attribute): Attribute;

    public function delete(int $id): bool;

    public function attachToGroup(int $attributeId, int $groupId): void;

    public function detachFromGroup(int $attributeId, int $groupId): void;

    public function findByGroupIds(array $groupIds): Collection;

    public function findBySlugs(array $slugs): Collection;
}


