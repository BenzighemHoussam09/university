<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grade_entries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('teacher_id')->index();
            $table->foreign('teacher_id')->references('id')->on('teachers')->cascadeOnDelete();
            $table->unsignedBigInteger('student_id')->index();
            $table->foreign('student_id')->references('id')->on('students')->cascadeOnDelete();
            $table->unsignedBigInteger('module_id')->index();
            $table->foreign('module_id')->references('id')->on('modules')->cascadeOnDelete();
            $table->decimal('exam_component', 4, 2)->default(0);
            $table->decimal('personal_work', 4, 2)->default(0);
            $table->decimal('attendance', 4, 2)->default(0);
            $table->decimal('participation', 4, 2)->default(0);
            $table->decimal('final_grade', 4, 2)->default(0);
            $table->timestamps();

            $table->unique(['student_id', 'module_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grade_entries');
    }
};
