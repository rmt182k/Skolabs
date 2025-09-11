<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Exception;

class SubjectController extends Controller
{
    /**
     * Display a listing of the resource.
     * * @DIUBAH: Mengambil data guru dari tabel pivot.
     */
    public function index(): JsonResponse
    {
        try {
            // 1. Ambil semua data subject
            $subjects = DB::table('subjects')
                ->select('id', 'name', 'code')
                ->orderBy('name', 'asc')
                ->get();

            if ($subjects->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'No subjects data found.',
                    'data' => []
                ]);
            }

            // 2. Ambil semua relasi guru dalam satu query untuk efisiensi (menghindari N+1 query problem)
            $subjectIds = $subjects->pluck('id');
            $teachers = DB::table('subject_teachers')
                ->join('teachers', 'subject_teachers.teacher_id', '=', 'teachers.id')
                ->join('users', 'teachers.user_id', '=', 'users.id')
                ->whereIn('subject_teachers.subject_id', $subjectIds)
                ->select('subject_teachers.subject_id', 'teachers.id', 'users.name')
                ->get()
                ->groupBy('subject_id'); // Kelompokkan guru berdasarkan subject_id

            // 3. Gabungkan data guru ke setiap subject
            $formattedData = $subjects->map(function ($subject) use ($teachers) {
                $subject->teachers = $teachers->get($subject->id, []); // Jika tidak ada guru, kembalikan array kosong
                return $subject;
            });

            return response()->json([
                'success' => true,
                'message' => 'All subjects data retrieved successfully.',
                'data' => $formattedData
            ]);
        } catch (Exception $e) {
            Log::error('Failed to retrieve subjects data: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve subjects data. ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     * * @DIUBAH: Menyimpan subject dan relasinya ke tabel pivot.
     */
    public function store(Request $request): JsonResponse
    {
        // @DIUBAH: Aturan validasi untuk teacher_ids sebagai array
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:subjects,code',
            'teacher_ids' => 'required|array',
            'teacher_ids.*' => 'required|exists:teachers,id', // Memastikan setiap ID guru valid
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors.',
                'errors' => $validator->errors()
            ], 422);
        }

        // @DIUBAH: Menggunakan transaksi database karena melibatkan 2 tabel
        DB::beginTransaction();
        try {
            // 1. Insert ke tabel 'subjects'
            $subjectId = DB::table('subjects')->insertGetId([
                'name' => $request->name,
                'code' => $request->code,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 2. Siapkan data untuk tabel pivot 'subject_teachers'
            $teacherRelations = [];
            foreach ($request->teacher_ids as $teacherId) {
                $teacherRelations[] = [
                    'subject_id' => $subjectId,
                    'teacher_id' => $teacherId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // 3. Insert ke tabel pivot
            if (!empty($teacherRelations)) {
                DB::table('subject_teachers')->insert($teacherRelations);
            }

            DB::commit(); // Jika semua berhasil, simpan perubahan

            return response()->json([
                'success' => true,
                'message' => 'Subject created successfully.',
                'data' => ['id' => $subjectId]
            ], 201);

        } catch (Exception $e) {
            DB::rollBack(); // Jika ada error, batalkan semua query
            Log::error('Failed to create subject: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create subject. ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource for editing.
     * * @DIUBAH: Mengambil subject beserta relasi gurunya.
     */
    public function show($id): JsonResponse
    {
        try {
            $subject = DB::table('subjects')->where('id', $id)->first();

            if (!$subject) {
                return response()->json(['success' => false, 'message' => 'Subject not found.'], 404);
            }

            // Ambil guru yang berelasi
            $teachers = DB::table('subject_teachers')
                ->join('teachers', 'subject_teachers.teacher_id', '=', 'teachers.id')
                ->join('users', 'teachers.user_id', '=', 'users.id')
                ->where('subject_teachers.subject_id', $id)
                ->select('teachers.id', 'users.name')
                ->get();

            $subject->teachers = $teachers;

            return response()->json(['success' => true, 'data' => $subject]);
        } catch (Exception $e) {
            Log::error('Failed to find subject: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to find subject. ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @DIUBAH: Memperbarui subject dan melakukan sinkronisasi relasi guru.
     */
    public function update(Request $request, $id): JsonResponse
    {
        // @DIUBAH: Aturan validasi untuk update
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:subjects,code,' . $id,
            'teacher_ids' => 'required|array',
            'teacher_ids.*' => 'required|exists:teachers,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation errors.', 'errors' => $validator->errors()], 422);
        }

        // @DIUBAH: Menggunakan transaksi database
        DB::beginTransaction();
        try {
            // 1. Update tabel 'subjects'
            $affected = DB::table('subjects')
                ->where('id', $id)
                ->update([
                    'name' => $request->name,
                    'code' => $request->code,
                    'updated_at' => now(),
                ]);

            if ($affected === 0) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Subject not found.'], 404);
            }

            // 2. Hapus relasi lama di tabel pivot
            DB::table('subject_teachers')->where('subject_id', $id)->delete();

            // 3. Buat relasi baru
            $teacherRelations = [];
            foreach ($request->teacher_ids as $teacherId) {
                $teacherRelations[] = [
                    'subject_id' => $id,
                    'teacher_id' => $teacherId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if (!empty($teacherRelations)) {
                DB::table('subject_teachers')->insert($teacherRelations);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Subject updated successfully.',
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to update subject: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update subject. ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @DIUBAH: Menghapus subject beserta relasinya di tabel pivot.
     */
    public function destroy($id): JsonResponse
    {
        // @DIUBAH: Menggunakan transaksi database
        DB::beginTransaction();
        try {
            // Cek dulu apakah subject ada
            $subject = DB::table('subjects')->where('id', $id)->first();
            if (!$subject) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Subject not found.'], 404);
            }

            // 1. Hapus relasi dari tabel pivot terlebih dahulu
            DB::table('subject_teachers')->where('subject_id', $id)->delete();

            // 2. Hapus data subject utama
            DB::table('subjects')->where('id', $id)->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Subject deleted successfully.'
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete subject: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete subject. ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get data needed for create/edit form.
     * * @TIDAK BERUBAH: Fungsi ini sudah benar, hanya mengambil daftar guru.
     */
    public function getCreateData(): JsonResponse
    {
        try {
            $teachers = DB::table('teachers')
                ->join('users', 'teachers.user_id', '=', 'users.id')
                ->select('teachers.id', 'users.name')
                ->orderBy('users.name', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'teachers' => $teachers
                ]
            ]);
        } catch (Exception $e) {
            Log::error('Failed to retrieve create-data for subjects: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve necessary data. ' . $e->getMessage(),
            ], 500);
        }
    }
}
