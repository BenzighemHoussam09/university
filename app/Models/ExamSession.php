<?php

namespace App\Models;

use Database\Factories\ExamSessionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamSession extends Model
{
    /** @use HasFactory<ExamSessionFactory> */
    use HasFactory;

    protected $fillable = [
        'exam_id',
        'student_id',
        'status',
        'started_at',
        'deadline',
        'student_extra_minutes',
        'last_heartbeat_at',
        'completed_at',
        'exam_score_raw',
        'exam_score_component',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'deadline' => 'datetime',
            'last_heartbeat_at' => 'datetime',
            'completed_at' => 'datetime',
            'student_extra_minutes' => 'integer',
            'exam_score_raw' => 'integer',
            'exam_score_component' => 'float',
        ];
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function assignedQuestions(): HasMany
    {
        return $this->hasMany(ExamSessionQuestion::class)->orderBy('display_order');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(StudentAnswer::class);
    }

    public function incidents(): HasMany
    {
        return $this->hasMany(StudentAnswerIncident::class);
    }
}
