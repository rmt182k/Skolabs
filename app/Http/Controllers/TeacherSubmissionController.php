<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Exception;

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

    /**
     * PERUBAHAN TOTAL: Method ini sekarang merespon permintaan Server-Side DataTables
     */
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

            // PERUBAHAN: Gunakan get() untuk mengambil semua data, bukan paginate()
            $allData = $baseQuery->orderBy('c.name')->orderBy('u.name')->get();

            // Kalkulasi summary dari data yang sudah diambil
            $summary = [
                'total_students' => $allData->count(),
                'submitted_count' => $allData->whereNotNull('submission_id')->count(),
                'graded_count' => $allData->whereNotNull('total_grade')->count(),
            ];

            // PERUBAHAN: Struktur JSON yang dikembalikan menjadi lebih simpel
            return response()->json([
                'success' => true,
                'data' => $allData, // Kirim semua data dalam properti 'data'
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
}
