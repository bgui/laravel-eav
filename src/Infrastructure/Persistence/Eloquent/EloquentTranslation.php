<?php

namespace Fiachehr\LaravelEav\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class EloquentTranslation extends Model
{
    protected $table = 'eav_translations';

    public $timestamps = false;

    protected $fillable = [
        'translatable_id',
        'translatable_type',
        'locale',
        'key',
        'value',
    ];

    /**
     * Get the parent translatable model.
     */
    public function translatable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Update or create a translation.
     */
    public static function updateOrCreateTranslation(
        string $translatableType,
        int $translatableId,
        string $locale,
        string $key,
        ?string $value
    ): self {
        return self::updateOrCreate(
            [
                'translatable_type' => $translatableType,
                'translatable_id' => $translatableId,
                'locale' => $locale,
                'key' => $key,
            ],
            [
                'value' => $value,
            ]
        );
    }

    /**
     * Delete a specific translation.
     */
    public static function deleteTranslation(
        string $translatableType,
        int $translatableId,
        string $locale,
        string $key
    ): bool {
        return self::where('translatable_type', $translatableType)
            ->where('translatable_id', $translatableId)
            ->where('locale', $locale)
            ->where('key', $key)
            ->delete();
    }
}

