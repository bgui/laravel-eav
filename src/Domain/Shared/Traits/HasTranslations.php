<?php

namespace Fiachehr\LaravelEav\Domain\Shared\Traits;

use Fiachehr\LaravelEav\Infrastructure\Persistence\Eloquent\EloquentTranslation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Trait for multilingual support in EAV models.
 * 
 * This trait provides:
 * - Translation system using eav_translations table
 * - Language-based scopes
 * - Helper methods for working with languages
 * - Methods to save and edit translations
 */
trait HasTranslations
{
    /**
     * Boot the trait.
     * Automatically delete translations when the model is deleted.
     */
    protected static function bootHasTranslations(): void
    {
        static::deleted(function ($model) {
            $model->deleteAllTranslations();
        });
    }

    /**
     * Get translations relationship.
     * 
     * This method automatically handles models that extend EloquentAttribute
     * by using the base class name for morph mapping compatibility.
     * It also supports both the base class name and custom class names.
     */
    public function translations(): MorphMany
    {
        $morphClass = $this->getMorphClassForTranslations();
        $currentClass = static::class;

        // Build the relation using morph class
        // Laravel will use getMorphClass() if available, otherwise static::class
        $relation = $this->morphMany(EloquentTranslation::class, 'translatable');

        // If morph class is different from current class, we need to support both
        if ($morphClass !== $currentClass) {
            // Get the base query builder (not Eloquent builder)
            $baseQuery = $relation->getQuery()->getQuery();

            // Find and remove the default translatable_type constraint
            $wheres = $baseQuery->wheres;
            $filteredWheres = [];

            foreach ($wheres as $where) {
                // Skip the default translatable_type constraint that matches current class
                if (
                    isset($where['column']) && $where['column'] === 'translatable_type' &&
                    isset($where['value']) && $where['value'] === $currentClass
                ) {
                    continue;
                }
                $filteredWheres[] = $where;
            }

            // Rebuild wheres array
            $baseQuery->wheres = array_values($filteredWheres);

            // Add our custom constraint that supports both types
            $relation->where(function ($q) use ($morphClass, $currentClass) {
                $q->where('translatable_type', $morphClass)
                    ->orWhere('translatable_type', $currentClass);
            });
        }

        return $relation;
    }

    /**
     * Get the morph class name for translations.
     * 
     * If the model extends EloquentAttribute or EloquentAttributeGroup, use the base class.
     * Otherwise, use the current class name.
     */
    protected function getMorphClassForTranslations(): string
    {
        // Check if this model extends EloquentAttribute or EloquentAttributeGroup
        $reflection = new \ReflectionClass($this);
        $parentClass = $reflection->getParentClass();

        if ($parentClass) {
            $parentClassName = $parentClass->getName();

            if ($parentClassName === \Fiachehr\LaravelEav\Infrastructure\Persistence\Eloquent\EloquentAttribute::class) {
                return \Fiachehr\LaravelEav\Infrastructure\Persistence\Eloquent\EloquentAttribute::class;
            }

            if ($parentClassName === \Fiachehr\LaravelEav\Infrastructure\Persistence\Eloquent\EloquentAttributeGroup::class) {
                return \Fiachehr\LaravelEav\Infrastructure\Persistence\Eloquent\EloquentAttributeGroup::class;
            }
        }

        // Use getMorphClass if available (Laravel's built-in method)
        if (method_exists($this, 'getMorphClass')) {
            return $this->getMorphClass();
        }

        // Fallback to static class name
        return static::class;
    }

    /**
     * Get translation for a specific field and locale.
     */
    public function getTranslation(string $key, string $locale): ?string
    {
        $translation = $this->translations()
            ->where('key', $key)
            ->where('locale', $locale)
            ->first();

        return $translation ? $translation->value : null;
    }

    /**
     * Set or update a translation for a specific field and locale.
     */
    public function setTranslation(string $key, string $locale, ?string $value): self
    {
        $morphClass = $this->getMorphClassForTranslations();

        EloquentTranslation::updateOrCreateTranslation(
            $morphClass,
            $this->getKey(),
            $locale,
            $key,
            $value
        );

        return $this;
    }

