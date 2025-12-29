<?php

namespace Fiachehr\LaravelEav\Infrastructure\Persistence\Eloquent;

use Fiachehr\LaravelEav\Domain\Enums\AttributeType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Builder;

class EloquentAttributeValue extends Model
{
    protected $table = 'attributable_attributes';

    protected $fillable = [
        'attributable_type',
        'attributable_id',
        'attribute_id',
        'value_text',
        'value_number',
        'value_decimal',
        'value_date',
        'value_datetime',
        'value_time',
        'value_boolean',
        'value_json',
        'value', // Keep for backward compatibility
    ];

    protected $casts = [
        'value_text' => 'string',
        'value_number' => 'integer',
        'value_decimal' => 'decimal:4',
        'value_date' => 'date',
        'value_datetime' => 'datetime',
        'value_time' => 'string',
        'value_boolean' => 'boolean',
        'value_json' => 'array',
        'value' => 'string',
    ];

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(EloquentAttribute::class, 'attribute_id');
    }

    public function attributable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the actual value based on attribute type
     */
    public function getActualValue(): mixed
    {
        if (!$this->attribute) {
            return $this->value;
        }

        $column = $this->attribute->type->getValueColumn();
        return $this->{$column} ?? $this->value;
    }

    /**
     * Set the value based on attribute type
     */
    public function setActualValue(mixed $value): void
    {
        if (!$this->attribute) {
            $this->value = $value;
            return;
        }

        $type = $this->attribute->type;
        $column = $type->getValueColumn();

        // Clear all value columns first
        $this->value_text = null;
        $this->value_number = null;
        $this->value_decimal = null;
        $this->value_date = null;
        $this->value_datetime = null;
        $this->value_time = null;
        $this->value_boolean = null;
        $this->value_json = null;

        // Set the appropriate column
        match ($column) {
            'value_text' => $this->value_text = (string) $value,
            'value_number' => $this->value_number = (int) $value,
            'value_decimal' => $this->value_decimal = (float) $value,
            'value_date' => $this->value_date = is_string($value) ? $value : $value,
            'value_datetime' => $this->value_datetime = is_string($value) ? $value : $value,
            'value_time' => $this->value_time = (string) $value,
            'value_boolean' => $this->value_boolean = (bool) $value,
            'value_json' => $this->value_json = is_array($value) ? $value : json_decode($value, true),
        };

        // Also set the legacy value column for backward compatibility
        $this->value = is_array($value) || is_object($value) ? json_encode($value) : (string) $value;
    }

    /**
     * Scope: Filter by attribute slug
     */
    public function scopeWhereAttribute(Builder $query, string|int $attribute): Builder
    {
        $attributeId = is_numeric($attribute) 
            ? $attribute 
            : EloquentAttribute::where('slug', $attribute)->value('id');

        return $query->where('attribute_id', $attributeId);
    }

    /**
     * Scope: Filter by text value (LIKE search)
     */
    public function scopeWhereTextValue(Builder $query, string $value, string $operator = '='): Builder
    {
        return $query->where('value_text', $operator, $value);
    }

    /**
     * Scope: Filter by text value (LIKE search)
     */
    public function scopeWhereTextLike(Builder $query, string $value): Builder
    {
        return $query->where('value_text', 'LIKE', "%{$value}%");
    }

    /**
     * Scope: Filter by number value
     */
    public function scopeWhereNumberValue(Builder $query, int|float $value, string $operator = '='): Builder
    {
        return $query->where('value_number', $operator, $value);
    }

    /**
     * Scope: Filter by number range
     */
    public function scopeWhereNumberBetween(Builder $query, int|float $min, int|float $max): Builder
    {
        return $query->whereBetween('value_number', [$min, $max]);
    }

    /**
     * Scope: Filter by decimal value
     */
    public function scopeWhereDecimalValue(Builder $query, float $value, string $operator = '='): Builder
    {
        return $query->where('value_decimal', $operator, $value);
    }

    /**
     * Scope: Filter by date value
     */
    public function scopeWhereDateValue(Builder $query, string $date, string $operator = '='): Builder
    {
        return $query->whereDate('value_date', $operator, $date);
    }

    /**
     * Scope: Filter by date range
     */
    public function scopeWhereDateBetween(Builder $query, string $startDate, string $endDate): Builder
    {
        return $query->whereBetween('value_date', [$startDate, $endDate]);
    }

    /**
     * Scope: Filter by datetime value
     */
    public function scopeWhereDateTimeValue(Builder $query, string $datetime, string $operator = '='): Builder
    {
        return $query->where('value_datetime', $operator, $datetime);
    }

    /**
     * Scope: Filter by boolean value
     */
    public function scopeWhereBooleanValue(Builder $query, bool $value): Builder
    {
        return $query->where('value_boolean', $value);
    }

    /**
     * Scope: Filter by JSON value
     */
    public function scopeWhereJsonContains(Builder $query, string $key, mixed $value): Builder
    {
        return $query->whereJsonContains("value_json->{$key}", $value);
    }

    /**
     * Scope: Filter by attributable type
     */
    public function scopeWhereAttributableType(Builder $query, string $type): Builder
    {
        return $query->where('attributable_type', $type);
    }

    /**
     * Scope: Filter by attributable ID
     */
    public function scopeWhereAttributableId(Builder $query, int $id): Builder
    {
        return $query->where('attributable_id', $id);
    }
}


