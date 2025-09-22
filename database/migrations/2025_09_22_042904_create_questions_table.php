<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assignment_id');
            $table->text('question_text');
            $table->enum('type', ['text', 'multiple_choice', 'essay']);
            $table->unsignedInteger('order');
            $table->unsignedInteger('score')->default(10);
            $table->text('correct_answer')->nullable()->comment('Untuk tipe soal Short Answer dan Essay');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
