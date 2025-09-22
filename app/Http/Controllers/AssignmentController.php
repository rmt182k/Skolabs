<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth; // <-- Tambahkan ini
use Exception;

class AssignmentController extends Controller
{
    /**
     * Display a listing of the assignments.
     * Menggunakan GROUP_CONCAT untuk menggabungkan nama kelas yang berelasi
     */
    public function index(): JsonResponse
    {
        try {
            // Menggunakan DB::raw untuk GROUP_CONCAT agar lebih efisien
            $assignments = DB::table('assignments as a')
                ->select(
                    'a.id',
                    'a.title',
                    'a.assignment_type',
                    'a.start_date',
                    'a.due_date',
                    's.name as subject_name',
                    DB::raw("GROUP_CONCAT(c.name SEPARATOR ', ') as class_names")
                )
                ->leftJoin('subjects as s', 'a.subject_id', '=', 's.id')
                ->leftJoin('assignment_class as ac', 'a.id', '=', 'ac.assignment_id')
                ->leftJoin('classes as c', 'ac.class_id', '=', 'c.id')
                ->groupBy('a.id', 'a.title', 'a.assignment_type', 'a.start_date', 'a.due_date', 's.name')
                ->orderBy('a.created_at', 'desc')
                ->get();

            return response()->json(['success' => true, 'data' => $assignments]);
        } catch (Exception $e) {
            Log::error('Failed to retrieve assignments: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to retrieve data.'], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     * Didesain untuk menerima payload dari form builder dinamis Anda.
     */
    public function store(Request $request): JsonResponse
    {
        // Validasi untuk payload yang kompleks
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'assignment_type' => 'required|in:task,quiz,exam',
            'subject_id' => 'required|exists:subjects,id',
            'class_id' => 'required|array|min:1',
            'class_id.*' => 'required|exists:classes,id',
            'start_date' => 'nullable|date',
            'due_date' => 'nullable|date|after_or_equal:start_date',
            'description' => 'nullable|string',
            'questions' => 'required|array|min:1',
            'questions.*.question_text' => 'required|string',
            'questions.*.type' => 'required|in:text,multiple_choice,essay',
            'questions.*.score' => 'required|integer|min:1',
            'questions.*.correct_answer' => 'nullable|string',
            'questions.*.options' => 'sometimes|array',
            'questions.*.options.*.option_text' => 'required|string',
            'questions.*.options.*.is_correct' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        // Membungkus semua operasi database dalam satu transaksi
        DB::beginTransaction();
        try {
            // 1. Simpan data assignment utama
            $assignmentId = DB::table('assignments')->insertGetId([
                'title' => $request->title,
                'assignment_type' => $request->assignment_type,
                'subject_id' => $request->subject_id,
                'description' => $request->description,
                'start_date' => $request->start_date,
                'due_date' => $request->due_date,
                'teacher_id' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 2. Simpan relasi ke kelas di pivot table 'assignment_class'
            $classes = [];
            foreach ($request->class_id as $classId) {
                $classes[] = [
                    'assignment_id' => $assignmentId,
                    'class_id' => $classId
                ];
            }
            DB::table('assignment_class')->insert($classes);

            // 3. Loop dan simpan setiap pertanyaan dan opsinya
            foreach ($request->questions as $questionData) {
                $questionId = DB::table('questions')->insertGetId([
                    'assignment_id' => $assignmentId,
                    'question_text' => $questionData['question_text'],
                    'type' => $questionData['type'],
                    'order' => $questionData['order'],
                    'score' => $questionData['score'],
                    'correct_answer' => ($questionData['type'] !== 'multiple_choice') ? $questionData['correct_answer'] : null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Jika tipe soal adalah pilihan ganda, simpan opsinya
                if ($questionData['type'] === 'multiple_choice' && !empty($questionData['options'])) {
                    $options = [];
                    foreach ($questionData['options'] as $optionData) {
                        $options[] = [
                            'question_id' => $questionId,
                            'option_letter' => $optionData['option_letter'],
                            'option_text' => $optionData['option_text'],
                            'is_correct' => $optionData['is_correct'],
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                    DB::table('question_options')->insert($options);
                }
            }

            // Jika semua berhasil, commit transaksi
            DB::commit();

            return response()->json(['success' => true, 'message' => 'Assignment created successfully.'], 201);
        } catch (Exception $e) {
            // Jika ada error, rollback semua query
            DB::rollBack();
            Log::error('Failed to create assignment: ' . $e->getMessage() . ' on line ' . $e->getLine());
            return response()->json(['success' => false, 'message' => 'Failed to create assignment.' . $e->getMessage()], 500);
        }
    }


    /**
     * Display the specified resource for editing.
     * Mengambil semua data terkait assignment untuk ditampilkan di form edit.
     */
    public function show($id): JsonResponse
    {
        try {
            $assignment = DB::table('assignments')->where('id', $id)->first();

            if (!$assignment) {
                return response()->json(['success' => false, 'message' => 'Assignment not found.'], 404);
            }

            // Ambil kelas terkait
            $assignment->class_id = DB::table('assignment_class')->where('assignment_id', $id)->pluck('class_id')->toArray();

            // Ambil pertanyaan terkait
            $questions = DB::table('questions')->where('assignment_id', $id)->orderBy('order')->get()->toArray();

            // Ambil opsi untuk setiap pertanyaan pilihan ganda
            foreach ($questions as $key => $question) {
                if ($question->type === 'multiple_choice') {
                    $options = DB::table('question_options')->where('question_id', $question->id)->get()->toArray();
                    // Konversi is_correct ke boolean jika perlu
                    foreach ($options as &$option) {
                        $option->is_correct = (bool) $option->is_correct;
                    }
                    $questions[$key]->options = $options;
                }
            }
            $assignment->questions = $questions;

            return response()->json(['success' => true, 'data' => $assignment]);
        } catch (Exception $e) {
            Log::error("Failed to fetch assignment {$id}: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to fetch assignment details.'], 500);
        }
    }


    /**
     * Update the specified resource in storage.
     * Logika: Hapus data lama (kelas, soal, opsi) lalu insert data baru dari request.
     * Ini lebih sederhana dan aman daripada mencoba mencocokkan ID satu per satu.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $assignment = DB::table('assignments')->where('id', $id)->first();
        if (!$assignment) {
            return response()->json(['success' => false, 'message' => 'Assignment not found.'], 404);
        }

        // Validasi sama dengan store, namun beberapa field bisa 'sometimes' jika tidak wajib diubah
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'assignment_type' => 'required|in:task,quiz,exam',
            'subject_id' => 'required|exists:subjects,id',
            'class_id' => 'required|array|min:1',
            'class_id.*' => 'required|exists:classes,id',
            'start_date' => 'nullable|date',
            'due_date' => 'nullable|date|after_or_equal:start_date',
            'description' => 'nullable|string',
            'questions' => 'required|array|min:1',
            'questions.*.question_text' => 'required|string',
            'questions.*.type' => 'required|in:text,multiple_choice,essay',
            'questions.*.score' => 'required|integer|min:1',
            'questions.*.correct_answer' => 'nullable|string',
            'questions.*.options' => 'sometimes|array',
            'questions.*.options.*.option_text' => 'required|string',
            'questions.*.options.*.is_correct' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            // 1. Update data assignment utama
            DB::table('assignments')->where('id', $id)->update([
                'title' => $request->title,
                'assignment_type' => $request->assignment_type,
                'subject_id' => $request->subject_id,
                'description' => $request->description,
                'start_date' => $request->start_date,
                'due_date' => $request->due_date,
                'updated_at' => now(),
            ]);

            // 2. Hapus relasi kelas lama & insert yang baru
            DB::table('assignment_class')->where('assignment_id', $id)->delete();
            $classes = [];
            foreach ($request->class_id as $classId) {
                $classes[] = ['assignment_id' => $id, 'class_id' => $classId];
            }
            DB::table('assignment_class')->insert($classes);

            // 3. Hapus semua soal & opsi lama
            $oldQuestionIds = DB::table('questions')->where('assignment_id', $id)->pluck('id');
            if ($oldQuestionIds->isNotEmpty()) {
                DB::table('question_options')->whereIn('question_id', $oldQuestionIds)->delete();
                DB::table('questions')->where('assignment_id', $id)->delete();
            }

            // 4. Insert soal & opsi baru (logika sama seperti store)
            foreach ($request->questions as $questionData) {
                $questionId = DB::table('questions')->insertGetId([
                    'assignment_id' => $id,
                    'question_text' => $questionData['question_text'],
                    'type' => $questionData['type'],
                    'order' => $questionData['order'],
                    'score' => $questionData['score'],
                    'correct_answer' => ($questionData['type'] !== 'multiple_choice') ? $questionData['correct_answer'] : null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                if ($questionData['type'] === 'multiple_choice' && !empty($questionData['options'])) {
                    $options = [];
                    foreach ($questionData['options'] as $optionData) {
                        $options[] = [
                            'question_id' => $questionId,
                            'option_letter' => $optionData['option_letter'],
                            'option_text' => $optionData['option_text'],
                            'is_correct' => $optionData['is_correct'],
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                    DB::table('question_options')->insert($options);
                }
            }

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Assignment updated successfully.']);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Failed to update assignment {$id}: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to update assignment.'], 500);
        }
    }


    /**
     * Remove the specified resource from storage.
     * Menghapus assignment beserta semua relasinya (kelas, soal, opsi, dan jawaban siswa).
     */
    public function destroy($id): JsonResponse
    {
        DB::beginTransaction();
        try {
            $assignment = DB::table('assignments')->where('id', $id)->first();
            if (!$assignment) {
                return response()->json(['success' => false, 'message' => 'Assignment not found.'], 404);
            }

            // Ambil ID semua soal & submission terkait
            $questionIds = DB::table('questions')->where('assignment_id', $id)->pluck('id');
            $submissionIds = DB::table('assignment_submissions')->where('assignment_id', $id)->pluck('id');

            // Hapus dari tabel anak terlebih dahulu untuk menghindari error foreign key
            if ($submissionIds->isNotEmpty()) {
                DB::table('submission_answers')->whereIn('assignment_submission_id', $submissionIds)->delete();
                DB::table('assignment_submissions')->where('assignment_id', $id)->delete();
            }
            if ($questionIds->isNotEmpty()) {
                DB::table('question_options')->whereIn('question_id', $questionIds)->delete();
                DB::table('questions')->where('assignment_id', $id)->delete();
            }
            DB::table('assignment_class')->where('assignment_id', $id)->delete();
            DB::table('assignments')->where('id', $id)->delete();

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
     * Metode ini tidak perlu diubah, sudah benar.
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

    public function edit($id)
    {
        // 1. Ambil data assignment utama
        $assignment = DB::table('assignments')->where('id', $id)->first();

        // Jika assignment tidak ditemukan, tampilkan halaman 404
        if (!$assignment) {
            abort(404, 'Assignment not found');
        }

        // 2. Ambil semua pertanyaan yang terhubung dengan assignment ini
        $questions = DB::table('questions')
            ->where('assignment_id', $id)
            ->orderBy('order')
            ->get();

        // 3. Ambil semua opsi jawaban (jika ada) untuk semua pertanyaan di atas dalam satu query
        $questionIds = $questions->pluck('id')->toArray();
        $options = collect(); // Siapkan koleksi kosong sebagai default

        if (!empty($questionIds)) {
            $options = DB::table('question_options')
                ->whereIn('question_id', $questionIds)
                ->get()
                ->groupBy('question_id'); // Kelompokkan opsi berdasarkan ID pertanyaan
        }

        // 4. Gabungkan opsi ke setiap pertanyaan yang sesuai
        foreach ($questions as $question) {
            $question->options = $options->get($question->id, collect())->toArray();
        }

        // 5. Tambahkan data pertanyaan ke objek assignment
        $assignment->questions = $questions;

        // 6. Ambil ID kelas yang terhubung (untuk Select2)
        $assignment->class_ids = DB::table('assignment_class')
            ->where('assignment_id', $id)
            ->pluck('class_id')
            ->toArray();


        // 7. Kirim objek $assignment yang sudah lengkap ke view
        return view('assignment.components.form', compact('assignment'));
    }
}
