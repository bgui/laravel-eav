<?php

namespace Fiachehr\LaravelEav\Infrastructure\Persistence\Eloquent;

use Fiachehr\LaravelEav\Domain\Shared\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class EloquentAttributeGroup extends Model
{
    use HasTranslations;
    protected $table = 'attribute_groups';

    public $timestamps = false;

    protected $fillable = [
        'title',
        'slug',
        'is_active',
        'language',
        'module_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Fields that can be translated.
     * Override this in your extending model if needed.
     */
    protected $translatable = [
        'title',
    ];

    public function attributes(): BelongsToMany
    {
        return $this->belongsToMany(
            EloquentAttribute::class,
            'attribute_group_attributes',
            'attribute_group_id',
            'attribute_id'
        );
    }

    public function attributables(): MorphToMany
    {
        return $this->morphToMany(
            Model::class,
            'attributable',
            'attributable_attribute_groups'
        );
    }

    public function getMorphClass(): string
    {
        return self::class;
    }
}
