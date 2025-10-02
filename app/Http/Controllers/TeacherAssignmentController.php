<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request; // <-- Tambahkan ini
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Exception;

class TeacherAssignmentController extends Controller
{
    /**
     * Mengambil dan memformat daftar tugas untuk halaman manajemen tugas guru,
     * lengkap dengan filter dan statistik.
     */
    public function getAssignments(Request $request): JsonResponse
    {
        try {
            $teacherId = Auth::id();
            $query = DB::table('assignments as a')
                ->select(
                    'a.id',
                    'a.title',
                    'a.assignment_type as type',
                    'a.due_date as dueDate',
                    's.name as course',
                    DB::raw("(SELECT GROUP_CONCAT(c.name SEPARATOR ', ') FROM assignment_class ac JOIN classes c ON ac.class_id = c.id WHERE ac.assignment_id = a.id) as class"),
                    DB::raw("(SELECT COUNT(DISTINCT sc.student_id) FROM assignment_class ac_sub JOIN class_students sc ON ac_sub.class_id = sc.class_id WHERE ac_sub.assignment_id = a.id) as totalStudents"),
                    DB::raw("(SELECT COUNT(id) FROM assignment_submissions WHERE assignment_id = a.id) as submittedCount"),
                    DB::raw("(SELECT COUNT(id) FROM assignment_submissions WHERE assignment_id = a.id AND total_grade IS NOT NULL) as gradedCount")
                )
                ->join('subjects as s', 'a.subject_id', '=', 's.id')
                ->where('a.teacher_id', $teacherId);

            // --- APLIKASIKAN FILTER ---

            // Filter berdasarkan pencarian judul
            if ($request->filled('search')) {
                $query->where('a.title', 'LIKE', '%' . $request->search . '%');
            }

            // Filter berdasarkan Mata Pelajaran (subject_id)
            if ($request->filled('course_id')) {
                $query->where('a.subject_id', $request->course_id);
            }

            // Filter berdasarkan Tipe Tugas
            if ($request->filled('type')) {
                $query->where('a.assignment_type', $request->type);
            }

            // Filter berdasarkan Kelas (class_id) - Query ini sedikit kompleks karena relasi many-to-many
            if ($request->filled('class_id')) {
                $query->whereExists(function ($subQuery) use ($request) {
                    $subQuery->select(DB::raw(1))
                        ->from('assignment_class as ac_filter')
                        ->whereRaw('ac_filter.assignment_id = a.id')
                        ->where('ac_filter.class_id', $request->class_id);
                });
            }

            // --- DAPATKAN HASIL ---
            $assignments = $query->orderBy('a.created_at', 'desc')->get();

            // --- FILTER BERDASARKAN STATUS (setelah query utama) ---
            // Status (completed, grading, active) dihitung di PHP agar logikanya lebih mudah dibaca
            if ($request->filled('status') && $request->status !== 'all') {
                $status = $request->status;
                $now = now();

                $assignments = $assignments->filter(function ($assignment) use ($status, $now) {
                    $isCompleted = $assignment->submittedCount > 0 && $assignment->submittedCount == $assignment->gradedCount;
                    $isGrading = $assignment->submittedCount > $assignment->gradedCount;

                    if ($status === 'completed') {
                        return $isCompleted;
                    }
                    if ($status === 'grading') {
                        return $isGrading;
                    }
                    if ($status === 'active') {
                        // Dianggap aktif jika tidak selesai, tidak perlu dinilai, atau batas waktu belum lewat
                        return !$isCompleted && !$isGrading;
                    }
                    return false;
                })->values(); // `values()` untuk mereset index array setelah filter
            }


            return response()->json(['success' => true, 'data' => $assignments]);

        } catch (Exception $e) {
            Log::error('Failed to retrieve teacher assignments: ' . $e->getMessage() . ' on line ' . $e->getLine());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data tugas.' . $e->getMessage(),
                'query' => $query->toSql(),
            ], 500);
        }
    }

    /**
     * Menyediakan data unik untuk mengisi dropdown filter di frontend.
     */
    public function getFilterData(): JsonResponse
    {
        try {
            $teacherId = Auth::id();

            // Ambil hanya mapel dan kelas yang diajar oleh guru ini
            $subjects = DB::table('subjects as s')
                ->select('s.id', 's.name')
                ->whereIn('s.id', function ($query) use ($teacherId) {
                    $query->select('subject_id')->from('assignments')->where('teacher_id', $teacherId);
                })->distinct()->orderBy('name')->get();

            $classes = DB::table('classes as c')
                ->select('c.id', 'c.name')
                ->whereIn('c.id', function ($query) use ($teacherId) {
                    $query->select('ac.class_id')->from('assignment_class as ac')
                        ->join('assignments as a', 'ac.assignment_id', '=', 'a.id')
                        ->where('a.teacher_id', $teacherId);
                })->distinct()->orderBy('name')->get();

            // --- TAMBAHKAN INI ---
            // Ambil semua tipe tugas unik yang pernah dibuat oleh guru ini
            $types = DB::table('assignments')
                ->where('teacher_id', $teacherId)
                ->select('assignment_type')
                ->distinct()
                ->pluck('assignment_type'); // pluck() untuk mendapatkan array sederhana ['task', 'exam', ...]
            // --- AKHIR TAMBAHAN ---


            // --- UBAH RESPON JSON ---
            return response()->json([
                'success' => true,
                'data' => [
                    'subjects' => $subjects,
                    'classes' => $classes,
                    'types' => $types // Sertakan data tipe di sini
                ]
            ]);

        } catch (Exception $e) {
            Log::error('Failed to get filter data: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal memuat data filter.'], 500);
        }
    }
}
