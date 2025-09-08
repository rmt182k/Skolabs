@extends('layouts.auth')

@section('title', 'Staff Management')

@section('content')
    <div class="container-fluid">
        {{-- Breadcrumb --}}
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Staff</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Dashboard</a></li>
                            <li class="breadcrumb-item active">Staff Management</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        {{-- Main Content --}}
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Staff List</h5>
                        <button id="staffAddBtn" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i> Add New Staff
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            @include('staff.components.table-staff')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- Staff Modal --}}
    @include('staff.components.modal-staff')
@endsection

@push('scripts')
    {{-- Pastikan path JS sudah benar --}}
    <script src="{{ asset('assets/js/app/staff/staff.js') }}"></script>
@endpush
