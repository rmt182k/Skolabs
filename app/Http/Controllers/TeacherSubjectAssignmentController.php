<?php

namespace App\Http\Controllers;

use App\Models\Classes;
use App\Models\ClassSubject;
use App\Models\Subject;
use App\Models\Teacher; // Import Teacher model
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

// Ganti nama class agar lebih sesuai
class TeacherSubjectAssignmentController extends Controller
{
    /**
     * Helper to get the active academic year ID.
     */
    private function getActiveAcademicYearId()
    {
        $activeYear = DB::table('academic_years')->where('status', 'active')->first();
        if (!$activeYear) {
            // Lemparkan exception yang lebih spesifik jika diperlukan
            throw new Exception("No active academic year found. Please set one up.");
        }
        return $activeYear->id;
    }

    /**
     * Provide data for the DataTable, with filtering.
     */
    public function data(Request $request)
    {
        try {
            $activeYearId = $this->getActiveAcademicYearId();

            $query = ClassSubject::query()
                ->join('classes', 'class_subjects.class_id', '=', 'classes.id')
                ->join('subjects', 'class_subjects.subject_id', '=', 'subjects.id')
                ->join('teachers', 'class_subjects.teacher_id', '=', 'teachers.id')
                ->join('users', 'teachers.user_id', '=', 'users.id')
                ->where('class_subjects.academic_year_id', $activeYearId);

            // Terapkan filter
            if ($request->filled('class_id')) {
                $query->where('class_subjects.class_id', $request->class_id);
            }
            if ($request->filled('subject_id')) {
                $query->where('class_subjects.subject_id', $request->subject_id);
            }
            if ($request->filled('teacher_id')) {
                $query->where('class_subjects.teacher_id', $request->teacher_id);
            }

            // Ganti nama variabel
            $assignmentsData = $query->select(
                'class_subjects.id',
                'classes.name as class_name',
                'subjects.name as subject_name',
                'users.name as teacher_name'
            )
                ->orderBy('classes.name')
                ->orderBy('subjects.name')
                ->get();

            // Ganti nama variabel
            $formattedAssignments = $assignmentsData->map(function ($item) {
                return [
                    'id' => $item->id,
                    'class' => ['name' => $item->class_name],
                    'subject' => ['name' => $item->subject_name],
                    'teacher' => ['name' => $item->teacher_name],
                ];
            });

            return response()->json(['success' => true, 'data' => $formattedAssignments]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Provide grouped data for the Card View, with filtering.
     */
    public function dataGroupedByClass(Request $request)
    {
        try {
            $activeYearId = $this->getActiveAcademicYearId();

            // Query sama dengan method data(), bisa di-refactor jika perlu
            $query = ClassSubject::query()
                ->join('classes', 'class_subjects.class_id', '=', 'classes.id')
                ->join('subjects', 'class_subjects.subject_id', '=', 'subjects.id')
                ->join('teachers', 'class_subjects.teacher_id', '=', 'teachers.id')
                ->join('users', 'teachers.user_id', '=', 'users.id')
                ->where('class_subjects.academic_year_id', $activeYearId);

            if ($request->filled('class_id')) {
                $query->where('class_subjects.class_id', $request->class_id);
            }
            if ($request->filled('subject_id')) {
                $query->where('class_subjects.subject_id', $request->subject_id);
            }
            if ($request->filled('teacher_id')) {
                $query->where('class_subjects.teacher_id', $request->teacher_id);
            }

            // Ganti nama variabel
            $assignmentsData = $query->select(
                'class_subjects.id',
                'classes.name as class_name',
                'subjects.name as subject_name',
                'users.name as teacher_name'
            )
                ->orderBy('classes.name')
                ->orderBy('subjects.name')
                ->get();

            // Ganti nama variabel
            $groupedAssignments = $assignmentsData->groupBy('class_name');

            return response()->json(['success' => true, 'data' => $groupedAssignments]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Get distinct data for populating filter dropdowns.
     */
    public function getFilterData()
    {
        try {
            $activeYearId = $this->getActiveAcademicYearId();

            $query = ClassSubject::where('academic_year_id', $activeYearId);

            $classIds = $query->distinct()->pluck('class_id');
            $subjectIds = $query->distinct()->pluck('subject_id');
            $teacherIds = $query->distinct()->pluck('teacher_id');

            $data = [
                'classes' => Classes::whereIn('id', $classIds)->orderBy('name')->get(['id', 'name']),
                'subjects' => Subject::whereIn('id', $subjectIds)->orderBy('name')->get(['id', 'name']),
                'teachers' => Teacher::join('users', 'teachers.user_id', '=', 'users.id')
                    ->whereIn('teachers.id', $teacherIds)
                    ->orderBy('users.name')
                    ->get(['teachers.id', 'users.name']),
            ];
            return response()->json(['success' => true, 'data' => $data]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Get data for creating a new assignment.
     */
    public function createData()
    {
        $data = [
            'classes' => Classes::select('id', 'name')->orderBy('name')->get(),
            'subjects' => Subject::select('id', 'name')->orderBy('name')->get(),
        ];
        return response()->json(['success' => true, 'data' => $data]);
    }

    /**
     * Store a newly created assignment.
     */
    public function store(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'required|exists:teachers,id',
        ]);

        try {
            $activeYearId = $this->getActiveAcademicYearId();

            $exists = ClassSubject::where('class_id', $request->class_id)
                ->where('subject_id', $request->subject_id)
                ->where('academic_year_id', $activeYearId)
                ->exists();

            if ($exists) {
                return response()->json(['message' => 'This subject is already assigned in this class for the current academic year.'], 422);
            }

            ClassSubject::create(array_merge($request->all(), [
                'academic_year_id' => $activeYearId
            ]));

            // Ganti pesan
            return response()->json(['success' => true, 'message' => 'Teaching assignment created successfully.']);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified assignment.
     */
    // Ganti nama variabel parameter
    public function show($id)
    {
        $classSubject = ClassSubject::findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => $classSubject
        ]);
    }

    /**
     * Update the specified assignment.
     */
    // Ganti nama variabel parameter
    public function update(Request $request, ClassSubject $teachingAssignment)
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'required|exists:teachers,id',
        ]);

        try {
            $academicYearId = $teachingAssignment->academic_year_id;

            $exists = ClassSubject::where('class_id', $request->class_id)
                ->where('subject_id', $request->subject_id)
                ->where('academic_year_id', $academicYearId)
                ->where('id', '!=', $teachingAssignment->id) // Cek duplikasi di luar data yang sedang diedit
                ->exists();

            if ($exists) {
                return response()->json(['message' => 'This subject is already assigned to another teacher in this class for this academic year.'], 422);
            }

            $teachingAssignment->update($request->all());

            // Ganti pesan
            return response()->json(['success' => true, 'message' => 'Teaching assignment updated successfully.']);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified assignment.
     */
    // Ganti nama variabel parameter
    public function destroy(ClassSubject $teachingAssignment)
    {
        try {
            $teachingAssignment->delete();
            // Ganti pesan
            return response()->json(['success' => true, 'message' => 'Teaching assignment deleted successfully.']);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
