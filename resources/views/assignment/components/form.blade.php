@extends('layouts.auth')
@section('title', 'Create New Assignment')

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('assets/css/app/assignment/assignment-create.css') }}">
@endpush

@section('content')
    <div class="container-fluid">
        @include('layouts.components.breadcrumb')

        <form id="assignmentForm" onsubmit="return false;" data-is-edit="{{ isset($assignment) ? 'true' : 'false' }}"
            data-assignment-id="{{ $assignment->id ?? '' }}">
            @csrf
            @if (isset($assignment))
                @method('PUT')
            @endif

            {{-- Assignment Details --}}
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0"><i class="fas fa-info-circle me-2"></i>Assignment Details</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label for="assignmentTitle" class="form-label fw-semibold">Assignment Title <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="assignmentTitle" name="title"
                                value="{{ $assignment->title ?? '' }}" placeholder="Enter assignment title" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="assignmentType" class="form-label fw-semibold">Assignment Type <span
                                    class="text-danger">*</span></label>
                            <select class="form-select" id="assignmentType" name="assignment_type" required>
                                <option value="">Choose Type</option>
                                <option value="task" @selected(old('assignment_type', $assignment->assignment_type ?? '') == 'task')>Task</option>
                                <option value="quiz" @selected(old('assignment_type', $assignment->assignment_type ?? '') == 'quiz')>Quiz</option>
                                <option value="exam" @selected(old('assignment_type', $assignment->assignment_type ?? '') == 'exam')>Exam</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="subjectId" class="form-label fw-semibold">Subject <span
                                    class="text-danger">*</span></label>
                            <select class="form-select" id="subjectId" name="subject_id" required>
                                <option value="">Loading Subjects...</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="classId" class="form-label fw-semibold">Class(es) <span
                                    class="text-danger">*</span></label>
                            <select class="form-select" id="classId" name="class_id[]" required
                                multiple="multiple"></select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="startDate" class="form-label fw-semibold">Start Date</label>
                            <input type="date" class="form-control" id="startDate" name="start_date"
                                value="{{ old('start_date', $assignment->start_date ?? '') }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="dueDate" class="form-label fw-semibold">Due Date</label>
                            <input type="date" class="form-control" id="dueDate" name="due_date"
                                value="{{ old('due_date', $assignment->due_date ?? '') }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="description" class="form-label fw-semibold">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="1" placeholder="Assignment description">{{ old('description', $assignment->description ?? '') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Questions Section --}}
            <div class="card">
                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0"><i class="fas fa-question-circle me-2"></i>Questions</h5>
                    <span class="badge bg-light text-dark" id="question-counter">0 Questions</span>
                </div>
                <div class="card-body">
                    <div id="question-builder"></div>
                    <div class="empty-state" id="empty-state">
                        <i class="fas fa-clipboard-question fa-3x text-muted mb-3"></i>
                        <h5>Start building your assignment</h5>
                        <p class="text-muted">Add questions to get started</p>
                    </div>
                    <div class="text-center mt-3">
                        <button type="button" class="btn btn-primary" id="add-question-btn"><i
                                class="fas fa-plus me-2"></i>Add Question</button>
                    </div>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
                <a href="/assignment" class="btn btn-secondary"><i
                        class="fas fa-arrow-left me-2"></i>Cancel</a>
                <button type="button" class="btn btn-success" id="saveBtn">
                    <i class="fas fa-save me-2"></i>
                    {{ isset($assignment) ? 'Update Assignment' : 'Save Assignment' }}
                </button>
            </div>
        </form>
    </div>

    {{-- Templates --}}
    <template id="question-template">
        <div class="question-card mb-3">
            <div class="question-header p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <div class="question-number me-3"></div>
                        <span class="fw-semibold">Question</span>
                    </div>
                    <button type="button" class="btn-remove-question" title="Delete Question">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
            <div class="p-3">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Question Text <span class="text-danger">*</span></label>
                        <textarea class="form-control question-text" rows="2" placeholder="Enter your question"></textarea>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Question Type</label>
                        <select class="form-select question-type-select">
                            <option value="text">Short Answer</option>
                            <option value="multiple_choice">Multiple Choice</option>
                            <option value="essay">Essay</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Score <span class="text-danger">*</span></label>
                        <input type="number" class="form-control question-score" min="1" value="10"
                            placeholder="e.g., 10">
                    </div>
                </div>
                <div class="answer-container">
                    {{-- Answer content will be rendered here --}}
                </div>
            </div>
        </div>
    </template>

    <template id="answer-text-template">
        <div>
            <label class="form-label fw-semibold text-success">Correct Answer <span class="text-danger">*</span></label>
            <input type="text" class="form-control correct-answer-input" placeholder="Enter the correct answer">
        </div>
    </template>

    <template id="answer-essay-template">
        <div>
            <label class="form-label fw-semibold text-success">Model Answer / Rubric</label>
            <textarea class="form-control correct-answer-textarea" rows="3"
                placeholder="Enter a model answer or grading rubric"></textarea>
        </div>
    </template>

    <template id="answer-mc-template">
        <div class="mb-3">
            <label class="form-label fw-semibold">Answer Options <small class="text-muted">(Check the correct
                    answer/s)</small></label>
            <div class="mc-options-list">
                {{-- Options will be added here --}}
            </div>
            <button type="button" class="btn btn-sm btn-outline-primary mt-2 add-option-btn">
                <i class="fas fa-plus me-1"></i>Add Option
            </button>
        </div>
    </template>

    <template id="mc-option-template">
        <div class="d-flex align-items-center mb-2 p-2 border rounded mc-option">
            <div class="form-check me-3">
                <input class="form-check-input correct-answer-checkbox" type="checkbox" value=""
                    style="width: 1.5em; height: 1.5em;">
            </div>
            <div class="option-label me-3"></div>
            <input type="text" class="form-control option-input" placeholder="Enter option text">
            <button type="button" class="btn-remove-option ms-2" title="Remove Option">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    </template>
@endsection

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="{{ asset('assets/js/app/assignment/assignmentForm.js') }}"></script>
@endpush
