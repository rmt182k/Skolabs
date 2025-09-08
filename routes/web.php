<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\EducationalLevelController;
use App\Http\Controllers\MajorController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TeacherController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::group(['auth', 'verified'], function () {

    Route::get('/dashboard', function () {
        return view('dashboard.index');
    });

    Route::get('/student', function () {
        return view('student.index');
    });

    Route::get('/teacher', function () {
        return view('teacher.index');
    });

    Route::get('/admin', function(){
        return view('admin.index');
    });

    Route::get('/staff', function(){
        return view('staff.index');
    });

    Route::get('/educational-level', function(){
        return view('educational-level.index');
    });

    Route::get('/major', function () {
        return view('major.index');
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
});
