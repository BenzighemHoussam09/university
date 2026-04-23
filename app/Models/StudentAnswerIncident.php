<?php

namespace App\Models;

use App\Enums\IncidentKind;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentAnswerIncident extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'exam_session_id',
        'kind',
        'occurred_at',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'kind' => IncidentKind::class,
            'occurred_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    public function examSession(): BelongsTo
    {
        return $this->belongsTo(ExamSession::class);
    }
}
