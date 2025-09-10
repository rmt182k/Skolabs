{{-- File: resources/views/staff/index.blade.php --}}

@extends('layouts.auth')

@section('title', 'Staff Management')

@section('content')
    <div class="container-fluid">
        {{-- Breadcrumb --}}
        @include('layouts.components.breadcrumb')

        {{-- Main Content --}}
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="header-title">Staff Table</h4>
                        <p class="text-muted font-14 mb-4">
                            List of staff registered in the system. You can add, edit, or delete staff data.
                        </p>

                        <div class="table-responsive">
                            <div class="d-flex justify-content-end mb-3">
                                <button type="button" class="btn btn-primary" id="staffAddBtn">
                                    <i class="fas fa-plus me-1"></i> Add New Staff
                                </button>
                            </div>
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
    <script src="{{ asset('assets/js/app/staff/staff.js') }}"></script>
@endpush
