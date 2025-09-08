<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Role;
use App\Models\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class TeacherController extends Controller
{
    public function index()
    {
        try {
            $teachers = DB::table('teachers')
                ->select(
                    'teachers.id',
                    'users.name',
                    'users.email',
                    'teachers.employee_id',
                    'teachers.status',
                    'teachers.phone_number',
                    'teachers.date_of_birth',
                    'teachers.gender',
                    'teachers.address'
                )
                ->join('users', 'teachers.user_id', '=', 'users.id')
                ->orderBy('teachers.created_at', 'DESC')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Teachers retrieved successfully.',
                'data' => $teachers
            ], 200);

        } catch (Exception $e) {
            Log::error('Error fetching teachers: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve teachers. ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $teacher = DB::table('teachers')
                ->select(
                    'teachers.id',
                    'teachers.user_id',
                    'users.name',
                    'users.email',
                    'teachers.employee_id',
                    'teachers.date_of_birth',
                    'teachers.gender',
                    'teachers.phone_number',
                    'teachers.address',
                    'teachers.status',
                    'teachers.created_at',
                    'teachers.updated_at'
                )
                ->join('users', 'teachers.user_id', '=', 'users.id')
                ->where('teachers.id', $id)
                ->first();

            if (!$teacher) {
                return response()->json(['success' => false, 'message' => 'Teacher not found.'], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Teacher retrieved successfully.',
                'data' => $teacher
            ], 200);

        } catch (Exception $e) {
            Log::error('Error fetching teacher with ID ' . $id . ': ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to retrieve teacher.'], 500);
        }
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email',
                'password' => 'nullable|string|min:8',
                'employee_id' => 'required|string|max:50|unique:teachers,employee_id',
                'date_of_birth' => 'nullable|date',
                'gender' => 'nullable|in:male,female',
                'phone_number' => 'nullable|string|max:20',
                'address' => 'nullable|string',
                'status' => 'nullable|in:active,inactive',
            ]);

            $password = $validatedData['password'] ?? 'password';

            $userId = DB::table('users')->insertGetId([
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'password' => Hash::make($password),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $role = Role::firstOrCreate(['name' => 'teacher'], ['description' => 'Default role for teachers']);
            UserRole::create(['user_id' => $userId, 'role_id' => $role->id]);

            $teacherId = DB::table('teachers')->insertGetId([
                'user_id' => $userId,
                'employee_id' => $validatedData['employee_id'],
                'date_of_birth' => $validatedData['date_of_birth'] ?? null,
                'gender' => $validatedData['gender'] ?? null,
                'phone_number' => $validatedData['phone_number'] ?? null,
                'address' => $validatedData['address'] ?? null,
                'status' => $validatedData['status'] ?? 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            $formattedData = [
                'id' => $teacherId,
                'user_id' => $userId,
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'employee_id' => $validatedData['employee_id'],
                'date_of_birth' => $validatedData['date_of_birth'] ?? null,
                'gender' => $validatedData['gender'] ?? null,
                'phone_number' => $validatedData['phone_number'] ?? null,
                'address' => $validatedData['address'] ?? null,
                'status' => $validatedData['status'] ?? 'active',
                'created_at' => now()->toDateTimeString(),
                'updated_at' => now()->toDateTimeString()
            ];

            return response()->json(['success' => true, 'message' => 'Teacher created successfully.', 'data' => $formattedData], 201);
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Validation failed.', 'errors' => $e->errors()], 422);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error creating teacher: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $teacher = DB::table('teachers')->where('id', $id)->first();
            if (!$teacher) {
                return response()->json(['success' => false, 'message' => 'Teacher not found.'], 404);
            }

            $user = DB::table('users')->where('id', $teacher->user_id)->first();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Associated user not found.'], 404);
            }

            $validatedData = $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $user->id,
                'employee_id' => 'sometimes|required|string|max:50|unique:teachers,employee_id,' . $teacher->id,
                'password' => 'nullable|string|min:8',
                'date_of_birth' => 'nullable|date',
                'gender' => 'nullable|in:male,female',
                'phone_number' => 'nullable|string|max:20',
                'address' => 'nullable|string',
                'status' => 'nullable|in:active,inactive',
            ]);

            $userUpdateData = [];
            if ($request->has('name'))
                $userUpdateData['name'] = $validatedData['name'];
            if ($request->has('email'))
                $userUpdateData['email'] = $validatedData['email'];
            if ($request->filled('password'))
                $userUpdateData['password'] = Hash::make($validatedData['password']);

            if (!empty($userUpdateData)) {
                $userUpdateData['updated_at'] = now();
                DB::table('users')->where('id', $user->id)->update($userUpdateData);
            }

            $teacherUpdateData = [];
            if ($request->has('employee_id'))
                $teacherUpdateData['employee_id'] = $validatedData['employee_id'];
            if ($request->has('date_of_birth'))
                $teacherUpdateData['date_of_birth'] = $validatedData['date_of_birth'];
            if ($request->has('gender'))
                $teacherUpdateData['gender'] = $validatedData['gender'];
            if ($request->has('phone_number'))
                $teacherUpdateData['phone_number'] = $validatedData['phone_number'];
            if ($request->has('address'))
                $teacherUpdateData['address'] = $validatedData['address'];
            if ($request->has('status'))
                $teacherUpdateData['status'] = $validatedData['status'];

            if (!empty($teacherUpdateData)) {
                $teacherUpdateData['updated_at'] = now();
                DB::table('teachers')->where('id', $id)->update($teacherUpdateData);
            }

            DB::commit();

            $updatedTeacher = DB::table('teachers')
                ->select(
                    'teachers.id',
                    'teachers.user_id',
                    'users.name',
                    'users.email',
                    'teachers.employee_id',
                    'teachers.date_of_birth',
                    'teachers.gender',
                    'teachers.phone_number',
                    'teachers.address',
                    'teachers.status',
                    'teachers.created_at',
                    'teachers.updated_at'
                )
                ->join('users', 'teachers.user_id', '=', 'users.id')
                ->where('teachers.id', $id)
                ->first();

            return response()->json(['success' => true, 'message' => 'Teacher updated successfully.', 'data' => $updatedTeacher]);
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Validation failed.', 'errors' => $e->errors()], 422);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error updating teacher: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to update teacher.'], 500);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $teacher = DB::table('teachers')->where('id', $id)->first();
            if (!$teacher) {
                return response()->json(['success' => false, 'message' => 'Teacher not found.'], 404);
            }

            DB::table('users')->where('id', $teacher->user_id)->delete();
            DB::table('teachers')->where('id', $id)->delete();

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Teacher deleted successfully.']);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error deleting teacher: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to delete teacher.'], 500);
        }
    }
}
