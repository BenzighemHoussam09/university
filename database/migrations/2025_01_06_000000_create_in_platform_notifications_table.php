<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('in_platform_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->nullable()->constrained('teachers')->cascadeOnDelete();
            $table->enum('recipient_type', ['teacher', 'student']);
            $table->unsignedBigInteger('recipient_id');
            $table->enum('kind', ['student_account_created', 'exam_reminder', 'results_available']);
            $table->json('payload')->nullable();
            $table->dateTime('read_at')->nullable();
            $table->dateTime('created_at')->useCurrent();

            // Inbox query index (name shortened to stay within MySQL's 64-char limit)
            $table->index(['recipient_type', 'recipient_id', 'read_at'], 'ipn_recipient_read_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('in_platform_notifications');
    }
};
