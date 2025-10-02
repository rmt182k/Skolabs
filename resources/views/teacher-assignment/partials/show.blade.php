{{-- resources/views/teacher/submissions/index.blade.php --}}
@extends('layouts.auth')

@section('title', 'Daftar Submission Tugas')

@push('styles')
    {{-- PERUBAHAN: Tambahkan CSS DataTables --}}
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
@endpush

@section('content')
    <div class="container-fluid">
        {{-- Breadcrumb --}}
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/teacher/dashboard') }}">Dashboard Guru</a></li>
                <li class="breadcrumb-item"><a href="{{ url('/teacher/assignment') }}">Manajemen Tugas</a></li>
                <li class="breadcrumb-item active" aria-current="page">Daftar Submission</li>
            </ol>
        </nav>

        {{-- Header Informasi Tugas --}}
        <div class="card mb-4">
            {{-- ... Konten Header Informasi & Statistik tidak berubah ... --}}
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between align-items-center">
                    <div>
                        <h2 class="fw-bold mb-1" id="assignment-title">
                            <span class="placeholder col-8"></span>
                        </h2>
                        <p class="text-muted mb-2" id="assignment-due-date">
                            <span class="placeholder col-6"></span>
                        </p>
                    </div>
                    <div class="text-end">
                        <a id="edit-assignment-link" href="#" class="btn btn-outline-secondary disabled">
                            <i class="bi bi-pencil-square"></i> Edit Tugas
                        </a>
                    </div>
                </div>
                <hr>
                <div class="row text-center gy-3" id="summary-stats">
                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h4 class="fw-bold mb-0" id="submitted-count">-/-</h4>
                                <small class="text-muted">Telah Mengumpulkan</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h4 class="fw-bold mb-0" id="graded-count">-/-</h4>
                                <small class="text-muted">Sudah Dinilai</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h4 class="fw-bold mb-0" id="missing-count">-/-</h4>
                                <small class="text-muted">Belum Mengumpulkan</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Daftar Submission --}}
        <div class="card">
            {{-- PERUBAHAN: card-header tidak lagi diperlukan untuk filter manual --}}
            <div class="card-body">
                <div class="table-responsive">
                    {{-- PERUBAHAN: Beri ID pada tabel dan pastikan thead/tbody ada --}}
                    <table id="submissions-table" class="table table-hover table-bordered" style="width:100%">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">Nama Siswa</th>
                                <th scope="col">Kelas</th>
                                <th scope="col">Waktu Pengumpulan</th>
                                <th scope="col">Status</th>
                                <th scope="col" class="text-center">Nilai</th>
                                <th scope="col" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- Biarkan kosong, DataTables yang akan mengisi --}}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    {{-- PERUBAHAN: Tambahkan JQuery dan JS DataTables sebelum script kustom Anda --}}
    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

    {{-- Script kustom Anda akan ditempatkan di file yang sama --}}
    <script src="{{ asset('assets/js/app/teacher-assignment/teacherSubmission.js') }}"></script>
@endpush
