<div class="modal fade" id="staffModal" tabindex="-1" aria-labelledby="staffModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="staffModalLabel"></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="staffForm" class="row g-3">
                    <input type="hidden" id="staffId" name="staff_id">
                    <div class="col-md-6">
                        <label for="staffName" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="staffName" name="name" required>
                    </div>
                    <div class="col-md-6">
                        <label for="staffEmail" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="staffEmail" name="email" required>
                    </div>
                    <div class="col-12">
                        <label for="staffPassword" class="form-label">Password</label>
                        <input type="password" class="form-control" id="staffPassword" name="password"
                            placeholder="Leave blank to keep unchanged">
                        <small class="form-text text-muted">Enter a new password to update it.</small>
                    </div>
                    <div class="col-md-6">
                        <label for="staffPosition" class="form-label">Position</label>
                        <input type="text" class="form-control" id="staffPosition" name="position">
                    </div>
                    <div class="col-md-6">
                        <label for="staffStatus" class="form-label">Status</label>
                        <select id="staffStatus" name="status" class="form-select">
                            <option value="active" selected>Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" id="saveStaffBtn" form="staffForm" class="btn btn-primary">Save Changes</button>
            </div>
        </div>
    </div>
</div>
