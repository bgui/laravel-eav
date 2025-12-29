<?php

namespace Fiachehr\LaravelEav\Infrastructure\Persistence\Eloquent;

use Fiachehr\LaravelEav\Domain\Enums\AttributeType;
use Fiachehr\LaravelEav\Domain\Shared\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class EloquentAttribute extends Model
{
    use HasTranslations;
    protected $table = 'attributes';

    public $timestamps = false;

    protected $fillable = [
        'logical_id',
        'title',
        'slug',
        'type',
        'description',
        'values',
        'validations',
        'is_active',
        'language',
    ];

    protected $casts = [
        'values' => 'array',
        'validations' => 'array',
        'is_active' => 'boolean',
        'type' => AttributeType::class,
    ];

    /**
     * Fields that can be translated.
     * Override this in your extending model if needed.
     */
    protected $translatable = [
        'title',
        'description',
    ];

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(
            EloquentAttributeGroup::class,
            'attribute_group_attributes',
            'attribute_id',
            'attribute_group_id'
        );
    }

    public function attributeValues(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(EloquentAttributeValue::class, 'attribute_id');
    }

    /**
     * Get the value column name for this attribute type.
     */
    public function getValueColumn(): string
    {
        return $this->type->getValueColumn();
    }

    /**
     * Check if this attribute is searchable.
     */
    public function isSearchable(): bool
    {
        return $this->type->isSearchable();
    }

    /**
     * Check if this attribute is numeric.
     */
    public function isNumeric(): bool
    {
        return $this->type->isNumeric();
    }

    /**
     * Check if this attribute is a date type.
     */
    public function isDate(): bool
    {
        return $this->type->isDate();
    }

    /**
     * Check if this attribute is boolean.
     */
    public function isBoolean(): bool
    {
        return $this->type->isBoolean();
    }

    /**
     * Get the morph class name for this model.
     * 
     * This ensures that when models extend this class, they use
     * the base class name for polymorphic relations (like translations).
     * This allows compatibility with existing data that uses the base class name.
     */
    public function getMorphClass(): string
    {
        return self::class;
    }
}
