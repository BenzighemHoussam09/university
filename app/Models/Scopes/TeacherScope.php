<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class TeacherScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if (Auth::guard('teacher')->check()) {
            $builder->where($model->getTable().'.teacher_id', Auth::guard('teacher')->id());
        } elseif (($student = Auth::guard('student')->getUser()) !== null) {
            // getUser() returns the cached user without a DB query, breaking the recursion
            // that would otherwise occur when Student itself is being resolved by the auth system.
            $builder->where($model->getTable().'.teacher_id', $student->teacher_id);
        }
        // No scope in console/queue context — caller must filter explicitly.
    }
}
