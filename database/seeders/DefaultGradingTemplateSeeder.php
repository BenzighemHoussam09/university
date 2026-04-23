<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DefaultGradingTemplateSeeder extends Seeder
{
    public function run(): void
    {
        // System default template (id=1, teacher_id=null)
        DB::table('grading_templates')->updateOrInsert(
            ['id' => 1],
            [
                'teacher_id' => null,
                'exam_max' => 12,
                'personal_work_max' => 4,
                'attendance_max' => 2,
                'participation_max' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}
