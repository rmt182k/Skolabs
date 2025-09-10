document.addEventListener('DOMContentLoaded', function () {
    // --- KONFIGURASI & VARIABEL GLOBAL ---
    const API_URL = '/api/students';
    const EDUCATIONAL_LEVELS_API_URL = '/api/educational-levels';
    const MAJORS_API_URL = '/api/majors';
    const csrfToken = $('meta[name="csrf-token"]').attr('content');
    const studentModal = new bootstrap.Modal(document.getElementById('studentModal'));

    // --- INISIALISASI ---

    // DIHAPUS: Inisialisasi Flatpickr tidak diperlukan lagi.
    // const dateOfBirthPicker = flatpickr(...);
    // const enrollmentDatePicker = flatpickr(...);

    // Inisialisasi DataTable
    const studentTable = $('#studentTable').DataTable({
        processing: true,
        ajax: {
            url: API_URL,
            dataSrc: 'data',
            error: (xhr) => handleAjaxError('Failed to fetch student data.', xhr)
        },
        columns: [
            { data: null, searchable: false, orderable: false, render: (data, type, row, meta) => meta.row + 1 },
            { data: 'nisn', defaultContent: '-' },
            { data: 'name', defaultContent: '-' },
            { data: 'grade_level', defaultContent: '-' },
            {
                data: 'status',
                render: function (data) {
                    const statusMap = {
                        'active': { 'class': 'success', 'text': 'Active' },
                        'graduated': { 'class': 'primary', 'text': 'Graduated' },
                        'dropout': { 'class': 'danger', 'text': 'Dropout' },
                        'suspended': { 'class': 'warning', 'text': 'Suspended' },
                        'transferred': { 'class': 'info', 'text': 'Transferred' },
                        'on_leave': { 'class': 'secondary', 'text': 'On Leave' }
                    };
                    const status = statusMap[data] || { 'class': 'light', 'text': 'Unknown' };
                    return `<span class="badge bg-${status.class}-subtle text-${status.class}">${status.text}</span>`;
                }
            },
            {
                data: null, orderable: false, searchable: false,
                render: (data) => `
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-warning edit-btn" data-id="${data.id}"><i class="fas fa-edit"></i> Edit</button>
                        <button class="btn btn-sm btn-danger delete-btn" data-id="${data.id}" data-name="${data.name}"><i class="fas fa-trash"></i> Delete</button>
                    </div>
                `
            }
        ]
    });

    // --- FUNGSI BANTUAN ---
    const showNotification = (icon, title, text = '') => Swal.fire({ icon, title, text, timer: 2000, showConfirmButton: false });
    const handleAjaxError = (defaultMessage, xhr = null) => {
        const message = xhr?.responseJSON?.message || defaultMessage;
        showNotification('error', 'An Error Occurred', message);
        console.error('AJAX Error:', xhr);
    };
    const clearValidationErrors = () => {
        $('.form-control, .form-select').removeClass('is-invalid');
        $('.invalid-feedback').text('');
    };
    const displayValidationErrors = (errors) => {
        clearValidationErrors();
        for (const field in errors) {
            const input = $(`[name="${field}"]`);
            input.addClass('is-invalid').next('.invalid-feedback').text(errors[field][0]);
        }
    };
    const populateDropdown = (selector, data, defaultOption) => {
        const dropdown = $(selector);
        dropdown.html(`<option value="">${defaultOption}</option>`);
        data.forEach(item => dropdown.append($('<option>', { value: item.id, text: item.name })));
    };
    const resetForm = () => {
        $('#createStudentForm')[0].reset();
        $('#studentId').val('');
        clearValidationErrors();
        // DIUBAH: Menggunakan jQuery untuk mengosongkan nilai input tanggal
        $('#studentDateOfBirth').val('');
        $('#studentEnrollmentDate').val('');
        $('#studentMajorField').prop('disabled', true).html('<option value="">Select Major</option>');
        $('#studentStatus').val('active');
    };

    // --- FUNGSI UTAMA ---
    function fetchEducationalLevels(selectedLevelId = null, majorId = null) {
        $.getJSON(EDUCATIONAL_LEVELS_API_URL)
            .done(response => {
                if (response.success) {
                    populateDropdown('#studentMajorLevel', response.data, 'Select Level');
                    if (selectedLevelId) $('#studentMajorLevel').val(selectedLevelId).trigger('change', [majorId]);
                }
            })
            .fail(xhr => handleAjaxError('Failed to load educational levels.', xhr));
    }

    function fetchMajors(levelId, selectedMajorId = null) {
        if (!levelId) {
            $('#studentMajorField').prop('disabled', true).html('<option value="">Select Major</option>');
            return;
        }
        $.getJSON(`${MAJORS_API_URL}?educational_level_id=${levelId}`)
            .done(response => {
                if (response.success && response.data.length > 0) {
                    populateDropdown('#studentMajorField', response.data, 'Select Major');
                    $('#studentMajorField').prop('disabled', false);
                    if (selectedMajorId) $('#studentMajorField').val(selectedMajorId);
                } else {
                    $('#studentMajorField').prop('disabled', true).html('<option value="">No majors available</option>');
                }
            })
            .fail(xhr => handleAjaxError('Failed to load majors.', xhr));
    }

    // --- EVENT LISTENERS ---
    $('#studentAddBtn').on('click', () => {
        resetForm();
        $('#studentModalLabel').text('👤 Add New Student');
        fetchEducationalLevels();
        studentModal.show();
    });

    $('#studentTable').on('click', '.edit-btn', function () {
        const studentId = $(this).data('id');
        $.getJSON(`${API_URL}/${studentId}`)
            .done(response => {
                if (response.success) {
                    resetForm();
                    const data = response.data;
                    $('#studentModalLabel').text('✏️ Edit Student');
                    $('#studentId').val(data.id);
                    $('#studentName').val(data.name);
                    $('#studentEmail').val(data.email);
                    $('#studentNisn').val(data.nisn);
                    $('#studentGender').val(data.gender);
                    $('#studentPhoneNumber').val(data.phone_number);
                    $('#studentAddress').val(data.address);
                    $('#studentGradeLevel').val(data.grade_level);
                    $('#studentStatus').val(data.status);
                    // DIUBAH: Menggunakan jQuery untuk mengisi nilai input tanggal
                    $('#studentDateOfBirth').val(data.date_of_birth);
                    $('#studentEnrollmentDate').val(data.enrollment_date);
                    const levelId = data.major ? data.major.educational_level_id : null;
                    const majorId = data.major ? data.major.id : null;
                    fetchEducationalLevels(levelId, majorId);
                    studentModal.show();
                } else {
                    showNotification('error', 'Error!', response.message);
                }
            })
            .fail(xhr => handleAjaxError('Failed to fetch student data.', xhr));
    });

    $('#studentTable').on('click', '.delete-btn', function () {
        const studentId = $(this).data('id');
        const studentName = $(this).data('name');
        Swal.fire({
            title: 'Are you sure?', text: `You are about to delete ${studentName}.`, icon: 'warning',
            showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `${API_URL}/${studentId}`, method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrfToken },
                    success: (response) => {
                        showNotification('success', 'Deleted!', response.message);
                        studentTable.ajax.reload(null, false);
                    },
                    error: (xhr) => handleAjaxError('Failed to delete student.', xhr)
                });
            }
        });
    });

    $('#studentMajorLevel').on('change', function (event, majorId) {
        fetchMajors($(this).val(), majorId);
    });

    $('#createStudentForm').on('submit', function (e) {
        e.preventDefault();
        clearValidationErrors();
        const studentId = $('#studentId').val();
        let url = studentId ? `${API_URL}/${studentId}` : API_URL;
        let formData = new FormData(this);
        if (studentId) formData.append('_method', 'PUT');

        $('#saveStudentBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Saving...');

        $.ajax({
            url: url, method: 'POST', data: formData, processData: false, contentType: false, headers: { 'X-CSRF-TOKEN': csrfToken },
            success: (response) => {
                showNotification('success', 'Success!', response.message);
                studentModal.hide();
                studentTable.ajax.reload();
            },
            error: (xhr) => {
                if (xhr.status === 422) displayValidationErrors(xhr.responseJSON.errors);
                else handleAjaxError('Failed to save student data.', xhr);
            },
            complete: () => $('#saveStudentBtn').prop('disabled', false).text('Save Student')
        });
    });
});
