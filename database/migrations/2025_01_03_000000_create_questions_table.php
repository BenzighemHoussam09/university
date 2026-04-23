<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $table->foreignId('module_id')->constrained()->cascadeOnDelete();
            $table->enum('level', ['L1', 'L2', 'L3', 'M1', 'M2']);
            $table->enum('difficulty', ['easy', 'medium', 'hard']);
            $table->text('text');
            $table->timestamps();

            // Filter index for question bank page
            $table->index(['teacher_id', 'module_id', 'level', 'difficulty']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
