{{-- File: resources/views/student/assignments/take.blade.php --}}

@extends('layouts.auth') {{-- Sesuaikan dengan layout utama siswa --}}
@section('title', 'Mengerjakan Tugas')

@push('styles')
    {{-- Tambahkan CSS kustom jika perlu --}}
    <style>
        .question-card {
            border: 1px solid #ddd;
            border-left: 5px solid #0d6efd;
            border-radius: 5px;
        }
    </style>
@endpush

@section('content')
<div class="container py-4">
    {{-- Spinner saat memuat data --}}
    <div id="loading-spinner" class="text-center py-5">
        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status"></div>
        <p class="mt-3">Memuat tugas...</p>
    </div>

    {{-- Konten Utama (awalnya disembunyikan) --}}
    <div id="assignment-content" class="d-none">
        {{-- 1. Detail Tugas --}}
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h1 id="assignment-title" class="h3 fw-bold"></h1>
                <div class="text-muted mb-3">
                    <span id="assignment-teacher" class="me-3"></span> |
                    <span id="assignment-subject" class="mx-3"></span> |
                    <span id="assignment-due-date" class="ms-3 text-danger fw-semibold"></span>
                </div>
                <hr>
                <p id="assignment-description"></p>
            </div>
        </div>

        {{-- 2. Form Pengerjaan --}}
        <form id="submissionForm">
            {{-- Kontainer untuk semua soal --}}
            <div id="question-container">
                {{-- Soal-soal akan dirender oleh JavaScript di sini --}}
            </div>

            {{-- 3. Tombol Aksi --}}
            <div class="mt-4 text-end">
                <button type="submit" class="btn btn-success btn-lg">
                    <i class="fas fa-paper-plane me-2"></i> Kumpulkan Jawaban
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ======================================================= --}}
{{-- TEMPLATE UNTUK SETIAP JENIS SOAL                     --}}
{{-- ======================================================= --}}

{{-- Template Soal Jawaban Singkat (Short Answer) --}}
<template id="short-answer-question-template">
    <div class="card shadow-sm mb-3 question-card">
        <div class="card-header bg-light d-flex justify-content-between">
            <h6 class="question-number fw-bold mb-0"></h6>
            <span class="question-score badge bg-primary"></span>
        </div>
        <div class="card-body">
            <div class="question-text mb-3"></div>
            <div class="answer-area">
                <label class="form-label fw-semibold">Jawaban Anda:</label>
                <input type="text" class="form-control student-answer" name="answer" placeholder="Ketik jawaban singkat Anda..." required>
            </div>
        </div>
    </div>
</template>

{{-- Template Soal Esai --}}
<template id="essay-question-template">
    <div class="card shadow-sm mb-3 question-card">
        <div class="card-header bg-light d-flex justify-content-between">
            <h6 class="question-number fw-bold mb-0"></h6>
            <span class="question-score badge bg-primary"></span>
        </div>
        <div class="card-body">
            <div class="question-text mb-3"></div>
            <div class="answer-area">
                <label class="form-label fw-semibold">Jawaban Anda:</label>
                <textarea class="form-control student-answer" name="answer" rows="5" placeholder="Ketik jawaban esai Anda..." required></textarea>
            </div>
        </div>
    </div>
</template>

{{-- Template Soal Pilihan Ganda (Multiple Choice) --}}
<template id="multiple-choice-question-template">
    <div class="card shadow-sm mb-3 question-card">
        <div class="card-header bg-light d-flex justify-content-between">
            <h6 class="question-number fw-bold mb-0"></h6>
            <span class="question-score badge bg-primary"></span>
        </div>
        <div class="card-body">
            <div class="question-text mb-3"></div>
            <div class="answer-area">
                <label class="form-label fw-bold">Pilih jawaban yang benar:</label>
                <div class="options-container">
                    {{-- Opsi jawaban akan dirender oleh JavaScript di sini --}}
                </div>
            </div>
        </div>
    </div>
</template>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> {{-- Untuk notifikasi --}}
<script src="{{ asset('assets/js/app/student-assigment/studentAssignmentForm.js') }}" defer></script>
@endpush
