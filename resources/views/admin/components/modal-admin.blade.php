<div class="modal fade" id="adminModal" tabindex="-1" aria-labelledby="adminModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="adminModalLabel"></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="adminForm" class="row g-3">
                    <input type="hidden" id="adminId" name="admin_id">

                    <div class="col-md-6">
                        <label for="adminName" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="adminName" name="name" required>
                    </div>
                    <div class="col-md-6">
                        <label for="adminEmail" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="adminEmail" name="email" required>
                    </div>
                    <div class="col-12">
                        <label for="adminPassword" class="form-label">Password</label>
                        <input type="password" class="form-control" id="adminPassword" name="password"
                            placeholder="Leave blank to keep unchanged">
                        <small class="form-text text-muted">Enter a new password to update it. Minimum 8
                            characters.</small>
                    </div>
                    <div class="col-md-6">
                        <label for="adminJobTitle" class="form-label">Job Title</label>
                        <input type="text" class="form-control" id="adminJobTitle" name="job_title">
                    </div>
                    <div class="col-md-6">
                        <label for="adminStatus" class="form-label">Status</label>
                        <select id="adminStatus" name="status" class="form-select">
                            <option value="active" selected>Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" id="saveAdminBtn" form="adminForm" class="btn btn-primary">Save
                    Changes</button>
            </div>
        </div>
    </div>
</div>
