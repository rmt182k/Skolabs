<div class="modal fade" id="teacherModal" tabindex="-1" aria-labelledby="teacherModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-3">
            <div class="modal-header bg-primary text-white border-0 rounded-top-3 p-4">
                <h5 class="modal-title fw-bold" id="teacherModalLabel"></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="teacherForm" class="row g-3">
                    <input type="hidden" id="teacherId" name="teacher_id">
                    <input type="hidden" id="userId" name="user_id">
                    {{-- User Name Field --}}
                    <div class="col-md-6">
                        <label for="teacherName" class="form-label">Name</label>
                        <input type="text" class="form-control" id="teacherName" name="name" required>
                        <div class="invalid-feedback"></div>
                    </div>

                    {{-- Employee ID Field --}}
                    <div class="col-md-6">
                        <label for="teacherEmployeeId" class="form-label">Employee ID</label>
                        <input type="text" class="form-control" id="teacherEmployeeId" name="employee_id" required>
                        <div class="invalid-feedback"></div>
                    </div>

                    {{-- Email Field --}}
                    <div class="col-md-6">
                        <label for="teacherEmail" class="form-label">Email</label>
                        <input type="email" class="form-control" id="teacherEmail" name="email" required>
                        <div class="invalid-feedback"></div>
                    </div>

                    {{-- Password Field --}}
                    <div class="col-md-6">
                        <label for="teacherPassword" class="form-label">Password</label>
                        <input type="password" class="form-control" id="teacherPassword" name="password">
                        <div id="passwordHelpBlock" class="form-text">
                            If left blank, the default password is "password".
                        </div>
                        <div class="invalid-feedback"></div>
                    </div>

                    {{-- Date of Birth Field --}}
                    <div class="col-md-6">
                        <label for="teacherDateOfBirth" class="form-label">Date of Birth</label>
                        <input type="date" class="form-control" id="teacherDateOfBirth" name="date_of_birth"
                            required>
                        <div class="invalid-feedback"></div>
                    </div>

                    {{-- Phone Number Field --}}
                    <div class="col-md-6">
                        <label for="teacherPhoneNumber" class="form-label">Phone Number</label>
                        <input type="tel" class="form-control" id="teacherPhoneNumber" name="phone_number">
                        <div class="invalid-feedback"></div>
                    </div>

                    {{-- Gender Field --}}
                    <div class="col-md-6">
                        <label for="teacherGender" class="form-label">Gender</label>
                        <select class="form-select" id="teacherGender" name="gender" required>
                            <option value="">Select Gender</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>

                    {{-- Status Field --}}
                    <div class="col-md-6">
                        <label for="teacherStatus" class="form-label">Status</label>
                        <select class="form-select" id="teacherStatus" name="status">
                            <option value="">Select Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>

                    {{-- Address Field --}}
                    <div class="col-md-12">
                        <label for="teacherAddress" class="form-label">Address</label>
                        <textarea class="form-control" id="teacherAddress" name="address" rows="3"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>

                </form>
            </div>
            <div class="modal-footer p-3 border-0 rounded-bottom-3">
                <button type="button" class="btn btn-outline-secondary px-4 me-2"
                    data-bs-dismiss="modal">Close</button>
                <button type="submit" id="saveTeacherBtn" form="teacherForm" class="btn btn-primary px-4">Save
                    Teacher</button>
            </div>
        </div>
    </div>
</div>
