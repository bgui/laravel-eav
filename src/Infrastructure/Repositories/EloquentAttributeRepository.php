<?php

namespace Fiachehr\LaravelEav\Infrastructure\Repositories;

use Fiachehr\LaravelEav\Domain\Entities\Attribute;
use Fiachehr\LaravelEav\Domain\Enums\AttributeType;
use Fiachehr\LaravelEav\Domain\Repositories\AttributeRepositoryInterface;
use Fiachehr\LaravelEav\Infrastructure\Persistence\Eloquent\EloquentAttribute;
use Illuminate\Support\Collection;

class EloquentAttributeRepository implements AttributeRepositoryInterface
{
    public function findById(int $id): ?Attribute
    {
        $model = EloquentAttribute::find($id);
        return $model ? $this->toEntity($model) : null;
    }

    public function findBySlug(string $slug): ?Attribute
    {
        $model = EloquentAttribute::where('slug', $slug)->first();
        return $model ? $this->toEntity($model) : null;
    }

    public function findByLogicalId(string $logicalId): ?Attribute
    {
        $model = EloquentAttribute::where('logical_id', $logicalId)->first();
        return $model ? $this->toEntity($model) : null;
    }

    public function findAll(): Collection
    {
        return EloquentAttribute::all()->map(fn($model) => $this->toEntity($model));
    }

    public function findActive(): Collection
    {
        return EloquentAttribute::where('is_active', true)
            ->get()
            ->map(fn($model) => $this->toEntity($model));
    }

    public function findByLanguage(string $language): Collection
    {
        return EloquentAttribute::forLanguage($language)
            ->get()
            ->map(fn($model) => $this->toEntity($model));
    }

    public function findByCurrentLanguage(): Collection
    {
        return EloquentAttribute::forCurrentLanguage()
            ->get()
            ->map(fn($model) => $this->toEntity($model));
    }

    public function findByLanguages(array $languages): Collection
    {
        return EloquentAttribute::forLanguages($languages)
            ->get()
            ->map(fn($model) => $this->toEntity($model));
    }

    public function create(Attribute $attribute): Attribute
    {
        $model = EloquentAttribute::create([
            'logical_id' => $attribute->logicalId,
            'title' => $attribute->title,
            'slug' => $attribute->slug,
            'type' => $attribute->type->value,
            'description' => $attribute->description,
            'values' => $attribute->values,
            'validations' => $attribute->validations,
            'is_active' => $attribute->isActive,
            'language' => $attribute->language,
        ]);

        return $this->toEntity($model);
    }

    public function update(Attribute $attribute): Attribute
    {
        $model = EloquentAttribute::findOrFail($attribute->id);
        $model->update([
            'title' => $attribute->title,
            'slug' => $attribute->slug,
            'type' => $attribute->type->value,
            'description' => $attribute->description,
            'values' => $attribute->values,
            'validations' => $attribute->validations,
            'is_active' => $attribute->isActive,
            'language' => $attribute->language,
        ]);

        return $this->toEntity($model->fresh());
    }

    public function delete(int $id): bool
    {
        return EloquentAttribute::destroy($id) > 0;
    }

    public function attachToGroup(int $attributeId, int $groupId): void
    {
        $attribute = EloquentAttribute::findOrFail($attributeId);
        $attribute->groups()->syncWithoutDetaching([$groupId]);
    }

    public function detachFromGroup(int $attributeId, int $groupId): void
    {
        $attribute = EloquentAttribute::findOrFail($attributeId);
        $attribute->groups()->detach($groupId);
    }

    public function findByGroupIds(array $groupIds): Collection
    {
        if (empty($groupIds)) {
            return collect([]);
        }

        return EloquentAttribute::whereHas('groups', function ($query) use ($groupIds) {
            $query->whereIn('attribute_groups.id', $groupIds);
        })
        ->get()
        ->map(fn($model) => $this->toEntity($model));
    }

    public function findBySlugs(array $slugs): Collection
    {
        if (empty($slugs)) {
            return collect([]);
        }

        return EloquentAttribute::whereIn('slug', $slugs)
            ->get()
            ->map(fn($model) => $this->toEntity($model));
    }

    private function toEntity(EloquentAttribute $model): Attribute
    {
        // Handle type - it might already be an AttributeType enum due to Eloquent casting
        $type = $model->type instanceof AttributeType 
            ? $model->type 
            : AttributeType::from($model->type);
        
        return new Attribute(
            id: $model->id,
            logicalId: $model->logical_id,
            title: $model->title,
            slug: $model->slug,
            type: $type,
            description: $model->description,
            values: $model->values ?? [],
            validations: $model->validations ?? [],
            isActive: $model->is_active,
            language: $model->language,
        );
    }
}


