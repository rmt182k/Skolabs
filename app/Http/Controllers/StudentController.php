<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Models\Student;
use App\Models\UserRole;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StudentController extends Controller
{
    public function index()
    {
        try {
            $students = DB::table('students')
                ->select(
                    'students.id',
                    'users.name',
                    'users.email',
                    'students.date_of_birth',
                    'students.gender',
                    'students.phone_number',
                    'students.address',
                    'students.enrollment_date',
                    'students.grade_level',
                    'students.created_at',
                    'students.updated_at'
                )
                ->join('users', 'students.user_id', '=', 'users.id')
                ->get();

            if ($students->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'No students found.',
                    'data' => []
                ], 200);
            }

            $formattedData = $students->map(function ($student) {
                return [
                    'id' => $student->id,
                    'name' => $student->name,
                    'email' => $student->email,
                    'date_of_birth' => $student->date_of_birth,
                    'gender' => $student->gender,
                    'phone_number' => $student->phone_number,
                    'address' => $student->address,
                    'enrollment_date' => $student->enrollment_date,
                    'grade_level' => $student->grade_level,
                    'created_at' => $student->created_at,
                    'updated_at' => $student->updated_at
                ];
            })->toArray();

            return response()->json([
                'success' => true,
                'message' => 'Students retrieved successfully.',
                'data' => $formattedData
            ], 200);

        } catch (Exception $e) {
            Log::error('Error fetching students: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                // 'message' => 'Failed to retrieve students. Please try again later.'
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $student = DB::table('students')
                ->select(
                    'students.id',
                    'users.name',
                    'users.email',
                    'students.date_of_birth',
                    'students.gender',
                    'students.phone_number',
                    'students.address',
                    'students.enrollment_date',
                    'students.grade_level',
                    'students.created_at',
                    'students.updated_at'
                )
                ->join('users', 'students.user_id', '=', 'users.id')
                ->where('students.id', '=', $id)
                ->first();

            if (!$student) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student not found.'
                ], 404);
            }

            $formattedData = [
                'id' => $student->id,
                'name' => $student->name,
                'email' => $student->email,
                'date_of_birth' => $student->date_of_birth,
                'gender' => $student->gender,
                'phone_number' => $student->phone_number,
                'address' => $student->address,
                'enrollment_date' => $student->enrollment_date,
                'grade_level' => $student->grade_level,
                'created_at' => $student->created_at,
                'updated_at' => $student->updated_at
            ];

            return response()->json([
                'success' => true,
                'message' => 'Student retrieved successfully.',
                'data' => $formattedData
            ], 200);

        } catch (Exception $e) {
            Log::error('Error fetching student with ID ' . $id . ': ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve student. Please try again later.'
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email',
                'password' => 'required|string|min:8',
                'date_of_birth' => 'nullable|date',
                'gender' => 'nullable|in:male,female,Other',
                'phone_number' => 'nullable|string|max:15',
                'address' => 'nullable|string',
                'enrollment_date' => 'nullable|date',
                'grade_level' => 'nullable|integer'
            ]);

            DB::beginTransaction();

            $user = new User();
            $user->name = $validatedData['name'];
            $user->email = $validatedData['email'];
            $user->password = Hash::make($validatedData['password']);
            $user->save();

            $role = Role::where('name', 'student')->first();
            if (!$role) {
                $role = new Role();
                $role->name = 'student';
                $role->description = 'Default role for students';
                $role->save();
            }

            $exists = UserRole::where('user_id', $user->id)
                ->where('role_id', $role->id)
                ->exists();

            if (!$exists) {
                UserRole::create([
                    'user_id' => $user->id,
                    'role_id' => $role->id,
                ]);
            }

            $student = new Student();
            $student->user_id = $user->id;
            $student->date_of_birth = $validatedData['date_of_birth'] ?? null;
            $student->gender = $validatedData['gender'] ?? null;
            $student->phone_number = $validatedData['phone_number'] ?? null;
            $student->address = $validatedData['address'] ?? null;
            $student->enrollment_date = $validatedData['enrollment_date'] ?? null;
            $student->grade_level = $validatedData['grade_level'] ?? null;
            $student->save();

            DB::commit();

            $formattedData = [
                'id' => $student->id,
                'name' => $user->name,
                'email' => $user->email,
                'date_of_birth' => $student->date_of_birth,
                'gender' => $student->gender,
                'phone_number' => $student->phone_number,
                'address' => $student->address,
                'enrollment_date' => $student->enrollment_date,
                'grade_level' => $student->grade_level,
                'created_at' => $student->created_at,
                'updated_at' => $student->updated_at
            ];

            return response()->json([
                'success' => true,
                'message' => 'Student created successfully.',
                'data' => $formattedData
            ], 201);

        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error creating student: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                // 'message' => 'Failed to create student. Please try again later.'
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $student = DB::table('students')->where('id', '=', $id)->first();
            if (!$student) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student not found.'
                ], 404);
            }

            $user = DB::table('users')->where('id', '=', $student->user_id)->first();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Associated user not found.'
                ], 404);
            }

            $validatedData = $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $user->id,
                'password' => 'sometimes|required|string|min:8',
                'date_of_birth' => 'nullable|date',
                'gender' => 'nullable|in:male,female,Other',
                'phone_number' => 'nullable|string|max:15',
                'address' => 'nullable|string',
                'enrollment_date' => 'nullable|date',
                'grade_level' => 'nullable|integer'
            ]);

            DB::beginTransaction();

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

            $studentUpdateData = [];
            if (isset($validatedData['date_of_birth'])) {
                $studentUpdateData['date_of_birth'] = $validatedData['date_of_birth'];
            }
            if (isset($validatedData['gender'])) {
                $studentUpdateData['gender'] = $validatedData['gender'];
            }
            if (isset($validatedData['phone_number'])) {
                $studentUpdateData['phone_number'] = $validatedData['phone_number'];
            }
            if (isset($validatedData['address'])) {
                $studentUpdateData['address'] = $validatedData['address'];
            }
            if (isset($validatedData['enrollment_date'])) {
                $studentUpdateData['enrollment_date'] = $validatedData['enrollment_date'];
            }
            if (isset($validatedData['grade_level'])) {
                $studentUpdateData['grade_level'] = $validatedData['grade_level'];
            }
            if (!empty($studentUpdateData)) {
                DB::table('students')->where('id', '=', $student->id)->update($studentUpdateData);
            }

            DB::commit();

            // Ambil data terbaru untuk response
            $updatedStudent = DB::table('students')
                ->select(
                    'students.id',
                    'users.name',
                    'users.email',
                    'students.date_of_birth',
                    'students.gender',
                    'students.phone_number',
                    'students.address',
                    'students.enrollment_date',
                    'students.grade_level',
                    'students.created_at',
                    'students.updated_at'
                )
                ->join('users', 'students.user_id', '=', 'users.id')
                ->where('students.id', '=', $id)
                ->first();

            $formattedData = [
                'id' => $updatedStudent->id,
                'name' => $updatedStudent->name,
                'email' => $updatedStudent->email,
                'date_of_birth' => $updatedStudent->date_of_birth,
                'gender' => $updatedStudent->gender,
                'phone_number' => $updatedStudent->phone_number,
                'address' => $updatedStudent->address,
                'enrollment_date' => $updatedStudent->enrollment_date,
                'grade_level' => $updatedStudent->grade_level,
                'created_at' => $updatedStudent->created_at,
                'updated_at' => $updatedStudent->updated_at
            ];

            return response()->json([
                'success' => true,
                'message' => 'Student updated successfully.',
                'data' => $formattedData
            ], 200);

        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error updating student with ID ' . $id . ': ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update student. Please try again later.'
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $student = DB::table('students')->where('id', '=', $id)->first();
            if (!$student) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student not found.'
                ], 404);
            }

            $user = DB::table('users')->where('id', '=', $student->user_id)->first();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Associated user not found.'
                ], 404);
            }

            DB::beginTransaction();

            DB::table('students')->where('id', '=', $id)->delete();
            DB::table('users')->where('id', '=', $student->user_id)->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Student deleted successfully.'
            ], 200);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error deleting student with ID ' . $id . ': ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete student. Please try again later.'
            ], 500);
        }
    }
}
