document.addEventListener('DOMContentLoaded', function () {
    // --- KONFIGURASI & VARIABEL GLOBAL ---
    const API_URL = '/api/students';
    const EDUCATIONAL_LEVELS_API_URL = '/api/educational-levels';
    const MAJORS_API_URL = '/api/majors';
    const csrfToken = $('meta[name="csrf-token"]').attr('content');
    const studentModal = new bootstrap.Modal(document.getElementById('studentModal'));

    // --- INISIALISASI ---

    // Inisialisasi Flatpickr untuk input tanggal
    const dateOfBirthPicker = flatpickr("#studentDateOfBirth", {
        altInput: true,
        altFormat: "F j, Y",
        dateFormat: "Y-m-d",
        maxDate: "today" // Siswa tidak mungkin lahir di masa depan
    });

    const enrollmentDatePicker = flatpickr("#studentEnrollmentDate", {
        altInput: true,
        altFormat: "F j, Y",
        dateFormat: "Y-m-d",
        defaultDate: "today"
    });

    // Inisialisasi DataTable
    const studentTable = $('#studentTable').DataTable({
        processing: true,
        serverSide: false, // Jika data tidak terlalu besar, false lebih cepat
        ajax: {
            url: API_URL,
            dataSrc: 'data',
            error: function (xhr, error, thrown) {
                handleAjaxError('Failed to fetch student data.', xhr);
            }
        },
        columns: [
            { data: null, searchable: false, orderable: false, render: (data, type, row, meta) => meta.row + 1 },
            { data: 'nisn', defaultContent: '-' },
            { data: 'name', defaultContent: '-' },
            { data: 'gender', render: (data) => data ? data.charAt(0).toUpperCase() + data.slice(1) : '-' },
            { data: 'grade_level', defaultContent: '-' },
            { data: 'enrollment_date', defaultContent: '-' },
            {
                data: null,
                orderable: false,
                searchable: false,
                render: function (data, type, row) {
                    return `
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-warning edit-btn" data-id="${row.id}">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="btn btn-sm btn-danger delete-btn" data-id="${row.id}" data-name="${row.name}">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </div>
                    `;
                }
            }
        ]
    });

    // --- FUNGSI BANTUAN (HELPER FUNCTIONS) ---

    // Menampilkan notifikasi sukses atau error
    const showNotification = (icon, title, text = '') => {
        Swal.fire({ icon, title, text, timer: 2000, showConfirmButton: false });
    };

    // Menangani error dari request AJAX
    const handleAjaxError = (defaultMessage, xhr = null) => {
        let message = defaultMessage;
        if (xhr && xhr.responseJSON && xhr.responseJSON.message) {
            message = xhr.responseJSON.message;
        }
        showNotification('error', 'An Error Occurred', message);
        console.error('AJAX Error:', xhr);
    };

    // Membersihkan pesan error validasi pada form
    const clearValidationErrors = () => {
        $('.form-control, .form-select').removeClass('is-invalid');
        $('.invalid-feedback').text('');
    };

    // Menampilkan pesan error validasi pada form
    const displayValidationErrors = (errors) => {
        clearValidationErrors();
        for (const field in errors) {
            const input = $(`[name="${field}"]`);
            const errorContainer = input.next('.invalid-feedback');
            input.addClass('is-invalid');
            if (errorContainer.length) {
                errorContainer.text(errors[field][0]);
            }
        }
    };

    // Mereset form ke kondisi awal
    const resetForm = () => {
        $('#createStudentForm')[0].reset();
        $('#studentId').val('');
        clearValidationErrors();
        $('#studentMajorField').prop('disabled', true).html('<option value="">Select Major</option>');
    };

    // Mengisi dropdown
    const populateDropdown = (selector, data, defaultOption, valueField = 'id', textField = 'name') => {
        const dropdown = $(selector);
        dropdown.html(`<option value="">${defaultOption}</option>`);
        data.forEach(item => {
            dropdown.append($('<option>', { value: item[valueField], text: item[textField] }));
        });
    };

    // --- FUNGSI UTAMA ---

    function fetchEducationalLevels(selectedLevelId = null, majorId = null) {
        $.getJSON(EDUCATIONAL_LEVELS_API_URL)
            .done(response => {
                if (response.success) {
                    populateDropdown('#studentMajorLevel', response.data, 'Select Level');
                    if (selectedLevelId) {
                        $('#studentMajorLevel').val(selectedLevelId).trigger('change', [majorId]);
                    }
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
                    if (selectedMajorId) {
                        $('#studentMajorField').val(selectedMajorId);
                    }
                } else {
                    $('#studentMajorField').prop('disabled', true).html('<option value="">No majors available</option>');
                }
            })
            .fail(xhr => handleAjaxError('Failed to load majors.', xhr));
    }

    // --- EVENT LISTENERS ---

    $('#studentAddBtn').on('click', function () {
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

                    // Set tanggal menggunakan instance Flatpickr
                    dateOfBirthPicker.setDate(data.date_of_birth, true);
                    enrollmentDatePicker.setDate(data.enrollment_date, true);

                    // Panggil fetch level dengan major yang sudah dipilih
                    const educationalLevelId = data.major ? data.major.educational_level_id : null;
                    const majorId = data.major ? data.major.id : null;
                    fetchEducationalLevels(educationalLevelId, majorId);

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
            title: 'Are you sure?',
            text: `You are about to delete ${studentName}. This action cannot be undone!`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `${API_URL}/${studentId}`,
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': csrfToken },
                    success: function (response) {
                        showNotification('success', 'Deleted!', response.message);
                        studentTable.ajax.reload(null, false);
                    },
                    error: function (xhr) {
                        handleAjaxError('Failed to delete student.', xhr);
                    }
                });
            }
        });
    });

    $('#studentMajorLevel').on('change', function (event, majorId) {
        const levelId = $(this).val();
        fetchMajors(levelId, majorId);
    });

    $('#createStudentForm').on('submit', function (e) {
        e.preventDefault();
        clearValidationErrors();

        const studentId = $('#studentId').val();
        const method = studentId ? 'POST' : 'POST'; // Laravel handle PUT/PATCH via _method
        let url = studentId ? `${API_URL}/${studentId}` : API_URL;

        let formData = new FormData(this);
        if (studentId) {
            formData.append('_method', 'PUT');
        }

        $('#saveStudentBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Saving...');

        $.ajax({
            url: url,
            method: method,
            data: formData,
            processData: false,
            contentType: false,
            headers: { 'X-CSRF-TOKEN': csrfToken },
            success: function (response) {
                showNotification('success', 'Success!', response.message);
                studentModal.hide();
                studentTable.ajax.reload();
            },
            error: function (xhr) {
                if (xhr.status === 422) {
                    displayValidationErrors(xhr.responseJSON.errors);
                } else {
                    handleAjaxError('Failed to save student data.', xhr);
                }
            },
            complete: function () {
                $('#saveStudentBtn').prop('disabled', false).text('Save Student');
            }
        });
    });
});
