@extends('layouts.auth')

@section('title', 'Penilaian Tugas')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .card-header .badge {
            font-size: .8rem
        }

        .question-card {
            border-left: 4px solid #0d6efd
        }

        .question-body-content {
            padding: 1.25rem
        }

        .question-text {
            font-size: 1.1rem;
            margin-bottom: 1.5rem
        }

        .answer-box {
            padding: 1rem;
            border-radius: .375rem;
            margin-bottom: 1rem;
            border: 1px solid #dee2e6
        }

        .answer-box-header {
            font-weight: 600;
            margin-bottom: .5rem;
            color: #6c757d
        }

        .student-answer-box {
            background-color: #e9ecef
        }

        .correct-answer-box {
            background-color: #d1e7dd;
            border-color: #badbcc
        }

        .mc-option {
            display: flex;
            align-items: center;
            padding: .75rem 1rem;
            border: 1px solid #dee2e6;
            border-radius: .375rem;
            margin-bottom: .5rem;
            transition: all .2s ease-in-out
        }

        .mc-option .icon {
            font-size: 1.2rem;
            width: 30px
        }

        .mc-option.student-choice {
            border-color: #0d6efd;
            box-shadow: 0 0 0 2px rgba(13, 110, 253, .25)
        }

        .mc-option.correct-answer {
            background-color: #d1e7dd;
            border-color: #badbcc
        }

        .mc-option.student-choice.wrong-answer {
            background-color: #f8d7da;
            border-color: #f5c2c7
        }

        .summary-panel.sticky-top {
            top: 20px
        }
        .correct-answer-box{
            padding: 5px;
        }
        .student-answer-box{
            padding: 5px;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb" id="breadcrumb-container">
                <li class="breadcrumb-item"><a href="{{ url('/teacher/dashboard') }}">Dashboard Guru</a></li>
                <li class="breadcrumb-item"><a href="{{ url('/teacher/assignment') }}">Manajemen Tugas</a></li>
                <li class="breadcrumb-item placeholder-glow"><span class="placeholder col-4"
                        id="breadcrumb-assignment-link"></span></li>
                <li class="breadcrumb-item active" aria-current="page" id="breadcrumb-action">Loading...</li>
            </ol>
        </nav>

        <form id="grading-form" onsubmit="return false;">
            <div class="row">
                <div class="col-lg-8" id="questions-container">
                    <div class="card mb-4">
                        <div class="card-body placeholder-glow"><span class="placeholder col-7"></span> <span
                                class="placeholder col-4"></span> <span class="placeholder col-4"></span> <span
                                class="placeholder col-6"></span> <span class="placeholder col-8"></span></div>
                    </div>
                    <div class="card mb-4">
                        <div class="card-body placeholder-glow"><span class="placeholder col-7"></span> <span
                                class="placeholder col-4"></span> <span class="placeholder col-4"></span> <span
                                class="placeholder col-6"></span> <span class="placeholder col-8"></span></div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card summary-panel sticky-top">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-clipboard-check me-2"></i> Ringkasan & Aksi</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3 placeholder-glow">
                                <div class="fa-3x text-secondary me-3"><i class="fas fa-user-circle"></i></div>
                                <div>
                                    <h6 class="mb-0 fw-bold" id="student-name"><span class="placeholder col-8"></span></h6>
                                    <span class="text-muted" id="student-nisn"><span
                                            class="placeholder col-12"></span></span>
                                </div>
                            </div>
                            <hr>
                            <ul class="list-unstyled placeholder-glow">
                                <li class="d-flex justify-content-between mb-2"><span class="text-muted">Waktu Kumpul</span>
                                    <strong id="submission-time"><span class="placeholder col-5"></span></strong></li>
                                <li class="d-flex justify-content-between"><span class="text-muted">Status</span> <span
                                        id="submission-status"><span class="placeholder col-4"></span></span></li>
                            </ul>
                            <hr>
                            <div class="text-center my-4">
                                <h6 class="text-muted mb-1">SKOR AKHIR</h6>
                                <h1 class="display-2 fw-bolder text-primary" id="final-score-display">--</h1>
                                <p class="mb-0 text-muted" id="final-points-display">(... / ... Poin)</p>
                            </div>
                            <div id="action-button-container">
                                <button class="btn btn-success btn-lg w-100 disabled placeholder col-12"></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    {{-- TEMPLATES --}}
    <template id="question-card-template">
        <div class="card mb-4 question-card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">
                    <i class="fas fa-question-circle text-primary me-2"></i>
                    <span class="question-number"></span>
                    <small class="text-muted fw-normal question-points"></small>
                </h5>
                <span class="badge bg-secondary question-type"></span>
            </div>
            <div class="question-body-content">
                <div class="question-text"></div>
                <div class="answer-content-wrapper"></div>
            </div>
            <div class="card-footer bg-light">
                <div class="row g-3 align-items-center">
                    <div class="col-md-8">
                        <label class="form-label fw-semibold">Feedback / Komentar (Opsional)</label>
                        <textarea class="form-control feedback-input" rows="1" placeholder="Tambahkan komentar untuk siswa..."></textarea>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Poin Diperoleh</label>
                        <div class="input-group">
                            <input type="number" class="form-control score-input" min="0" placeholder="Poin">
                            <span class="input-group-text max-score-text"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </template>

    <template id="text-essay-template">
        <div class="correct-answer-box" style="display: none;">
            <div class="answer-box-header">
                <i class="fas fa-check-circle me-2"></i>
                <span class="correct-answer-title"></span>
            </div>
            <p class="mb-0 correct-answer-content"></p>
        </div>
        <div class="student-answer-box">
            <div class="answer-box-header">
                <i class="fas fa-user-graduate me-2"></i> Jawaban Siswa
            </div>
            <p class="mb-0 student-answer-content"></p>
        </div>
    </template>

    <template id="multiple-choice-template">
        <div class="correct-answer-box">
            <div class="answer-box-header">
                <i class="fas fa-check-circle me-2"></i> Kunci Jawaban
            </div>
            <p class="mb-0 correct-answer-content"></p>
        </div>
        <div class="student-answer-box">
            <div class="answer-box-header">
                <i class="fas fa-user-graduate me-2"></i> Jawaban Siswa
            </div>
            <div class="mc-options-container"></div>
        </div>
    </template>

    <template id="mc-option-template">
        <div class="mc-option">
            <span class="icon me-3"></span>
            <span class="option-text"></span>
        </div>
    </template>

    <script src="{{ asset('assets/js/app/teacher-assignment/teacherGrading.js') }}"></script>
@endpush
