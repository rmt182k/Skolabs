<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Exception;

class AssignmentSubmissionController extends Controller
{
    /**
     * [UNTUK GURU] Menampilkan semua submission untuk assignment tertentu.
     * Digunakan di halaman detail tugas untuk melihat siapa saja yang sudah mengumpulkan.
     */
    public function index($assignment_id): JsonResponse
    {
        try {
            $submissions = DB::table('assignment_submissions as as')
                ->join('users as u', 'as.student_id', '=', 'u.id') // Asumsi siswa ada di tabel 'users'
                ->where('as.assignment_id', $assignment_id)
                ->select(
                    'as.id',
                    'as.student_id',
                    'u.name as student_name',
                    'as.status',
                    'as.submitted_at',
                    'as.total_grade'
                )
                ->orderBy('as.submitted_at', 'desc')
                ->get();

            return response()->json(['success' => true, 'data' => $submissions]);
        } catch (Exception $e) {
            Log::error("Failed to retrieve submissions for assignment {$assignment_id}: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal mengambil data pengumpulan tugas.'], 500);
        }
    }

    public function getAssignmentsByTeacher(): JsonResponse
    {
        try {
            $teacherId = Auth::id();

            $assignments = DB::table('assignments as a')
                ->where('a.teacher_id', $teacherId)
                ->join('subjects as s', 'a.subject_id', '=', 's.id')
                ->select('a.id', 'a.title', 'a.assignment_type', 'a.due_date', 's.name as subject_name')
                ->orderBy('a.created_at', 'desc')
                ->get();

            return response()->json(['success' => true, 'data' => $assignments]);
        } catch (Exception $e) {
            Log::error('Failed to get assignments for teacher ' . Auth::id() . ': ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal mengambil data tugas.'], 500);
        }
    }

