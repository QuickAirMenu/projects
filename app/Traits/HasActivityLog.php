<?php

namespace App\Traits;

/**
 * HasActivityLog — automatically logs model events using Spatie ActivityLog.
 * Simply add `use HasActivityLog;` to any model to enable full audit trail.
 */
trait HasActivityLog
{
    public static function bootHasActivityLog(): void
    {
        static::created(fn($model) => activity()
            ->causedBy(auth()->user())
            ->performedOn($model)
            ->log('created'));

        static::updated(fn($model) => activity()
            ->causedBy(auth()->user())
            ->performedOn($model)
            ->withProperties(['changed' => $model->getDirty()])
            ->log('updated'));

        static::deleted(fn($model) => activity()
            ->causedBy(auth()->user())
            ->performedOn($model)
            ->log('deleted'));
    }
}
