<?php

namespace App\Http\Controllers;

use App\Models\Classes;
use App\Models\ClassSubject;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class TeacherSubjectAssignmentController extends Controller
{
    /**
     * Helper to get the active academic year object.
     * GAYA PENULISAN: Diubah untuk mengembalikan seluruh object.
     */
    private function getActiveAcademicYear()
    {
        // Ganti 'academic_years' dengan nama tabel yang sesuai jika berbeda
        $activeYear = DB::table('academic_years')->where('status', 'active')->first();
        if (!$activeYear) {
            throw new Exception("No active academic year found. Please set one up.");
        }
        return $activeYear;
    }

    /**
     * Provide data for the DataTable, with filtering.
     */
    public function data(Request $request)
    {
        try {
            $activeYear = $this->getActiveAcademicYear();

            $query = ClassSubject::query()
                ->join('classes', 'class_subjects.class_id', '=', 'classes.id')
                ->join('subjects', 'class_subjects.subject_id', '=', 'subjects.id')
                ->join('teachers', 'class_subjects.teacher_id', '=', 'teachers.id')
                ->join('users', 'teachers.user_id', '=', 'users.id')
                ->where('class_subjects.academic_year_id', $activeYear->id);

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

            $assignmentsData = $query->select(
                'class_subjects.id',
                'classes.name as class_name',
                'subjects.name as subject_name',
                'users.name as teacher_name'
            )
                ->orderBy('classes.name')
                ->orderBy('subjects.name')
                ->get();

            $formattedAssignments = $assignmentsData->map(function ($item) {
                return [
                    'id' => $item->id,
                    'class' => ['name' => $item->class_name],
                    'subject' => ['name' => $item->subject_name],
                    'teacher' => ['name' => $item->teacher_name],
                ];
            });

            // GAYA PENULISAN: Tambahkan academic_year ke response.
            // Ganti 'year' dengan nama kolom yang sesuai (misal: 'name', 'academic_year').
            return response()->json([
                'success' => true,
                'data' => $formattedAssignments,
                'academic_year' => $activeYear->year
            ]);

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
            $activeYear = $this->getActiveAcademicYear();

            // Query sama dengan method data()
            $query = ClassSubject::query()
                ->join('classes', 'class_subjects.class_id', '=', 'classes.id')
                ->join('subjects', 'class_subjects.subject_id', '=', 'subjects.id')
                ->join('teachers', 'class_subjects.teacher_id', '=', 'teachers.id')
                ->join('users', 'teachers.user_id', '=', 'users.id')
                ->where('class_subjects.academic_year_id', $activeYear->id);

            if ($request->filled('class_id')) {
                $query->where('class_subjects.class_id', $request->class_id);
            }
            if ($request->filled('subject_id')) {
                $query->where('class_subjects.subject_id', $request->subject_id);
            }
            if ($request->filled('teacher_id')) {
                $query->where('class_subjects.teacher_id', $request->teacher_id);
            }

            $assignmentsData = $query->select(
                'class_subjects.id',
                'classes.name as class_name',
                'subjects.name as subject_name',
                'users.name as teacher_name'
            )
                ->orderBy('classes.name')
                ->orderBy('subjects.name')
                ->get();

            $groupedAssignments = $assignmentsData->groupBy('class_name');

            // GAYA PENULISAN: Tambahkan academic_year ke response.
            return response()->json([
                'success' => true,
                'data' => $groupedAssignments,
                'academic_year' => $activeYear->year
            ]);

        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ... sisa method (getFilterData, createData, store, show, update, destroy) tetap sama ...
    // ... (kode dari user sudah baik dan tidak perlu diubah untuk fungsionalitas ini) ...

    /**
     * Get distinct data for populating filter dropdowns.
     */
    public function getFilterData()
    {
        try {
            $activeYear = $this->getActiveAcademicYear();

            $query = ClassSubject::where('academic_year_id', $activeYear->id);

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
            $activeYear = $this->getActiveAcademicYear();

            $exists = ClassSubject::where('class_id', $request->class_id)
                ->where('subject_id', $request->subject_id)
                ->where('academic_year_id', $activeYear->id)
                ->exists();

            if ($exists) {
                return response()->json(['message' => 'This subject is already assigned in this class for the current academic year.'], 422);
            }

            ClassSubject::create(array_merge($request->all(), [
                'academic_year_id' => $activeYear->id
            ]));

            return response()->json(['success' => true, 'message' => 'Teaching assignment created successfully.']);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified assignment.
     */
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
    public function update(Request $request, $id)
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'required|exists:teachers,id',
        ]);

        try {
            $classSubject = ClassSubject::findOrFail($id);
            $academicYearId = $classSubject->academic_year_id;

            $exists = ClassSubject::where('class_id', $request->class_id)
                ->where('subject_id', $request->subject_id)
                ->where('academic_year_id', $academicYearId)
                ->where('id', '!=', $classSubject->id)
                ->exists();

            if ($exists) {
                return response()->json(['message' => 'This subject is already assigned to another teacher in this class for this academic year.'], 422);
            }

            $classSubject->update($request->all());

            return response()->json(['success' => true, 'message' => 'Teaching assignment updated successfully.']);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified assignment.
     */
    public function destroy(ClassSubject $teachingAssignment)
    {
        try {
            $teachingAssignment->delete();
            return response()->json(['success' => true, 'message' => 'Teaching assignment deleted successfully.']);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
