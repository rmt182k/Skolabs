<div class="modal fade" id="classModal" tabindex="-1" aria-labelledby="classModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="classModalLabel">Add New Class</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="classForm">
                    <input type="hidden" id="classId" name="id">
                    <div class="mb-3">
                        <label for="className" class="form-label">Class Name</label>
                        <input type="text" class="form-control" id="className" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="majorId" class="form-label">Major</label>
                        <select class="form-select" id="majorId" name="major_id" required>
                            <!-- Options will be populated dynamically -->
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="teacherId" class="form-label">Teacher</label>
                        <select class="form-select" id="teacherId" name="teacher_id" required>
                            <!-- Options will be populated dynamically -->
                        </select>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
