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
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email',
                'password' => 'nullable|string|min:8',
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

            $staffId = DB::table('staffs')->insertGetId([
                'user_id' => $userId,
                'position' => $validatedData['position'] ?? null,
                'status' => $validatedData['status'] ?? 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            $responseData = [
                'id' => $staffId,
                'user_id' => $userId,
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
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

            $validatedData = $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $user->id,
                'password' => 'nullable|string|min:8',
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

            $staffUpdateData = [];
            if ($request->has('position'))
                $staffUpdateData['position'] = $validatedData['position'];
            if ($request->has('status'))
                $staffUpdateData['status'] = $validatedData['status'];
            if (!empty($staffUpdateData)) {
                $staffUpdateData['updated_at'] = now();
                DB::table('staffs')->where('id', $id)->update($staffUpdateData);
            }

            DB::commit();

            $updatedStaff = DB::table('staffs')
                ->select('staffs.id', 'staffs.user_id', 'users.name', 'users.email', 'staffs.position', 'staffs.status')
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

            DB::table('users')->where('id', $staff->user_id)->delete();
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
