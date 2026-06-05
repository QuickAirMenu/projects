<?php

namespace App\Traits;

/**
 * Translatable — for models that have `name_ar` / `name_en` columns.
 * Adds a `name` accessor that returns the correct locale value automatically.
 */
trait Translatable
{
    /**
     * Get the translated `name` based on the active application locale.
     */
    public function getNameAttribute(): string
    {
        $locale = app()->getLocale();
        $col    = "name_{$locale}";

        return $this->attributes[$col]
            ?? $this->attributes['name_ar']
            ?? $this->attributes['name_en']
            ?? '';
    }

    /**
     * Get the translated `description` if the model has one.
     */
    public function getDescriptionAttribute(): ?string
    {
        $locale = app()->getLocale();
        $col    = "description_{$locale}";

        return $this->attributes[$col]
            ?? $this->attributes['description_ar']
            ?? $this->attributes['description_en']
            ?? null;
    }
}
