<?php

// File: app/Http/Controllers/StudentController.php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class StudentController extends Controller
{
    public function index()
    {
        try {
            $students = DB::table('students')
                ->join('users', 'students.user_id', '=', 'users.id')
                ->select(
                    'students.id', 'users.name', 'students.nisn',
                    'students.grade_level', 'students.status'
                )
                ->orderBy('students.created_at', 'DESC')
                ->get();

            return response()->json(['success' => true, 'data' => $students]);
        } catch (Exception $e) {
            Log::error('Error fetching students: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'An error occurred on the server.'], 500);
        }
    }

    public function show($id)
    {
        try {
            $student = DB::table('students')
                ->join('users', 'students.user_id', '=', 'users.id')
                ->leftJoin('majors', 'students.major_id', '=', 'majors.id')
                ->where('students.id', $id)
                ->select(
                    'students.id', 'users.name', 'users.email', 'students.nisn',
                    'students.date_of_birth', 'students.gender', 'students.phone_number',
                    'students.address', 'students.enrollment_date', 'students.grade_level',
                    'students.status', 'students.major_id', 'majors.educational_level_id'
                )
                ->first();

            if (!$student) {
                return response()->json(['success' => false, 'message' => 'Student not found.'], 404);
            }

            // Format data agar konsisten dengan ekspektasi frontend
            $formattedData = [
                'id' => $student->id, 'name' => $student->name, 'email' => $student->email, 'nisn' => $student->nisn,
                'date_of_birth' => $student->date_of_birth, 'gender' => $student->gender,
                'phone_number' => $student->phone_number, 'address' => $student->address,
                'enrollment_date' => $student->enrollment_date, 'grade_level' => $student->grade_level,
                'status' => $student->status,
                'major' => $student->major_id ? [
                    'id' => $student->major_id,
                    'educational_level_id' => $student->educational_level_id
                ] : null,
            ];

            return response()->json(['success' => true, 'data' => $formattedData]);
        } catch (Exception $e) {
            Log::error("Error fetching student ID {$id}: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'An error occurred on the server.'], 500);
        }
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $validatedData = $this->validateStudent($request);
            $passwordToHash = $validatedData['password'] ?? 'password';

            $userId = DB::table('users')->insertGetId([
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'password' => Hash::make($passwordToHash),
                'created_at' => now(), 'updated_at' => now(),
            ]);

            $studentRole = DB::table('roles')->where('name', 'student')->first();
            if ($studentRole) {
                DB::table('user_roles')->insert(['user_id' => $userId, 'role_id' => $studentRole->id]);
            }

            DB::table('students')->insert([
                'user_id' => $userId,
                'nisn' => $validatedData['nisn'],
                'date_of_birth' => $validatedData['date_of_birth'],
                'gender' => $validatedData['gender'],
                'phone_number' => $validatedData['phone_number'],
                'address' => $validatedData['address'],
                'enrollment_date' => $validatedData['enrollment_date'],
                'grade_level' => $validatedData['grade_level'],
                'major_id' => $validatedData['major_id'],
                'status' => $validatedData['status'],
                'created_at' => now(), 'updated_at' => now(),
            ]);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Student created successfully.'], 201);
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'errors' => $e->errors()], 422);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error creating student: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'An error occurred.'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $student = DB::table('students')->where('id', $id)->first();
            if (!$student) {
                return response()->json(['success' => false, 'message' => 'Student not found.'], 404);
            }

            $validatedData = $this->validateStudent($request, $student->user_id, $id);
            $userUpdateData = ['name' => $validatedData['name'], 'email' => $validatedData['email'], 'updated_at' => now()];
            if (!empty($validatedData['password'])) {
                $userUpdateData['password'] = Hash::make($validatedData['password']);
            }
            DB::table('users')->where('id', $student->user_id)->update($userUpdateData);

            DB::table('students')->where('id', $id)->update([
                'nisn' => $validatedData['nisn'],
                'date_of_birth' => $validatedData['date_of_birth'],
                'gender' => $validatedData['gender'],
                'phone_number' => $validatedData['phone_number'],
                'address' => $validatedData['address'],
                'enrollment_date' => $validatedData['enrollment_date'],
                'grade_level' => $validatedData['grade_level'],
                'major_id' => $validatedData['major_id'],
                'status' => $validatedData['status'],
                'updated_at' => now(),
            ]);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Student updated successfully.']);
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'errors' => $e->errors()], 422);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Error updating student ID {$id}: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'An error occurred.'], 500);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $student = DB::table('students')->where('id', $id)->first();
            if (!$student) {
                return response()->json(['success' => false, 'message' => 'Student not found.'], 404);
            }

            DB::table('user_roles')->where('user_id', $student->user_id)->delete();
            DB::table('students')->where('id', $id)->delete();
            DB::table('users')->where('id', $student->user_id)->delete();

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Student deleted successfully.']);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Error deleting student ID {$id}: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to delete student.'], 500);
        }
    }

    private function validateStudent(Request $request, $userId = null, $studentId = null)
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($userId)],
            'password' => ['nullable', 'string', 'min:8'],
            'nisn' => ['required', 'string', 'max:20', Rule::unique('students')->ignore($studentId)],
            'date_of_birth' => 'nullable|date_format:Y-m-d',
            'enrollment_date' => 'nullable|date_format:Y-m-d',
            'gender' => 'nullable|in:male,female',
            'phone_number' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'grade_level' => 'required|integer',
            'educational_level_id' => 'required|exists:educational_levels,id',
            'major_id' => 'nullable|exists:majors,id',
            'status' => 'required|in:active,graduated,dropout,suspended,transferred,on_leave',
        ]);
    }
}
