<?php

namespace Fiachehr\LaravelEav\Domain\Repositories;

use Fiachehr\LaravelEav\Domain\Entities\AttributeGroup;
use Illuminate\Support\Collection;

interface AttributeGroupRepositoryInterface
{
    public function findById(int $id): ?AttributeGroup;

    public function findBySlug(string $slug): ?AttributeGroup;

    public function findAll(): Collection;

    public function findActive(): Collection;

    public function findByLanguage(string $language): Collection;

    public function findByCurrentLanguage(): Collection;

    public function findByLanguages(array $languages): Collection;

    public function create(AttributeGroup $group): AttributeGroup;

    public function update(AttributeGroup $group): AttributeGroup;

    public function delete(int $id): bool;

    public function syncAttributes(int $groupId, array $attributeIds): void;
}


