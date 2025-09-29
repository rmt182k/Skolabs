<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubmissionAnswer extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * Relasi ke model AssignmentSubmission (Satu jawaban adalah bagian dari satu pengumpulan tugas).
     */
    public function assignmentSubmission()
    {
        return $this->belongsTo(AssignmentSubmission::class);
    }

    /**
     * Relasi ke model Question (Satu jawaban merujuk ke satu pertanyaan).
     */
    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    /**
     * Relasi ke model Student (Satu jawaban dimiliki oleh satu siswa).
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
