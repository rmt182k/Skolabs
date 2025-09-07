<div class="modal fade" id="majorModal" tabindex="-1" aria-labelledby="majorModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="majorModalLabel">Add Major</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="majorForm">
                    <input type="hidden" id="majorId">
                    <div class="mb-3">
                        <label for="level" class="form-label">Education Level</label>
                        <select class="form-select" id="level" name="level" required>
                            <option value="">Select Level</option>
                            <option value="elementary_school">Elementary School (SD)</option>
                            <option value="junior_high">Junior High School (SMP)</option>
                            <option value="senior_high_general">Senior High General (SMA)</option>
                            <option value="senior_high_vocational">Senior High Vocational (SMK)</option>
                        </select>

                    </div>
                    <div class="mb-3">
                        <label for="value" class="form-label">Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="text" class="form-label">Description</label>
                        <input type="text" class="form-control" id="text" name="description" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveMajorBtn">Save</button>
            </div>
        </div>
    </div>
</div>
