{{-- File: resources/views/student/components/modal-student.blade.php --}}

<div class="modal fade" id="studentModal" tabindex="-1" aria-labelledby="studentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-3">
            <div class="modal-header bg-primary text-white border-0 rounded-top-3 p-4">
                <h5 class="modal-title fw-bold" id="studentModalLabel"></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="createStudentForm" class="row g-3">
                    <input type="hidden" id="studentId">

                    {{-- Baris 1: Name & Email --}}
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

                    {{-- Baris 2: NISN & Password --}}
                    <div class="col-md-6">
                        <label for="studentNisn" class="form-label">NISN</label>
                        <input type="text" class="form-control" id="studentNisn" name="nisn" required
                            maxlength="20">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-6">
                        <label for="studentPassword" class="form-label">Password</label>
                        <input type="password" class="form-control" id="studentPassword" name="password"
                            autocomplete="new-password">
                        <div class="form-text text-muted">Biarkan kosong untuk default atau tidak mengubah.</div>
                        <div class="invalid-feedback"></div>
                    </div>

                    {{-- Baris 3: Date of Birth & Gender --}}
                    <div class="col-md-6">
                        <label for="studentDateOfBirth" class="form-label">Date of Birth</label>
                        <input type="date" class="form-control" id="studentDateOfBirth" name="date_of_birth">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-6">
                        <label for="studentGender" class="form-label">Gender</label>
                        <select class="form-select" id="studentGender" name="gender">
                            <option value="">Select Gender</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>

                    {{-- Baris 4: Phone & Enrollment Date --}}
                    <div class="col-md-6">
                        <label for="studentPhoneNumber" class="form-label">Phone Number</label>
                        <input type="text" class="form-control" id="studentPhoneNumber" name="phone_number">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-6">
                        <label for="studentEnrollmentDate" class="form-label">Enrollment Date</label>
                        <input type="date" class="form-control" id="studentEnrollmentDate" name="enrollment_date">
                        <div class="invalid-feedback"></div>
                    </div>

                    {{-- Baris 5: Grade & Education Level --}}
                    <div class="col-md-6">
                        <label for="studentGradeLevel" class="form-label">Grade Level</label>
                        <select class="form-select" id="studentGradeLevel" name="grade_level" required>
                            <option value="">Select Grade</option>
                            @for ($i = 1; $i <= 12; $i++)
                                <option value="{{ $i }}">{{ $i }}</option>
                            @endfor
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-6">
                        <label for="studentMajorLevel" class="form-label">Education Level</label>
                        <select class="form-select" id="studentMajorLevel" name="educational_level_id" required>
                            <option value="">Select Level</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>

                    {{-- Baris 6: Major & Status --}}
                    <div class="col-md-6">
                        <label for="studentMajorField" class="form-label">Major</label>
                        <select class="form-select" id="studentMajorField" name="major_id" disabled>
                            <option value="">Select Major</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="studentStatus" class="form-label">Status</label>
                        <select class="form-select" id="studentStatus" name="status" required>
                            <option value="active">Active</option>
                            <option value="graduated">Graduated</option>
                            <option value="dropout">Dropout</option>
                            <option value="suspended">Suspended</option>
                            <option value="transferred">Transferred</option>
                            <option value="on_leave">On Leave</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                    {{-- Baris 7: Address --}}
                    <div class="col-md-12">
                        <label for="studentAddress" class="form-label">Address</label>
                        <textarea class="form-control" id="studentAddress" name="address" rows="3"></textarea>
                    </div>

                </form>
            </div>
            <div class="modal-footer p-3 border-0 rounded-bottom-3">
                <button type="button" class="btn btn-outline-secondary px-4 me-2"
                    data-bs-dismiss="modal">Close</button>
                <button type="submit" id="saveStudentBtn" form="createStudentForm"
                    class="btn btn-primary px-4">Save Student</button>
            </div>
        </div>
    </div>
</div>
