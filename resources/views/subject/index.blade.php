@extends('layouts.auth')

@section('title', 'Subject Management')

@section('content')
    <div class="container-fluid">
        {{-- Breadcrumb --}}
        @include('layouts.components.breadcrumb')

        {{-- Main Content --}}
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Subject List</h5>
                        <button id="subjectAddBtn" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i> Add New Subject
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            @include('subject.components.table-subject')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- Subject Modal --}}
    @include('subject.components.modal-subject')
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/app/subject/subject.js') }}"></script>
@endpush
