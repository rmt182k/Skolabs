@extends('layouts.auth')

@section('title', 'Skolabs Teachers')

@section('content')
    <div class="container-fluid">
        @include('layouts.components.breadcrumb')

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="header-title">Teachers Table</h4>
                        <p class="text-muted font-14 mb-4">
                            List of teachers registered in the system. You can add, edit, or delete teacher data.
                        </p>

                        {{-- Teachers Table --}}
                        <div class="table-responsive">
                            <div class="d-flex justify-content-end mb-3">
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#teacherModal" id="teacherAddBtn">Add Teacher</button>
                            </div>
                            @include('teacher.components.table-teacher')
                        </div>
                    </div> </div> </div></div> {{-- Modal --}}
        @include('teacher.components.modal-teacher')
    </div>
@endsection

@push('styles')
@endpush

@push('scripts')
    <script src="{{ asset('assets/js/app/teacher/teacher.js') }}"></script>
    <script src="{{ asset('assets/js/app/utils/tableConfig.js') }}"></script>
@endpush
