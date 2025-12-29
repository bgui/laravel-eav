<?php

namespace Fiachehr\LaravelEav\Infrastructure\Query;

use Fiachehr\LaravelEav\Domain\Enums\AttributeType;
use Fiachehr\LaravelEav\Infrastructure\Persistence\Eloquent\EloquentAttribute;
use Fiachehr\LaravelEav\Infrastructure\Persistence\Eloquent\EloquentAttributeValue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class EavQueryBuilder
{
    protected Builder $query;
    protected string $attributableType;
    protected array $filters = [];
    protected array $attributeFilters = [];

    public function __construct(string $attributableType)
    {
        $this->attributableType = $attributableType;
        $this->query = EloquentAttributeValue::query()
            ->where('attributable_type', $attributableType);
    }

    /**
     * Filter by attribute slug or ID
     */
    public function whereAttribute(string|int $attribute): self
    {
        $attributeId = $this->getAttributeId($attribute);
        if ($attributeId) {
            $this->query->where('attribute_id', $attributeId);
        }
        return $this;
    }

    /**
     * Filter by multiple attributes
     */
    public function whereAttributes(array $attributes): self
    {
        $attributeIds = [];
        foreach ($attributes as $attribute) {
            $id = $this->getAttributeId($attribute);
            if ($id) {
                $attributeIds[] = $id;
            }
        }

        if (!empty($attributeIds)) {
            $this->query->whereIn('attribute_id', $attributeIds);
        }

        return $this;
    }

    /**
     * Filter by text value (exact match)
     */
    public function whereText(string|int $attribute, string $value, string $operator = '='): self
    {
        $attributeId = $this->getAttributeId($attribute);
        if ($attributeId) {
            $this->query->where('attribute_id', $attributeId);
            $this->applyTextFilter($value, $operator);
        }
        return $this;
    }

    /**
     * Filter by text value (LIKE search)
     */
    public function whereTextLike(string|int $attribute, string $value): self
    {
        $attributeId = $this->getAttributeId($attribute);
        if ($attributeId) {
            $this->query->where('attribute_id', $attributeId);
            $this->applyTextFilter($value, 'LIKE', true);
        }
        return $this;
    }

    /**
     * Filter by number value
     */
    public function whereNumber(string|int $attribute, int|float $value, string $operator = '='): self
    {
        $attributeId = $this->getAttributeId($attribute);
        if ($attributeId) {
            $this->query->where('attribute_id', $attributeId);
            $this->applyNumberFilter($value, $operator);
        }
        return $this;
    }

    /**
     * Filter by number range
     */
    public function whereNumberBetween(string|int $attribute, int|float $min, int|float $max): self
    {
        $attributeId = $this->getAttributeId($attribute);
        if ($attributeId) {
            $this->query->where('attribute_id', $attributeId);
            $this->applyNumberRangeFilter($min, $max);
        }
        return $this;
    }

    /**
     * Filter by decimal value
     */
    public function whereDecimal(string|int $attribute, float $value, string $operator = '='): self
    {
        $attributeId = $this->getAttributeId($attribute);
        if ($attributeId) {
            $this->query->where('attribute_id', $attributeId);
            $this->applyDecimalFilter($value, $operator);
        }
        return $this;
    }

    /**
     * Filter by date value
     */
    public function whereDate(string|int $attribute, string $date, string $operator = '='): self
    {
        $attributeId = $this->getAttributeId($attribute);
        if ($attributeId) {
            $this->query->where('attribute_id', $attributeId);
            $this->applyDateFilter($date, $operator);
        }
        return $this;
    }

    /**
     * Filter by date range
     */
    public function whereDateBetween(string|int $attribute, string $startDate, string $endDate): self
    {
        $attributeId = $this->getAttributeId($attribute);
        if ($attributeId) {
            $this->query->where('attribute_id', $attributeId);
            $this->applyDateRangeFilter($startDate, $endDate);
        }
        return $this;
    }

    /**
     * Filter by datetime value
     */
    public function whereDateTime(string|int $attribute, string $datetime, string $operator = '='): self
    {
        $attributeId = $this->getAttributeId($attribute);
        if ($attributeId) {
            $this->query->where('attribute_id', $attributeId);
            $this->applyDateTimeFilter($datetime, $operator);
        }
        return $this;
    }

    /**
     * Filter by boolean value
     */
    public function whereBoolean(string|int $attribute, bool $value): self
    {
        $attributeId = $this->getAttributeId($attribute);
        if ($attributeId) {
            $this->query->where('attribute_id', $attributeId);
            $this->applyBooleanFilter($value);
        }
        return $this;
    }

    /**
     * Filter by JSON contains
     */
    public function whereJsonContains(string|int $attribute, string $key, mixed $value): self
    {
        $attributeId = $this->getAttributeId($attribute);
        if ($attributeId) {
            $this->query->where('attribute_id', $attributeId);
            $this->applyJsonFilter($key, $value);
        }
        return $this;
    }

    /**
     * Get attribute ID from slug or ID
     */
    protected function getAttributeId(string|int $attribute): ?int
    {
        if (is_numeric($attribute)) {
            return (int) $attribute;
        }

        return EloquentAttribute::where('slug', $attribute)->value('id');
    }

    /**
     * Filter by multiple conditions (AND)
     * Each condition must match the same attributable_id
     */
    public function whereMultiple(array $conditions): self
    {
        if (empty($conditions)) {
            return $this;
        }

        // Get attributable IDs that match all conditions
        $matchingIds = null;
        
        foreach ($conditions as $condition) {
            $tempQuery = EloquentAttributeValue::query()
                ->where('attributable_type', $this->attributableType);
            
            $this->applyConditionToQuery($tempQuery, $condition);
            
            $ids = $tempQuery->distinct()->pluck('attributable_id');
            
            if ($matchingIds === null) {
                $matchingIds = $ids;
            } else {
                $matchingIds = $matchingIds->intersect($ids);
            }
            
            if ($matchingIds->isEmpty()) {
                break;
            }
        }
        
        // Filter main query by matching IDs
        if ($matchingIds !== null && !$matchingIds->isEmpty()) {
            $this->query->whereIn('attributable_id', $matchingIds);
        } else {
            // No matches, return empty result
            $this->query->whereRaw('1 = 0');
        }

        return $this;
    }

    /**
     * Filter by multiple conditions (OR)
     */
    public function whereAny(array $conditions): self
    {
        $this->query->where(function ($query) use ($conditions) {
            foreach ($conditions as $condition) {
                $query->orWhere(function ($q) use ($condition) {
                    $this->applyConditionToQuery($q, $condition);
                });
            }
        });

        return $this;
    }

    /**
     * Add a where clause with closure for complex conditions
     */
    public function where(\Closure $callback): self
    {
        $this->query->where($callback);
        return $this;
    }

    /**
     * Add an orWhere clause with closure
     */
    public function orWhere(\Closure $callback): self
    {
        $this->query->orWhere($callback);
        return $this;
    }

    /**
     * Filter by text value IN array
     */
    public function whereTextIn(string|int $attribute, array $values): self
    {
        $attributeId = $this->getAttributeId($attribute);
        if ($attributeId) {
            $this->query->where('attribute_id', $attributeId)
                ->whereIn('value_text', $values);
        }
        return $this;
    }

    /**
     * Filter by text value NOT IN array
     */
    public function whereTextNotIn(string|int $attribute, array $values): self
    {
        $attributeId = $this->getAttributeId($attribute);
        if ($attributeId) {
            $this->query->where('attribute_id', $attributeId)
                ->whereNotIn('value_text', $values);
        }
        return $this;
    }

    /**
     * Filter by number value IN array
     */
    public function whereNumberIn(string|int $attribute, array $values): self
    {
        $attributeId = $this->getAttributeId($attribute);
        if ($attributeId) {
            $this->query->where('attribute_id', $attributeId)
                ->whereIn('value_number', $values);
        }
        return $this;
    }

    /**
     * Filter by number value NOT IN array
     */
    public function whereNumberNotIn(string|int $attribute, array $values): self
    {
        $attributeId = $this->getAttributeId($attribute);
        if ($attributeId) {
            $this->query->where('attribute_id', $attributeId)
                ->whereNotIn('value_number', $values);
        }
        return $this;
    }

    /**
     * Filter where text value is NULL
     */
    public function whereTextNull(string|int $attribute): self
    {
        $attributeId = $this->getAttributeId($attribute);
        if ($attributeId) {
            $this->query->where('attribute_id', $attributeId)
                ->whereNull('value_text');
        }
        return $this;
    }

    /**
     * Filter where text value is NOT NULL
     */
    public function whereTextNotNull(string|int $attribute): self
    {
        $attributeId = $this->getAttributeId($attribute);
        if ($attributeId) {
            $this->query->where('attribute_id', $attributeId)
                ->whereNotNull('value_text');
        }
        return $this;
    }

    /**
     * Filter where number value is NULL
     */
    public function whereNumberNull(string|int $attribute): self
    {
        $attributeId = $this->getAttributeId($attribute);
        if ($attributeId) {
            $this->query->where('attribute_id', $attributeId)
                ->whereNull('value_number');
        }
        return $this;
    }

    /**
     * Filter where number value is NOT NULL
     */
    public function whereNumberNotNull(string|int $attribute): self
    {
        $attributeId = $this->getAttributeId($attribute);
        if ($attributeId) {
            $this->query->where('attribute_id', $attributeId)
                ->whereNotNull('value_number');
        }
        return $this;
    }

    /**
     * Filter where boolean value is true
     */
    public function whereTrue(string|int $attribute): self
    {
        return $this->whereBoolean($attribute, true);
    }

    /**
     * Filter where boolean value is false
     */
    public function whereFalse(string|int $attribute): self
    {
        return $this->whereBoolean($attribute, false);
    }

    /**
     * Order by text value
     */
    public function orderByText(string|int $attribute, string $direction = 'asc'): self
    {
        $attributeId = $this->getAttributeId($attribute);
        if ($attributeId) {
            $this->query->where('attribute_id', $attributeId)
                ->orderBy('value_text', $direction);
        }
        return $this;
    }

    /**
     * Order by number value
     */
    public function orderByNumber(string|int $attribute, string $direction = 'asc'): self
    {
        $attributeId = $this->getAttributeId($attribute);
        if ($attributeId) {
            $this->query->where('attribute_id', $attributeId)
                ->orderBy('value_number', $direction);
        }
        return $this;
    }

    /**
     * Order by date value
     */
    public function orderByDate(string|int $attribute, string $direction = 'asc'): self
    {
        $attributeId = $this->getAttributeId($attribute);
        if ($attributeId) {
            $this->query->where('attribute_id', $attributeId)
                ->orderBy('value_date', $direction);
        }
        return $this;
    }

    /**
     * Order by datetime value
     */
    public function orderByDateTime(string|int $attribute, string $direction = 'asc'): self
    {
        $attributeId = $this->getAttributeId($attribute);
        if ($attributeId) {
            $this->query->where('attribute_id', $attributeId)
                ->orderBy('value_datetime', $direction);
        }
        return $this;
    }

    /**
     * Group by attribute
     */
    public function groupByAttribute(string|int $attribute): self
    {
        $attributeId = $this->getAttributeId($attribute);
        if ($attributeId) {
            $this->query->where('attribute_id', $attributeId)
                ->groupBy('value_text', 'value_number', 'value_decimal', 'value_date', 'value_datetime', 'value_boolean');
        }
        return $this;
    }

    /**
     * Limit results
     */
    public function limit(int $limit): self
    {
        $this->query->limit($limit);
        return $this;
    }

    /**
     * Offset results
     */
    public function offset(int $offset): self
    {
        $this->query->offset($offset);
        return $this;
    }

    /**
     * Get distinct attributable IDs that match the filters
     */
    public function getAttributableIds(): Collection
    {
        return $this->query
            ->distinct()
            ->pluck('attributable_id');
    }

    /**
     * Get count of matching records
     */
    public function count(): int
    {
        return $this->query->distinct()->count('attributable_id');
    }

    /**
     * Get the query builder
     */
    public function getQuery(): Builder
    {
        return $this->query;
    }

    /**
     * Get all attribute values for matching entities
     */
    public function getAttributeValues(string|int $attribute): Collection
    {
        $attributeId = $this->getAttributeId($attribute);
        if (!$attributeId) {
            return collect();
        }

        return $this->query
            ->where('attribute_id', $attributeId)
            ->get()
            ->map(function ($value) {
                return $value->getActualValue();
            });
    }

    /**
     * Get distinct attribute values for matching entities
     */
    public function getDistinctAttributeValues(string|int $attribute): Collection
    {
        $attributeId = $this->getAttributeId($attribute);
        if (!$attributeId) {
            return collect();
        }

        $attributeModel = EloquentAttribute::find($attributeId);
        if (!$attributeModel) {
            return collect();
        }

        $column = $attributeModel->type->getValueColumn();

        return $this->query
            ->where('attribute_id', $attributeId)
            ->distinct()
            ->pluck($column)
            ->filter()
            ->values();
    }

    /**
     * Get sum of numeric attribute values
     */
    public function sum(string|int $attribute): float
    {
        $attributeId = $this->getAttributeId($attribute);
        if (!$attributeId) {
            return 0;
        }

        $attributeModel = EloquentAttribute::find($attributeId);
        if (!$attributeModel || !$attributeModel->isNumeric()) {
            return 0;
        }

        $column = $attributeModel->type->getValueColumn();
        
        return (float) $this->query
            ->where('attribute_id', $attributeId)
            ->sum($column);
    }

    /**
     * Get average of numeric attribute values
     */
    public function avg(string|int $attribute): float
    {
        $attributeId = $this->getAttributeId($attribute);
        if (!$attributeId) {
            return 0;
        }

        $attributeModel = EloquentAttribute::find($attributeId);
        if (!$attributeModel || !$attributeModel->isNumeric()) {
            return 0;
        }

        $column = $attributeModel->type->getValueColumn();
        
        return (float) $this->query
            ->where('attribute_id', $attributeId)
            ->avg($column);
    }

    /**
     * Get minimum value of numeric attribute
     */
    public function min(string|int $attribute): float
    {
        $attributeId = $this->getAttributeId($attribute);
        if (!$attributeId) {
            return 0;
        }

        $attributeModel = EloquentAttribute::find($attributeId);
        if (!$attributeModel || !$attributeModel->isNumeric()) {
            return 0;
        }

        $column = $attributeModel->type->getValueColumn();
        
        return (float) $this->query
            ->where('attribute_id', $attributeId)
            ->min($column);
    }

    /**
     * Get maximum value of numeric attribute
     */
    public function max(string|int $attribute): float
    {
        $attributeId = $this->getAttributeId($attribute);
        if (!$attributeId) {
            return 0;
        }

        $attributeModel = EloquentAttribute::find($attributeId);
        if (!$attributeModel || !$attributeModel->isNumeric()) {
            return 0;
        }

        $column = $attributeModel->type->getValueColumn();
        
        return (float) $this->query
            ->where('attribute_id', $attributeId)
            ->max($column);
    }

    /**
     * Apply text filter
     */
    protected function applyTextFilter(string $value, string $operator, bool $like = false): self
    {
        if ($like) {
            $this->query->where('value_text', 'LIKE', "%{$value}%");
        } else {
            $this->query->where('value_text', $operator, $value);
        }

        return $this;
    }

    /**
     * Apply number filter
     */
    protected function applyNumberFilter(int|float $value, string $operator): self
    {
        $this->query->where('value_number', $operator, $value);
        return $this;
    }

    /**
     * Apply number range filter
     */
    protected function applyNumberRangeFilter(int|float $min, int|float $max): self
    {
        $this->query->whereBetween('value_number', [$min, $max]);
        return $this;
    }

    /**
     * Apply decimal filter
     */
    protected function applyDecimalFilter(float $value, string $operator): self
    {
        $this->query->where('value_decimal', $operator, $value);
        return $this;
    }

    /**
     * Apply date filter
     */
    protected function applyDateFilter(string $date, string $operator): self
    {
        $this->query->whereDate('value_date', $operator, $date);
        return $this;
    }

    /**
     * Apply date range filter
     */
    protected function applyDateRangeFilter(string $startDate, string $endDate): self
    {
        $this->query->whereBetween('value_date', [$startDate, $endDate]);
        return $this;
    }

    /**
     * Apply datetime filter
     */
    protected function applyDateTimeFilter(string $datetime, string $operator): self
    {
        $this->query->where('value_datetime', $operator, $datetime);
        return $this;
    }

    /**
     * Apply boolean filter
     */
    protected function applyBooleanFilter(bool $value): self
    {
        $this->query->where('value_boolean', $value);
        return $this;
    }

    /**
     * Apply JSON filter
     */
    protected function applyJsonFilter(string $key, mixed $value): self
    {
        $this->query->whereJsonContains("value_json->{$key}", $value);
        return $this;
    }

    /**
     * Apply a condition
     */
    protected function applyCondition(array $condition): void
    {
        $attribute = $condition['attribute'] ?? null;
        $value = $condition['value'] ?? null;
        $operator = $condition['operator'] ?? '=';
        $type = $condition['type'] ?? null;

        if (!$attribute || $value === null) {
            return;
        }

        $attributeId = $this->getAttributeId($attribute);
        if (!$attributeId) {
            return;
        }

        $this->query->where('attribute_id', $attributeId);

        if ($type) {
            match ($type) {
                'text', 'text_like' => $this->applyTextFilter($value, $operator, $type === 'text_like'),
                'number', 'number_between' => $type === 'number_between' 
                    ? $this->applyNumberRangeFilter($condition['min'], $condition['max'])
                    : $this->applyNumberFilter($value, $operator),
                'decimal' => $this->applyDecimalFilter($value, $operator),
                'date', 'date_between' => $type === 'date_between'
                    ? $this->applyDateRangeFilter($condition['start_date'], $condition['end_date'])
                    : $this->applyDateFilter($value, $operator),
                'datetime' => $this->applyDateTimeFilter($value, $operator),
                'boolean' => $this->applyBooleanFilter((bool) $value),
                'json' => $this->applyJsonFilter($condition['key'], $value),
                default => null,
            };
        } else {
            // Auto-detect type based on attribute
            $attributeId = $this->getAttributeId($attribute);
            $attributeModel = $attributeId ? EloquentAttribute::find($attributeId) : null;

            if ($attributeModel) {
                $attributeType = $attributeModel->type;
                $column = $attributeType->getValueColumn();

                match ($column) {
                    'value_text' => $this->applyTextFilter($value, $operator),
                    'value_number' => $this->applyNumberFilter($value, $operator),
                    'value_decimal' => $this->applyDecimalFilter($value, $operator),
                    'value_date' => $this->applyDateFilter($value, $operator),
                    'value_datetime' => $this->applyDateTimeFilter($value, $operator),
                    'value_boolean' => $this->applyBooleanFilter((bool) $value),
                    'value_json' => $this->applyJsonFilter('', $value),
                    default => null,
                };
            }
        }
    }

    /**
     * Apply condition to a specific query builder
     */
    protected function applyConditionToQuery(Builder $query, array $condition): void
    {
        $attribute = $condition['attribute'] ?? null;
        $value = $condition['value'] ?? null;
        $operator = $condition['operator'] ?? '=';
        $type = $condition['type'] ?? null;

        if (!$attribute || $value === null) {
            return;
        }

        $attributeId = $this->getAttributeId($attribute);
        if (!$attributeId) {
            return;
        }

        $query->where('attribute_id', $attributeId);

        if ($type) {
            match ($type) {
                'text', 'text_like' => $this->applyTextFilterToQuery($query, $value, $operator, $type === 'text_like'),
                'number', 'number_between' => $type === 'number_between' 
                    ? $this->applyNumberRangeFilterToQuery($query, $condition['min'], $condition['max'])
                    : $this->applyNumberFilterToQuery($query, $value, $operator),
                'decimal' => $this->applyDecimalFilterToQuery($query, $value, $operator),
                'date', 'date_between' => $type === 'date_between'
                    ? $this->applyDateRangeFilterToQuery($query, $condition['start_date'], $condition['end_date'])
                    : $this->applyDateFilterToQuery($query, $value, $operator),
                'datetime' => $this->applyDateTimeFilterToQuery($query, $value, $operator),
                'boolean' => $this->applyBooleanFilterToQuery($query, (bool) $value),
                'json' => $this->applyJsonFilterToQuery($query, $condition['key'], $value),
                default => null,
            };
        } else {
            // Auto-detect type based on attribute
            $attributeModel = EloquentAttribute::find($attributeId);
            if ($attributeModel) {
                $attributeType = $attributeModel->type;
                $column = $attributeType->getValueColumn();

                match ($column) {
                    'value_text' => $this->applyTextFilterToQuery($query, $value, $operator),
                    'value_number' => $this->applyNumberFilterToQuery($query, $value, $operator),
                    'value_decimal' => $this->applyDecimalFilterToQuery($query, $value, $operator),
                    'value_date' => $this->applyDateFilterToQuery($query, $value, $operator),
                    'value_datetime' => $this->applyDateTimeFilterToQuery($query, $value, $operator),
                    'value_boolean' => $this->applyBooleanFilterToQuery($query, (bool) $value),
                    'value_json' => $this->applyJsonFilterToQuery($query, '', $value),
                    default => null,
                };
            }
        }
    }

    /**
     * Apply text filter to a specific query builder
     */
    protected function applyTextFilterToQuery(Builder $query, string $value, string $operator = '=', bool $like = false): void
    {
        if ($like || $operator === 'LIKE' || $operator === 'like') {
            $query->where('value_text', 'LIKE', "%{$value}%");
        } else {
            $query->where('value_text', $operator, $value);
        }
    }

    /**
     * Apply number filter to a specific query builder
     */
    protected function applyNumberFilterToQuery(Builder $query, int|float $value, string $operator = '='): void
    {
        $query->where('value_number', $operator, $value);
    }

    /**
     * Apply number range filter to a specific query builder
     */
    protected function applyNumberRangeFilterToQuery(Builder $query, int|float $min, int|float $max): void
    {
        $query->whereBetween('value_number', [$min, $max]);
    }

    /**
     * Apply decimal filter to a specific query builder
     */
    protected function applyDecimalFilterToQuery(Builder $query, int|float $value, string $operator = '='): void
    {
        $query->where('value_decimal', $operator, $value);
    }

    /**
     * Apply date filter to a specific query builder
     */
    protected function applyDateFilterToQuery(Builder $query, string $value, string $operator = '='): void
    {
        $query->where('value_date', $operator, $value);
    }

    /**
     * Apply date range filter to a specific query builder
     */
    protected function applyDateRangeFilterToQuery(Builder $query, string $startDate, string $endDate): void
    {
        $query->whereBetween('value_date', [$startDate, $endDate]);
    }

    /**
     * Apply datetime filter to a specific query builder
     */
    protected function applyDateTimeFilterToQuery(Builder $query, string $value, string $operator = '='): void
    {
        $query->where('value_datetime', $operator, $value);
    }

    /**
     * Apply boolean filter to a specific query builder
     */
    protected function applyBooleanFilterToQuery(Builder $query, bool $value): void
    {
        $query->where('value_boolean', $value);
    }

    /**
     * Apply JSON filter to a specific query builder
     */
    protected function applyJsonFilterToQuery(Builder $query, string $key, mixed $value): void
    {
        $query->whereJsonContains("value_json->{$key}", $value);
    }
}

