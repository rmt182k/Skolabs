<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Exception;

class AssignmentSubmissionController extends Controller
{
    /**
     * Menampilkan halaman submission untuk SATU assignment spesifik.
     */
    public function viewSubmissionsPage($assignmentId)
    {
        $assignment = DB::table('assignments')->where('id', $assignmentId)->first();

        if (!$assignment) {
            abort(404, 'Assignment not found');
        }

        return view('assignment.submissions', compact('assignment'));
    }

    /**
     * Mengambil data submission untuk SATU assignment spesifik (API).
     */
    public function index($assignmentId): JsonResponse
    {
        try {
            $submissions = DB::table('assignment_submissions as as')
                ->select(
                    'as.id',
                    'as.submitted_at',
                    'as.status',
                    'as.grade',
                    'u.name as student_name'
                )
                ->leftJoin('users as u', 'as.student_id', '=', 'u.id')
                ->where('as.assignment_id', $assignmentId)
                ->orderBy('as.submitted_at', 'desc')
                ->get();

            $formattedData = $submissions->map(function ($submission) {
                return [
                    'id' => $submission->id,
                    'student' => ['name' => $submission->student_name ?? 'Unknown Student'],
                    'submitted_at' => date('Y-m-d H:i', strtotime($submission->submitted_at)),
                    'status' => ucfirst($submission->status),
                    'grade' => $submission->grade ?? 'Not Graded',
                ];
            });

            return response()->json(['success' => true, 'data' => $formattedData]);
        } catch (Exception $e) {
            Log::error("Failed to retrieve submissions for assignment {$assignmentId}: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to retrieve submission data.'], 500);
        }
    }

    /**
     * Menampilkan halaman untuk melihat SEMUA submission dari semua assignment.
     */
    public function viewAllSubmissionsPage()
    {
        return view('assignment-submission.index');
    }

    /**
     * Mengambil semua data submission untuk ditampilkan di DataTable (API).
     */
    public function getAllSubmissions(): JsonResponse
    {
        try {
            $submissions = DB::table('assignment_submissions as as')
                ->select(
                    'as.id',
                    'as.submitted_at',
                    'as.status',
                    'as.grade',
                    'u.name as student_name',
                    'a.title as assignment_title'
                )
                ->leftJoin('users as u', 'as.student_id', '=', 'u.id')
                ->leftJoin('assignments as a', 'as.assignment_id', '=', 'a.id')
                ->orderBy('as.submitted_at', 'desc')
                ->get();

            $formattedData = $submissions->map(function ($submission) {
                return [
                    'id' => $submission->id,
                    'assignment' => ['title' => $submission->assignment_title ?? 'Unknown Assignment'],
                    'student' => ['name' => $submission->student_name ?? 'Unknown Student'],
                    'submitted_at' => date('Y-m-d H:i', strtotime($submission->submitted_at)),
                    'status' => ucfirst($submission->status),
                    'grade' => $submission->grade ?? 'Not Graded',
                ];
            });

            return response()->json(['success' => true, 'data' => $formattedData]);
        } catch (Exception $e) {
            Log::error("Failed to retrieve all submissions: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to retrieve submission data.'], 500);
        }
    }

    /**
     * Menampilkan detail SATU submission spesifik (API).
     */
    public function show($id): JsonResponse
    {
        try {
            $submission = DB::table('assignment_submissions as as')
                ->select('as.*', 'u.name as student_name')
                ->leftJoin('users as u', 'as.student_id', '=', 'u.id')
                ->where('as.id', $id)
                ->first();

            if (!$submission) {
                return response()->json(['success' => false, 'message' => 'Submission not found.'], 404);
            }

            if ($submission->file_path) {
                $submission->file_url = asset('storage/' . str_replace('public/', '', $submission->file_path));
            }

            return response()->json(['success' => true, 'data' => $submission]);
        } catch (Exception $e) {
            Log::error("Failed to show submission {$id}: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to retrieve submission details.'], 500);
        }
    }

    /**
     * Memberi nilai dan feedback pada SATU submission (API).
     */
    public function grade(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'grade' => 'nullable|numeric|min:0|max:100',
            'feedback' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $submission = DB::table('assignment_submissions')->where('id', $id)->first();
            if (!$submission) {
                return response()->json(['success' => false, 'message' => 'Submission not found.'], 404);
            }

            DB::table('assignment_submissions')
                ->where('id', $id)
                ->update([
                    'grade' => $request->input('grade'),
                    'feedback' => $request->input('feedback'),
                    'status' => 'graded',
                    'updated_at' => now(),
                ]);

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Submission has been graded successfully.']);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Failed to grade submission {$id}: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to grade the submission.'], 500);
        }
    }
}
