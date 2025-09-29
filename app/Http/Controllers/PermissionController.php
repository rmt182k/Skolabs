<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class PermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $permissions = DB::table('permissions')
                ->select('id', 'name', 'description', 'created_at', 'updated_at')
                ->orderBy('name', 'ASC')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Permissions retrieved successfully.',
                'data' => $permissions
            ], 200);

        } catch (Exception $e) {
            Log::error('Error fetching permissions: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve permissions. ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $permission = DB::table('permissions')->where('id', $id)->first();

            if (!$permission) {
                return response()->json(['success' => false, 'message' => 'Permission not found.'], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Permission retrieved successfully.',
                'data' => $permission
            ], 200);

        } catch (Exception $e) {
            Log::error('Error fetching permission with ID ' . $id . ': ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to retrieve permission.'], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255|unique:permissions,name',
                'description' => 'nullable|string|max:255',
            ]);

            $permissionId = DB::table('permissions')->insertGetId([
                'name' => $validatedData['name'],
                'description' => $validatedData['description'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $newPermission = DB::table('permissions')->where('id', $permissionId)->first();

            return response()->json(['success' => true, 'message' => 'Permission created successfully.', 'data' => $newPermission], 201);

        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validation failed.', 'errors' => $e->errors()], 422);
        } catch (Exception $e) {
            Log::error('Error creating permission: ' . $e->getMessage());
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
            $permission = DB::table('permissions')->where('id', $id)->first();
            if (!$permission) {
                return response()->json(['success' => false, 'message' => 'Permission not found.'], 404);
            }

            $validatedData = $request->validate([
                'name' => 'sometimes|required|string|max:255|unique:permissions,name,' . $id,
                'description' => 'nullable|string|max:255',
            ]);

            $updateData = [];
            if ($request->has('name')) $updateData['name'] = $validatedData['name'];
            if ($request->has('description')) $updateData['description'] = $validatedData['description'];

            if (!empty($updateData)) {
                $updateData['updated_at'] = now();
                DB::table('permissions')->where('id', $id)->update($updateData);
            }

            $updatedPermission = DB::table('permissions')->where('id', $id)->first();

            return response()->json(['success' => true, 'message' => 'Permission updated successfully.', 'data' => $updatedPermission]);

        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validation failed.', 'errors' => $e->errors()], 422);
        } catch (Exception $e) {
            Log::error('Error updating permission: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to update permission.'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $permission = DB::table('permissions')->where('id', $id)->first();
            if (!$permission) {
                return response()->json(['success' => false, 'message' => 'Permission not found.'], 404);
            }

            // Optional: Check if the permission is assigned to any role. Assumes a 'role_permissions' pivot table.
            $isUsed = DB::table('role_permissions')->where('permission_id', $id)->exists();
            if ($isUsed) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete permission. It is currently assigned to one or more roles.'
                ], 409);
            }

            DB::table('permissions')->where('id', $id)->delete();

            return response()->json(['success' => true, 'message' => 'Permission deleted successfully.']);

        } catch (Exception $e) {
            Log::error('Error deleting permission: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to delete permission.'], 500);
        }
    }
}
