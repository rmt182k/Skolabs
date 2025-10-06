{{-- Teaching Assignment Modal --}}
<div class="modal fade" id="assignmentModal" tabindex="-1" aria-labelledby="assignmentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="assignmentModalLabel">Add New Teaching Assignment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="assignmentForm" onsubmit="return false;">
                    @csrf
                    <input type="hidden" id="assignmentId" name="id">

                    <div class="mb-3">
                        <label for="classId" class="form-label">Class</label>
                        <select class="form-select" id="classId" name="class_id" required style="width: 100%;">
                            {{-- Options will be populated by JavaScript --}}
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="subjectId" class="form-label">Subject</label>
                        <select class="form-select" id="subjectId" name="subject_id" required style="width: 100%;">
                            {{-- Options will be populated by JavaScript --}}
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="teacherId" class="form-label">Teacher</label>
                        <select class="form-select" id="teacherId" name="teacher_id" required disabled
                            style="width: 100%;">
                            <option value="">Select Subject First</option>
                        </select>
                        <small class="form-text text-muted">Only teachers qualified for the selected subject will
                            appear.</small>
                    </div>

                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary" id="saveAssignmentBtn" form="assignmentForm">Save
                    Assignment</button>
            </div>
        </div>
    </div>
</div>
