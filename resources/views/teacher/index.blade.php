{{-- File: resources/views/teacher/index.blade.php --}}

@extends('layouts.auth')

@section('title', 'Teacher Management')

@section('content')
    <div class="container-fluid">
        {{-- Breadcrumb --}}
        @include('layouts.components.breadcrumb')

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
    <script src="{{ asset('assets/js/app/teacher/teacher.js') }}"></script>
@endpush
