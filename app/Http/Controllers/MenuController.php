<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class MenuController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $menus = DB::table('menus')
                ->select('id', 'name', 'route', 'icon', 'parent_id', 'order', 'created_at', 'updated_at')
                ->orderBy('order', 'ASC')
                ->orderBy('name', 'ASC')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Menus retrieved successfully.',
                'data' => $menus
            ], 200);

        } catch (Exception $e) {
            Log::error('Error fetching menus: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve menus. ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $menu = DB::table('menus')->where('id', $id)->first();

            if (!$menu) {
                return response()->json(['success' => false, 'message' => 'Menu not found.'], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Menu retrieved successfully.',
                'data' => $menu
            ], 200);

        } catch (Exception $e) {
            Log::error('Error fetching menu with ID ' . $id . ': ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to retrieve menu.'], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'route' => 'nullable|string|max:255',
                'icon' => 'nullable|string|max:255',
                'parent_id' => 'required|integer|min:0',
                'order' => 'required|integer|min:0'
            ]);

            $menuId = DB::table('menus')->insertGetId([
                'name' => $validatedData['name'],
                'route' => $validatedData['route'] ?? null,
                'icon' => $validatedData['icon'] ?? null,
                'parent_id' => $validatedData['parent_id'],
                'order' => $validatedData['order'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $newMenu = DB::table('menus')->where('id', $menuId)->first();

            return response()->json(['success' => true, 'message' => 'Menu created successfully.', 'data' => $newMenu], 201);

        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validation failed.', 'errors' => $e->errors()], 422);
        } catch (Exception $e) {
            Log::error('Error creating menu: ' . $e->getMessage());
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
            $menu = DB::table('menus')->where('id', $id)->first();
            if (!$menu) {
                return response()->json(['success' => false, 'message' => 'Menu not found.'], 404);
            }

            $validatedData = $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'route' => 'nullable|string|max:255',
                'icon' => 'nullable|string|max:255',
                'parent_id' => 'sometimes|required|integer|min:0',
                'order' => 'sometimes|required|integer|min:0'
            ]);

            $updateData = [];
            if ($request->has('name')) $updateData['name'] = $validatedData['name'];
            if ($request->has('route')) $updateData['route'] = $validatedData['route'];
            if ($request->has('icon')) $updateData['icon'] = $validatedData['icon'];
            if ($request->has('parent_id')) $updateData['parent_id'] = $validatedData['parent_id'];
            if ($request->has('order')) $updateData['order'] = $validatedData['order'];

            if (!empty($updateData)) {
                $updateData['updated_at'] = now();
                DB::table('menus')->where('id', $id)->update($updateData);
            }

            $updatedMenu = DB::table('menus')->where('id', $id)->first();

            return response()->json(['success' => true, 'message' => 'Menu updated successfully.', 'data' => $updatedMenu]);

        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validation failed.', 'errors' => $e->errors()], 422);
        } catch (Exception $e) {
            Log::error('Error updating menu: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to update menu.'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $menu = DB::table('menus')->where('id', $id)->first();
            if (!$menu) {
                return response()->json(['success' => false, 'message' => 'Menu not found.'], 404);
            }

            // Check if this menu is a parent to any other menu
            $hasChildren = DB::table('menus')->where('parent_id', $id)->exists();
            if ($hasChildren) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete menu. It is a parent to other menus. Please delete or reassign child menus first.'
                ], 409);
            }

            DB::table('menus')->where('id', $id)->delete();

            return response()->json(['success' => true, 'message' => 'Menu deleted successfully.']);

        } catch (Exception $e) {
            Log::error('Error deleting menu: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to delete menu.'], 500);
        }
    }
}
