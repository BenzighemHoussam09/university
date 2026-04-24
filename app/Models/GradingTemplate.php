<?php

namespace App\Models;

use App\Domain\Exam\Exceptions\InvalidGradingTemplateException;
use App\Models\Concerns\BelongsToTeacher;
use Database\Factories\GradingTemplateFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GradingTemplate extends Model
{
    /** @use HasFactory<GradingTemplateFactory> */
    use BelongsToTeacher, HasFactory;

    protected $fillable = [
        'teacher_id',
        'exam_max',
        'personal_work_max',
        'attendance_max',
        'participation_max',
    ];

    protected function casts(): array
    {
        return [
            'teacher_id' => 'integer',
            'exam_max' => 'integer',
            'personal_work_max' => 'integer',
            'attendance_max' => 'integer',
            'participation_max' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (GradingTemplate $template) {
            $template->ensureSumIsTwenty();
        });
    }

    public function ensureSumIsTwenty(): void
    {
        $sum = $this->exam_max + $this->personal_work_max + $this->attendance_max + $this->participation_max;
        if ($sum !== 20) {
            throw InvalidGradingTemplateException::sumNotTwenty($sum);
        }
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    /**
     * Resolve the effective template for a teacher.
     * Checks teacher's own template first, then teacher's assigned template FK, then system fallback.
     */
    public static function resolveForTeacher(Teacher $teacher): ?self
    {
        $own = self::withoutGlobalScopes()->where('teacher_id', $teacher->id)->first();
        if ($own) {
            return $own;
        }

        if ($teacher->grading_template_id) {
            $assigned = self::withoutGlobalScopes()->find($teacher->grading_template_id);
            if ($assigned) {
                return $assigned;
            }
        }

        return self::withoutGlobalScopes()->find(1);
    }
}
