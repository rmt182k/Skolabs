<div class="modal fade" id="staffModal" tabindex="-1" aria-labelledby="staffModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-3">
            <div class="modal-header bg-primary text-white border-0 rounded-top-3 p-4">
                <h5 class="modal-title fw-bold" id="staffModalLabel"></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="staffForm" class="row g-3">
                    <input type="hidden" id="staffId" name="id">

                    <div class="col-md-6">
                        <label for="staffName" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="staffName" name="name" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-6">
                        <label for="staffEmail" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="staffEmail" name="email" required>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="col-md-6">
                        <label for="staffEmployeeId" class="form-label">Employee ID</label>
                        <input type="text" class="form-control" id="staffEmployeeId" name="employee_id">
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="col-md-6">
                        <label for="staffDateOfBirth" class="form-label">Date of Birth</label>
                        <input type="date" class="form-control" id="staffDateOfBirth" name="date_of_birth">
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="col-md-6">
                        <label for="staffPosition" class="form-label">Position</label>
                        <input type="text" class="form-control" id="staffPosition" name="position"
                            placeholder="e.g., Finance, IT Support">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-6">
                        <label for="staffStatus" class="form-label">Status</label>
                        <select id="staffStatus" name="status" class="form-select" required>
                            <option value="active" selected>Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-12">
                        <label for="staffPassword" class="form-label">Password</label>
                        <input type="password" class="form-control" id="staffPassword" name="password"
                            autocomplete="new-password">
                        <div class="form-text text-muted">Untuk edit, biarkan kosong jika tidak ingin mengubah password.
                        </div>
                        <div class="invalid-feedback"></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer p-3 border-0 rounded-bottom-3">
                <button type="button" class="btn btn-outline-secondary px-4 me-2"
                    data-bs-dismiss="modal">Close</button>
                <button type="submit" id="saveStaffBtn" form="staffForm" class="btn btn-primary px-4">Save
                    Staff</button>
            </div>
        </div>
    </div>
</div>
