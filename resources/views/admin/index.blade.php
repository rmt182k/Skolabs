@extends('layouts.auth')

@section('title', 'Admins Management')

@section('content')
    <div class="container-fluid">
        {{-- Breadcrumb --}}
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Admins</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Dashboard</a></li>
                            <li class="breadcrumb-item active">Admins Management</li>
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
                        <h5 class="card-title mb-0">Admin List</h5>
                        <button id="adminAddBtn" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i> Add New Admin
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            @include('admin.components.table-admin')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Admin Modal --}}
    @include('admin.components.modal-admin')
@endsection

@push('styles')
    {{-- Include DataTables CSS if not globally available --}}
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
@endpush

@push('scripts')
    <script src="{{ asset('assets/js/app/admin/admin.js') }}"></script>
    <script src="{{ asset('assets/js/app/utils/tableConfig.js') }}"></script>
@endpush
