<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\AssignmentSubmissionController;
use App\Http\Controllers\ClassController;
use App\Http\Controllers\ClassStudentController;
use App\Http\Controllers\EducationalLevelController;
use App\Http\Controllers\LearningMaterialController;
use App\Http\Controllers\MajorController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\ModuleManagementController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\StudentAssigmentController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\TeacherAssignmentController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\TeacherSubmissionController;
use Illuminate\Support\Facades\Route;
use App\Models\LearningMaterial;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'verified'])->group(function () {

    // --- Page View Routes ---
    Route::get('/dashboard', function () {
        return view('dashboard.index');
    });

    Route::get('/student', function () {
        return view('student.index');
    });

    Route::get('/teacher', function () {
        return view('teacher.index');
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
    Route::get('/assignment/create', function () {
        return view('assignment.components.form');
    });
    Route::get('/module-management', function () {
        return view('module-management.index');
    });

    Route::get('/assignment-submission', function () {
        return view('assignment-submission.index');
    });

    Route::get('/student-assignment', function () {
        return view('student-assignment.index');
    });

    Route::get('/student-assignments/{id}/take', function () {
        return view('student-assignment.partials.form');
    })->name('student.assignments.show');

    Route::get('/teacher-assignment', function () {
        return view('teacher-assignment.index');
    });

    Route::get('/teacher-assignment/create', function () {
        return view('teacher-assignment.partials.create');
    });

    Route::get('/teacher-assignment/{id}/submissions', function () {
        return view('teacher-assignment.partials.show');
    })->name('teacher-assignment-submission.show');

    Route::get('/teacher-submission/{id}/grade', function () {
        return view('teacher-assignment.partials.grade');
    })->name('teacher-assignment.grade');

    Route::get('/api/teacher-assignment/{assignment}/submissions', [TeacherSubmissionController::class, 'getSubmissions'])->name('api.teacher.submissions');
    Route::get('/api/teacher-submissions/{submission}/grade', [TeacherSubmissionController::class, 'showGradeForm']);
    Route::post('/api/teacher-submissions/{submission}/grade', [TeacherSubmissionController::class, 'storeGrade']);

    Route::get('/api/student-assignments/{id}/show', [StudentAssigmentController::class, 'showForTaking'])->name('student-assignments.show');
    Route::post('/api/student-assignments/{id}/submit', [StudentAssigmentController::class, 'submitAnswers'])->name('student-assignments.submit');



    Route::get('/api/student-assignments', [StudentAssigmentController::class, 'index'])->name('student-assigments.index');

    Route::get('/api/roles', [RoleController::class, 'index'])->name('roles.index');
    Route::get('/api/permissions', [PermissionController::class, 'index'])->name('permissions.index');
    Route::get('/api/menus', [MenuController::class, 'index'])->name('menus.index');

    Route::post('api/module-management/menus', [ModuleManagementController::class, 'storeMenu'])->name('module-management.store-menu');
    Route::put('api/module-management/menus/{id}', [ModuleManagementController::class, 'updateMenu'])->name('module-management.update-menu');
    Route::get('/api/module-management/role-permissions', [ModuleManagementController::class, 'getPermissions'])->name('role-permissions.index');
    Route::post('api/module-management/role-permissions', [ModuleManagementController::class, 'savePermissions'])->name('module-management.store-permission');

    // --- Student API Routes ---
    Route::get('/api/students', [StudentController::class, 'index'])->name('students.index');
    Route::get('/api/students/{id}', [StudentController::class, 'show'])->name('students.show');
    Route::get('/api/students/create-data', [StudentController::class, 'getCreateData'])->name('students.create-data');
    Route::post('/api/students', [StudentController::class, 'store'])->name('students.store');
    Route::put('/api/students/{id}', [StudentController::class, 'update'])->name('students.update');
    Route::delete('/api/students/{id}', [StudentController::class, 'delete'])->name('students.delete');

    // --- Teacher API Routes ---
    Route::get('/api/teachers', [TeacherController::class, 'index'])->name('teachers.index');
    Route::get('/api/teachers/{id}', [TeacherController::class, 'show'])->name('teachers.show');
    Route::post('/api/teachers', [TeacherController::class, 'store'])->name('teachers.store');
    Route::put('/api/teachers/{id}', [TeacherController::class, 'update'])->name('teachers.update');

    // --- Staff API Routes ---
    Route::get('/api/staffs', [StaffController::class, 'index'])->name('staffs.index');
    Route::get('/api/staffs/{id}', [StaffController::class, 'show'])->name('staffs.show');
    Route::post('/api/staffs', [StaffController::class, 'store'])->name('staffs.store');
    Route::put('/api/staffs/{id}', [StaffController::class, 'update'])->name('staffs.update');

    // --- Educational Level API Routes ---
    Route::get('/api/educational-levels', [EducationalLevelController::class, 'index'])->name('educational-levels.index');
    Route::get('/api/educational-levels/{id}', [EducationalLevelController::class, 'show'])->name('educational-levels.show');
    Route::post('api/educational-levels', [EducationalLevelController::class, 'store'])->name('educational-levels.store');
    Route::put('/api/educational-levels/{id}', [EducationalLevelController::class, 'update'])->name('educational-levels.update');

    // --- Major API Routes ---
    Route::get('/api/majors', [MajorController::class, 'index'])->name('majors.index');
    Route::get('/api/majors/{id}', [MajorController::class, 'show'])->name('majors.show');
    Route::post('/api/majors', [MajorController::class, 'store'])->name('majors.store');
    Route::put('/api/majors/{id}', [MajorController::class, 'update'])->name('majors.update');

    // --- Class API Routes ---
    Route::get('/api/class', [ClassController::class, 'index'])->name('class.index');
    Route::post('/api/class', [ClassController::class, 'store'])->name('class.store');
    Route::get('/api/class/create-data', [ClassController::class, 'getCreateData'])->name('class.create-data');
    Route::get('/api/class/{id}', [ClassController::class, 'show'])->name('class.show');
    Route::put('/api/class/{id}', [ClassController::class, 'update'])->name('class.update');
    Route::delete('/api/class/{id}', [ClassController::class, 'destroy'])->name('class.destroy');

    // --- Class Student API Routes ---
    Route::get('/api/class-students', [ClassStudentController::class, 'index']);
    Route::post('/api/class-students', [ClassStudentController::class, 'store']);
    Route::get('/api/class-students/create-data', [ClassStudentController::class, 'getCreateData']);
    Route::delete('/api/class-students/{id}', [ClassStudentController::class, 'destroy']);

    // --- Subject API Routes ---
    Route::get('/api/subjects', [SubjectController::class, 'index'])->name('subjects.index');
    Route::post('/api/subjects', [SubjectController::class, 'store'])->name('subjects.store');
    Route::get('/api/subjects/create-data', [SubjectController::class, 'getCreateData'])->name('subjects.create-data');
    Route::get('/api/subjects/{id}', [SubjectController::class, 'show'])->name('subjects.show');
    Route::put('/api/subjects/{id}', [SubjectController::class, 'update'])->name('subjects.update');
    Route::delete('/api/subjects/{id}', [SubjectController::class, 'destroy'])->name('subjects.destroy');

    // --- Learning Material API Routes ---
    Route::get('/api/learning-materials', [LearningMaterialController::class, 'index'])->name('learning-materials.index');
    Route::post('/api/learning-materials', [LearningMaterialController::class, 'store'])->name('learning-materials.store');
    Route::get('/api/learning-materials/create-data', [LearningMaterialController::class, 'getCreateData'])->name('learning-materials.create-data');
    Route::get('/api/learning-materials/{id}', [LearningMaterialController::class, 'show'])->name('learning-materials.show');
    Route::put('/api/learning-materials/{id}', [LearningMaterialController::class, 'update'])->name('learning-materials.update');
    Route::delete('/api/learning-materials/{id}', [LearningMaterialController::class, 'destroy'])->name('learning-materials.destroy');

    // --- Assignment API Routes ---
    Route::get('/api/assignments', [AssignmentController::class, 'index'])->name('assignments.index');
    Route::get('/assignments/{id}/edit', [AssignmentController::class, 'edit'])->name('assignments.index');
    Route::post('/api/assignments', [AssignmentController::class, 'store'])->name('assignments.store');
    Route::get('/api/assignments/create-data', [AssignmentController::class, 'getCreateData'])->name('assignments.create-data');
    Route::get('/api/assignments/{id}', [AssignmentController::class, 'show'])->name('assignments.show');
    Route::put('/api/assignments/{id}', [AssignmentController::class, 'update'])->name('assignments.update');
    Route::delete('/api/assignments/{id}', [AssignmentController::class, 'destroy'])->name('assignments.destroy');

    // --- Assignment Submission API Routes ---
    Route::get('/assignment-submissions', [AssignmentSubmissionController::class, 'index'])->name('all-submissions.page');
    Route::get('/assignment/{assignmentId}/submissions', [AssignmentSubmissionController::class, 'viewSubmissionsPage'])->name('assignment.submissions.page');
    Route::get('/api/assignment-submissions', [AssignmentSubmissionController::class, 'getAllSubmissions'])->name('all-submissions.api.index');
    Route::get('/api/assignments/{assignmentId}/submissions', [AssignmentSubmissionController::class, 'index'])->name('assignment.submissions.index');
    Route::get('/api/submissions/{submissionId}', [AssignmentSubmissionController::class, 'show'])->name('submission.show');
    Route::post('/api/submissions/{submissionId}/grade', [AssignmentSubmissionController::class, 'grade'])->name('submission.grade');

    Route::get('student-assigments', [StudentAssigmentController::class, 'index'])->name('student-assigments.index');

    Route::get('/api/teacher/assignments', [TeacherAssignmentController::class, 'getAssignments']);
    Route::get('/api/teacher/assignments/filters', [TeacherAssignmentController::class, 'getFilterData']);
    Route::get('/teacher/assignment/{assignment}/submissions', [TeacherSubmissionController::class, 'getSubmissions']);

});
