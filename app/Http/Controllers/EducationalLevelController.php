<?php

namespace App\Http\Controllers;

use App\Models\EducationalLevel;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class EducationalLevelController extends Controller
{
    public function index()
    {
        try {
            $levels = EducationalLevel::all();

            if ($levels->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'No educational levels found.',
                    'data' => []
                ], 200);
            }

            return response()->json([
                'success' => true,
                'message' => 'Educational levels retrieved successfully.',
                'data' => $levels
            ], 200);

        } catch (Exception $e) {
            Log::error('Error fetching educational levels: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve educational levels: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $level = EducationalLevel::find($id);

            if (!$level) {
                return response()->json([
                    'success' => false,
                    'message' => 'Educational level not found.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Educational level retrieved successfully.',
                'data' => $level
            ], 200);

        } catch (Exception $e) {
            Log::error('Error fetching educational level with ID ' . $id . ': ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve educational level. Please try again later.'
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255|unique:educational_levels,name',
                'duration_years' => 'required|integer',
                'description' => 'nullable|string|max:255'
            ]);

            $level = EducationalLevel::create($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Educational level created successfully.',
                'data' => $level
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            Log::error('Error creating educational level: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create educational level. Please try again later.'
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $level = EducationalLevel::find($id);

            if (!$level) {
                return response()->json([
                    'success' => false,
                    'message' => 'Educational level not found.'
                ], 404);
            }

            $validatedData = $request->validate([
                'name' => 'sometimes|required|string|max:255|unique:educational_levels,name,' . $id,
                'duration_years' => 'sometimes|required|integer',
                'description' => 'nullable|string|max:255'
            ]);

            $level->update($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Educational level updated successfully.',
                'data' => $level
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            Log::error('Error updating educational level with ID ' . $id . ': ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update educational level. Please try again later.'
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $level = EducationalLevel::find($id);

            if (!$level) {
                return response()->json([
                    'success' => false,
                    'message' => 'Educational level not found.'
                ], 404);
            }

            $level->delete();

            return response()->json([
                'success' => true,
                'message' => 'Educational level deleted successfully.'
            ], 200);

        } catch (Exception $e) {
            Log::error('Error deleting educational level with ID ' . $id . ': ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete educational level. Please try again later.'
            ], 500);
        }
    }
}
