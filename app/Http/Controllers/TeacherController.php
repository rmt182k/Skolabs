<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class TeacherController extends Controller
{
    public function index()
    {
        try {
            $teachers = Teacher::all();

            if ($teachers->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'No teachers found.',
                    'data' => []
                ], 200);
            }

            return response()->json([
                'success' => true,
                'message' => 'Teachers retrieved successfully.',
                'data' => $teachers
            ], 200);

        } catch (Exception $e) {
            Log::error('Error fetching teachers: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve teachers. Please try again later.'
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $teacher = Teacher::findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Teacher retrieved successfully.',
                'data' => $teacher
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Teacher not found.'
            ], 404);
        } catch (Exception $e) {
            Log::error('Error fetching teacher with ID ' . $id . ': ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve teacher. Please try again later.'
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
            ]);

            $teacher = Teacher::create([
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'password' => Hash::make('password2025'),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Teacher created successfully',
                'data' => $teacher
            ], 201);

        } catch (Exception $e) {
            Log::error('Error creating teacher: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to create teacher. Please try again later.'
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $teacher = Teacher::findOrFail($id);

            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            ]);

            $teacher->update($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Teacher updated successfully',
                'data' => $teacher
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Teacher not found.'
            ], 404);
        } catch (Exception $e) {
            Log::error('Error updating teacher: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update teacher. Please try again later.'
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $teacher = Teacher::findOrFail($id);

            $teacher->delete();

            return response()->json([
                'success' => true,
                'message' => 'Teacher deleted successfully'
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Teacher not found.'
            ], 404);
        } catch (Exception $e) {
            Log::error('Error deleting teacher: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete teacher. Please try again later.'
            ], 500);
        }
    }
}
