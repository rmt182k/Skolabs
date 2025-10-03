@extends('layouts.auth')

@section('title', 'Class & Student Management')

@section('styles')
    {{-- Select2 for rich select inputs --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        /* Custom styles to enhance the look and feel of Select2 */
        .select2-container--default .select2-selection--multiple {
            border-radius: 0.375rem;
            border: 1px solid #ced4da;
            padding: 0.2rem;
        }

        .select2-container--default .select2-selection--single {
            border-radius: 0.375rem;
            border: 1px solid #ced4da;
            height: calc(1.5em + 0.75rem + 2px);
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid">
        {{-- Breadcrumb --}}
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Class & Student Management</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Dashboard</a></li>
                            <li class="breadcrumb-item active">Classes & Students</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        {{-- Main Content with Tabs --}}
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <ul class="nav nav-tabs card-header-tabs" id="managementTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="class-list-tab" data-bs-toggle="tab"
                                    data-bs-target="#class-list-pane" type="button" role="tab"
                                    aria-controls="class-list-pane" aria-selected="true">
                                    <i class="fas fa-chalkboard-teacher me-1"></i> Class Management
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="student-assign-tab" data-bs-toggle="tab"
                                    data-bs-target="#student-assign-pane" type="button" role="tab"
                                    aria-controls="student-assign-pane" aria-selected="false">
                                    <i class="fas fa-user-plus me-1"></i> Assign Students
                                </button>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content" id="managementTabsContent">
                            {{-- Tab 1: Class List --}}
                            <div class="tab-pane fade show active" id="class-list-pane" role="tabpanel"
                                aria-labelledby="class-list-tab" tabindex="0">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="card-title mb-0">Class List</h5>
                                    <button id="classAddBtn" class="btn btn-primary">
                                        <i class="fas fa-plus me-1"></i> Add New Class
                                    </button>
                                </div>
                                <div class="table-responsive">
                                    <table id="classesTable" class="table table-bordered table-striped dt-responsive nowrap"
                                        style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>Class Name</th>
                                                <th>Grade</th>
                                                <th>Level</th>
                                                <th>Major</th>
                                                <th>Homeroom Teacher</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {{-- Data populated by JavaScript --}}
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            {{-- Tab 2: Assign Students --}}
                            <div class="tab-pane fade" id="student-assign-pane" role="tabpanel"
                                aria-labelledby="student-assign-tab" tabindex="0">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="card-title mb-0">Student Assignments</h5>
                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                        data-bs-target="#assignStudentModal" id="addAssignStudentBtn">
                                        <i class="fas fa-user-plus me-1"></i> Assign Student
                                    </button>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped" id="assignStudentsTable"
                                        style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>Class Name</th>
                                                <th>Student Name</th>
                                                <th>Major</th>
                                                <th>Homeroom Teacher</th>
                                                <th>Assigned At</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {{-- Data populated by JavaScript --}}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal for Adding/Editing Class --}}
    <div class="modal fade" id="classModal" tabindex="-1" aria-labelledby="classModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="classModalLabel">Add New Class</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="classForm" novalidate>
                        @csrf
                        <input type="hidden" id="classId" name="id">
                        <input type="hidden" id="generatedClassName" name="name">

                        <div class="mb-3">
                            <label for="gradeLevel" class="form-label">Grade</label>
                            <select class="form-select" id="gradeLevel" name="grade_level" required>
                                <option value="">Select Grade</option>
                                {{-- Options 1-12 will be generated by JavaScript --}}
                            </select>
                            <div class="invalid-feedback">Please select a grade.</div>
                        </div>

                        <div class="mb-3">
                            <label for="educationalLevelId" class="form-label">Educational Level</label>
                            <select class="form-select" id="educationalLevelId" name="educational_level_id" required>
                                <option value="">Select Level</option>
                                {{-- Options loaded from API --}}
                            </select>
                            <div class="invalid-feedback">Please select an educational level.</div>
                        </div>

                        <div class="mb-3">
                            <label for="majorId" class="form-label">Major</label>
                            <select class="form-select" id="majorId" name="major_id" required disabled>
                                <option value="">Select Level First</option>
                                {{-- Options loaded from API based on Level --}}
                            </select>
                            <div class="invalid-feedback">Please select a major.</div>
                        </div>

                        <div class="mb-3">
                            <label for="teacherId" class="form-label">Homeroom Teacher</label>
                            <select class="form-select" id="teacherId" name="teacher_id" required>
                                <option value="">Select a Teacher</option>
                                {{-- Options loaded from API --}}
                            </select>
                            <div class="invalid-feedback">Please select a homeroom teacher.</div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="saveClassBtn" form="classForm">Save
                        Changes</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal for Assigning Students --}}
    <div class="modal fade" id="assignStudentModal" tabindex="-1" role="dialog"
        aria-labelledby="assignStudentModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="assignStudentModalLabel">Assign Student to Class</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="assignStudentForm">
                        <input type="hidden" id="assignStudentId" name="id">
                        <div class="mb-3">
                            <label for="class_id" class="form-label">Class</label>
                            <select class="form-control" id="class_id" name="class_id" style="width: 100%;" required>
                                <option value="">Select a Class</option>
                                {{-- Options will be populated by JavaScript --}}
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="student_ids" class="form-label">Students</label>
                            <select class="form-control" id="student_ids" name="student_ids[]" multiple="multiple"
                                style="width: 100%;" required>
                                {{-- Options populated by JavaScript --}}
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="saveAssignStudentBtn">Save Assignment</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    {{-- Third-party libraries --}}
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    {{-- Application-specific scripts --}}
    <script src="{{ asset('assets/js/app/class/class.js') }}"></script>
    <script src="{{ asset('assets/js/app/class-student/class-student.js') }}"></script>
    <script src="{{ asset('assets/js/app/utils/tableConfig.js') }}"></script>
@endpush
