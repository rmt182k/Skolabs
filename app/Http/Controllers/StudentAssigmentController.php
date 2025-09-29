<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use DB;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Log;

class StudentAssigmentController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            // 1. Dapatkan ID siswa yang sedang login
            $studentId = Auth::user()->id;


            if (!$studentId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Please login.'
                ], 401);
            }

            // 2. Dapatkan ID kelas dari siswa tersebut dari tabel pivot 'class_student'
            $studentClassId = DB::table('class_students')
                ->where('student_id', $studentId)
                ->value('class_id'); // Asumsi satu siswa hanya punya satu kelas

            // Jika siswa tidak terdaftar di kelas manapun, kembalikan data kosong
            if (!$studentClassId) {
                return response()->json([
                    'success' => true,
                    'message' => 'Student is not enrolled in any class.',
                    'data' => []
                ], 200);
            }

            // 3. Ambil semua tugas yang ditugaskan ke kelas siswa tersebut
            $assignments = DB::table('assignments as a')
                ->select(
                    'a.id',
                    'a.title',
                    'a.assignment_type',
                    'a.start_date',
                    'a.due_date',
                    's.name as subject_name',
                    'u.name as teacher_name' // <-- DIUBAH: Mengambil nama dari tabel 'users'
                )
                ->join('subjects as s', 'a.subject_id', '=', 's.id')
                ->join('teachers as t', 'a.teacher_id', '=', 't.id')       // Tetap join ke tabel teachers
                ->join('users as u', 't.user_id', '=', 'u.id')             // <-- DITAMBAHKAN: Join dari teachers ke users
                ->join('assignment_class as ac', 'a.id', '=', 'ac.assignment_id')
                ->where('ac.class_id', $studentClassId)
                ->orderBy('a.due_date', 'asc')
                ->get();

            // 4. (Opsional) Tambahkan status untuk setiap tugas (misal: 'Belum Dikerjakan', 'Selesai', 'Terlambat')
            //    Untuk ini, Anda perlu memeriksa tabel `assignment_submissions`.
            //    Ini adalah contoh sederhana, bisa dikembangkan lebih lanjut.
            $submissionStatuses = DB::table('assignment_submissions')
                ->where('student_id', $studentId)
                ->pluck('submitted_at', 'assignment_id'); // Ambil status pengumpulan

            $data = $assignments->map(function ($assignment) use ($submissionStatuses) {
                $assignment->status = 'Not Submitted'; // Default status
                if (isset($submissionStatuses[$assignment->id])) {
                    $assignment->status = 'Submitted';
                } elseif (now() > $assignment->due_date) {
                    $assignment->status = 'Overdue';
                }
                return $assignment;
            });


            return response()->json([
                'success' => true,
                'message' => 'Assignments for the student retrieved successfully.',
                'data' => $data
            ], 200);

        } catch (Exception $e) {
            Log::error('Failed to retrieve student assignments: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching assignments.' . $e->getMessage()
            ], 500);
        }
    }
}
