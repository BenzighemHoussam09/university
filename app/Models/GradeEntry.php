<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTeacher;
use Database\Factories\GradeEntryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GradeEntry extends Model
{
    /** @use HasFactory<GradeEntryFactory> */
    use BelongsToTeacher, HasFactory;

    protected $fillable = [
        'teacher_id',
        'student_id',
        'module_id',
        'exam_component',
        'personal_work',
        'attendance',
        'participation',
        'final_grade',
    ];

    protected function casts(): array
    {
        return [
            'exam_component' => 'float',
            'personal_work' => 'float',
            'attendance' => 'float',
            'participation' => 'float',
            'final_grade' => 'float',
        ];
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }
}
