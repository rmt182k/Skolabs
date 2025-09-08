<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Role;
use App\Models\Teacher;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class TeacherController extends Controller
{
    public function index()
    {
        try {
            // Use DB::table with a join to retrieve teacher and user data
            $teachers = DB::table('teachers')
                ->join('users', 'teachers.user_id', '=', 'users.id')
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
                ->orderBy('teachers.created_at', 'DESC')
                ->get();

            $formattedData = $teachers->map(function ($teacher) {
                return [
                    'id' => $teacher->id,
                    'name' => $teacher->name,
                    'email' => $teacher->email,
                    'employee_id' => $teacher->employee_id,
                    'status' => $teacher->status,
                    'phone_number' => $teacher->phone_number,
                    'date_of_birth' => $teacher->date_of_birth,
                    'gender' => $teacher->gender,
                    'address' => $teacher->address,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Teachers retrieved successfully.',
                'data' => $formattedData
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
                ->join('users', 'teachers.user_id', '=', 'users.id')
                ->where('teachers.id', $id)
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
                ->first();

            if (!$teacher) {
                return response()->json(['success' => false, 'message' => 'Teacher not found.'], 404);
            }

            $formattedData = [
                'id' => $teacher->id,
                'user_id' => $teacher->user_id,
                'name' => $teacher->name,
                'email' => $teacher->email,
                'employee_id' => $teacher->employee_id,
                'date_of_birth' => $teacher->date_of_birth,
                'gender' => $teacher->gender,
                'phone_number' => $teacher->phone_number,
                'address' => $teacher->address,
                'status' => $teacher->status,
                'created_at' => $teacher->created_at,
                'updated_at' => $teacher->updated_at
            ];

            return response()->json([
                'success' => true,
                'message' => 'Teacher retrieved successfully.',
                'data' => $formattedData
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

            // Set default password if the field is empty or not provided
            $password = $validatedData['password'] ?? 'password';

            $user = User::create([
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'password' => Hash::make($password),
            ]);

            $role = Role::firstOrCreate(['name' => 'teacher'], ['description' => 'Default role for teachers']);
            UserRole::create(['user_id' => $user->id, 'role_id' => $role->id]);

            $teacher = Teacher::create([
                'user_id' => $user->id,
                'employee_id' => $validatedData['employee_id'],
                'date_of_birth' => $validatedData['date_of_birth'] ?? null,
                'gender' => $validatedData['gender'] ?? null,
                'phone_number' => $validatedData['phone_number'] ?? null,
                'address' => $validatedData['address'] ?? null,
                'status' => $validatedData['status'] ?? 'active',
            ]);

            DB::commit();

            $teacher->load('user');
            $formattedData = [
                'id' => $teacher->id,
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'employee_id' => $teacher->employee_id,
                'date_of_birth' => $teacher->date_of_birth,
                'gender' => $teacher->gender,
                'phone_number' => $teacher->phone_number,
                'address' => $teacher->address,
                'status' => $teacher->status,
                'created_at' => $teacher->created_at,
                'updated_at' => $teacher->updated_at
            ];

            return response()->json(['success' => true, 'message' => 'Teacher created successfully.', 'data' => $formattedData], 201);
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Validation failed.', 'errors' => $e->errors()], 422);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error creating teacher: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'An unexpected error occurred.'], 500);
        }
    }

    /**
     * Mengupdate data guru yang ada.
     */
    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $teacher = DB::table('teachers')->where('id', '=', $id)->first();
            if (!$teacher) {
                return response()->json([
                    'success' => false,
                    'message' => 'Teacher not found.'
                ], 404);
            }

            $user = DB::table('users')->where('id', '=', $teacher->user_id)->first();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Associated user not found.'
                ], 404);
            }

            $validatedData = $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $user->id,
                'employee_id' => 'sometimes|required|string|max:50|unique:teachers,employee_id,' . $teacher->id,
                'password' => 'nullable|string|min:8|confirmed',
                'date_of_birth' => 'nullable|date',
                'gender' => 'nullable|in:male,female',
                'phone_number' => 'nullable|string|max:20',
                'address' => 'nullable|string',
                'status' => 'nullable|in:active,inactive',
            ]);

            $userUpdateData = [];
            if (isset($validatedData['name'])) {
                $userUpdateData['name'] = $validatedData['name'];
            }
            if (isset($validatedData['email'])) {
                $userUpdateData['email'] = $validatedData['email'];
            }
            if (isset($validatedData['password'])) {
                $userUpdateData['password'] = Hash::make($validatedData['password']);
            }
            if (!empty($userUpdateData)) {
                DB::table('users')->where('id', '=', $user->id)->update($userUpdateData);
            }

            $teacherUpdateData = [];
            if (isset($validatedData['employee_id'])) {
                $teacherUpdateData['employee_id'] = $validatedData['employee_id'];
            }
            if (isset($validatedData['date_of_birth'])) {
                $teacherUpdateData['date_of_birth'] = $validatedData['date_of_birth'];
            }
            if (isset($validatedData['gender'])) {
                $teacherUpdateData['gender'] = $validatedData['gender'];
            }
            if (isset($validatedData['phone_number'])) {
                $teacherUpdateData['phone_number'] = $validatedData['phone_number'];
            }
            if (isset($validatedData['address'])) {
                $teacherUpdateData['address'] = $validatedData['address'];
            }
            if (isset($validatedData['status'])) {
                $teacherUpdateData['status'] = $validatedData['status'];
            }
            if (!empty($teacherUpdateData)) {
                DB::table('teachers')->where('id', '=', $teacher->id)->update($teacherUpdateData);
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
                ->where('teachers.id', '=', $id)
                ->first();

            $formattedData = [
                'id' => $updatedTeacher->id,
                'user_id' => $updatedTeacher->user_id,
                'name' => $updatedTeacher->name,
                'email' => $updatedTeacher->email,
                'employee_id' => $updatedTeacher->employee_id,
                'date_of_birth' => $updatedTeacher->date_of_birth,
                'gender' => $updatedTeacher->gender,
                'phone_number' => $updatedTeacher->phone_number,
                'address' => $updatedTeacher->address,
                'status' => $updatedTeacher->status,
                'created_at' => $updatedTeacher->created_at,
                'updated_at' => $updatedTeacher->updated_at
            ];

            return response()->json(['success' => true, 'message' => 'Teacher updated successfully.', 'data' => $formattedData]);
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
            $teacher = DB::table('teachers')->where('id', '=', $id)->first();
            if (!$teacher) {
                return response()->json(['success' => false, 'message' => 'Teacher not found.'], 404);
            }

            $user = DB::table('users')->where('id', '=', $teacher->user_id)->first();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Associated user not found.'], 404);
            }

            DB::table('teachers')->where('id', '=', $id)->delete();
            DB::table('users')->where('id', '=', $teacher->user_id)->delete();

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Teacher deleted successfully.']);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error deleting teacher: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to delete teacher.'], 500);
        }
    }
}
