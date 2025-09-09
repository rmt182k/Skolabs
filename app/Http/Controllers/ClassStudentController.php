<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Exception;

class ClassStudentController extends Controller
{
    /**
     * Display a listing of the resource for DataTables.
     */
    public function index(): JsonResponse
    {
        try {
            // Mengambil data penugasan siswa dengan join beberapa tabel
            $assignments = DB::table('class_students as cs') // DIUBAH: Menggunakan nama tabel 'class_students'
                ->select(
                    'cs.id',
                    'cs.created_at',
                    'c.name as class_name',
                    's_user.name as student_name',
                    'm.name as major_name',
                    't_user.name as teacher_name'
                )
                ->leftJoin('classes as c', 'cs.class_id', '=', 'c.id')
                ->leftJoin('students as s', 'cs.student_id', '=', 's.id')
                ->leftJoin('users as s_user', 's.user_id', '=', 's_user.id')
                ->leftJoin('majors as m', 'c.major_id', '=', 'm.id')
                ->leftJoin('teachers as t', 'c.teacher_id', '=', 't.id')
                ->leftJoin('users as t_user', 't.user_id', '=', 't_user.id')
                ->orderBy('cs.created_at', 'desc')
                ->get();

            // Memformat data agar sesuai dengan yang diharapkan oleh DataTables (nested object)
            $formattedData = $assignments->map(function ($item) {
                return [
                    'id' => $item->id,
                    'class' => [
                        'name' => $item->class_name,
                        'major' => ['name' => $item->major_name],
                        'teacher' => ['name' => $item->teacher_name],
                    ],
                    'student' => [
                        'name' => $item->student_name,
                    ],
                    'created_at' => $item->created_at,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Student assignments retrieved successfully.',
                'data' => $formattedData
            ]);
        } catch (Exception $e) {
            Log::error('Failed to retrieve student assignments: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve student assignments. ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'class_id' => 'required|integer|exists:classes,id',
            'student_ids' => 'required|array',
            'student_ids.*' => 'required|integer|exists:students,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors.',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $classId = $request->class_id;
            $studentIds = $request->student_ids;
            $assignmentsToInsert = [];

            foreach ($studentIds as $studentId) {
                // Cek apakah siswa sudah ada di kelas tersebut untuk menghindari duplikat
                $exists = DB::table('class_students') // DIUBAH: Menggunakan nama tabel 'class_students'
                    ->where('class_id', $classId)
                    ->where('student_id', $studentId)
                    ->exists();

                if (!$exists) {
                    $assignmentsToInsert[] = [
                        'class_id' => $classId,
                        'student_id' => $studentId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            if (!empty($assignmentsToInsert)) {
                DB::table('class_students')->insert($assignmentsToInsert); // DIUBAH: Menggunakan nama tabel 'class_students'
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Students assigned to class successfully.',
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to assign students: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to assign students. ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id): JsonResponse
    {
        try {
            $deleted = DB::table('class_students')->where('id', $id)->delete(); // DIUBAH: Menggunakan nama tabel 'class_students'

            if ($deleted === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Assignment not found.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Student removed from class successfully.'
            ]);
        } catch (Exception $e) {
            Log::error('Failed to delete student assignment: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete student assignment. ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get data required for the create/edit form.
     */
    public function getCreateData(): JsonResponse
    {
        try {
            $classes = DB::table('classes')
                ->select('id', 'name')
                ->orderBy('name', 'asc')
                ->get();

            $students = DB::table('students')
                ->join('users', 'students.user_id', '=', 'users.id')
                ->select('students.id', 'users.name')
                ->orderBy('users.name', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'classes' => $classes,
                    'students' => $students
                ]
            ]);
        } catch (Exception $e) {
            Log::error('Failed to retrieve create-data for assignments: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve necessary data. ' . $e->getMessage(),
            ], 500);
        }
    }
}

