<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SubjectController extends Controller
{
    /**
     * Mengambil semua data mata pelajaran untuk ditampilkan di tabel (Client-Side).
     */
    public function index(): JsonResponse
    {
        try {
            $subjects = DB::table('subjects')
                ->select('id', 'name', 'code')
                ->orderBy('name', 'asc')
                ->get();

            $subjectIds = $subjects->pluck('id');
            $teachers = DB::table('subject_teachers')
                ->join('teachers', 'subject_teachers.teacher_id', '=', 'teachers.id')
                ->join('users', 'teachers.user_id', '=', 'users.id')
                ->whereIn('subject_teachers.subject_id', $subjectIds)
                ->select('subject_teachers.subject_id', 'teachers.id', 'users.name')
                ->get()
                ->groupBy('subject_id');

            $formattedData = $subjects->map(function ($subject) use ($teachers) {
                $subject->teachers = $teachers->get($subject->id, []);
                return $subject;
            });

            return response()->json(['success' => true, 'data' => $formattedData]);
        } catch (Exception $e) {
            Log::error('Error fetching subjects: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'An error occurred on the server.'], 500);
        }
    }

    /**
     * Mengambil data satu mata pelajaran spesifik untuk form edit.
     */
    public function show($id): JsonResponse
    {
        try {
            $subject = DB::table('subjects')->where('id', $id)->first();
            if (!$subject) {
                return response()->json(['success' => false, 'message' => 'Subject not found.'], 404);
            }

            $teachers = DB::table('subject_teachers')
                ->join('teachers', 'subject_teachers.teacher_id', '=', 'teachers.id')
                ->join('users', 'teachers.user_id', '=', 'users.id')
                ->where('subject_teachers.subject_id', $id)
                ->select('teachers.id', 'users.name')
                ->get();

            $subject->teachers = $teachers;

            return response()->json(['success' => true, 'data' => $subject]);
        } catch (Exception $e) {
            Log::error("Error fetching subject ID {$id}: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'An error occurred on the server.'], 500);
        }
    }

    /**
     * Menyimpan mata pelajaran baru.
     */
    public function store(Request $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $validatedData = $this->validateSubject($request);

            $subjectId = DB::table('subjects')->insertGetId([
                'name' => $validatedData['name'],
                'code' => $validatedData['code'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->syncTeachers($subjectId, $validatedData['teacher_ids']);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Subject created successfully.'], 201);
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'errors' => $e->errors()], 422);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error creating subject: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'An error occurred.'], 500);
        }
    }

    /**
     * Memperbarui data mata pelajaran yang sudah ada.
     */
    public function update(Request $request, $id): JsonResponse
    {
        DB::beginTransaction();
        try {
            $subject = DB::table('subjects')->where('id', $id)->first();
            if (!$subject) {
                return response()->json(['success' => false, 'message' => 'Subject not found.'], 404);
            }

            $validatedData = $this->validateSubject($request, $id);

            DB::table('subjects')->where('id', $id)->update([
                'name' => $validatedData['name'],
                'code' => $validatedData['code'],
                'updated_at' => now(),
            ]);

            $this->syncTeachers($id, $validatedData['teacher_ids']);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Subject updated successfully.']);
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'errors' => $e->errors()], 422);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Error updating subject ID {$id}: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'An error occurred.'], 500);
        }
    }

    /**
     * Menghapus mata pelajaran.
     */
    public function destroy($id): JsonResponse
    {
        DB::beginTransaction();
        try {
            $subject = DB::table('subjects')->where('id', $id)->exists();
            if (!$subject) {
                return response()->json(['success' => false, 'message' => 'Subject not found.'], 404);
            }

            DB::table('subject_teachers')->where('subject_id', $id)->delete();
            DB::table('subjects')->where('id', $id)->delete();

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Subject deleted successfully.']);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Error deleting subject ID {$id}: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to delete subject.'], 500);
        }
    }

    /**
     * Fungsi validasi private untuk store dan update.
     */
    private function validateSubject(Request $request, $subjectId = null)
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'code' => ['required', 'string', 'max:20', Rule::unique('subjects')->ignore($subjectId)],
            'teacher_ids' => 'sometimes|array',
            'teacher_ids.*' => 'exists:teachers,id',
        ]);
    }

    /**
     * Fungsi untuk sinkronisasi guru pada tabel pivot.
     */
    private function syncTeachers($subjectId, $teacherIds = [])
    {
        DB::table('subject_teachers')->where('subject_id', $subjectId)->delete();
        if (!empty($teacherIds)) {
            $relations = array_map(function ($teacherId) use ($subjectId) {
                return [
                    'subject_id' => $subjectId,
                    'teacher_id' => $teacherId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }, $teacherIds);
            DB::table('subject_teachers')->insert($relations);
        }
    }
}
