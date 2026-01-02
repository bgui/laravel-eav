<?php

namespace Fiachehr\LaravelEav\Domain\Shared\Traits;

use Fiachehr\LaravelEav\Domain\Enums\AttributeType;
use Fiachehr\LaravelEav\Infrastructure\Persistence\Eloquent\EloquentAttribute;
use Fiachehr\LaravelEav\Infrastructure\Persistence\Eloquent\EloquentAttributeGroup;
use Fiachehr\LaravelEav\Infrastructure\Persistence\Eloquent\EloquentAttributeValue;
use Fiachehr\LaravelEav\Infrastructure\Query\EavQueryBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Collection;

trait HasAttributes
{
    /**
     * Get all attributes for this model.
     */
    public function eavAttributes(): MorphToMany
    {
        return $this->morphToMany(
            EloquentAttribute::class,
            'attributable',
            'attributable_attributes',
            'attributable_id',
            'attribute_id'
        )->withPivot([
            'value_text',
            'value_number',
            'value_decimal',
            'value_date',
            'value_datetime',
            'value_time',
            'value_boolean',
            'value_json',
            'value', // Keep for backward compatibility
        ]);
    }

    /**
     * Get all attribute values as Eloquent models.
     */
    public function eavAttributeValues(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(EloquentAttributeValue::class, 'attributable_id')
            ->where('attributable_type', static::class);
    }

    /**
     * Get all attribute groups for this model.
     */
    public function eavAttributeGroups(): MorphToMany
    {
        return $this->morphToMany(
            EloquentAttributeGroup::class,
            'attributable',
            'attributable_attribute_groups',
            'attributable_id',
            'attribute_group_id'
        );
    }

    /**
     * Get attribute value for a specific attribute (by ID or slug).
     * 
     * @param string|int $attribute Attribute ID or slug
     * @param string|null $locale Locale code (defaults to current locale)
     * @return mixed
     */
    public function getEavAttributeValue(string|int $attribute, ?string $locale = null): mixed
    {
        $attributeId = is_numeric($attribute)
            ? $attribute
            : EloquentAttribute::where('slug', $attribute)->value('id');

        if (!$attributeId) {
            return null;
        }

        $locale = $locale ?? app()->getLocale();
        
        $attributeValue = EloquentAttributeValue::where('attributable_type', static::class)
            ->where('attributable_id', $this->getKey())
            ->where('attribute_id', $attributeId)
            ->where('locale', $locale)
            ->with('attribute')
            ->first();

        return $attributeValue?->getActualValue();
    }

    /**
     * Get attribute value by slug.
     */
    public function getEavAttributeValueBySlug(string $slug): mixed
    {
        return $this->getEavAttributeValue($slug);
    }

    /**
     * Get all attribute values as key-value pairs.
     * Keys can be attribute IDs, slugs, or logical IDs.
     * 
     * @param string $keyType Type of key to use ('id', 'slug', 'logical_id')
     * @param string|null $locale Locale code (defaults to current locale). If null, returns all locales grouped by attribute.
     * @param bool $groupByLocale If true and locale is null, returns values grouped by locale: ['slug' => ['en' => 'value', 'fa' => 'مقدار']]
     * @return Collection|array
     */
    public function getEavAttributeValues(string $keyType = 'id', ?string $locale = null, bool $groupByLocale = false): Collection|array
    {
        $query = EloquentAttributeValue::where('attributable_type', static::class)
            ->where('attributable_id', $this->getKey())
            ->with('attribute');

        $currentLocale = app()->getLocale();

        if ($locale !== null) {
            $query->where('locale', $locale);
        } elseif (!$groupByLocale) {
            // For backward compatibility, filter by current locale
            $query->where('locale', $currentLocale);
        }

        $values = $query->get();

        // If grouping by locale, return nested array structure
        if ($groupByLocale && $locale === null) {
            $grouped = [];
            foreach ($values as $attributeValue) {
                $attribute = $attributeValue->attribute;
                $key = match ($keyType) {
                    'slug' => $attribute->slug,
                    'logical_id' => $attribute->logical_id,
                    default => $attribute->id,
                };
                
                if (!isset($grouped[$key])) {
                    $grouped[$key] = [];
                }
                
                $valueLocale = $attributeValue->locale ?? $currentLocale;
                $grouped[$key][$valueLocale] = $attributeValue->getActualValue();
            }
            return $grouped;
        }

        // Return flat structure (backward compatible)
        return $values->mapWithKeys(function ($attributeValue) use ($keyType) {
            $attribute = $attributeValue->attribute;
            $key = match ($keyType) {
                'slug' => $attribute->slug,
                'logical_id' => $attribute->logical_id,
                default => $attribute->id,
            };

            return [$key => $attributeValue->getActualValue()];
        });
    }

