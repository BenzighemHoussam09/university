<?php

namespace App\Models;

use App\Enums\ExamStatus;
use App\Models\Concerns\BelongsToTeacher;
use Database\Factories\ExamFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Exam extends Model
{
    /** @use HasFactory<ExamFactory> */
    use BelongsToTeacher, HasFactory;

    protected $fillable = [
        'teacher_id',
        'group_id',
        'title',
        'easy_count',
        'medium_count',
        'hard_count',
        'duration_minutes',
        'scheduled_at',
        'status',
        'started_at',
        'ended_at',
        'global_extra_minutes',
        'reminders_sent_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => ExamStatus::class,
            'scheduled_at' => 'datetime',
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
            'reminders_sent_at' => 'datetime',
            'easy_count' => 'integer',
            'medium_count' => 'integer',
            'hard_count' => 'integer',
            'duration_minutes' => 'integer',
            'global_extra_minutes' => 'integer',
        ];
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(ExamSession::class);
    }

    /**
     * Total number of questions per student.
     */
    public function totalQuestions(): int
    {
        return $this->easy_count + $this->medium_count + $this->hard_count;
    }
}
