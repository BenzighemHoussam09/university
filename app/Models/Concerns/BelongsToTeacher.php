<?php

namespace App\Models\Concerns;

use App\Models\Scopes\TeacherScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

trait BelongsToTeacher
{
    public static function bootBelongsToTeacher(): void
    {
        static::addGlobalScope(new TeacherScope);

        static::creating(function (Model $model) {
            // Always set teacher_id from authenticated guard if available,
            // preventing mass-assignment from overriding it
            if (Auth::guard('teacher')->check()) {
                $model->teacher_id = Auth::guard('teacher')->id();
            } elseif (! isset($model->teacher_id)) {
                // Only allow external setting if no teacher is authenticated
                // This supports tests and direct model creation
            }
        });
    }
}
