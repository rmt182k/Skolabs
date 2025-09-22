<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('assignment_submissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assignment_id');
            $table->unsignedBigInteger('student_id');
            $table->timestamp('submitted_at')->useCurrent();
            $table->string('status', 50)->default('submitted')->comment('Contoh: submitted, graded, late');
            $table->decimal('total_grade', 5, 2)->nullable();
            $table->text('feedback')->nullable()->comment('Feedback umum dari guru untuk submission ini');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignment_submissions');
    }
};
