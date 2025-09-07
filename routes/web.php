<?php

use App\Http\Controllers\MajorController;
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

    Route::get('/major', function () {
        return view('major.index');
    });

    Route::get('/api/students', [StudentController::class, 'index'])->name('students.index');
    Route::get('/api/students/{id}', [StudentController::class, 'show'])->name('students.show');
    Route::post('/api/students', [StudentController::class, 'store'])->name('students.store');
    Route::put('/api/students/{id}', [StudentController::class, 'update'])->name('students.update');
    Route::delete('/api/students/{id}', [StudentController::class, 'delete'])->name('students.delete');

    // Route::get('/major')
    Route::get('/api/majors', [MajorController::class, 'index'])->name('majors.index');
    Route::get('/api/majors/{id}', [MajorController::class, 'show'])->name('majors.show');
    Route::post('api/majors', [MajorController::class, 'store'])->name('majors.store');

    Route::get('/api/teachers', [TeacherController::class, 'index'])->name('teachers.index');
    Route::get('/api/teachers/{id}', [TeacherController::class, 'show'])->name('teachers.show');
});
