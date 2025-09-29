<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\MenuRole;
use App\Models\Role;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ModuleManagementController extends Controller
{
    /**
     * Display the module management page
     */
    public function index()
    {
        return view('module-management.index');
    }

    /**
     * Get all roles
     */
    public function getRoles()
    {
        try {
            $roles = Role::select('id', 'name')->orderBy('name')->get();

            if ($roles->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'No roles found.',
                    'data' => []
                ], 200);
            }

            return response()->json([
                'success' => true,
                'message' => 'Roles retrieved successfully.',
                'data' => $roles
            ], 200);

        } catch (Exception $e) {
            Log::error('Error fetching roles: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve roles. Please try again later.'
            ], 500);
        }
    }

    /**
     * Get all menus with their permissions
     */
    public function getMenus()
    {
        try {
            $menus = Menu::with('menuRoles')->orderBy('order')->get();

            if ($menus->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'No menus found.',
                    'data' => []
                ], 200);
            }

            return response()->json([
                'success' => true,
                'message' => 'Menus retrieved successfully.',
                'data' => $menus
            ], 200);

        } catch (Exception $e) {
            Log::error('Error fetching menus: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve menus. Please try again later.'
            ], 500);
        }
    }

    /**
     * Get permissions for permission table
     */
    public function getPermissions()
    {
        try {
            $permissions = MenuRole::all();

            if ($permissions->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'No permissions found.',
                    'data' => []
                ], 200);
            }

            return response()->json([
                'success' => true,
                'message' => 'Permissions retrieved successfully.',
                'data' => $permissions
            ], 200);

        } catch (Exception $e) {
            Log::error('Error fetching permissions: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve permissions. Please try again later.'
            ], 500);
        }
    }

    /**
     * Get single menu by ID
     */
    public function show($id)
    {
        try {
            $menu = Menu::find($id);

            if (!$menu) {
                return response()->json([
                    'success' => false,
                    'message' => 'Menu not found.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Menu retrieved successfully.',
                'data' => $menu
            ], 200);

        } catch (Exception $e) {
            Log::error('Error fetching menu with ID ' . $id . ': ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve menu. Please try again later.'
            ], 500);
        }
    }

    /**
     * Store a new menu
     */
    public function storeMenu(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'route' => 'nullable|string|max:255',
                'icon' => 'nullable|string|max:255',
                'parent_id' => 'nullable|integer',
                'order' => 'required|integer'
            ]);

            // Set default parent_id if not provided
            if (!isset($validatedData['parent_id'])) {
                $validatedData['parent_id'] = 0;
            }

            $menu = Menu::create($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Menu created successfully.',
                'data' => $menu
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            Log::error('Error creating menu: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create menu. ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update an existing menu
     */
    public function updateMenu(Request $request, $id)
    {
        try {
            $menu = Menu::find($id);

            if (!$menu) {
                return response()->json([
                    'success' => false,
                    'message' => 'Menu not found.'
                ], 404);
            }

            $validatedData = $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'route' => 'nullable|string|max:255',
                'icon' => 'nullable|string|max:255',
                'parent_id' => 'nullable|integer',
                'order' => 'sometimes|required|integer'
            ]);

            // Prevent menu from being its own parent
            if (isset($validatedData['parent_id']) && $validatedData['parent_id'] == $id) {
                return response()->json([
                    'success' => false,
                    'message' => 'A menu cannot be its own parent.'
                ], 422);
            }

            $menu->update($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Menu updated successfully.',
                'data' => $menu
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            Log::error('Error updating menu with ID ' . $id . ': ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update menu. Please try again later.'
            ], 500);
        }
    }

    /**
     * Delete a menu
     */
    public function deleteMenu($id)
    {
        try {
            $menu = Menu::find($id);

            if (!$menu) {
                return response()->json([
                    'success' => false,
                    'message' => 'Menu not found.'
                ], 404);
            }

            // Check if menu has children
            $hasChildren = Menu::where('parent_id', $id)->exists();
            if ($hasChildren) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete menu with child menus. Please delete child menus first.'
                ], 400);
            }

            // Delete associated permissions
            MenuRole::where('menu_id', $id)->delete();

            $menu->delete();

            return response()->json([
                'success' => true,
                'message' => 'Menu deleted successfully.'
            ], 200);

        } catch (Exception $e) {
            Log::error('Error deleting menu with ID ' . $id . ': ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete menu. Please try again later.'
            ], 500);
        }
    }

    /**
     * Save all permissions (bulk update)
     */
    public function savePermissions(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'permissions' => 'required|array',
                'permissions.*.menu_id' => 'required|integer|exists:menus,id',
                'permissions.*.role_id' => 'required|integer|exists:roles,id',
                'permissions.*.can_view' => 'required|boolean',
                'permissions.*.can_create' => 'required|boolean',
                'permissions.*.can_update' => 'required|boolean',
                'permissions.*.can_delete' => 'required|boolean',
            ]);

            DB::beginTransaction();

            foreach ($validatedData['permissions'] as $permission) {
                MenuRole::updateOrCreate(
                    [
                        'menu_id' => $permission['menu_id'],
                        'role_id' => $permission['role_id']
                    ],
                    [
                        'can_view' => $permission['can_view'],
                        'can_create' => $permission['can_create'],
                        'can_update' => $permission['can_update'],
                        'can_delete' => $permission['can_delete']
                    ]
                );
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Permissions saved successfully.'
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
            Log::error('Error saving permissions: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to save permissions. Please try again later.' . $e->getMessage()
            ], 500);
        }
    }
}
