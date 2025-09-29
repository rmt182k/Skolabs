<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * Relasi ke model Assignment (Satu pertanyaan dimiliki oleh satu tugas).
     */
    public function assignment()
    {
        return $this->belongsTo(Assignment::class);
    }

    /**
     * Relasi ke model SubmissionAnswer (Satu pertanyaan bisa memiliki banyak jawaban dari submission yang berbeda).
     */
    public function answers()
    {
        return $this->hasMany(SubmissionAnswer::class);
    }
}
