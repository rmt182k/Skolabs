@extends('layouts.auth')

@section('title', 'Teaching Assignment')

@section('styles')
    {{-- Select2 for rich select inputs --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        /* Custom styles for Select2 */
        .select2-container--default .select2-selection--single {
            border-radius: 0.375rem;
            border: 1px solid #ced4da;
            height: calc(1.5em + 0.75rem + 2px);
            padding: 0.375rem 0.75rem;
        }

        .select2-container .select2-selection--single .select2-selection__arrow {
            height: calc(1.5em + 0.75rem);
        }

        /* Enhanced styles for Card View */
        .class-group-card {
            border-left: 4px solid #0d6efd;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .assignment-item { /* Ganti dari .schedule-item */
            transition: all 0.2s ease-in-out;
            border-right: 4px solid transparent;
        }

        .assignment-item:hover { /* Ganti dari .schedule-item:hover */
            background-color: #f8f9fa;
            transform: translateX(3px);
            border-right-color: #0d6efd;
        }

        /* Ensure filter selects use full width */
        .select2-container {
            width: 100% !important;
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid">
        {{-- Breadcrumb --}}
        @include('layouts.components.breadcrumb', ['title' => 'Teaching Assignment'])

        {{-- Filters --}}
        <div class="card mb-4">
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label for="filter-class" class="form-label">Filter by Class</label>
                        <select id="filter-class" name="class_id">
                            <option value=""></option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="filter-subject" class="form-label">Filter by Subject</label>
                        <select id="filter-subject" name="subject_id">
                            <option value=""></option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="filter-teacher" class="form-label">Filter by Teacher</label>
                        <select id="filter-teacher" name="teacher_id">
                            <option value=""></option>
                        </select>
                    </div>
                    <div class="col-md-3 text-start text-md-end">
                        <button class="btn btn-secondary" id="reset-filters-btn">
                            <i class="bi bi-arrow-counterclockwise"></i> Reset
                        </button>
                    </div>
                </div>
            </div>
        </div>


        {{-- Main Content --}}
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5 class="card-title mb-0">Teaching Assignment List</h5>
                <div class="d-flex align-items-center gap-2">
                    {{-- View Toggle Buttons --}}
                    <div class="btn-group" role="group" aria-label="View toggle">
                        <button type="button" class="btn btn-outline-primary active" id="view-table-btn"
                            title="Table View">
                            <i class="bi bi-table"></i> Table
                        </button>
                        <button type="button" class="btn btn-outline-primary" id="view-card-btn" title="Card View">
                            <i class="bi bi-grid-3x3-gap-fill"></i> Card
                        </button>
                    </div>
                    <button id="addAssignmentBtn" class="btn btn-primary"> {{-- Ganti id --}}
                        <i class="fas fa-plus me-1"></i> Add New
                    </button>
                </div>
            </div>
            <div class="card-body">
                {{-- Container for Table View --}}
                <div id="assignment-table-view"> {{-- Ganti id --}}
                    <div class="table-responsive">
                        <table id="assignmentsTable" class="table table-bordered table-striped" style="width:100%"> {{-- Ganti id --}}
                            <thead>
                                <tr>
                                    <th>Class</th>
                                    <th>Subject</th>
                                    <th>Assigned Teacher</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>

                {{-- Container for Card View (initially hidden) --}}
                <div id="assignment-card-view" class="d-none"> {{-- Ganti id --}}
                    {{-- Cards will be generated by JavaScript --}}
                </div>

            </div>
        </div>
    </div>

    {{-- Modal --}}
    {{-- Sebaiknya nama file juga diganti agar konsisten --}}
    @include('class-subject-assignment.partials.modal')

@endsection

@push('scripts')
    {{-- Third-party libraries --}}
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    {{-- Application-specific scripts --}}
    {{-- Sebaiknya nama file juga diganti --}}
    <script src="{{ asset('assets/js/app/class-subject-assignment/classSubjectAssignment.js') }}"></script>
@endpush
