<?php

namespace App\Http\Controllers;

use App\Models\Major;
use App\Models\EducationalLevel;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class MajorController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $educationalLevelId = $request->query('educational_level_id');

            $query = DB::table('majors')
                ->select(
                    'majors.id',
                    'majors.name',
                    'majors.description',
                    'majors.educational_level_id',
                    'educational_levels.name as educational_level_name',
                    'majors.created_at',
                    'majors.updated_at'
                )
                ->join('educational_levels', 'majors.educational_level_id', '=', 'educational_levels.id');

            if ($educationalLevelId) {
                $query->where('majors.educational_level_id', $educationalLevelId);
            }

            $majors = $query->get();

            if ($majors->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'No majors found.',
                    'data' => []
                ], 200);
            }

            return response()->json([
                'success' => true,
                'message' => 'Majors retrieved successfully.',
                'data' => $majors
            ], 200);

        } catch (Exception $e) {
            Log::error('Error fetching majors: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve majors. Please try again later.'
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $major = Major::find($id);

            if (!$major) {
                return response()->json([
                    'success' => false,
                    'message' => 'Major not found.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Major retrieved successfully.',
                'data' => $major
            ], 200);

        } catch (Exception $e) {
            Log::error('Error fetching major with ID ' . $id . ': ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve major. Please try again later.'
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'educational_level_id' => 'required|exists:educational_levels,id',
                'name' => 'required|string|max:255|unique:majors,name',
                'description' => 'required|string|max:255'
            ]);

            $major = Major::create($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Major created successfully.',
                'data' => $major
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            Log::error('Error creating major: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create major. ' . $e->getMessage()
            ], 500);
        }
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        try {
            $major = Major::find($id);

            if (!$major) {
                return response()->json([
                    'success' => false,
                    'message' => 'Major not found.'
                ], 404);
            }

            $validatedData = $request->validate([
                'educational_level_id' => 'sometimes|required|exists:educational_levels,id',
                'name' => 'sometimes|required|string|max:255|unique:majors,name,' . $id,
                'description' => 'sometimes|required|string|max:255'
            ]);

            $major->update($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Major updated successfully.',
                'data' => $major
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            Log::error('Error updating major with ID ' . $id . ': ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update major. Please try again later.'
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $major = Major::find($id);

            if (!$major) {
                return response()->json([
                    'success' => false,
                    'message' => 'Major not found.'
                ], 404);
            }

            $major->delete();

            return response()->json([
                'success' => true,
                'message' => 'Major deleted successfully.'
            ], 200);

        } catch (Exception $e) {
            Log::error('Error deleting major with ID ' . $id . ': ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete major. Please try again later.'
            ], 500);
        }
    }
}
