<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Exception;

class AssignmentController extends Controller
{
    /**
     * Display a listing of the assignments for teachers.
     */
    public function index(): JsonResponse
    {
        try {
            $assignments = DB::table('assignments as a')
                ->select(
                    'a.id',
                    'a.title',
                    'a.due_date',
                    's.name as subject_name',
                    'c.name as class_name'
                )
                ->leftJoin('subjects as s', 'a.subject_id', '=', 's.id')
                ->leftJoin('classes as c', 'a.class_id', '=', 'c.id')
                ->orderBy('a.due_date', 'desc')
                ->get();

            $formattedData = $assignments->map(function ($assignment) {
                return [
                    'id' => $assignment->id,
                    'title' => $assignment->title,
                    'due_date' => $assignment->due_date ? date('Y-m-d H:i', strtotime($assignment->due_date)) : '-',
                    'subject' => ['name' => $assignment->subject_name],
                    'class' => ['name' => $assignment->class_name],
                ];
            });

            return response()->json(['success' => true, 'data' => $formattedData]);
        } catch (Exception $e) {
            Log::error('Failed to retrieve assignments: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to retrieve data.'], 500);
        }
    }

    /**
     * Store a newly created assignment.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'subject_id' => 'required|exists:subjects,id',
            'class_id' => 'required|exists:classes,id',
            'due_date' => 'nullable|date',
            'file' => 'nullable|file|mimes:pdf,docx,pptx,jpg,png,zip|max:10240', // Max 10MB
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $insertData = $request->only('title', 'description', 'subject_id', 'class_id', 'due_date');
            $insertData['teacher_id'] = 1; // GANTI DENGAN ID GURU YANG LOGIN
            $insertData['created_at'] = now();
            $insertData['updated_at'] = now();

            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $insertData['file_path'] = $file->store('public/assignments');
                $insertData['file_name'] = $file->getClientOriginalName();
            }

            DB::table('assignments')->insert($insertData);

            return response()->json(['success' => true, 'message' => 'Assignment created successfully.'], 201);
        } catch (Exception $e) {
            Log::error('Failed to create assignment: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to create assignment.'], 500);
        }
    }

    /**
     * Display a specific assignment for editing.
     */
    public function show($id): JsonResponse
    {
        $assignment = DB::table('assignments')->where('id', $id)->first();
        if (!$assignment) {
            return response()->json(['success' => false, 'message' => 'Assignment not found.'], 404);
        }
        return response()->json(['success' => true, 'data' => $assignment]);
    }

    /**
     * Update an existing assignment.
     * Menggunakan Request karena method asli adalah PUT/PATCH.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'subject_id' => 'required|exists:subjects,id',
            'class_id' => 'required|exists:classes,id',
            'due_date' => 'nullable|date',
            'file' => 'nullable|file|mimes:pdf,docx,pptx,jpg,png,zip|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $assignment = DB::table('assignments')->where('id', $id)->first();
            if (!$assignment) {
                return response()->json(['success' => false, 'message' => 'Assignment not found.'], 404);
            }

            $updateData = $request->only('title', 'description', 'subject_id', 'class_id', 'due_date');
            $updateData['updated_at'] = now();

            if ($request->hasFile('file')) {
                // Hapus file lama jika ada
                if ($assignment->file_path && Storage::exists($assignment->file_path)) {
                    Storage::delete($assignment->file_path);
                }
                // Upload file baru
                $file = $request->file('file');
                $updateData['file_path'] = $file->store('public/assignments');
                $updateData['file_name'] = $file->getClientOriginalName();
            }

            DB::table('assignments')->where('id', $id)->update($updateData);
            DB::commit();

            return response()->json(['success' => true, 'message' => 'Assignment updated successfully.']);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Failed to update assignment {$id}: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to update assignment.'], 500);
        }
    }


    /**
     * Remove an assignment.
     */
    public function destroy($id): JsonResponse
    {
        DB::beginTransaction();
        try {
            $assignment = DB::table('assignments')->where('id', $id)->first();
            if (!$assignment) {
                return response()->json(['success' => false, 'message' => 'Assignment not found.'], 404);
            }

            // Hapus file dari storage jika ada
            if ($assignment->file_path && Storage::exists($assignment->file_path)) {
                Storage::delete($assignment->file_path);
            }

            // Hapus record dari database
            DB::table('assignments')->where('id', $id)->delete();

            // Hapus juga semua submission terkait (opsional tapi direkomendasikan)
            // Lakukan penghapusan file submission juga jika ada
            // DB::table('assignment_submissions')->where('assignment_id', $id)->delete();

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Assignment deleted successfully.']);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Failed to delete assignment {$id}: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to delete assignment.'], 500);
        }
    }

    /**
     * Get data for create/edit form dropdowns.
     */
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
            Log::error('Failed to get create data for assignments: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to load form data.'], 500);
        }
    }
}
