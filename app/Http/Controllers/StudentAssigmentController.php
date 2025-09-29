<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\SubmissionAnswer;
use DB;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
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

    public function showForTaking(int $assignmentId): JsonResponse
    {
        try {
            $studentId = Auth::user()->id;

            // 1. Verifikasi apakah siswa berhak mengakses tugas ini
            $studentClassId = DB::table('class_students')->where('student_id', $studentId)->value('class_id');
            $isAssigned = DB::table('assignment_class')
                ->where('assignment_id', $assignmentId)
                ->where('class_id', $studentClassId)
                ->exists();

            if (!$isAssigned) {
                return response()->json(['success' => false, 'message' => 'You are not authorized to take this assignment.'], 403);
            }

            // =================================================================
            // BAGIAN QUERY BUILDER YANG SUDAH DISESUAIKAN DENGAN SKEMA ANDA
            // =================================================================

            // Query 1: Ambil data utama assignment, subject, dan teacher
            $assignment = DB::table('assignments as a')
                ->join('subjects as s', 'a.subject_id', '=', 's.id')
                ->join('teachers as t', 'a.teacher_id', '=', 't.id')
                ->join('users as u', 't.user_id', '=', 'u.id')
                ->select(
                    'a.id',
                    'a.title',
                    'a.description',
                    'a.due_date',
                    's.name as subject_name',
                    'u.name as teacher_name'
                )
                ->where('a.id', $assignmentId)
                ->first();

            // Pengecekan manual jika tugas tidak ditemukan
            if (!$assignment) {
                return response()->json(['success' => false, 'message' => 'Assignment not found.'], 404);
            }

            // Query 2: Ambil semua soal untuk tugas ini
            // Kolom 'correct_answer' tidak dipilih untuk keamanan
            $questions = DB::table('questions')
                ->select('id', 'question_text', 'type', 'score', 'order')
                ->where('assignment_id', $assignmentId)
                ->orderBy('order', 'asc')
                ->get();

            if ($questions->isNotEmpty()) {
                $questionIds = $questions->pluck('id');

                // Query 3: Ambil semua opsi dari tabel 'question_options'
                // Kolom 'is_correct' tidak dipilih untuk keamanan
                $options = DB::table('question_options') // <-- NAMA TABEL SUDAH DIPERBAIKI
                    ->select('id', 'question_id', 'option_letter', 'option_text')
                    ->whereIn('question_id', $questionIds)
                    ->orderBy('option_letter', 'asc')
                    ->get()
                    ->groupBy('question_id'); // Kelompokkan opsi berdasarkan question_id

                // Gabungkan opsi ke setiap soal yang sesuai
                $questions->each(function ($question) use ($options) {
                    $question->options = $options->get($question->id, collect());
                });
            }

            // Gabungkan soal ke dalam data assignment utama
            $assignment->questions = $questions;

            // Strukturisasi ulang data agar cocok dengan ekspektasi frontend
            $data = [
                'id' => $assignment->id,
                'title' => $assignment->title,
                'description' => $assignment->description,
                'due_date' => $assignment->due_date,
                'subject' => ['name' => $assignment->subject_name],
                'teacher' => ['name' => $assignment->teacher_name],
                'questions' => $assignment->questions,
            ];

            return response()->json(['success' => true, 'data' => $data]);

        } catch (Exception $e) {
            Log::error('Failed to retrieve assignment for taking: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching the assignment.'
            ], 500);
        }
    }

    /**
     * [METHOD BARU]
     * Menerima dan menyimpan jawaban tugas dari siswa.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $assignmentId
     * @return \Illuminate\Http\JsonResponse
     */
    public function submitAnswers(Request $request, int $assignmentId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'answers' => 'required|array',
            'answers.*.question_id' => 'required|exists:questions,id',
            'answers.*.answer' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        DB::beginTransaction();
        try {
            $studentId = Auth::user()->id;

            // Cek apakah sudah pernah submit sebelumnya
            $existingSubmission = AssignmentSubmission::where('assignment_id', $assignmentId)
                ->where('student_id', $studentId)->first();
            if ($existingSubmission) {
                return response()->json(['success' => false, 'message' => 'You have already submitted this assignment.'], 409);
            }

            // 1. Buat record pengumpulan utama (submission)
            $submission = AssignmentSubmission::create([
                'assignment_id' => $assignmentId,
                'student_id' => $studentId,
                'submitted_at' => now(),
                'status' => 'submitted', // Status awal adalah "submitted"
            ]);

            // 2. Simpan setiap jawaban siswa
            foreach ($request->answers as $answerData) {
                SubmissionAnswer::create([
                    'assignment_submission_id' => $submission->id,
                    'question_id' => $answerData['question_id'],
                    'answer' => is_array($answerData['answer']) ? json_encode($answerData['answer']) : $answerData['answer'],
                ]);
            }

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Jawaban berhasil dikumpulkan!']);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Submission failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan saat mengumpulkan jawaban.'], 500);
        }
    }
}
