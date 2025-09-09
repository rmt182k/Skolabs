<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Exception;

class SubjectController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $subjects = DB::table('subjects')
                ->select(
                    'subjects.id',
                    'subjects.name',
                    'subjects.code',
                    'users.name as teacher_name'
                )
                ->leftJoin('teachers', 'subjects.teacher_id', '=', 'teachers.id')
                ->leftJoin('users', 'teachers.user_id', '=', 'users.id')
                ->orderBy('subjects.name', 'asc')
                ->get();

            $formattedData = $subjects->map(function ($subject) {
                return [
                    'id' => $subject->id,
                    'name' => $subject->name,
                    'code' => $subject->code,
                    'teacher' => [
                        'name' => $subject->teacher_name,
                    ],
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'All subjects data retrieved successfully.',
                'data' => $formattedData
            ]);
        } catch (Exception $e) {
            Log::error('Failed to retrieve subjects data: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve subjects data. ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:subjects,code',
            'teacher_id' => 'required|exists:teachers,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Menggunakan Query Builder untuk insert
            $subjectId = DB::table('subjects')->insertGetId([
                'name' => $request->name,
                'code' => $request->code,
                'teacher_id' => $request->teacher_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $newSubject = DB::table('subjects')->where('id', $subjectId)->first();

            return response()->json([
                'success' => true,
                'message' => 'Subject created successfully.',
                'data' => $newSubject
            ], 201);
        } catch (Exception $e) {
            Log::error('Failed to create subject: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create subject. ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource for editing.
     */
    public function show($id): JsonResponse
    {
        try {
            $subject = DB::table('subjects')->where('id', $id)->first();

            if (!$subject) {
                return response()->json([
                    'success' => false,
                    'message' => 'Subject not found.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $subject
            ]);
        } catch (Exception $e) {
            Log::error('Failed to find subject: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to find subject. ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:subjects,code,' . $id,
            'teacher_id' => 'required|exists:teachers,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Menggunakan Query Builder untuk update
            $affected = DB::table('subjects')
                ->where('id', $id)
                ->update([
                    'name' => $request->name,
                    'code' => $request->code,
                    'teacher_id' => $request->teacher_id,
                    'updated_at' => now(),
                ]);

            if ($affected === 0) {
                 return response()->json([
                    'success' => false,
                    'message' => 'Subject not found or no changes were made.'
                ], 404);
            }

            $updatedSubject = DB::table('subjects')->where('id', $id)->first();

            return response()->json([
                'success' => true,
                'message' => 'Subject updated successfully.',
                'data' => $updatedSubject
            ]);
        } catch (Exception $e) {
            Log::error('Failed to update subject: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update subject. ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $deleted = DB::table('subjects')->where('id', $id)->delete();

            if ($deleted === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Subject not found.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Subject deleted successfully.'
            ]);
        } catch (Exception $e) {
            Log::error('Failed to delete subject: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete subject. ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getCreateData(): JsonResponse
    {
        try {
            $teachers = DB::table('teachers')
                ->join('users', 'teachers.user_id', '=', 'users.id')
                ->select('teachers.id', 'users.name')
                ->orderBy('users.name', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'teachers' => $teachers
                ]
            ]);
        } catch (Exception $e) {
            Log::error('Failed to retrieve create-data for subjects: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve necessary data. ' . $e->getMessage(),
            ], 500);
        }
    }
}
