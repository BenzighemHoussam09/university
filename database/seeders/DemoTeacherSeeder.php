<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DemoTeacherSeeder extends Seeder
{
    public function run(): void
    {
        // Clone the system default grading template for the demo teacher
        $clonedId = DB::table('grading_templates')->insertGetId([
            'teacher_id' => null, // will be updated after teacher is created
            'exam_max' => 12,
            'personal_work_max' => 4,
            'attendance_max' => 2,
            'participation_max' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create the demo teacher
        $teacherId = DB::table('teachers')->insertGetId([
            'name' => 'Demo Teacher',
            'email' => 'demo.teacher@univ.dz',
            'password' => Hash::make('password'),
            'grading_template_id' => $clonedId,
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Update the cloned template with the actual teacher_id
        DB::table('grading_templates')->where('id', $clonedId)->update([
            'teacher_id' => $teacherId,
        ]);
    }
}
