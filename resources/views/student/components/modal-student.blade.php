<div class="modal fade" id="studentModal" tabindex="-1" aria-labelledby="studentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-3">
            <div class="modal-header bg-primary text-white border-0 rounded-top-3 p-4">
                <h5 class="modal-title fw-bold" id="studentModalLabel"></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="createStudentForm" class="row g-3">
                    <input type="hidden" id="studentId" name="student_id">
                    <div class="col-md-6">
                        <label for="studentName" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="studentName" name="name" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-6">
                        <label for="studentEmail" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="studentEmail" name="email" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-6">
                        <label for="studentPassword" class="form-label">Password</label>
                        <input type="password" class="form-control" id="studentPassword" name="password" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-6">
                        <label for="studentDateOfBirth" class="form-label">Date of Birth</label>
                        <input type="date" class="form-control" id="studentDateOfBirth" name="date_of_birth">
                    </div>
                    <div class="col-md-6">
                        <label for="studentGender" class="form-label">Gender</label>
                        <select class="form-select" id="studentGender" name="gender">
                            <option value="">Select Gender</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="studentPhoneNumber" class="form-label">Phone Number</label>
                        <input type="text" class="form-control" id="studentPhoneNumber" name="phone_number">
                    </div>
                    <div class="col-md-12">
                        <label for="studentAddress" class="form-label">Address</label>
                        <textarea class="form-control" id="studentAddress" name="address" rows="3"></textarea>
                    </div>
                    <div class="col-md-6">
                        <label for="enrollmentDate" class="form-label">Enrollment Date</label>
                        <input type="date" class="form-control" id="enrollmentDate" name="enrollment_date">
                    </div>
                    <div class="col-md-6">
                        <label for="gradeLevel" class="form-label">Grade Level</label>
                        <select class="form-select" id="gradeLevel" name="grade_level">
                            <option selected disabled value="">Please Select</option>
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                            <option value="5">5</option>
                            <option value="6">6</option>
                            <option value="7">7</option>
                            <option value="8">8</option>
                            <option value="9">9</option>
                            <option value="10">10</option>
                            <option value="11">11</option>
                            <option value="12">12</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer p-3 border-0 rounded-bottom-3">
                <button type="button" class="btn btn-outline-secondary px-4 me-2"
                    data-bs-dismiss="modal">Close</button>
                <button type="submit" id="saveStudentBtn" form="createStudentForm" class="btn btn-primary px-4">Save Student</button>
            </div>
        </div>
    </div>
</div>
