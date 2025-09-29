<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $roles = DB::table('roles')
                ->select('id', 'name', 'description', 'created_at', 'updated_at')
                ->orderBy('created_at', 'DESC')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Roles retrieved successfully.',
                'data' => $roles
            ], 200);

        } catch (Exception $e) {
            Log::error('Error fetching roles: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve roles. ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $role = DB::table('roles')->where('id', $id)->first();

            if (!$role) {
                return response()->json(['success' => false, 'message' => 'Role not found.'], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Role retrieved successfully.',
                'data' => $role
            ], 200);

        } catch (Exception $e) {
            Log::error('Error fetching role with ID ' . $id . ': ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to retrieve role.'], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255|unique:roles,name',
                'description' => 'nullable|string',
            ]);

            $roleId = DB::table('roles')->insertGetId([
                'name' => $validatedData['name'],
                'description' => $validatedData['description'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $newRole = DB::table('roles')->where('id', $roleId)->first();

            return response()->json(['success' => true, 'message' => 'Role created successfully.', 'data' => $newRole], 201);

        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validation failed.', 'errors' => $e->errors()], 422);
        } catch (Exception $e) {
            Log::error('Error creating role: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $role = DB::table('roles')->where('id', $id)->first();
            if (!$role) {
                return response()->json(['success' => false, 'message' => 'Role not found.'], 404);
            }

            $validatedData = $request->validate([
                'name' => 'sometimes|required|string|max:255|unique:roles,name,' . $id,
                'description' => 'nullable|string',
            ]);

            $updateData = [];
            if ($request->has('name')) {
                $updateData['name'] = $validatedData['name'];
            }
            if ($request->has('description')) {
                $updateData['description'] = $validatedData['description'];
            }

            if (!empty($updateData)) {
                $updateData['updated_at'] = now();
                DB::table('roles')->where('id', $id)->update($updateData);
            }

            $updatedRole = DB::table('roles')->where('id', $id)->first();

            return response()->json(['success' => true, 'message' => 'Role updated successfully.', 'data' => $updatedRole]);

        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validation failed.', 'errors' => $e->errors()], 422);
        } catch (Exception $e) {
            Log::error('Error updating role: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to update role.'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $role = DB::table('roles')->where('id', $id)->first();
            if (!$role) {
                return response()->json(['success' => false, 'message' => 'Role not found.'], 404);
            }

            // Optional: Check if role is in use before deleting
            $isUsed = DB::table('user_roles')->where('role_id', $id)->exists();
            if ($isUsed) {
                return response()->json(['success' => false, 'message' => 'Cannot delete role. It is currently assigned to one or more users.'], 409);
            }

            DB::table('roles')->where('id', $id)->delete();

            return response()->json(['success' => true, 'message' => 'Role deleted successfully.']);

        } catch (Exception $e) {
            Log::error('Error deleting role: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to delete role.'], 500);
        }
    }
}