    public function getAssignmentsForStudent(): JsonResponse
    {
        try {
            $student = Auth::user();

            // Asumsi: tabel 'users' punya kolom 'class_id' untuk menandakan kelas siswa.
            // Jika struktur Anda berbeda, sesuaikan query ini.
            if (!$student->class_id) {
                return response()->json(['success' => true, 'data' => []]); // Tidak ada kelas, tidak ada tugas
            }

            // Subquery untuk mendapatkan assignment_id yang sudah dikumpulkan siswa ini
            $submittedAssignmentIds = DB::table('assignment_submissions')
                ->where('student_id', $student->id)
                ->pluck('assignment_id');

            $assignments = DB::table('assignments as a')
                ->join('assignment_class as ac', 'a.id', '=', 'ac.assignment_id')
                ->join('subjects as s', 'a.subject_id', '=', 's.id')
                ->where('ac.class_id', $student->class_id)
                ->select(
                    'a.id',
                    'a.title',
                    'a.assignment_type',
                    'a.due_date',
                    's.name as subject_name'
                )
                ->selectRaw('CASE WHEN a.id IN (?) THEN "submitted" ELSE "not_submitted" END as submission_status', [$submittedAssignmentIds->implode(',')])
                ->distinct()
                ->orderBy('a.due_date', 'asc')
                ->get();

            return response()->json(['success' => true, 'data' => $assignments]);
        } catch (Exception $e) {
            Log::error('Failed to get assignments for student ' . Auth::id() . ': ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal mengambil data tugas.'], 500);
        }
    }

    /**
     * [UNTUK SISWA] Menyimpan jawaban dari siswa.
     * Ini adalah proses "submit" tugas.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'assignment_id' => 'required|exists:assignments,id',
            'answers' => 'required|array|min:1',
            'answers.*.question_id' => 'required|exists:questions,id',
            'answers.*.answer_text' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $studentId = Auth::id();
        $assignmentId = $request->assignment_id;

        // Cek apakah siswa sudah pernah mengumpulkan tugas ini
        $existingSubmission = DB::table('assignment_submissions')
            ->where('assignment_id', $assignmentId)
            ->where('student_id', $studentId)
            ->exists();

        if ($existingSubmission) {
            return response()->json(['success' => false, 'message' => 'Anda sudah mengumpulkan tugas ini.'], 409);
        }

        DB::beginTransaction();
        try {
            // 1. Buat "wadah" submission terlebih dahulu
            $submissionId = DB::table('assignment_submissions')->insertGetId([
                'assignment_id' => $assignmentId,
                'student_id' => $studentId,
                'submitted_at' => now(),
                'status' => 'submitted',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 2. Siapkan data jawaban untuk di-insert
            $answersData = [];
            foreach ($request->answers as $answer) {
                $answersData[] = [
                    'assignment_submission_id' => $submissionId,
                    'question_id' => $answer['question_id'],
                    'student_id' => $studentId, // Duplikasi untuk mempermudah query
                    'answer_text' => $answer['answer_text'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // 3. Insert semua jawaban sekaligus
            DB::table('submission_answers')->insert($answersData);

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Tugas berhasil dikumpulkan.'], 201);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to submit assignment: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal mengumpulkan tugas.'], 500);
        }
    }

    /**
     * [UNTUK GURU] Menampilkan detail satu submission untuk dinilai.
     * Menggabungkan data soal asli dengan jawaban siswa.
     */
    public function show($submission_id): JsonResponse
    {
        try {
            // 1. Ambil data submission utama dan data siswa
            $submission = DB::table('assignment_submissions as as')
                ->join('users as u', 'as.student_id', '=', 'u.id')
                ->join('assignments as a', 'as.assignment_id', '=', 'a.id')
                ->where('as.id', $submission_id)
                ->select(
                    'as.id',
                    'as.status',
                    'as.total_grade',
                    'as.submitted_at',
                    'as.feedback',
                    'u.name as student_name',
                    'a.id as assignment_id',
                    'a.title as assignment_title'
                )
                ->first();

            if (!$submission) {
                return response()->json(['success' => false, 'message' => 'Submission tidak ditemukan.'], 404);
            }

            // 2. Ambil semua soal asli dari assignment tersebut
            $questions = DB::table('questions')
                ->where('assignment_id', $submission->assignment_id)
                ->orderBy('order')
                ->get();

            // 3. Ambil semua jawaban siswa untuk submission ini dan kelompokkan berdasarkan question_id
            $studentAnswers = DB::table('submission_answers')
                ->where('assignment_submission_id', $submission_id)
                ->get()
                ->keyBy('question_id');

            // 4. Gabungkan jawaban siswa ke setiap soal
            foreach ($questions as $question) {
                $question->student_answer = $studentAnswers->get($question->id);
            }

            $submission->questions = $questions;

            return response()->json(['success' => true, 'data' => $submission]);
        } catch (Exception $e) {
            Log::error("Failed to fetch submission {$submission_id}: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal mengambil detail data.'], 500);
        }
    }

    /**
     * [UNTUK GURU] Update nilai dan feedback untuk sebuah submission.
     * Ini adalah proses "grading" atau menilai.
     */
    public function update(Request $request, $submission_id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'feedback' => 'nullable|string',
            'answers' => 'required|array',
            'answers.*.id' => 'required|exists:submission_answers,id', // ID dari jawaban siswa
            'answers.*.grade' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $totalGrade = 0;

            // 1. Update nilai untuk setiap jawaban
            foreach ($request->answers as $answer) {
                DB::table('submission_answers')
                    ->where('id', $answer['id'])
                    ->where('assignment_submission_id', $submission_id) // Keamanan tambahan
                    ->update([
                        'grade' => $answer['grade'],
                        'updated_at' => now()
                    ]);
                $totalGrade += $answer['grade'];
            }

            // 2. Update nilai total dan status di submission utama
            DB::table('assignment_submissions')->where('id', $submission_id)->update([
                'total_grade' => $totalGrade,
                'feedback' => $request->feedback,
                'status' => 'graded', // Ubah status menjadi sudah dinilai
                'updated_at' => now(),
            ]);

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Nilai berhasil disimpan.']);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Failed to grade submission {$submission_id}: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal menyimpan nilai.'], 500);
        }
    }
}
