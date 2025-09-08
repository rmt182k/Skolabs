@extends('layouts.auth')

@section('title', 'Educational Levels')

@section('content')
    <div class="container-fluid">
        @include('layouts.components.breadcrumb')

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="header-title">Educational Levels Table</h4>
                        <p class="text-muted font-14 mb-4">
                            Manage the various educational levels within the system.
                        </p>

                        {{-- Table Educational Levels --}}
                        <div class="table-responsive">
                            <div class="d-flex justify-content-end mb-3">
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                    data-bs-target="#educationalLevelModal" id="addEducationalLevelBtn">Add</button>
                            </div>
                            @include('educational-level.components.table-educational_level')
                        </div>
                    </div> <!-- end card body-->
                </div> <!-- end card -->
            </div><!-- end col-->
        </div> <!-- end row-->

        @include('educational-level.components.modal-educational_level')
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/app/educational-level/educational-level.js') }}"></script>
    <script src="{{ asset('assets/js/app/utils/tableConfig.js') }}"></script>
@endpush