    /**
     * Set or update attribute values.
     * 
     * Supports locale-based values:
     * - Simple: ['attribute_slug' => 'value'] - uses current locale
     * - Locale-based: ['attribute_slug' => ['en' => 'English', 'fa' => 'فارسی']]
     *
     * @param array<string|int, mixed> $attributeValues Array of [attribute_id/slug => value] or [attribute_id/slug => [locale => value]]
     * @param string|null $locale Locale code (if provided, all values will use this locale)
     */
    public function setEavAttributeValues(array $attributeValues, ?string $locale = null): void
    {
        foreach ($attributeValues as $attribute => $value) {
            // If value is an array with locale keys, process each locale
            if (is_array($value) && !$this->isSequentialArray($value)) {
                // This is a locale-based array: ['en' => 'English', 'fa' => 'فارسی']
                foreach ($value as $valueLocale => $localeValue) {
                    $this->setEavAttributeValue($attribute, $localeValue, $valueLocale);
                }
            } else {
                // Simple value, use provided locale or current locale
                $this->setEavAttributeValue($attribute, $value, $locale);
            }
        }
    }

    /**
     * Set a single attribute value.
     * 
     * @param string|int $attribute Attribute ID or slug
     * @param mixed $value Value to set
     * @param string|null $locale Locale code (defaults to current locale)
     */
    public function setEavAttributeValue(string|int $attribute, mixed $value, ?string $locale = null): void
    {
        $attributeModel = is_numeric($attribute)
            ? EloquentAttribute::find($attribute)
            : EloquentAttribute::where('slug', $attribute)->first();

        if (!$attributeModel) {
            return;
        }

        $locale = $locale ?? app()->getLocale();

        $attributeValue = EloquentAttributeValue::firstOrNew([
            'attributable_type' => static::class,
            'attributable_id' => $this->getKey(),
            'attribute_id' => $attributeModel->id,
            'locale' => $locale,
        ]);

        $attributeValue->attribute()->associate($attributeModel);
        $attributeValue->locale = $locale;
        $attributeValue->setActualValue($value);
        $attributeValue->save();
    }

    /**
     * Check if array is sequential (not associative)
     */
    private function isSequentialArray(array $array): bool
    {
        return array_keys($array) === range(0, count($array) - 1);
    }

    /**
     * Sync attribute values (removes old ones not in the array).
     *
     * @param array<string|int, mixed> $attributeValues Array of [attribute_id/slug => value]
     */
    public function syncEavAttributeValues(array $attributeValues): void
    {
        // Get all current attribute IDs
        $currentAttributeIds = EloquentAttributeValue::where('attributable_type', static::class)
            ->where('attributable_id', $this->getKey())
            ->pluck('attribute_id')
            ->toArray();

        // Process new values
        $newAttributeIds = [];
        foreach ($attributeValues as $attribute => $value) {
            $this->setEavAttributeValue($attribute, $value);

            $attributeModel = is_numeric($attribute)
                ? EloquentAttribute::find($attribute)
                : EloquentAttribute::where('slug', $attribute)->first();

            if ($attributeModel) {
                $newAttributeIds[] = $attributeModel->id;
            }
        }

        // Remove attributes not in the new list
        $toRemove = array_diff($currentAttributeIds, $newAttributeIds);
        if (!empty($toRemove)) {
            EloquentAttributeValue::where('attributable_type', static::class)
                ->where('attributable_id', $this->getKey())
                ->whereIn('attribute_id', $toRemove)
                ->delete();
        }
    }

