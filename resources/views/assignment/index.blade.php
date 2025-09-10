@extends('layouts.auth')

@section('title', 'Assignment Management')

@section('content')
    <div class="container-fluid">
        {{-- Breadcrumb --}}
        @include('layouts.components.breadcrumb')

        {{-- Main Content --}}
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Assignment List</h5>
                        {{-- Tombol ini idealnya hanya muncul untuk Guru --}}
                        <button id="assignmentAddBtn" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i> Add New Assignment
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            @include('assignment.components.table-assignment')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- Assignment Modal --}}
    @include('assignment.components.modal-assignment')
@endsection

@push('scripts')
    {{-- Pastikan Anda memiliki datepicker terinstal, contoh: flatpickr --}}
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="{{ asset('assets/js/app/assignment/assignment.js') }}"></script>
@endpush
