document.addEventListener('DOMContentLoaded', function () {
    // --- KONFIGURASI & VARIABEL GLOBAL ---
    const API_URL = '/api/teachers';
    const csrfToken = $('meta[name="csrf-token"]').attr('content');
    const teacherModal = new bootstrap.Modal(document.getElementById('teacherModal'));

    // --- INISIALISASI ---
    // DIHAPUS: Inisialisasi Flatpickr tidak diperlukan lagi.
    // const dateOfBirthPicker = flatpickr(...);

    const teacherTable = $('#teachers-datatable').DataTable({
        processing: true,
        ajax: {
            url: API_URL,
            dataSrc: 'data',
            error: (xhr) => handleAjaxError('Failed to fetch teacher data.', xhr)
        },
        columns: [
            { data: null, searchable: false, orderable: false, render: (data, type, row, meta) => meta.row + 1 },
            { data: 'name', defaultContent: '-' },
            { data: 'employee_id', defaultContent: '-' },
            { data: 'phone_number', defaultContent: '-' },
            {
                data: 'status',
                render: (data) => data === 'active'
                    ? '<span class="badge bg-success-subtle text-success">Active</span>'
                    : '<span class="badge bg-danger-subtle text-danger">Inactive</span>'
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
    const resetForm = () => {
        $('#teacherForm')[0].reset();
        $('#teacherId').val('');
        clearValidationErrors();
        // DIUBAH: Menggunakan jQuery untuk mengosongkan nilai input tanggal
        $('#teacherDateOfBirth').val('');
    };

    // --- EVENT LISTENERS ---
    $('#teacherAddBtn').on('click', () => {
        resetForm();
        $('#teacherModalLabel').text('👤 Add New Teacher');
        teacherModal.show();
    });

    $('#teachers-datatable').on('click', '.edit-btn', function () {
        const teacherId = $(this).data('id');
        $.getJSON(`${API_URL}/${teacherId}`)
            .done(response => {
                if (response.success) {
                    resetForm();
                    const data = response.data;
                    $('#teacherModalLabel').text('✏️ Edit Teacher');
                    $('#teacherId').val(data.id);
                    $('#teacherName').val(data.name);
                    $('#teacherEmail').val(data.email);
                    $('#teacherEmployeeId').val(data.employee_id);
                    // DIUBAH: Menggunakan jQuery untuk mengisi nilai input tanggal
                    $('#teacherDateOfBirth').val(data.date_of_birth);
                    $('#teacherPhoneNumber').val(data.phone_number);
                    $('#teacherAddress').val(data.address);
                    $('#teacherGender').val(data.gender);
                    $('#teacherStatus').val(data.status);
                    teacherModal.show();
                } else {
                    showNotification('error', 'Error!', response.message);
                }
            })
            .fail(xhr => handleAjaxError('Failed to fetch teacher data.', xhr));
    });

    $('#teachers-datatable').on('click', '.delete-btn', function () {
        const teacherId = $(this).data('id');
        const teacherName = $(this).data('name');
        Swal.fire({
            title: 'Are you sure?', text: `You are about to delete ${teacherName}.`, icon: 'warning',
            showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `${API_URL}/${teacherId}`, method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrfToken },
                    success: (response) => {
                        showNotification('success', 'Deleted!', response.message);
                        teacherTable.ajax.reload(null, false);
                    },
                    error: (xhr) => handleAjaxError('Failed to delete teacher.', xhr)
                });
            }
        });
    });

    $('#teacherForm').on('submit', function (e) {
        e.preventDefault();
        clearValidationErrors();
        const teacherId = $('#teacherId').val();
        let url = teacherId ? `${API_URL}/${teacherId}` : API_URL;
        let formData = new FormData(this);
        if (teacherId) formData.append('_method', 'PUT');

        $('#saveTeacherBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Saving...');

        $.ajax({
            url: url, method: 'POST', data: formData, processData: false, contentType: false, headers: { 'X-CSRF-TOKEN': csrfToken },
            success: (response) => {
                showNotification('success', 'Success!', response.message);
                teacherModal.hide();
                teacherTable.ajax.reload();
            },
            error: (xhr) => {
                if (xhr.status === 422) displayValidationErrors(xhr.responseJSON.errors);
                else handleAjaxError('Failed to save teacher data.', xhr);
            },
            complete: () => $('#saveTeacherBtn').prop('disabled', false).text('Save Teacher')
        });
    });
});
