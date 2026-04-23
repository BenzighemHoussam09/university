<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTeacher;
use Database\Factories\ModuleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Module extends Model
{
    /** @use HasFactory<ModuleFactory> */
    use BelongsToTeacher, HasFactory;

    protected $fillable = [
        'teacher_id',
        'name',
        'created_from_catalog_id',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function catalog(): BelongsTo
    {
        return $this->belongsTo(ModuleCatalog::class, 'created_from_catalog_id');
    }

    public function groups(): HasMany
    {
        return $this->hasMany(Group::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }
}