    /**
     * Remove all attribute values.
     */
    public function clearEavAttributeValues(): void
    {
        EloquentAttributeValue::where('attributable_type', static::class)
            ->where('attributable_id', $this->getKey())
            ->delete();
    }

    /**
     * Remove a specific attribute value.
     */
    public function removeEavAttributeValue(string|int $attribute): void
    {
        $attributeId = is_numeric($attribute)
            ? $attribute
            : EloquentAttribute::where('slug', $attribute)->value('id');

        if ($attributeId) {
            EloquentAttributeValue::where('attributable_type', static::class)
                ->where('attributable_id', $this->getKey())
                ->where('attribute_id', $attributeId)
                ->delete();
        }
    }

    /**
     * Attach attribute groups.
     *
     * @param array<int> $groupIds
     */
    public function attachEavAttributeGroups(array $groupIds): void
    {
        $this->eavAttributeGroups()->syncWithoutDetaching($groupIds);
    }

    /**
     * Sync attribute groups (replaces all existing).
     *
     * @param array<int> $groupIds
     */
    public function syncEavAttributeGroups(array $groupIds): void
    {
        $this->eavAttributeGroups()->sync($groupIds);
    }

    /**
     * Detach attribute groups.
     *
     * @param array<int> $groupIds
     */
    public function detachEavAttributeGroups(array $groupIds): void
    {
        $this->eavAttributeGroups()->detach($groupIds);
    }

    /**
     * Get attributes through groups.
     */
    public function getEavAttributesThroughGroups(): Collection
    {
        return $this->eavAttributeGroups()
            ->with('attributes')
            ->get()
            ->pluck('attributes')
            ->flatten()
            ->unique('id');
    }

    /**
     * Scope: Filter models by attribute value.
     */
    public function scopeWhereEavAttribute(Builder $query, string|int $attribute, mixed $value, string $operator = '='): Builder
    {
        $attributeId = is_numeric($attribute)
            ? $attribute
            : EloquentAttribute::where('slug', $attribute)->value('id');

        if (!$attributeId) {
            return $query->whereRaw('1 = 0'); // Return no results
        }

        $attributeModel = EloquentAttribute::find($attributeId);
        if (!$attributeModel) {
            return $query->whereRaw('1 = 0');
        }

        $column = $attributeModel->type->getValueColumn();

        return $query->whereHas('eavAttributeValues', function ($q) use ($attributeId, $column, $value, $operator) {
            $q->where('attribute_id', $attributeId);

            if ($operator === 'LIKE' || $operator === 'like') {
                $q->where($column, 'LIKE', "%{$value}%");
            } else {
                $q->where($column, $operator, $value);
            }
        });
    }

    /**
     * Scope: Filter models by multiple attribute values (AND).
     */
    public function scopeWhereEavAttributes(Builder $query, array $conditions): Builder
    {
        foreach ($conditions as $condition) {
            $attribute = $condition['attribute'] ?? null;
            $value = $condition['value'] ?? null;
            $operator = $condition['operator'] ?? '=';

            if ($attribute && $value !== null) {
                $query->whereEavAttribute($attribute, $value, $operator);
            }
        }

        return $query;
    }

    /**
     * Scope: Filter models by attribute value (LIKE search).
     */
    public function scopeWhereEavAttributeLike(Builder $query, string|int $attribute, string $value): Builder
    {
        return $query->whereEavAttribute($attribute, $value, 'LIKE');
    }

    /**
     * Scope: Filter models by number range.
     */
    public function scopeWhereEavAttributeBetween(Builder $query, string|int $attribute, int|float $min, int|float $max): Builder
    {
        $attributeId = is_numeric($attribute)
            ? $attribute
            : EloquentAttribute::where('slug', $attribute)->value('id');

        if (!$attributeId) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereHas('eavAttributeValues', function ($q) use ($attributeId, $min, $max) {
            $q->where('attribute_id', $attributeId)
                ->whereBetween('value_number', [$min, $max]);
        });
    }

    /**
     * Scope: Filter models by date range.
     */
    public function scopeWhereEavAttributeDateBetween(Builder $query, string|int $attribute, string $startDate, string $endDate): Builder
    {
        $attributeId = is_numeric($attribute)
            ? $attribute
            : EloquentAttribute::where('slug', $attribute)->value('id');

        if (!$attributeId) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereHas('eavAttributeValues', function ($q) use ($attributeId, $startDate, $endDate) {
            $q->where('attribute_id', $attributeId)
                ->whereBetween('value_date', [$startDate, $endDate]);
        });
    }

