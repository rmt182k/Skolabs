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

            $table->string('submission_type', 50);
            $table->text('content')->nullable();
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();

            $table->timestamp('submitted_at')->useCurrent();

            $table->string('status', 50)->default('submitted');
            $table->decimal('grade', 5, 2)->nullable();
            $table->text('feedback')->nullable();

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