    /**
     * Set multiple translations at once.
     * 
     * @param array $translations Array of translations: ['key' => ['locale' => 'value', ...], ...]
     *                            or ['locale' => ['key' => 'value', ...], ...]
     */
    public function setTranslations(array $translations): self
    {
        foreach ($translations as $key => $data) {
            if (is_array($data)) {
                // Format: ['key' => ['locale' => 'value', ...]]
                foreach ($data as $locale => $value) {
                    $this->setTranslation($key, $locale, $value);
                }
            } else {
                // Format: ['locale' => ['key' => 'value', ...]]
                // This would require a different structure, but we'll handle the first format
                if (is_string($key)) {
                    // Assume it's a locale and data is key-value pairs
                    foreach ($data as $translationKey => $value) {
                        $this->setTranslation($translationKey, $key, $value);
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Delete a translation for a specific field and locale.
     */
    public function deleteTranslation(string $key, string $locale): bool
    {
        $morphClass = $this->getMorphClassForTranslations();

        return EloquentTranslation::deleteTranslation(
            $morphClass,
            $this->getKey(),
            $locale,
            $key
        );
    }

    /**
     * Delete all translations for a specific locale.
     */
    public function deleteTranslationsForLocale(string $locale): bool
    {
        return $this->translations()
            ->where('locale', $locale)
            ->delete();
    }

    /**
     * Delete all translations for a specific key across all locales.
     */
    public function deleteTranslationsForKey(string $key): bool
    {
        return $this->translations()
            ->where('key', $key)
            ->delete();
    }

    /**
     * Delete all translations for this model.
     */
    public function deleteAllTranslations(): bool
    {
        return $this->translations()->delete();
    }

    /**
     * Scope: Filter by language
     */
    public function scopeForLanguage(Builder $query, string $language): Builder
    {
        return $query->where('language', $language);
    }

    /**
     * Scope: Filter by current locale
     */
    public function scopeForCurrentLanguage(Builder $query): Builder
    {
        return $query->where('language', app()->getLocale());
    }

    /**
     * Scope: Filter by multiple languages
     */
    public function scopeForLanguages(Builder $query, array $languages): Builder
    {
        return $query->whereIn('language', $languages);
    }

    /**
     * Scope: Exclude specific language
     */
    public function scopeExcludeLanguage(Builder $query, string $language): Builder
    {
        return $query->where('language', '!=', $language);
    }

    /**
     * Get all translations for this model.
     * 
     * @return array Format: ['locale' => ['key' => 'value', ...], ...]
     */
    public function getAllTranslations(): array
    {
        return $this->translations()
            ->get()
            ->groupBy('locale')
            ->map(function ($translations) {
                return $translations->pluck('value', 'key')->toArray();
            })
            ->toArray();
    }

    /**
     * Get translations for a specific locale.
     * 
     * @return array Format: ['key' => 'value', ...]
     */
    public function getTranslationsForLocale(string $locale): array
    {
        return $this->translations()
            ->where('locale', $locale)
            ->get()
            ->pluck('value', 'key')
            ->toArray();
    }

    /**
     * Get translations for a specific key across all locales.
     * 
     * @return array Format: ['locale' => 'value', ...]
     */
    public function getTranslationsForKey(string $key): array
    {
        return $this->translations()
            ->where('key', $key)
            ->get()
            ->pluck('value', 'locale')
            ->toArray();
    }

    /**
     * Check if model has translation for a specific locale.
     */
    public function hasTranslation(string $locale): bool
    {
        return $this->translations()
            ->where('locale', $locale)
            ->exists();
    }

    /**
     * Check if model has translation for a specific key and locale.
     */
    public function hasTranslationForKey(string $key, string $locale): bool
    {
        return $this->translations()
            ->where('key', $key)
            ->where('locale', $locale)
            ->exists();
    }

    /**
     * Get default language from config or fallback to 'en'
     */
    protected function getDefaultLanguage(): string
    {
        return config('app.locale', 'en');
    }

    /**
     * Get supported languages from config
     */
    protected function getSupportedLanguages(): array
    {
        return config('app.supported_locales', ['en']);
    }

    /**
     * Get translatable fields as a Collection.
     * This method is used by TranslationValidation trait for validation rules.
     * 
     * @return \Illuminate\Support\Collection
     */
    public static function getTranslatableFields(): \Illuminate\Support\Collection
    {
        $instance = new static();
        $fields = $instance->translatable ?? [];

        return \Illuminate\Support\Collection::make($fields);
    }
}
