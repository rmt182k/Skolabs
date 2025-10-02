<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Exception;
use Carbon\Carbon;

class TeacherSubmissionController extends Controller
{
    // Method index tidak berubah
    public function index($assignmentId)
    {
        $assignmentExists = Assignment::where('id', $assignmentId)
            ->where('teacher_id', Auth::id())
            ->exists();
        if (!$assignmentExists) {
            abort(404, 'Tugas tidak ditemukan atau Anda tidak memiliki akses.');
        }
        return view('teacher.submissions.index');
    }

    // Method getSubmissions tidak berubah
    public function getSubmissions(Request $request, Assignment $assignment): JsonResponse
    {
        if ($assignment->teacher_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        try {
            $baseQuery = DB::table('students as s')
                ->join('users as u', 'u.id', '=', 's.user_id')
                ->select(
                    's.id as student_id',
                    'u.name as student_name',
                    's.nisn',
                    'c.name as class_name',
                    'sub.id as submission_id',
                    'sub.submitted_at',
                    'sub.total_grade'
                )
                ->join('class_students as cs', 's.id', '=', 'cs.student_id')
                ->join('classes as c', 'cs.class_id', '=', 'c.id')
                ->leftJoin('assignment_submissions as sub', function ($join) use ($assignment) {
                    $join->on('s.id', '=', 'sub.student_id')
                        ->where('sub.assignment_id', '=', $assignment->id);
                })
                ->whereIn('cs.class_id', function ($subQuery) use ($assignment) {
                    $subQuery->select('class_id')
                        ->from('assignment_class')
                        ->where('assignment_id', $assignment->id);
                });

            $allData = $baseQuery->orderBy('c.name')->orderBy('u.name')->get();

            $summary = [
                'total_students' => $allData->count(),
                'submitted_count' => $allData->whereNotNull('submission_id')->count(),
                'graded_count' => $allData->whereNotNull('total_grade')->count(),
            ];

            return response()->json([
                'success' => true,
                'data' => $allData,
                'assignment' => [
                    'id' => $assignment->id,
                    'title' => $assignment->title,
                    'due_date' => $assignment->due_date,
                ],
                'summary' => $summary,
            ]);

        } catch (Exception $e) {
            Log::error('Gagal mengambil data submission: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan pada server.'
            ], 500);
        }
    }


