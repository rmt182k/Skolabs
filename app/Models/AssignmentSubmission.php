<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssignmentSubmission extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * Relasi ke model Assignment (Satu pengumpulan dimiliki oleh satu tugas).
     */
    public function assignment()
    {
        return $this->belongsTo(Assignment::class);
    }

    /**
     * Relasi ke model Student (Satu pengumpulan dimiliki oleh satu siswa).
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Relasi ke model SubmissionAnswer (Satu pengumpulan memiliki banyak jawaban).
     */
    public function answers()
    {
        return $this->hasMany(SubmissionAnswer::class);
    }
}
