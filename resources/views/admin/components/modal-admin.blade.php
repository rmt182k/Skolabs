{{-- File: resources/views/admin/components/modal-admin.blade.php --}}

<div class="modal fade" id="adminModal" tabindex="-1" aria-labelledby="adminModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-3">
            <div class="modal-header bg-primary text-white border-0 rounded-top-3 p-4">
                <h5 class="modal-title fw-bold" id="adminModalLabel"></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="adminForm" class="row g-3">
                    {{-- Hidden input untuk ID, tidak perlu atribut 'name' --}}
                    <input type="hidden" id="adminId">

                    <div class="col-md-6">
                        <label for="adminName" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="adminName" name="name" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-6">
                        <label for="adminEmail" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="adminEmail" name="email" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-6">
                        <label for="adminJobTitle" class="form-label">Job Title</label>
                        <input type="text" class="form-control" id="adminJobTitle" name="job_title"
                            placeholder="e.g., Staff Akademik">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-6">
                        <label for="adminStatus" class="form-label">Status</label>
                        <select id="adminStatus" name="status" class="form-select" required>
                            <option value="active" selected>Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-12">
                        <label for="adminPassword" class="form-label">Password</label>
                        <input type="password" class="form-control" id="adminPassword" name="password"
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
                <button type="submit" id="saveAdminBtn" form="adminForm" class="btn btn-primary px-4">Save
                    Admin</button>
            </div>
        </div>
    </div>
</div>
