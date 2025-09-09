@extends('layouts.auth')

@section('title', 'Learning Material Management')

@section('content')
    <div class="container-fluid">
        {{-- Breadcrumb --}}
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Learning Materials</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Dashboard</a></li>
                            <li class="breadcrumb-item active">Learning Material Management</li>
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
                        <h5 class="card-title mb-0">Learning Material List</h5>
                        <button id="materialAddBtn" class="btn btn-primary">
                            <i class="fas fa-upload me-1"></i> Add New Material
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            @include('learning-material.components.table-learning_material')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- Learning Material Modal --}}
    @include('learning-material.components.modal-learning_material')
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/app/learning-material/learning-material.js') }}"></script>
@endpush
