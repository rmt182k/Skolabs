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

class AdminController extends Controller
{
    public function index()
    {
        try {
            $admins = DB::table('admins')
                ->select(
                    'admins.id',
                    'users.name',
                    'users.email',
                    'admins.job_title',
                    'admins.status'
                )
                ->join('users', 'admins.user_id', '=', 'users.id')
                ->orderBy('admins.created_at', 'DESC')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Admins retrieved successfully.',
                'data' => $admins
            ], 200);

        } catch (Exception $e) {
            Log::error('Error fetching admins: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve admins. ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $admin = DB::table('admins')
                ->select(
                    'admins.id',
                    'admins.user_id',
                    'users.name',
                    'users.email',
                    'admins.job_title',
                    'admins.status',
                    'admins.created_at',
                    'admins.updated_at'
                )
                ->join('users', 'admins.user_id', '=', 'users.id')
                ->where('admins.id', $id)
                ->first();

            if (!$admin) {
                return response()->json(['success' => false, 'message' => 'Admin not found.'], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Admin retrieved successfully.',
                'data' => $admin
            ], 200);

        } catch (Exception $e) {
            Log::error('Error fetching admin with ID ' . $id . ': ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to retrieve admin.'], 500);
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
                'job_title' => 'nullable|string|max:255',
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

            $role = Role::firstOrCreate(['name' => 'admin'], ['description' => 'Default role for admins']);
            UserRole::create(['user_id' => $userId, 'role_id' => $role->id]);

            $adminId = DB::table('admins')->insertGetId([
                'user_id' => $userId,
                'job_title' => $validatedData['job_title'] ?? null,
                'status' => $validatedData['status'] ?? 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            $formattedData = [
                'id' => $adminId,
                'user_id' => $userId,
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'job_title' => $validatedData['job_title'] ?? null,
                'status' => $validatedData['status'] ?? 'active',
                'created_at' => now()->toDateTimeString(),
                'updated_at' => now()->toDateTimeString()
            ];

            return response()->json(['success' => true, 'message' => 'Admin created successfully.', 'data' => $formattedData], 201);
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Validation failed.', 'errors' => $e->errors()], 422);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error creating admin: ' . $e->getMessage());
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
            $admin = DB::table('admins')->where('id', $id)->first();
            if (!$admin) {
                return response()->json(['success' => false, 'message' => 'Admin not found.'], 404);
            }

            $user = DB::table('users')->where('id', $admin->user_id)->first();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Associated user not found.'], 404);
            }

            $validatedData = $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $user->id,
                'password' => 'nullable|string|min:8',
                'job_title' => 'nullable|string|max:255',
                'status' => 'nullable|in:active,inactive',
            ]);

            $userUpdateData = [];
            if ($request->has('name')) $userUpdateData['name'] = $validatedData['name'];
            if ($request->has('email')) $userUpdateData['email'] = $validatedData['email'];
            if ($request->filled('password')) $userUpdateData['password'] = Hash::make($validatedData['password']);

            if (!empty($userUpdateData)) {
                $userUpdateData['updated_at'] = now();
                DB::table('users')->where('id', $user->id)->update($userUpdateData);
            }

            $adminUpdateData = [];
            if ($request->has('job_title')) $adminUpdateData['job_title'] = $validatedData['job_title'];
            if ($request->has('status')) $adminUpdateData['status'] = $validatedData['status'];

            if (!empty($adminUpdateData)) {
                $adminUpdateData['updated_at'] = now();
                DB::table('admins')->where('id', $id)->update($adminUpdateData);
            }

            DB::commit();

            $updatedAdmin = DB::table('admins')
                ->select(
                    'admins.id', 'admins.user_id', 'users.name', 'users.email',
                    'admins.job_title', 'admins.status', 'admins.created_at', 'admins.updated_at'
                )
                ->join('users', 'admins.user_id', '=', 'users.id')
                ->where('admins.id', $id)
                ->first();

            return response()->json(['success' => true, 'message' => 'Admin updated successfully.', 'data' => $updatedAdmin]);
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Validation failed.', 'errors' => $e->errors()], 422);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error updating admin: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to update admin.'], 500);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $admin = DB::table('admins')->where('id', $id)->first();
            if (!$admin) {
                return response()->json(['success' => false, 'message' => 'Admin not found.'], 404);
            }

            DB::table('users')->where('id', $admin->user_id)->delete();
            DB::table('admins')->where('id', $id)->delete();

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Admin deleted successfully.']);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error deleting admin: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to delete admin.'], 500);
        }
    }
}
