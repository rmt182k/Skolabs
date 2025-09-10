<div class="modal fade" id="assignmentModal" tabindex="-1" aria-labelledby="assignmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="assignmentModalLabel">Add New Assignment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="assignmentForm" name="assignmentForm" enctype="multipart/form-data">
                    <input type="hidden" name="assignment_id" id="assignmentId">
                    <div class="mb-3">
                        <label for="assignmentTitle" class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="assignmentTitle" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="assignmentDescription" class="form-label">Description / Instructions</label>
                        <textarea class="form-control" id="assignmentDescription" name="description" rows="4"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="subjectId" class="form-label">Subject <span class="text-danger">*</span></label>
                            <select class="form-select" id="subjectId" name="subject_id" required>
                                <option value="">Select Subject</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="classId" class="form-label">Class <span class="text-danger">*</span></label>
                            <select class="form-select" id="classId" name="class_id" required>
                                <option value="">Select Class</option>
                            </select>
                        </div>
                    </div>
                     <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="startDate" class="form-label">Start Date</label>
                            <input type="text" class="form-control" id="startDate" name="start_date" placeholder="Defaults to now if empty">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="dueDate" class="form-label">Due Date</label>
                            <input type="text" class="form-control" id="dueDate" name="due_date" placeholder="Select date and time">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="assignmentFile" class="form-label">Attach File (Optional)</label>
                        <input class="form-control" type="file" id="assignmentFile" name="file">
                        <small class="form-text text-muted">You can attach a file like question sheets, etc.</small>
                    </div>
                    <div id="current-file-container" class="mb-3" style="display: none;">
                        <p class="mb-1"><strong>Current File:</strong> <span id="current-file-name"></span></p>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary" form="assignmentForm" id="saveBtn">Save Assignment</button>
            </div>
        </div>
    </div>
</div>
