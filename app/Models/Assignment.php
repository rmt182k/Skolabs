<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * Relasi ke model Subject (Satu tugas dimiliki oleh satu mata pelajaran).
     */
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Relasi ke model Teacher (Satu tugas dibuat oleh satu guru).
     */
    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    /**
     * Relasi ke model Question (Satu tugas memiliki banyak pertanyaan).
     */
    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    /**
     * Relasi ke model AssignmentSubmission (Satu tugas memiliki banyak pengumpulan dari siswa).
     */
    public function submissions()
    {
        return $this->hasMany(AssignmentSubmission::class);
    }

    /**
     * Relasi Many-to-Many ke model Class (Satu tugas bisa diberikan ke banyak kelas).
     * Saya asumsikan nama modelnya adalah 'SchoolClass' untuk menghindari konflik dengan keyword 'class'.
     */
    public function classes()
    {
        return $this->belongsToMany(SchoolClass::class, 'assignment_class', 'assignment_id', 'class_id');
    }
}
