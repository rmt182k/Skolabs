<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AdminController extends Controller
{
    public function index()
    {
        try {
            $admins = DB::table('admins')
                ->join('users', 'admins.user_id', '=', 'users.id')
                ->select('admins.id', 'users.name', 'users.email', 'admins.job_title', 'admins.status')
                ->orderBy('admins.created_at', 'DESC')
                ->get();

            return response()->json(['success' => true, 'message' => 'Admins retrieved successfully.', 'data' => $admins]);
        } catch (Exception $e) {
            Log::error('Error fetching admins: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'An error occurred on the server.'], 500);
        }
    }

    public function show($id)
    {
        try {
            $admin = DB::table('admins')
                ->join('users', 'admins.user_id', '=', 'users.id')
                ->where('admins.id', $id)
                ->select('admins.id', 'users.name', 'users.email', 'admins.job_title', 'admins.status')
                ->first();

            if (!$admin) {
                return response()->json(['success' => false, 'message' => 'Admin not found.'], 404);
            }

            return response()->json(['success' => true, 'message' => 'Admin retrieved successfully.', 'data' => $admin]);
        } catch (Exception $e) {
            Log::error("Error fetching admin ID {$id}: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'An error occurred on the server.'], 500);
        }
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $validatedData = $this->validateAdmin($request);

            // 1. Buat data di tabel 'users'
            // Logika ini sekarang akan berfungsi dengan benar
            $passwordToHash = $validatedData['password'] ?? 'password';

            $userId = DB::table('users')->insertGetId([
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'password' => Hash::make($passwordToHash),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 2. Assign role 'admin' ke user (Query Builder style)
            $adminRole = DB::table('roles')->where('name', 'admin')->first();
            if ($adminRole) {
                DB::table('user_roles')->insert(['user_id' => $userId, 'role_id' => $adminRole->id]);
            }

            // 3. Buat data di tabel 'admins'
            DB::table('admins')->insert([
                'user_id' => $userId,
                'job_title' => $validatedData['job_title'],
                'status' => $validatedData['status'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Admin created successfully.'], 201);
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Validation failed.', 'errors' => $e->errors()], 422);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error creating admin: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'An error occurred while creating admin.'], 500);
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

            $validatedData = $this->validateAdmin($request, $admin->user_id);

            // 1. Update data di tabel 'users'
            $userUpdateData = [
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'updated_at' => now(),
            ];
            if (!empty($validatedData['password'])) {
                $userUpdateData['password'] = Hash::make($validatedData['password']);
            }
            DB::table('users')->where('id', $admin->user_id)->update($userUpdateData);

            // 2. Update data di tabel 'admins'
            DB::table('admins')->where('id', $id)->update([
                'job_title' => $validatedData['job_title'],
                'status' => $validatedData['status'],
                'updated_at' => now(),
            ]);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Admin updated successfully.']);
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Validation failed.', 'errors' => $e->errors()], 422);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Error updating admin ID {$id}: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'An error occurred while updating admin.'], 500);
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

            DB::table('user_roles')->where('user_id', $admin->user_id)->delete();
            DB::table('admins')->where('id', $id)->delete();
            DB::table('users')->where('id', $admin->user_id)->delete();

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Admin deleted successfully.']);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Error deleting admin ID {$id}: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to delete admin.'], 500);
        }
    }

    // Private helper function untuk validasi
    private function validateAdmin(Request $request, $userId = null)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($userId)],
            'job_title' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
        ];

        // --- INI BAGIAN YANG DIPERBAIKI ---
        // Password sekarang selalu 'nullable' baik saat create maupun update.
        // Jika kosong saat create, akan di-handle oleh logika default di method store().
        $rules['password'] = ['nullable', 'string', 'min:8'];

        return $request->validate($rules);
    }
}
