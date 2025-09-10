{{-- File: resources/views/teacher/index.blade.php --}}

@extends('layouts.auth')

@section('title', 'Teacher Management')

@push('styles')
    {{-- Flatpickr CSS untuk date-time picker --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@endpush

@section('content')
    <div class="container-fluid">
        {{-- Breadcrumb --}}
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Teachers</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Dashboard</a></li>
                            <li class="breadcrumb-item active">Teacher Management</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        {{-- Main Content --}}
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="header-title">Teachers Table</h4>
                        <p class="text-muted font-14 mb-4">
                            List of teachers registered in the system. You can add, edit, or delete teacher data.
                        </p>

                        <div class="table-responsive">
                            <div class="d-flex justify-content-end mb-3">
                                <button type="button" class="btn btn-primary" id="teacherAddBtn">
                                    <i class="fas fa-plus me-1"></i> Add New Teacher
                                </button>
                            </div>
                            @include('teacher.components.table-teacher')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal untuk Tambah/Edit Guru --}}
    @include('teacher.components.modal-teacher')
@endsection

@push('scripts')
    {{-- Flatpickr JS untuk date-time picker --}}
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="{{ asset('assets/js/app/teacher/teacher.js') }}"></script>
@endpush
