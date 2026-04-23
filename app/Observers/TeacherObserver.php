<?php

namespace App\Observers;

use App\Models\GradingTemplate;
use App\Models\Teacher;
use Illuminate\Support\Facades\DB;

class TeacherObserver
{
    /**
     * On teacher creation, clone the system default GradingTemplate (id=1)
     * and assign it to the new teacher.
     */
    public function created(Teacher $teacher): void
    {
        $systemDefault = DB::table('grading_templates')->where('id', 1)->first();

        if (! $systemDefault) {
            return;
        }

        $cloned = GradingTemplate::withoutEvents(function () use ($teacher, $systemDefault) {
            return GradingTemplate::create([
                'teacher_id' => $teacher->id,
                'exam_max' => $systemDefault->exam_max,
                'personal_work_max' => $systemDefault->personal_work_max,
                'attendance_max' => $systemDefault->attendance_max,
                'participation_max' => $systemDefault->participation_max,
            ]);
        });

        $teacher->grading_template_id = $cloned->id;
        $teacher->saveQuietly();
    }
}
