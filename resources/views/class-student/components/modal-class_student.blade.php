<div class="modal fade" id="assignStudentModal" tabindex="-1" role="dialog" aria-labelledby="assignStudentModalLabel"
    aria-hidden="true">
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
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveAssignStudentBtn">Save</button>
            </div>
        </div>
    </div>
</div>
