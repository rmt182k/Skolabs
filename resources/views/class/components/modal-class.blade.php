{{-- File: resources/views/class/components/modal-class.blade.php --}}
<div class="modal fade" id="classModal" tabindex="-1" aria-labelledby="classModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="classModalLabel">Add New Class</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="classForm" novalidate>
                    @csrf
                    {{-- Hidden input untuk ID saat edit dan untuk nama kelas yang digenerate --}}
                    <input type="hidden" id="classId" name="id">
                    <input type="hidden" id="generatedClassName" name="name">

                    <div class="row">
                        {{-- Dropdown untuk Tingkat Kelas (1-12) --}}
                        <div class="col-md-12 mb-3">
                            <label for="gradeLevel" class="form-label">Grade</label>
                            <select class="form-select" id="gradeLevel" name="grade_level" required>
                                <option value="">Select Grade</option>
                                {{-- Opsi 1-12 akan di-generate oleh JavaScript --}}
                            </select>
                            <div class="invalid-feedback">Please select a grade.</div>
                        </div>

                        {{-- Dropdown untuk Jenjang Pendidikan --}}
                        <div class="col-md-12 mb-3">
                            <label for="educationalLevelId" class="form-label">Educational Level</label>
                            <select class="form-select" id="educationalLevelId" name="educational_level_id" required>
                                <option value="">Select Level</option>
                                {{-- Opsi dimuat dari API --}}
                            </select>
                            <div class="invalid-feedback">Please select an educational level.</div>
                        </div>

                        {{-- Dropdown untuk Jurusan (tergantung Jenjang) --}}
                        <div class="col-md-12 mb-3">
                            <label for="majorId" class="form-label">Major</label>
                            <select class="form-select" id="majorId" name="major_id" required disabled>
                                <option value="">Select Level First</option>
                                {{-- Opsi dimuat dari API berdasarkan Jenjang --}}
                            </select>
                            <div class="invalid-feedback">Please select a major.</div>
                        </div>

                        {{-- Dropdown untuk Wali Kelas --}}
                        <div class="col-md-12 mb-3">
                            <label for="teacherId" class="form-label">Homeroom Teacher</label>
                            <select class="form-select" id="teacherId" name="teacher_id" required>
                                <option value="">Select a Teacher</option>
                                {{-- Opsi dimuat dari API --}}
                            </select>
                            <div class="invalid-feedback">Please select a homeroom teacher.</div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" id="saveClassBtn">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
