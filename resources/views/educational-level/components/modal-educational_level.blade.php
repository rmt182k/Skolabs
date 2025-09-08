<div class="modal fade" id="educationalLevelModal" tabindex="-1" role="dialog" aria-labelledby="educationalLevelModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="educationalLevelModalLabel">Add Educational Level</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="educationalLevelForm">
                    <input type="hidden" id="educationalLevelId" name="id">
                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="duration_years" class="form-label">Duration (Years)</label>
                        <input type="number" class="form-control" id="duration_years" name="duration_years" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveEducationalLevelBtn">Save</button>
            </div>
        </div>
    </div>
</div>
