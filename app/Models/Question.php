<?php

namespace App\Models;

use App\Enums\Difficulty;
use App\Enums\Level;
use App\Models\Concerns\BelongsToTeacher;
use Database\Factories\QuestionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    /** @use HasFactory<QuestionFactory> */
    use BelongsToTeacher, HasFactory;

    protected $fillable = [
        'teacher_id',
        'module_id',
        'level',
        'difficulty',
        'text',
    ];

    protected $casts = [
        'level' => Level::class,
        'difficulty' => Difficulty::class,
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    public function choices(): HasMany
    {
        return $this->hasMany(QuestionChoice::class);
    }
}
