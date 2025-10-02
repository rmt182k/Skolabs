@extends('layouts.auth')

@section('title', 'Manajemen Tugas Guru')

@section('content')
    <div class="container-fluid">
        {{-- Breadcrumb disesuaikan untuk Guru --}}
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/teacher/dashboard">Dashboard Guru</a></li>
                <li class="breadcrumb-item active" aria-current="page">Manajemen Tugas</li>
            </ol>
        </nav>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold mb-0">📋 Manajemen Tugas & Penilaian</h2>
            {{-- Tombol Aksi Utama untuk Guru: Membuat Tugas Baru --}}
            <a href="/teacher-assignment/create" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> Buat Tugas Baru
            </a>
        </div>

        {{-- Bagian Filter dan Search yang lebih lengkap untuk Guru --}}
        <div class="card mb-4">
            <div class="card-body">
                {{-- UBAH INI: Layout kolom diubah untuk mengakomodasi filter baru --}}
                <div class="row g-3 align-items-center">
                    <div class="col-md-4"> {{-- Diubah dari col-md-5 --}}
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="search" class="form-control" id="search-input"
                                placeholder="Cari berdasarkan judul tugas...">
                        </div>
                    </div>
                    <div class="col-md-3"> {{-- Diubah dari col-md-4 --}}
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-people"></i></span>
                            <select class="form-select" id="class-filter">
                                <option value="">Semua Kelas</option>
                                {{-- Opsi kelas akan di-generate oleh JavaScript --}}
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3"> {{-- Tetap col-md-3 --}}
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-book"></i></span>
                            <select class="form-select" id="course-filter">
                                <option value="">Semua Mata Pelajaran</option>
                                {{-- Opsi mata pelajaran akan di-generate oleh JavaScript --}}
                            </select>
                        </div>
                    </div>
                    {{-- TAMBAHKAN INI: Filter baru untuk Tipe Tugas --}}
                    <div class="col-md-2">
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-tags"></i></span>
                            <select class="form-select" id="type-filter">
                                <option value="">Semua Tipe</option>
                                {{-- Opsi tipe akan di-generate oleh JavaScript --}}
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Kontrol Tampilan dan Status Tugas (Tabs) --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
            {{-- Tabs disesuaikan untuk alur kerja Guru --}}
            <ul class="nav nav-pills" id="pills-tab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="pills-all-tab" data-bs-toggle="pill" data-bs-target="#pills-all"
                        type="button" role="tab" aria-selected="true">Semua</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="pills-active-tab" data-bs-toggle="pill" data-bs-target="#pills-active"
                        type="button" role="tab" aria-selected="false">Aktif</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="pills-grading-tab" data-bs-toggle="pill" data-bs-target="#pills-grading"
                        type="button" role="tab" aria-selected="false">Perlu Dinilai</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="pills-completed-tab" data-bs-toggle="pill"
                        data-bs-target="#pills-completed" type="button" role="tab"
                        aria-selected="false">Selesai</button>
                </li>
            </ul>

            {{-- Tombol untuk beralih tampilan --}}
            <div class="btn-group" role="group" aria-label="View toggle">
                <button type="button" class="btn btn-outline-primary active" id="view-card-btn" title="Tampilan Kartu">
                    Card<i class="bi bi-grid-3x3-gap-fill"></i>
                </button>
                <button type="button" class="btn btn-outline-primary" id="view-table-btn" title="Tampilan Tabel">
                    Table<i class="bi bi-table"></i>
                </button>
            </div>
        </div>


        {{-- Container untuk Tampilan Kartu --}}
        <div class="row" id="assignment-card-view">
            {{-- Data kartu akan di-generate oleh JavaScript --}}
        </div>

        {{-- Container untuk Tampilan Tabel (awalnya disembunyikan) --}}
        <div class="card d-none" id="assignment-table-view">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th scope="col">Judul Tugas</th>
                                {{-- TAMBAHKAN INI: Kolom header baru untuk Tipe --}}
                                <th scope="col">Tipe</th>
                                <th scope="col">Kelas & Mapel</th>
                                <th scope="col">Batas Waktu</th>
                                <th scope="col">Progres Pengumpulan</th>
                                <th scope="col" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="assignment-table-body">
                            {{-- Data tabel akan di-generate oleh JavaScript --}}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/app/teacher-assignment/teacher-assignment.css') }}">
@endpush

@push('scripts')
    <script src="{{ asset('assets/js/app/teacher-assignment/teacherAssignment.js') }}"></script>
@endpush
