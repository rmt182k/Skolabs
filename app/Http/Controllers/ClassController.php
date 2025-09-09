<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Exception;

class ClassController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $classes = DB::table('classes')
                ->select(
                    'classes.id',
                    'classes.name',
                    'majors.name as major_name',
                    'users.name as teacher_name'
                )
                ->leftJoin('majors', 'classes.major_id', '=', 'majors.id')
                ->leftJoin('teachers', 'classes.teacher_id', '=', 'teachers.id')
                ->leftJoin('users', 'teachers.user_id', '=', 'users.id')
                ->orderBy('classes.name', 'asc')
                ->get();

            $formattedData = $classes->map(function ($class) {
                return [
                    'id' => $class->id,
                    'name' => $class->name,
                    'major' => [
                        'name' => $class->major_name,
                    ],
                    'teacher' => [
                        'name' => $class->teacher_name,
                    ],
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'All classes data retrieved successfully.',
                'data' => $formattedData
            ]);
        } catch (Exception $e) {
            Log::error('Failed to retrieve classes data: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve classes data. ' . $e->getMessage()
            ], 500);
        }
    }
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:classes,name',
            'major_id' => 'required|exists:majors,id',
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
            $classId = DB::table('classes')->insertGetId([
                'name' => $request->name,
                'major_id' => $request->major_id,
                'teacher_id' => $request->teacher_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $newClass = DB::table('classes')->where('id', $classId)->first();

            return response()->json([
                'success' => true,
                'message' => 'Class created successfully.',
                'data' => $newClass
            ], 201);
        } catch (Exception $e) {
            Log::error('Failed to create class: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create class. ' . $e->getMessage(),
            ], 500);
        }
    }
    public function show($id): JsonResponse
    {
        try {
            $class = DB::table('classes')->where('id', $id)->first();

            if (!$class) {
                return response()->json([
                    'success' => false,
                    'message' => 'Class not found.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $class
            ]);
        } catch (Exception $e) {
            Log::error('Failed to find class: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to find class. ' . $e->getMessage(),
            ], 500);
        }
    }
    public function update(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:classes,name,' . $id,
            'major_id' => 'required|exists:majors,id',
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
            $affected = DB::table('classes')
                ->where('id', $id)
                ->update([
                    'name' => $request->name,
                    'major_id' => $request->major_id,
                    'teacher_id' => $request->teacher_id,
                    'updated_at' => now(),
                ]);

            if ($affected === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Class not found or no changes were made.'
                ], 404);
            }

            $updatedClass = DB::table('classes')->where('id', $id)->first();

            return response()->json([
                'success' => true,
                'message' => 'Class updated successfully.',
                'data' => $updatedClass
            ]);
        } catch (Exception $e) {
            Log::error('Failed to update class: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update class. ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $deleted = DB::table('classes')->where('id', $id)->delete();

            if ($deleted === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Class not found.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Class deleted successfully.'
            ]);
        } catch (Exception $e) {
            Log::error('Failed to delete class: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete class. ' . $e->getMessage(),
            ], 500);
        }
    }
    public function getCreateData(): JsonResponse
    {
        try {
            $majors = DB::table('majors')->select('id', 'name')->get();

            $teachers = DB::table('teachers')
                ->join('users', 'teachers.user_id', '=', 'users.id')
                ->select('teachers.id', 'users.name')
                ->orderBy('users.name', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'majors' => $majors,
                    'teachers' => $teachers
                ]
            ]);
        } catch (Exception $e) {
            Log::error('Failed to retrieve create-data: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve necessary data. ' . $e->getMessage(),
            ], 500);
        }
    }
}
