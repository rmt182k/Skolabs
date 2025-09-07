@extends('layouts.auth')

@section('title', 'Majors')

@section('content')
    <div class="container-fluid">
        @include('layouts.components.breadcrumb')

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="header-title">Majors Table</h4>
                        <p class="text-muted font-14 mb-4">
                            Manage majors for different education levels
                        </p>

                        {{-- Table Majors --}}
                        <div class="table-responsive">
                            <div class="d-flex justify-content-end mb-3">
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                    data-bs-target="#majorModal" id="majorAddBtn">Add</button>
                            </div>
                            @include('major.components.table-major')
                        </div>

                    </div> <!-- end card body-->
                </div> <!-- end card -->
            </div><!-- end col-->
        </div> <!-- end row-->

        @include('major.components.modal-major')
    </div>
@endsection
@push('styles')
    <style>
        .table th,
        .table td {
            vertical-align: middle;
        }
    </style>
@endpush
@push('scripts')
    <script src="{{ asset('assets/js/app/major/major.js') }}"></script>
    <script src="{{ asset('assets/js/app/utils/tableConfig.js') }}"></script>
@endpush
