@extends('layouts.auth')

@section('title', 'Student Assignment')

@section('content')
    <div class="container-fluid">
        {{-- Breadcrumb bisa Anda sesuaikan --}}
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Daftar Tugas</li>
            </ol>
        </nav>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold mb-0">📚 Daftar Tugas Anda</h2>
            {{-- Tombol untuk beralih tampilan --}}
            <div class="btn-group" role="group" aria-label="View toggle">
                <button type="button" class="btn btn-outline-primary active" id="view-card-btn" title="Tampilan Kartu">
                    <i class="bi bi-grid-3x3-gap-fill"></i> Card
                </button>
                <button type="button" class="btn btn-outline-primary" id="view-table-btn" title="Tampilan Tabel">
                    <i class="bi bi-table"></i> Tabel
                </button>
            </div>
        </div>

        {{-- Bagian Filter dan Search --}}
        <div class="card mb-4">
            <div class="card-body">
                <div class="row g-3 align-items-center">
                    <div class="col-md-7">
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="search" class="form-control" id="search-input"
                                placeholder="Cari berdasarkan judul tugas...">
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-book"></i></span>
                            <select class="form-select" id="course-filter">
                                <option value="">Semua Mata Pelajaran</option>
                                {{-- Opsi mata pelajaran akan di-generate oleh JavaScript --}}
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div class="card">
            <div class="card-body">
                <ul class="nav nav-pills" id="pills-tab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="pills-all-tab" data-bs-toggle="pill" data-bs-target="#pills-all"
                            type="button" role="tab" aria-controls="pills-all" aria-selected="true">Semua</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="pills-pending-tab" data-bs-toggle="pill"
                            data-bs-target="#pills-pending" type="button" role="tab" aria-controls="pills-pending"
                            aria-selected="false">Belum
                            Dikerjakan</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="pills-completed-tab" data-bs-toggle="pill"
                            data-bs-target="#pills-completed" type="button" role="tab" aria-controls="pills-completed"
                            aria-selected="false">Selesai</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="pills-overdue-tab" data-bs-toggle="pill"
                            data-bs-target="#pills-overdue" type="button" role="tab" aria-controls="pills-overdue"
                            aria-selected="false">Terlambat</button>
                    </li>
                </ul>
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
                                <th scope="col">Mata Pelajaran</th>
                                <th scope="col">Batas Waktu</th>
                                <th scope="col">Status</th>
                                <th scope="col" class="text-end">Aksi</th>
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
    <link rel="stylesheet" href="{{ asset('assets/css/app/student-assignment/student-assigment.css') }}">
@endpush
@push('scripts')
    <script src="{{ asset('assets/js/app/student-assigment/studentAssignment.js') }}"></script>
@endpush
