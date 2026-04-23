<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grading_templates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('teacher_id')->nullable()->index();
            $table->unsignedSmallInteger('exam_max');
            $table->unsignedSmallInteger('personal_work_max');
            $table->unsignedSmallInteger('attendance_max');
            $table->unsignedSmallInteger('participation_max');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grading_templates');
    }
};