    /**
     * Scope: Filter models where attribute value is IN array.
     */
    public function scopeWhereEavAttributeIn(Builder $query, string|int $attribute, array $values): Builder
    {
        $attributeId = is_numeric($attribute)
            ? $attribute
            : EloquentAttribute::where('slug', $attribute)->value('id');

        if (!$attributeId) {
            return $query->whereRaw('1 = 0');
        }

        $attributeModel = EloquentAttribute::find($attributeId);
        if (!$attributeModel) {
            return $query->whereRaw('1 = 0');
        }

        $column = $attributeModel->type->getValueColumn();

        return $query->whereHas('eavAttributeValues', function ($q) use ($attributeId, $column, $values) {
            $q->where('attribute_id', $attributeId)
                ->whereIn($column, $values);
        });
    }

    /**
     * Scope: Filter models where attribute value is NOT IN array.
     */
    public function scopeWhereEavAttributeNotIn(Builder $query, string|int $attribute, array $values): Builder
    {
        $attributeId = is_numeric($attribute)
            ? $attribute
            : EloquentAttribute::where('slug', $attribute)->value('id');

        if (!$attributeId) {
            return $query->whereRaw('1 = 0');
        }

        $attributeModel = EloquentAttribute::find($attributeId);
        if (!$attributeModel) {
            return $query->whereRaw('1 = 0');
        }

        $column = $attributeModel->type->getValueColumn();

        return $query->whereHas('eavAttributeValues', function ($q) use ($attributeId, $column, $values) {
            $q->where('attribute_id', $attributeId)
                ->whereNotIn($column, $values);
        });
    }

    /**
     * Scope: Filter models where attribute value is NULL.
     */
    public function scopeWhereEavAttributeNull(Builder $query, string|int $attribute): Builder
    {
        $attributeId = is_numeric($attribute)
            ? $attribute
            : EloquentAttribute::where('slug', $attribute)->value('id');

        if (!$attributeId) {
            return $query->whereRaw('1 = 0');
        }

        $attributeModel = EloquentAttribute::find($attributeId);
        if (!$attributeModel) {
            return $query->whereRaw('1 = 0');
        }

        $column = $attributeModel->type->getValueColumn();

        return $query->whereHas('eavAttributeValues', function ($q) use ($attributeId, $column) {
            $q->where('attribute_id', $attributeId)
                ->whereNull($column);
        });
    }

    /**
     * Scope: Filter models where attribute value is NOT NULL.
     */
    public function scopeWhereEavAttributeNotNull(Builder $query, string|int $attribute): Builder
    {
        $attributeId = is_numeric($attribute)
            ? $attribute
            : EloquentAttribute::where('slug', $attribute)->value('id');

        if (!$attributeId) {
            return $query->whereRaw('1 = 0');
        }

        $attributeModel = EloquentAttribute::find($attributeId);
        if (!$attributeModel) {
            return $query->whereRaw('1 = 0');
        }

        $column = $attributeModel->type->getValueColumn();

        return $query->whereHas('eavAttributeValues', function ($q) use ($attributeId, $column) {
            $q->where('attribute_id', $attributeId)
                ->whereNotNull($column);
        });
    }

    /**
     * Scope: Filter models where attribute has any of the given values (OR).
     */
    public function scopeWhereEavAttributeAny(Builder $query, string|int $attribute, array $values): Builder
    {
        return $query->whereEavAttributeIn($attribute, $values);
    }

    /**
     * Get EAV Query Builder for advanced queries.
     */
    public static function eavQuery(): EavQueryBuilder
    {
        return new EavQueryBuilder(static::class);
    }

    /**
     * Find models by EAV query.
     */
    public static function findByEav(callable $callback): Collection
    {
        $queryBuilder = static::eavQuery();
        $callback($queryBuilder);

        $ids = $queryBuilder->getAttributableIds();

        return static::whereIn('id', $ids)->get();
    }
}