    /**
     * Menampilkan data submission siswa untuk dinilai atau direview oleh guru.
     * Method ini di-refactor untuk menghasilkan struktur JSON yang sesuai dengan frontend.
     * Merespon ke: GET /api/teacher-submissions/{submission}/grade
     *
     * @param AssignmentSubmission $submission
     * @return JsonResponse
     */
    public function showGradeForm(AssignmentSubmission $submission): JsonResponse
    {
        try {
            // Otorisasi menggunakan Query Builder
            // DIPERBAIKI: Mengganti 'type' dengan 'assignment_type' sesuai skema
            $assignment = DB::table('assignments')->where('id', $submission->assignment_id)->select('id', 'teacher_id', 'title', 'due_date', 'assignment_type')->first();
            if (!$assignment || $assignment->teacher_id !== Auth::id()) {
                return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
            }

            // Mengambil data siswa dan submission menggunakan Query Builder
            $submissionInfo = DB::table('assignment_submissions as asub')
                ->join('students as s', 'asub.student_id', '=', 's.id')
                ->join('users as u', 's.user_id', '=', 'u.id')
                ->where('asub.id', $submission->id)
                ->select('u.name as student_name', 's.nisn', 'asub.submitted_at', 'asub.total_grade')
                ->first();

            // Mengambil semua jawaban dan data pertanyaan terkait menggunakan Query Builder
            // DIPERBAIKI: Menghapus 'q.model_answer' dari select
            $answers = DB::table('submission_answers as sa')
                ->join('questions as q', 'sa.question_id', '=', 'q.id')
                ->where('sa.assignment_submission_id', $submission->id)
                ->select(
                    'sa.id as answerId',
                    'sa.answer',
                    'sa.grade',
                    'sa.feedback',
                    'q.id as question_id',
                    'q.question_text',
                    'q.type',
                    'q.order',
                    'q.score',
                    'q.correct_answer'
                    // 'q.model_answer' DIHAPUS KARENA TIDAK ADA DI DATABASE
                )
                ->orderBy('q.order', 'asc')
                ->get();

            // Mengambil semua opsi untuk pertanyaan pilihan ganda dalam satu query
            $questionIds = $answers->pluck('question_id')->all();
            $allOptions = DB::table('question_options')
                ->whereIn('question_id', $questionIds)
                ->orderBy('id', 'asc')
                ->get()
                ->groupBy('question_id');

            // Menghitung status pengumpulan
            $isLate = $assignment->due_date && Carbon::parse($submissionInfo->submitted_at)->isAfter($assignment->due_date);
            $statusText = $isLate ? 'Terlambat' : 'Tepat Waktu';

            // Memetakan jawaban ke dalam format 'results' yang diharapkan frontend
            $results = $answers->map(function ($answer) use ($allOptions) {
                $pointsAwarded = $answer->grade;
                $optionsData = [];
                $correctAnswerLetters = [];
                $studentAnswerDisplay = $answer->answer;
                $questionOptions = $allOptions->get($answer->question_id, collect());

                if ($answer->type === 'multiple_choice') {
                    foreach ($questionOptions as $index => $option) {
                        $letter = chr(65 + $index);
                        $optionsData[] = ['letter' => $letter, 'text' => $option->option_text];
                        if ($option->is_correct) {
                            $correctAnswerLetters[] = $letter;
                        }
                    }

                    $studentOptionIndex = $questionOptions->search(fn($opt) => $opt->id == $answer->answer);
                    $studentAnswerDisplay = ($studentOptionIndex !== false) ? chr(65 + $studentOptionIndex) : null;

                    if (is_null($pointsAwarded)) {
                        $correctOption = $questionOptions->firstWhere('is_correct', true);
                        $isStudentCorrect = $correctOption && ($correctOption->id == $answer->answer);
                        $pointsAwarded = $isStudentCorrect ? $answer->score : 0;
                        DB::table('submission_answers')->where('id', $answer->answerId)->update(['grade' => $pointsAwarded]);
                    }
                }

                return [
                    'answerId' => $answer->answerId,
                    'question' => [
                        'id' => $answer->question_id,
                        'order' => $answer->order,
                        'type' => $answer->type,
                        'text' => $answer->question_text,
                        'score' => $answer->score,
                        'options' => $optionsData,
                        'correctAnswer' => $answer->type === 'multiple_choice' ? $correctAnswerLetters : ($answer->correct_answer ?? ''),
                        // 'modelAnswer' DIHAPUS KARENA TIDAK ADA DI DATABASE
                    ],
                    'studentAnswer' => $studentAnswerDisplay,
                    'pointsAwarded' => $pointsAwarded,
                    'feedback' => $answer->feedback ?? '',
                ];
            });

            // Struktur data final yang dikirim ke frontend
            $data = [
                'student' => ['name' => $submissionInfo->student_name, 'nisn' => $submissionInfo->nisn],
                // DIPERBAIKI: Menggunakan 'assignment_type' dan mengirimkannya sebagai 'type'
                'assignment' => ['id' => $assignment->id, 'title' => $assignment->title, 'type' => $assignment->assignment_type],
                'submission' => [
                    'id' => $submission->id,
                    'submittedAt' => Carbon::parse($submissionInfo->submitted_at)->toDateTimeString(),
                    'status' => $statusText,
                    'isGraded' => !is_null($submissionInfo->total_grade),
                ],
                'results' => $results,
            ];

            return response()->json(['success' => true, 'data' => $data]);
        } catch (Exception $e) {
            Log::error("Gagal memuat data penilaian untuk submission ID {$submission->id}: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server saat memuat data.' . $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Menyimpan nilai dan feedback yang diberikan guru.
     * Method ini di-refactor untuk menggunakan Query Builder.
     * Merespon ke: POST /api/teacher-submissions/{submission}/grade
     *
     * @param Request $request
     * @param AssignmentSubmission $submission
     * @return JsonResponse
     */
    public function storeGrade(Request $request, AssignmentSubmission $submission): JsonResponse
    {
        // Otorisasi menggunakan Query Builder
        $assignment = DB::table('assignments')->where('id', $submission->assignment_id)->first();
        if (!$assignment || $assignment->teacher_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        // Validasi
        $validator = Validator::make($request->all(), [
            'grades' => ['required', 'array'],
            'grades.*.score' => ['nullable', 'numeric', 'min:0'],
            'grades.*.feedback' => ['nullable', 'string', 'max:5000'],
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Data input tidak valid.', 'errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $gradesData = $request->input('grades');
            $totalPointsEarned = 0;
            $maxTotalPoints = 0;

            // Mengambil semua jawaban dan data skor pertanyaan terkait
            $answers = DB::table('submission_answers as sa')
                ->join('questions as q', 'sa.question_id', '=', 'q.id')
                ->where('sa.assignment_submission_id', $submission->id)
                ->select('sa.id', 'sa.grade', 'q.score as max_score')
                ->get()->keyBy('id');

            foreach ($gradesData as $answerId => $data) {
                if (!$answers->has($answerId))
                    continue;

                $answer = $answers[$answerId];
                $maxScore = $answer->max_score;
                $maxTotalPoints += $maxScore;
                $score = $data['score'] ?? $answer->grade;

                if (!is_null($score) && $score > $maxScore) {
                    $score = $maxScore;
                }

                // Update nilai dan feedback menggunakan Query Builder
                DB::table('submission_answers')->where('id', $answerId)->update([
                    'grade' => $score,
                    'feedback' => $data['feedback'] ?? null
                ]);

                $totalPointsEarned += $score ?? 0;
            }

            // Hitung nilai akhir (skala 0-100)
            $finalGrade = ($maxTotalPoints > 0) ? round(($totalPointsEarned / $maxTotalPoints) * 100) : 0;

            // Update submission utama dengan nilai total dan status
            DB::table('assignment_submissions')->where('id', $submission->id)->update([
                'total_grade' => $finalGrade,
                'status' => 'graded'
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Penilaian berhasil disimpan.',
                'redirect_url' => route('teacher-assignment-submission.show', $submission->assignment_id)
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Gagal menyimpan penilaian untuk submission ID {$submission->id}: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server saat menyimpan data.' . $e->getMessage()
            ], 500);
        }
    }
}

