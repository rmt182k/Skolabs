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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('description')->nullable();
            $table->timestamps();
        });
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('description')->nullable();
            $table->timestamps();
        });
        Schema::create('user_roles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('role_id');
            $table->timestamps();
        });
        Schema::create('role_permissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('role_id');
            $table->unsignedBigInteger('permission_id');
            $table->timestamps();
        });
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned();
            $table->bigInteger('nisn')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female'])->nullable();
            $table->string('phone_number', 15)->nullable();
            $table->text('address')->nullable();
            $table->date('enrollment_date')->nullable();
            $table->integer('grade_level')->nullable();
            $table->integer('major_id')->nullable();
            $table->enum('status', ['active', 'graduated', 'dropout', 'suspended', 'transferred', 'on_leave'])->default('active');
            $table->timestamps();
        });
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned();
            $table->string('employee_id')->nullable();
            $table->date('date_of_birth');
            $table->string('phone_number', 15)->nullable();
            $table->text('address')->nullable();
            $table->enum('gender', ['male', 'female'])->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
        });
        Schema::create('staffs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('employee_id')->nullable();
            $table->date('date_of_birth');
            $table->string('position')->nullable(); // Kolom 'position' sebagai ganti 'job_title'
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
        Schema::create('majors', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('educational_level_id');
            $table->string('name')->unique();
            $table->string('description');
            $table->timestamps();
        });
        Schema::create('educational_levels', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->tinyInteger('duration_years');
            $table->text('description')->nullable();
            $table->timestamps();
        });
        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Generated name, e.g., "10 RPL 1"');
            $table->tinyInteger('grade_level')->comment('e.g., 10, 11, 12');
            $table->unsignedBigInteger('educational_level_id');
            $table->unsignedBigInteger('major_id')->nullable();
            $table->unsignedBigInteger('teacher_id')->comment('Homeroom Teacher');
            $table->timestamps();
        });
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->timestamps();
        });
        Schema::create('subject_teachers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subject_id');
            $table->unsignedBigInteger('teacher_id');
            $table->timestamps();
        });
        Schema::create('academic_years', function (Blueprint $table) {
            $table->id();
            $table->string('year', 9)->comment('Contoh: 2024/2025');
            $table->enum('semester', ['Ganjil', 'Genap']);
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['active', 'inactive', 'archived'])->default('inactive');
            $table->timestamps();
            $table->unique(['year', 'semester']); // Pastikan tidak ada duplikat tahun & semester
        });
        Schema::create('class_students', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('class_id');
            $table->unsignedBigInteger('student_id');
            $table->timestamps();
        });
        Schema::create('class_subjects', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('class_id');
            $table->unsignedBigInteger('subject_id');
            $table->unsignedBigInteger('teacher_id');
            $table->dropColumn('academic_year_id');
            $table->timestamps();
        });
        Schema::create('learning_materials', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('file_path');
            $table->string('file_name');
            $table->string('file_type', 50);
            $table->unsignedBigInteger('file_size')->nullable();

            $table->unsignedBigInteger('subject_id');
            $table->unsignedBigInteger('class_id');
            $table->unsignedBigInteger('teacher_id');

            $table->timestamps();
        });
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->enum('assignment_type', ['task', 'quiz', 'exam']);
            $table->unsignedBigInteger('subject_id');
            $table->text('description')->nullable();
            $table->date('start_date')->nullable();
            $table->date('due_date')->nullable();
            $table->unsignedBigInteger('teacher_id');
            $table->timestamps();
        });
        Schema::create('assignment_class', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assignment_id');
            $table->unsignedBigInteger('class_id');
        });
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
        Schema::create('question_options', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('question_id');
            $table->char('option_letter', 1);
            $table->string('option_text');
            $table->boolean('is_correct')->default(false);
            $table->timestamps();
        });
        Schema::create('submission_answers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assignment_submission_id');
            $table->unsignedBigInteger('question_id');
            $table->text('answer')->nullable();
            $table->decimal('grade', 5, 2)->nullable();
            $table->text('feedback')->nullable();
            $table->timestamps();
        });
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nama menu, cth: Dashboard
            $table->string('route')->nullable(); // Route/URL, cth: /dashboard
            $table->string('icon')->nullable(); // Class icon, cth: uil-home-alt
            $table->unsignedBigInteger('parent_id')->default(0); // Untuk submenu, 0 = menu utama
            $table->integer('order')->default(0); // Urutan menu
            $table->timestamps();
        });
        Schema::create('menu_roles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('role_id');
            $table->unsignedBigInteger('menu_id');
            $table->boolean('can_view')->default(false);
            $table->boolean('can_create')->default(false);
            $table->boolean('can_update')->default(false);
            $table->boolean('can_delete')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('submission_answers');
        Schema::dropIfExists('question_options');
        Schema::dropIfExists('questions');
        Schema::dropIfExists('assignment_submissions');
        Schema::dropIfExists('assignment_class');
        Schema::dropIfExists('assignments');
        Schema::dropIfExists('learning_materials');
        Schema::dropIfExists('class_students');
        Schema::dropIfExists('subject_teachers');
        Schema::dropIfExists('class_subjects');
        Schema::dropIfExists('subjects');
        Schema::dropIfExists('classes');
        Schema::dropIfExists('educational_levels');
        Schema::dropIfExists('majors');
        Schema::dropIfExists('staffs');
        Schema::dropIfExists('teachers');
        Schema::dropIfExists('students');
        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('user_roles');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('users');
        Schema::dropIfExists('menus');
        Schema::dropIfExists('menu_roles');
        Schema::dropIfExists('academic_years');
    }

};
