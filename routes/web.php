<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\ClassController;
use App\Http\Controllers\ClassStudentController;
use App\Http\Controllers\EducationalLevelController;
use App\Http\Controllers\LearningMaterialController;
use App\Http\Controllers\MajorController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\TeacherController;
use App\Models\LearningMaterial;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', function () {
        return view('dashboard.index');
    });

    Route::get('/student', function () {
        return view('student.index');
    });

    Route::get('/teacher', function () {
        return view('teacher.index');
    });

    Route::get('/admin', function () {
        return view('admin.index');
    });

    Route::get('/staff', function () {
        return view('staff.index');
    });

    Route::get('/educational-level', function () {
        return view('educational-level.index');
    });

    Route::get('/major', function () {
        return view('major.index');
    });

    Route::get('/class', function () {
        return view('class.index');
    });

    Route::get('/class-student', function () {
        return view('class-student.index');
    });

    Route::get('/subject', function () {
        return view('subject.index');
    });

    Route::get('/learning-material', function () {
        return view('learning-material.index');
    });

    Route::get('/assignment', function () {
        return view('assignment.index');
    });

    Route::get('/api/students', [StudentController::class, 'index'])->name('students.index');
    Route::get('/api/students/{id}', [StudentController::class, 'show'])->name('students.show');
    Route::post('/api/students', [StudentController::class, 'store'])->name('students.store');
    Route::put('/api/students/{id}', [StudentController::class, 'update'])->name('students.update');
    Route::delete('/api/students/{id}', [StudentController::class, 'delete'])->name('students.delete');

    Route::get('/api/teachers', [TeacherController::class, 'index'])->name('teachers.index');
    Route::get('/api/teachers/{id}', [TeacherController::class, 'show'])->name('teachers.show');
    Route::post('/api/teachers', [TeacherController::class, 'store'])->name('teachers.store');
    Route::put('/api/teachers/{id}', [TeacherController::class, 'update'])->name('teachers.update');

    Route::get('/api/admins', [AdminController::class, 'index'])->name('admins.index');
    Route::get('/api/admins/{id}', [AdminController::class, 'show'])->name('admins.show');
    Route::post('/api/admins', [AdminController::class, 'store'])->name('admins.store');
    Route::put('/api/admins/{id}', [AdminController::class, 'update'])->name('admins.update');

    Route::get('/api/staffs', [StaffController::class, 'index'])->name('staffs.index');
    Route::get('/api/staffs/{id}', [StaffController::class, 'show'])->name('staffs.show');
    Route::post('/api/staffs', [StaffController::class, 'store'])->name('staffs.store');
    Route::put('/api/staffs/{id}', [StaffController::class, 'update'])->name('staffs.update');

    Route::get('/api/educational-levels', [EducationalLevelController::class, 'index'])->name('educational-levels.index');
    Route::get('/api/educational-levels/{id}', [EducationalLevelController::class, 'show'])->name('educational-levels.show');
    Route::post('api/educational-levels', [EducationalLevelController::class, 'store'])->name('educational-levels.store');
    Route::put('/api/educational-levels/{id}', [EducationalLevelController::class, 'update'])->name('educational-levels.update');

    Route::get('/api/majors', [MajorController::class, 'index'])->name('majors.index');
    Route::get('/api/majors/{id}', [MajorController::class, 'show'])->name('majors.show');
    Route::post('/api/majors', [MajorController::class, 'store'])->name('majors.store');
    Route::put('/api/majors/{id}', [MajorController::class, 'update'])->name('majors.update');

    Route::get('/api/class', [ClassController::class, 'index'])->name('class.index');
    Route::post('/api/class', [ClassController::class, 'store'])->name('class.store');
    Route::get('/api/class/create-data', [ClassController::class, 'getCreateData'])->name('class.create-data');
    Route::get('/api/class/{id}', [ClassController::class, 'show'])->name('class.show');
    Route::put('/api/class/{id}', [ClassController::class, 'update'])->name('class.update');
    Route::delete('/api/class/{id}', [ClassController::class, 'destroy'])->name('class.destroy');

    Route::get('/api/class-students', [ClassStudentController::class, 'index']);
    Route::post('/api/class-students', [ClassStudentController::class, 'store']);
    Route::get('/api/class-students/create-data', [ClassStudentController::class, 'getCreateData']);
    Route::delete('/api/class-students/{id}', [ClassStudentController::class, 'destroy']);

    Route::get('/api/subjects', [SubjectController::class, 'index'])->name('subjects.index');
    Route::post('/api/subjects', [SubjectController::class, 'store'])->name('subjects.store');
    Route::get('/api/subjects/create-data', [SubjectController::class, 'getCreateData'])->name('subjects.create-data');
    Route::get('/api/subjects/{id}', [SubjectController::class, 'show'])->name('subjects.show');
    Route::put('/api/subjects/{id}', [SubjectController::class, 'update'])->name('subjects.update');
    Route::delete('/api/subjects/{id}', [SubjectController::class, 'destroy'])->name('subjects.destroy');

    Route::get('/api/learning-materials', [LearningMaterialController::class, 'index'])->name('learning-materials.index');
    Route::post('/api/learning-materials', [LearningMaterialController::class, 'store'])->name('learning-materials.store');
    Route::get('/api/learning-materials/create-data', [LearningMaterialController::class, 'getCreateData'])->name('learning-materials.create-data');
    Route::get('/api/learning-materials/{id}', [LearningMaterialController::class, 'show'])->name('learning-materials.show');
    Route::put('/api/learning-materials/{id}', [LearningMaterialController::class, 'update'])->name('learning-materials.update');
    Route::delete('/api/learning-materials/{id}', [LearningMaterialController::class, 'destroy'])->name('learning-materials.destroy');

    Route::get('/api/assignments', [AssignmentController::class, 'index'])->name('assignments.index');
    Route::post('/api/assignments', [AssignmentController::class, 'store'])->name('assignments.store');
    Route::get('/api/assignments/create-data', [AssignmentController::class, 'getCreateData'])->name('assignments.create-data');
    Route::get('/api/assignments/{id}', [AssignmentController::class, 'show'])->name('assignments.show');
    Route::put('/api/assignments/{id}', [AssignmentController::class, 'update'])->name('assignments.update');
    Route::delete('/api/assignments/{id}', [AssignmentController::class, 'destroy'])->name('assignments.destroy');

});
