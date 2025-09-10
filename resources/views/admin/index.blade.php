{{-- File: resources/views/admin/index.blade.php --}}

@extends('layouts.auth')

@section('title', 'Admin Management')

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
                            <li class="breadcrumb-item active">Admin Management</li>
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
                        <h4 class="header-title">Admins Table</h4>
                        <p class="text-muted font-14 mb-4">
                            List of admins registered in the system. You can add, edit, or delete admin data.
                        </p>

                        {{-- Tombol Tambah dan Tabel Admin --}}
                        <div class="table-responsive">
                            <div class="d-flex justify-content-end mb-3">
                                <button type="button" class="btn btn-primary" id="adminAddBtn">
                                    <i class="fas fa-plus me-1"></i> Add New Admin
                                </button>
                            </div>
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

@push('scripts')
    {{-- Path ke file JS untuk admin --}}
    <script src="{{ asset('assets/js/app/admin/admin.js') }}"></script>
@endpush
