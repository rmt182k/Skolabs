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
            <a href="/teacher/assignment/create" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> Buat Tugas Baru
            </a>
        </div>

        {{-- Bagian Filter dan Search yang lebih lengkap untuk Guru --}}
        <div class="card mb-4">
            <div class="card-body">
                <div class="row g-3 align-items-center">
                    <div class="col-md-5">
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="search" class="form-control" id="search-input"
                                placeholder="Cari berdasarkan judul tugas...">
                        </div>
                    </div>
                    {{-- Filter tambahan untuk Kelas --}}
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-people"></i></span>
                            <select class="form-select" id="class-filter">
                                <option value="">Semua Kelas</option>
                                {{-- Opsi kelas akan di-generate oleh JavaScript --}}
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
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
                    <i class="bi bi-grid-3x3-gap-fill"></i>
                </button>
                <button type="button" class="btn btn-outline-primary" id="view-table-btn" title="Tampilan Tabel">
                    <i class="bi bi-table"></i>
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
    <style>
        .assignment-card {
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
            border: 1px solid #e0e0e0;
            border-left-width: 5px;
        }

        .assignment-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        /* Warna border sesuai status untuk Guru */
        .assignment-card.status-active {
            border-left-color: #0d6efd; /* Biru untuk tugas aktif */
        }
        .assignment-card.status-grading {
            border-left-color: #ffc107; /* Kuning untuk perlu dinilai */
        }
        .assignment-card.status-completed {
            border-left-color: #198754; /* Hijau untuk selesai */
        }

        .status-badge {
            font-size: 0.8rem;
            padding: 0.4em 0.8em;
            border-radius: 50px;
            color: #fff;
            font-weight: 500;
        }

        .status-badge.bg-active {
            background-color: #0d6efd;
        }
        .status-badge.bg-grading {
            background-color: #ffc107;
            color: #000;
        }
        .status-badge.bg-completed {
            background-color: #198754;
        }
        .progress-container {
            margin-top: 0.5rem;
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // --- DATA DUMMY (Disesuaikan untuk Guru) ---
            const dummyAssignments = [{
                id: 1,
                title: 'Analisis Puisi "Aku" karya Chairil Anwar',
                course: 'Bahasa Indonesia',
                class: 'XII IPA 1',
                dueDate: '2025-10-05',
                totalStudents: 32,
                submittedCount: 30,
                gradedCount: 15,
            }, {
                id: 2,
                title: 'Laporan Praktikum Reaksi Kimia',
                course: 'Kimia',
                class: 'XI MIPA 3',
                dueDate: '2025-10-02',
                totalStudents: 35,
                submittedCount: 35,
                gradedCount: 35,
            }, {
                id: 3,
                title: 'Mengerjakan Soal Vektor',
                course: 'Matematika Peminatan',
                class: 'XII IPA 1',
                dueDate: '2025-10-15',
                totalStudents: 32,
                submittedCount: 10,
                gradedCount: 5,
            }, {
                id: 4,
                title: 'Presentasi Sejarah Kerajaan Majapahit',
                course: 'Sejarah Indonesia',
                class: 'X IPS 2',
                dueDate: '2025-10-10',
                totalStudents: 30,
                submittedCount: 28,
                gradedCount: 28,
            }, {
                id: 5,
                title: 'Membuat Program CRUD Sederhana',
                course: 'Dasar Pemrograman',
                class: 'X RPL',
                dueDate: '2025-10-20',
                totalStudents: 25,
                submittedCount: 0,
                gradedCount: 0,
            }];

            // --- ELEMEN DOM ---
            const cardContainer = document.getElementById('assignment-card-view');
            const tableContainer = document.getElementById('assignment-table-view');
            const tableBody = document.getElementById('assignment-table-body');
            const viewCardBtn = document.getElementById('view-card-btn');
            const viewTableBtn = document.getElementById('view-table-btn');
            const searchInput = document.getElementById('search-input');
            const courseFilter = document.getElementById('course-filter');
            const classFilter = document.getElementById('class-filter'); // Filter kelas baru

            let currentView = 'card';

            // --- FUNGSI POPULASI FILTER ---
            function populateFilters() {
                const courses = [...new Set(dummyAssignments.map(a => a.course))];
                const classes = [...new Set(dummyAssignments.map(a => a.class))];

                courses.sort().forEach(course => {
                    courseFilter.innerHTML += `<option value="${course}">${course}</option>`;
                });
                classes.sort().forEach(cls => {
                    classFilter.innerHTML += `<option value="${cls}">${cls}</option>`;
                });
            }

            // --- FUNGSI UNTUK MENDAPATKAN STATUS & INFO TUGAS DARI SISI GURU ---
            function getAssignmentInfo(assignment) {
                if (assignment.gradedCount === assignment.submittedCount && assignment.submittedCount > 0) {
                    return { status: 'completed', text: 'Selesai Dinilai', class: 'bg-completed' };
                }
                if (assignment.submittedCount > assignment.gradedCount) {
                    return { status: 'grading', text: 'Perlu Dinilai', class: 'bg-grading' };
                }
                return { status: 'active', text: 'Aktif', class: 'bg-active' };
            }

            // --- FUNGSI RENDER UTAMA ---
            function renderAssignmentsAsCards(data) {
                cardContainer.innerHTML = '';
                if (data.length === 0) {
                    cardContainer.innerHTML = `<div class="col-12"><div class="alert alert-info text-center">Tidak ada tugas yang sesuai dengan filter.</div></div>`;
                    return;
                }
                data.forEach(assignment => {
                    const info = getAssignmentInfo(assignment);
                    const progress = assignment.totalStudents > 0 ? (assignment.submittedCount / assignment.totalStudents) * 100 : 0;
                    const dueDate = new Date(assignment.dueDate);
                    const cardHTML = `
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card assignment-card status-${info.status}">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h5 class="card-title fw-bold mb-0">${assignment.title}</h5>
                                        <span class="status-badge ${info.class}">${info.text}</span>
                                    </div>
                                    <h6 class="card-subtitle text-muted">${assignment.class} &middot; ${assignment.course}</h6>
                                    <hr>
                                    <div class="progress-container">
                                        <div class="d-flex justify-content-between small">
                                            <span>Terkumpul: <strong>${assignment.submittedCount}/${assignment.totalStudents}</strong></span>
                                            <span>Ternilai: <strong>${assignment.gradedCount}/${assignment.submittedCount}</strong></span>
                                        </div>
                                        <div class="progress mt-1" style="height: 10px;">
                                            <div class="progress-bar" role="progressbar" style="width: ${progress}%;" aria-valuenow="${progress}" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mt-3">
                                        <p class="card-text mb-0 small text-muted">
                                            <i class="bi bi-calendar-check"></i> Batas: ${dueDate.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })}
                                        </p>
                                        <a href="/teacher/assignment/${assignment.id}/submissions" class="btn btn-primary btn-sm">
                                            Lihat Submission
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>`;
                    cardContainer.innerHTML += cardHTML;
                });
            }

            function renderAssignmentsAsTable(data) {
                tableBody.innerHTML = '';
                if (data.length === 0) {
                    tableBody.innerHTML = `<tr><td colspan="5" class="text-center"><div class="alert alert-info mb-0">Tidak ada tugas yang sesuai dengan filter.</div></td></tr>`;
                    return;
                }
                data.forEach(assignment => {
                    const info = getAssignmentInfo(assignment);
                    const progress = assignment.totalStudents > 0 ? (assignment.submittedCount / assignment.totalStudents) * 100 : 0;
                    const dueDate = new Date(assignment.dueDate);
                    const rowHTML = `
                        <tr>
                            <td>
                                <div class="fw-bold">${assignment.title}</div>
                                <div class="small text-muted">${info.text}</div>
                            </td>
                            <td>
                                <div class="fw-medium">${assignment.class}</div>
                                <div class="small text-muted">${assignment.course}</div>
                            </td>
                            <td>${dueDate.toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' })}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <span class="me-2">${assignment.submittedCount}/${assignment.totalStudents}</span>
                                    <div class="progress flex-grow-1" style="height: 8px;">
                                        <div class="progress-bar" role="progressbar" style="width: ${progress}%;" aria-valuenow="${progress}"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">
                                <div class="btn-group">
                                    <a href="/teacher/assignment/${assignment.id}/submissions" class="btn btn-sm btn-outline-primary" title="Lihat Submission">
                                        <i class="bi bi-eye-fill"></i>
                                    </a>
                                    <a href="/teacher/assignment/${assignment.id}/edit" class="btn btn-sm btn-outline-secondary" title="Edit Tugas">
                                        <i class="bi bi-pencil-fill"></i>
                                    </a>
                                    <button class="btn btn-sm btn-outline-danger" title="Hapus Tugas">
                                        <i class="bi bi-trash-fill"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>`;
                    tableBody.innerHTML += rowHTML;
                });
            }

            // --- FUNGSI FILTER DATA ---
            function getFilteredData() {
                const statusFilter = document.querySelector('#pills-tab button.active').id.replace('-tab', '').replace('pills-', '');
                const searchTerm = searchInput.value.toLowerCase();
                const selectedCourse = courseFilter.value;
                const selectedClass = classFilter.value;

                return dummyAssignments.filter(assignment => {
                    const info = getAssignmentInfo(assignment);
                    const statusMatch = (statusFilter === 'all') || (info.status === statusFilter);
                    const searchMatch = assignment.title.toLowerCase().includes(searchTerm);
                    const courseMatch = !selectedCourse || assignment.course === selectedCourse;
                    const classMatch = !selectedClass || assignment.class === selectedClass;
                    return statusMatch && searchMatch && courseMatch && classMatch;
                });
            }

            // --- FUNGSI RENDER KONTEN ---
            function renderContent() {
                const data = getFilteredData();
                if (currentView === 'card') {
                    renderAssignmentsAsCards(data);
                } else {
                    renderAssignmentsAsTable(data);
                }
            }

            // --- EVENT LISTENERS ---
            viewCardBtn.addEventListener('click', () => {
                if (currentView !== 'card') {
                    currentView = 'card';
                    tableContainer.classList.add('d-none');
                    cardContainer.classList.remove('d-none');
                    viewCardBtn.classList.add('active');
                    viewTableBtn.classList.remove('active');
                    renderContent();
                }
            });

            viewTableBtn.addEventListener('click', () => {
                if (currentView !== 'table') {
                    currentView = 'table';
                    cardContainer.classList.add('d-none');
                    tableContainer.classList.remove('d-none');
                    viewTableBtn.classList.add('active');
                    viewCardBtn.classList.remove('active');
                    renderContent();
                }
            });

            [searchInput, courseFilter, classFilter].forEach(el => el.addEventListener('input', renderContent));

            document.querySelectorAll('#pills-tab button').forEach(tab => {
                tab.addEventListener('click', renderContent);
            });

            // --- INISIALISASI ---
            populateFilters();
            renderContent();
        });
    </script>
@endpush
