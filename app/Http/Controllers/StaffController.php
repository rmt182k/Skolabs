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

class StaffController extends Controller
{
    public function index()
    {
        try {
            $staffs = DB::table('staffs')
                ->select(
                    'staffs.id',
                    'users.name',
                    'users.email',
                    'staffs.employee_id', // DIUBAH: Menambahkan employee_id
                    'staffs.position',
                    'staffs.status'
                )
                ->join('users', 'staffs.user_id', '=', 'users.id')
                ->orderBy('staffs.created_at', 'DESC')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Staff retrieved successfully.',
                'data' => $staffs
            ], 200);
        } catch (Exception $e) {
            Log::error('Error fetching staff: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve staff.'
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $staff = DB::table('staffs')
                ->select(
                    'staffs.id',
                    'staffs.user_id',
                    'users.name',
                    'users.email',
                    'staffs.employee_id',   // DIUBAH: Menambahkan employee_id
                    'staffs.date_of_birth', // DIUBAH: Menambahkan date_of_birth
                    'staffs.position',
                    'staffs.status',
                    'staffs.created_at',
                    'staffs.updated_at'
                )
                ->join('users', 'staffs.user_id', '=', 'users.id')
                ->where('staffs.id', $id)
                ->first();

            if (!$staff) {
                return response()->json([
                    'success' => false,
                    'message' => 'Staff not found.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Staff retrieved successfully.',
                'data' => $staff
            ], 200);
        } catch (Exception $e) {
            Log::error('Error fetching staff with ID ' . $id . ': ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to retrieve staff.'], 500);
        }
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            // DIUBAH: Menambahkan validasi untuk field baru
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email',
                'password' => 'nullable|string|min:8',
                'employee_id' => 'nullable|string|max:255|unique:staffs,employee_id',
                'date_of_birth' => 'nullable|date',
                'position' => 'nullable|string|max:255',
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

            $role = Role::firstOrCreate(['name' => 'staff'], ['description' => 'Default role for staff']);
            UserRole::create(['user_id' => $userId, 'role_id' => $role->id]);

            // DIUBAH: Menyimpan field baru ke database
            $staffId = DB::table('staffs')->insertGetId([
                'user_id' => $userId,
                'employee_id' => $validatedData['employee_id'] ?? null,
                'date_of_birth' => $validatedData['date_of_birth'] ?? null,
                'position' => $validatedData['position'] ?? null,
                'status' => $validatedData['status'] ?? 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            // DIUBAH: Menambahkan field baru ke data respons
            $responseData = [
                'id' => $staffId,
                'user_id' => $userId,
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'employee_id' => $validatedData['employee_id'] ?? null,
                'date_of_birth' => $validatedData['date_of_birth'] ?? null,
                'position' => $validatedData['position'] ?? null,
                'status' => $validatedData['status'] ?? 'active',
                'created_at' => now()->toDateTimeString(),
                'updated_at' => now()->toDateTimeString()
            ];

            return response()->json([
                'success' => true,
                'message' => 'Staff created successfully.',
                'data' => $responseData
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
            Log::error('Error creating staff: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'An unexpected error occurred.'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $staff = DB::table('staffs')->where('id', $id)->first();
            if (!$staff) {
                return response()->json([
                    'success' => false,
                    'message' => 'Staff not found.'
                ], 404);
            }

            $user = DB::table('users')->where('id', $staff->user_id)->first();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Associated user not found.'
                ], 404);
            }

            // DIUBAH: Menambahkan validasi untuk field baru saat update
            $validatedData = $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $user->id,
                'password' => 'nullable|string|min:8',
                'employee_id' => 'sometimes|nullable|string|max:255|unique:staffs,employee_id,' . $id,
                'date_of_birth' => 'sometimes|nullable|date',
                'position' => 'nullable|string|max:255',
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

            // DIUBAH: Menambahkan field baru ke data update staff
            $staffUpdateData = [];
            if ($request->has('employee_id'))
                $staffUpdateData['employee_id'] = $validatedData['employee_id'];
            if ($request->has('date_of_birth'))
                $staffUpdateData['date_of_birth'] = $validatedData['date_of_birth'];
            if ($request->has('position'))
                $staffUpdateData['position'] = $validatedData['position'];
            if ($request->has('status'))
                $staffUpdateData['status'] = $validatedData['status'];
            if (!empty($staffUpdateData)) {
                $staffUpdateData['updated_at'] = now();
                DB::table('staffs')->where('id', $id)->update($staffUpdateData);
            }

            DB::commit();

            // DIUBAH: Mengambil data terbaru dengan field baru
            $updatedStaff = DB::table('staffs')
                ->select('staffs.id', 'staffs.user_id', 'users.name', 'users.email', 'staffs.employee_id', 'staffs.date_of_birth', 'staffs.position', 'staffs.status')
                ->join('users', 'staffs.user_id', '=', 'users.id')->where('staffs.id', $id)->first();

            return response()->json([
                'success' => true,
                'message' => 'Staff updated successfully.',
                'data' => $updatedStaff
            ]);
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error updating staff: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update staff.'
            ], 500);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $staff = DB::table('staffs')->where('id', $id)->first();
            if (!$staff) {
                return response()->json(['success' => false, 'message' => 'Staff not found.'], 404);
            }

            // Hapus dari tabel users akan otomatis menghapus dari user_roles jika ada foreign key constraint (cascade)
            DB::table('users')->where('id', $staff->user_id)->delete();
            // Menghapus staff juga akan ter-handle oleh cascade jika di-setting di migration
            // Namun, untuk kepastian, kita hapus manual jika tidak ada cascade.
            DB::table('staffs')->where('id', $id)->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Staff deleted successfully.'
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error deleting staff: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete staff.'
            ], 500);
        }
    }
}
