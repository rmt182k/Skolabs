@extends('layouts.auth')

@section('title', 'Skolabs Student')

@section('content')
    <div class="container-fluid">
        @include('layouts.components.breadcrumb')

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="header-title">Students Table</h4>
                        <p class="text-muted font-14 mb-4">
                            {{-- put notes here --}}
                        </p>

                        {{-- Table Students --}}
                        <div class="table-responsive">
                            <div class="d-flex justify-content-end mb-3">
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" id="studentAddBtn">Add</button>
                            </div>
                            @include('student.components.table-student')
                        </div>

                    </div> <!-- end card body-->
                </div> <!-- end card -->
            </div><!-- end col-->
        </div> <!-- end row-->

        @include('student.components.modal-student')
    </div>
@endsection
@push('styles')
@endpush
@push('scripts')
    <script src="{{ asset('assets/js/app/utils/student.js') }}"></script>
    <script src="{{ asset('assets/js/app/utils/tableConfig.js') }}"></script>
@endpush
