<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('teachers')->cascadeOnDelete();
            $table->foreignId('group_id')->constrained('groups')->cascadeOnDelete();
            $table->string('title', 160);
            $table->unsignedSmallInteger('easy_count')->default(0);
            $table->unsignedSmallInteger('medium_count')->default(0);
            $table->unsignedSmallInteger('hard_count')->default(0);
            $table->unsignedSmallInteger('duration_minutes');
            $table->dateTime('scheduled_at');
            $table->enum('status', ['draft', 'scheduled', 'active', 'ended'])->default('scheduled');
            $table->dateTime('started_at')->nullable();
            $table->dateTime('ended_at')->nullable();
            $table->integer('global_extra_minutes')->default(0);
            $table->dateTime('reminders_sent_at')->nullable();
            $table->timestamps();

            $table->index(['teacher_id', 'scheduled_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exams');
    }
};
