<?php

namespace Fiachehr\LaravelEav\Infrastructure\Repositories;

use Fiachehr\LaravelEav\Domain\Entities\AttributeValue;
use Fiachehr\LaravelEav\Domain\Repositories\AttributeValueRepositoryInterface;
use Fiachehr\LaravelEav\Infrastructure\Persistence\Eloquent\EloquentAttributeValue;
use Illuminate\Support\Collection;

class EloquentAttributeValueRepository implements AttributeValueRepositoryInterface
{
    public function findById(int $id): ?AttributeValue
    {
        $model = EloquentAttributeValue::find($id);
        return $model ? $this->toEntity($model) : null;
    }

    public function findByAttributable(string $attributableType, int $attributableId): Collection
    {
        return EloquentAttributeValue::where('attributable_type', $attributableType)
            ->where('attributable_id', $attributableId)
            ->get()
            ->map(fn($model) => $this->toEntity($model));
    }

    public function findByAttribute(int $attributeId, string $attributableType, int $attributableId): ?AttributeValue
    {
        $model = EloquentAttributeValue::where('attribute_id', $attributeId)
            ->where('attributable_type', $attributableType)
            ->where('attributable_id', $attributableId)
            ->first();

        return $model ? $this->toEntity($model) : null;
    }

    public function create(AttributeValue $value): AttributeValue
    {
        $model = EloquentAttributeValue::create([
            'attributable_type' => $value->attributableType,
            'attributable_id' => $value->attributableId,
            'attribute_id' => $value->attributeId,
            'value' => $value->value,
        ]);

        return $this->toEntity($model);
    }

    public function update(AttributeValue $value): AttributeValue
    {
        $model = EloquentAttributeValue::findOrFail($value->id);
        $model->update([
            'value' => $value->value,
        ]);

        return $this->toEntity($model->fresh());
    }

    public function delete(int $id): bool
    {
        return EloquentAttributeValue::destroy($id) > 0;
    }

    public function syncForAttributable(string $attributableType, int $attributableId, array $attributeValues): void
    {
        // Delete existing values
        $this->deleteForAttributable($attributableType, $attributableId);

        // Create new values
        foreach ($attributeValues as $attributeId => $value) {
            EloquentAttributeValue::create([
                'attributable_type' => $attributableType,
                'attributable_id' => $attributableId,
                'attribute_id' => $attributeId,
                'value' => $value,
            ]);
        }
    }

    public function deleteForAttributable(string $attributableType, int $attributableId): void
    {
        EloquentAttributeValue::where('attributable_type', $attributableType)
            ->where('attributable_id', $attributableId)
            ->delete();
    }

    private function toEntity(EloquentAttributeValue $model): AttributeValue
    {
        return new AttributeValue(
            id: $model->id,
            attributeId: $model->attribute_id,
            attributableType: $model->attributable_type,
            attributableId: $model->attributable_id,
            value: $model->value,
        );
    }
}


