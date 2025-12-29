<?php

namespace Fiachehr\LaravelEav\Infrastructure\Repositories;

use Fiachehr\LaravelEav\Domain\Entities\AttributeGroup;
use Fiachehr\LaravelEav\Domain\Repositories\AttributeGroupRepositoryInterface;
use Fiachehr\LaravelEav\Infrastructure\Persistence\Eloquent\EloquentAttributeGroup;
use Illuminate\Support\Collection;

class EloquentAttributeGroupRepository implements AttributeGroupRepositoryInterface
{
    public function findById(int $id): ?AttributeGroup
    {
        $model = EloquentAttributeGroup::with('attributes')->find($id);
        return $model ? $this->toEntity($model) : null;
    }

    public function findBySlug(string $slug): ?AttributeGroup
    {
        $model = EloquentAttributeGroup::with('attributes')->where('slug', $slug)->first();
        return $model ? $this->toEntity($model) : null;
    }

    public function findAll(): Collection
    {
        return EloquentAttributeGroup::with('attributes')
            ->get()
            ->map(fn($model) => $this->toEntity($model));
    }

    public function findActive(): Collection
    {
        return EloquentAttributeGroup::with('attributes')
            ->where('is_active', true)
            ->get()
            ->map(fn($model) => $this->toEntity($model));
    }

    public function findByLanguage(string $language): Collection
    {
        return EloquentAttributeGroup::with('attributes')
            ->forLanguage($language)
            ->get()
            ->map(fn($model) => $this->toEntity($model));
    }

    public function findByCurrentLanguage(): Collection
    {
        return EloquentAttributeGroup::with('attributes')
            ->forCurrentLanguage()
            ->get()
            ->map(fn($model) => $this->toEntity($model));
    }

    public function findByLanguages(array $languages): Collection
    {
        return EloquentAttributeGroup::with('attributes')
            ->forLanguages($languages)
            ->get()
            ->map(fn($model) => $this->toEntity($model));
    }

    public function create(AttributeGroup $group): AttributeGroup
    {
        $model = EloquentAttributeGroup::create([
            'title' => $group->title,
            'slug' => $group->slug,
            'is_active' => $group->isActive,
            'language' => $group->language,
            'module_id' => $group->moduleId,
        ]);

        if (!empty($group->attributeIds)) {
            $model->attributes()->sync($group->attributeIds);
        }

        return $this->toEntity($model->load('attributes'));
    }

    public function update(AttributeGroup $group): AttributeGroup
    {
        $model = EloquentAttributeGroup::findOrFail($group->id);
        $model->update([
            'title' => $group->title,
            'slug' => $group->slug,
            'is_active' => $group->isActive,
            'language' => $group->language,
            'module_id' => $group->moduleId,
        ]);

        if (isset($group->attributeIds)) {
            $model->attributes()->sync($group->attributeIds);
        }

        return $this->toEntity($model->fresh('attributes'));
    }

    public function delete(int $id): bool
    {
        return EloquentAttributeGroup::destroy($id) > 0;
    }

    public function syncAttributes(int $groupId, array $attributeIds): void
    {
        $group = EloquentAttributeGroup::findOrFail($groupId);
        $group->attributes()->sync($attributeIds);
    }

    private function toEntity(EloquentAttributeGroup $model): AttributeGroup
    {
        return new AttributeGroup(
            id: $model->id,
            title: $model->title,
            slug: $model->slug,
            isActive: $model->is_active,
            language: $model->language,
            moduleId: $model->module_id,
            attributeIds: $model->attributes->pluck('id')->toArray(),
        );
    }
}


