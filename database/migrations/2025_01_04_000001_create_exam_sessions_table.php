<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained('exams')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->enum('status', ['waiting', 'active', 'completed'])->default('waiting');
            $table->dateTime('started_at')->nullable();
            $table->dateTime('deadline')->nullable();
            $table->integer('student_extra_minutes')->default(0);
            $table->dateTime('last_heartbeat_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->unsignedSmallInteger('exam_score_raw')->nullable();
            $table->decimal('exam_score_component', 4, 2)->nullable();
            $table->timestamps();

            $table->unique(['exam_id', 'student_id']);
            $table->index(['exam_id', 'status']);
            $table->index(['deadline', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_sessions');
    }
};
