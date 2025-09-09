<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Exception;

class LearningMaterialController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $materials = DB::table('learning_materials as lm')
                ->select(
                    'lm.id',
                    'lm.title',
                    'lm.file_type',
                    'subjects.name as subject_name',
                    'classes.name as class_name',
                    'users.name as teacher_name'
                )
                ->leftJoin('subjects', 'lm.subject_id', '=', 'subjects.id')
                ->leftJoin('classes', 'lm.class_id', '=', 'classes.id')
                ->leftJoin('teachers', 'lm.teacher_id', '=', 'teachers.id')
                ->leftJoin('users', 'teachers.user_id', '=', 'users.id')
                ->orderBy('lm.created_at', 'desc')
                ->get();

            $formattedData = $materials->map(function ($material) {
                return [
                    'id' => $material->id,
                    'title' => $material->title,
                    'file_type' => $material->file_type,
                    'subject' => ['name' => $material->subject_name],
                    'class' => ['name' => $material->class_name],
                    'teacher' => ['name' => $material->teacher_name],
                ];
            });

            return response()->json(['success' => true, 'data' => $formattedData]);
        } catch (Exception $e) {
            Log::error('Failed to retrieve learning materials: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to retrieve data.'], 500);
        }
    }
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'subject_id' => 'required|exists:subjects,id',
            'class_id' => 'required|exists:classes,id',
            'file' => 'required|file|mimes:pdf,docx,pptx,jpg,png,mp4|max:10240', // Max 10MB
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation errors', 'errors' => $validator->errors()], 422);
        }

        try {
            $file = $request->file('file');
            $originalName = $file->getClientOriginalName();
            $path = $file->store('public/learning_materials');

            DB::table('learning_materials')->insert([
                'title' => $request->title,
                'description' => $request->description,
                'file_path' => $path,
                'file_name' => $originalName,
                'file_type' => $file->getClientOriginalExtension(),
                'file_size' => $file->getSize(),
                'subject_id' => $request->subject_id,
                'class_id' => $request->class_id,
                'teacher_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json(['success' => true, 'message' => 'Material uploaded successfully.'], 201);
        } catch (Exception $e) {
            Log::error('Failed to store material: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to upload material.'], 500);
        }
    }
    public function show($id): JsonResponse
    {
        try {
            $material = DB::table('learning_materials')->where('id', $id)->first();
            if (!$material) {
                return response()->json(['success' => false, 'message' => 'Material not found.'], 404);
            }
            return response()->json(['success' => true, 'data' => $material]);
        } catch (Exception $e) {
            Log::error("Error fetching material {$id}: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Could not find the material.'], 500);
        }
    }
    public function update(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'subject_id' => 'required|exists:subjects,id',
            'class_id' => 'required|exists:classes,id',
            'file' => 'nullable|file|mimes:pdf,docx,pptx,jpg,png,mp4|max:10240', // File is optional on update
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation errors', 'errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $material = DB::table('learning_materials')->where('id', $id)->first();
            if (!$material) {
                return response()->json(['success' => false, 'message' => 'Material not found.'], 404);
            }

            $updateData = [
                'title' => $request->title,
                'description' => $request->description,
                'subject_id' => $request->subject_id,
                'class_id' => $request->class_id,
                'updated_at' => now(),
            ];

            if ($request->hasFile('file')) {
                if ($material->file_path && Storage::exists($material->file_path)) {
                    Storage::delete($material->file_path);
                }

                $file = $request->file('file');
                $updateData['file_path'] = $file->store('public/learning_materials');
                $updateData['file_name'] = $file->getClientOriginalName();
                $updateData['file_type'] = $file->getClientOriginalExtension();
                $updateData['file_size'] = $file->getSize();
            }

            DB::table('learning_materials')->where('id', $id)->update($updateData);
            DB::commit();

            return response()->json(['success' => true, 'message' => 'Material updated successfully.']);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Failed to update material {$id}: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to update material.'], 500);
        }
    }
    public function destroy($id): JsonResponse
    {
        DB::beginTransaction();
        try {
            $material = DB::table('learning_materials')->where('id', $id)->first();
            if (!$material) {
                return response()->json(['success' => false, 'message' => 'Material not found.'], 404);
            }

            if ($material->file_path && Storage::exists($material->file_path)) {
                Storage::delete($material->file_path);
            }

            DB::table('learning_materials')->where('id', $id)->delete();
            DB::commit();

            return response()->json(['success' => true, 'message' => 'Material deleted successfully.']);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Failed to delete material {$id}: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to delete material.'], 500);
        }
    }
    public function getCreateData(): JsonResponse
    {
        try {
            $subjects = DB::table('subjects')->select('id', 'name')->orderBy('name')->get();
            $classes = DB::table('classes')->select('id', 'name')->orderBy('name')->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'subjects' => $subjects,
                    'classes' => $classes,
                ]
            ]);
        } catch (Exception $e) {
            Log::error('Failed to get create data for materials: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to load form data.'], 500);
        }
    }
    public function download($id)
    {
        try {
            $material = DB::table('learning_materials')->where('id', $id)->first();
            if (!$material || !Storage::exists($material->file_path)) {
                abort(404, 'File not found.');
            }
            return Storage::download($material->file_path, $material->file_name);
        } catch (Exception $e) {
            Log::error("Failed to download material {$id}: " . $e->getMessage());
            abort(500, 'Could not download the file.');
        }
    }
}
