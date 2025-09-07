<?php

namespace App\Http\Controllers;

use App\Models\Major;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MajorController extends Controller
{
    public function index(Request $request)
    {
        try {
            $level = $request->query('level');
            $query = DB::table('majors')
                ->select('id', 'level', 'name', 'description', 'created_at', 'updated_at');

            if ($level) {
                $query->where('level', '=', $level);
            }

            $majors = $query->get();

            if ($majors->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'No majors found.',
                    'data' => []
                ], 200);
            }

            $formattedData = $majors->map(function ($major) {
                return [
                    'id' => $major->id,
                    'level' => $major->level,
                    'name' => $major->name,
                    'description' => $major->description,
                    'created_at' => $major->created_at,
                    'updated_at' => $major->updated_at
                ];
            })->toArray();

            return response()->json([
                'success' => true,
                'message' => 'Majors retrieved successfully.',
                'data' => $formattedData
            ], 200);

        } catch (Exception $e) {
            Log::error('Error fetching majors: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $major = DB::table('majors')
                ->select('id', 'level', 'name', 'description', 'created_at', 'updated_at')
                ->where('id', '=', $id)
                ->first();

            if (!$major) {
                return response()->json([
                    'success' => false,
                    'message' => 'Major not found.'
                ], 404);
            }

            $formattedData = [
                'id' => $major->id,
                'level' => $major->level,
                'name' => $major->name,
                'description' => $major->description,
                'created_at' => $major->created_at,
                'updated_at' => $major->updated_at
            ];

            return response()->json([
                'success' => true,
                'message' => 'Major retrieved successfully.',
                'data' => $formattedData
            ], 200);

        } catch (Exception $e) {
            Log::error('Error fetching major with ID ' . $id . ': ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve major. Please try again later.'
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'level' => 'required|in:senior_high_general,senior_high_vocational',
                'name' => 'required|string|max:255|unique:majors,name',
                'description' => 'required|string|max:255'
            ]);

            DB::beginTransaction();

            $major = new Major();
            $major->level = $validatedData['level'];
            $major->name = $validatedData['name'];
            $major->description = $validatedData['description'];
            $major->save();

            DB::commit();

            $formattedData = [
                'id' => $major->id,
                'level' => $major->level,
                'name' => $major->name,
                'description' => $major->description,
                'created_at' => $major->created_at,
                'updated_at' => $major->updated_at
            ];

            return response()->json([
                'success' => true,
                'message' => 'Major created successfully.',
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
            Log::error('Error creating major: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $major = DB::table('majors')->where('id', '=', $id)->first();
            if (!$major) {
                return response()->json([
                    'success' => false,
                    'message' => 'Major not found.'
                ], 404);
            }

            $validatedData = $request->validate([
                'level' => 'sometimes|required|in:senior_high_general,senior_high_vocational,university',
                'name' => 'sometimes|required|string|max:255|unique:majors,name,' . $id,
                'description' => 'sometimes|required|string|max:255'
            ]);

            DB::beginTransaction();

            $updateData = [];
            if (isset($validatedData['level'])) {
                $updateData['level'] = $validatedData['level'];
            }
            if (isset($validatedData['name'])) {
                $updateData['name'] = $validatedData['name'];
            }
            if (isset($validatedData['description'])) {
                $updateData['description'] = $validatedData['description'];
            }
            if (!empty($updateData)) {
                DB::table('majors')->where('id', '=', $id)->update($updateData);
            }

            DB::commit();

            // Ambil data terbaru untuk response
            $updatedMajor = DB::table('majors')
                ->select('id', 'level', 'name', 'description', 'created_at', 'updated_at')
                ->where('id', '=', $id)
                ->first();

            $formattedData = [
                'id' => $updatedMajor->id,
                'level' => $updatedMajor->level,
                'name' => $updatedMajor->name,
                'description' => $updatedMajor->description,
                'created_at' => $updatedMajor->created_at,
                'updated_at' => $updatedMajor->updated_at
            ];

            return response()->json([
                'success' => true,
                'message' => 'Major updated successfully.',
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
            Log::error('Error updating major with ID ' . $id . ': ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update major. Please try again later.'
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $major = DB::table('majors')->where('id', '=', $id)->first();
            if (!$major) {
                return response()->json([
                    'success' => false,
                    'message' => 'Major not found.'
                ], 404);
            }

            DB::beginTransaction();

            DB::table('majors')->where('id', '=', $id)->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Major deleted successfully.'
            ], 200);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error deleting major with ID ' . $id . ': ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete major. Please try again later.'
            ], 500);
        }
    }
}
