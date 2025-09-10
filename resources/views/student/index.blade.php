@extends('layouts.auth')

@section('title', 'Student Management')

@section('content')
    <div class="container-fluid">
        {{-- Breadcrumb bisa Anda sesuaikan --}}
        @include('layouts.components.breadcrumb')

        <div class="row">
            <div class="col-12">
                <div class="card">
                     <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Student List</h5>
                        <button id="studentAddBtn" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i> Add New Student
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            @include('student.components.table-student')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('student.components.modal-student')
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/app/student/student.js') }}"></script>
@endpush
