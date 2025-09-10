// File: public/assets/js/app/staff/staff.js

document.addEventListener('DOMContentLoaded', function () {
    // --- KONFIGURASI & VARIABEL GLOBAL ---
    const API_URL = '/api/staffs';
    const csrfToken = $('meta[name="csrf-token"]').attr('content');
    const staffModal = new bootstrap.Modal(document.getElementById('staffModal'));

    // --- INISIALISASI DATATABLE ---
    const staffTable = $('#staff-datatable').DataTable({
        processing: true,
        ajax: {
            url: API_URL,
            dataSrc: 'data',
            error: (xhr) => handleAjaxError('Failed to fetch staff data.', xhr)
        },
        columns: [
            { data: null, searchable: false, orderable: false, render: (data, type, row, meta) => meta.row + 1 },
            { data: 'name', defaultContent: '-' },
            { data: 'position', defaultContent: '-' },
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

    // --- FUNGSI BANTUAN (HELPER FUNCTIONS) ---
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
        $('#staffForm')[0].reset();
        $('#staffId').val('');
        clearValidationErrors();
    };

    // --- EVENT LISTENERS ---
    $('#staffAddBtn').on('click', () => {
        resetForm();
        $('#staffModalLabel').text('👤 Add New Staff');
        staffModal.show();
    });

    $('#staff-datatable').on('click', '.edit-btn', function () {
        const staffId = $(this).data('id');
        $.getJSON(`${API_URL}/${staffId}`)
            .done(response => {
                if (response.success) {
                    resetForm();
                    const data = response.data;
                    $('#staffModalLabel').text('✏️ Edit Staff');
                    $('#staffId').val(data.id);
                    $('#staffName').val(data.name);
                    $('#staffEmail').val(data.email);
                    $('#staffPosition').val(data.position);
                    $('#staffStatus').val(data.status);
                    staffModal.show();
                } else {
                    showNotification('error', 'Error!', response.message);
                }
            })
            .fail(xhr => handleAjaxError('Failed to fetch staff data.', xhr));
    });

    $('#staff-datatable').on('click', '.delete-btn', function () {
        const staffId = $(this).data('id');
        const staffName = $(this).data('name');
        Swal.fire({
            title: 'Are you sure?',
            text: `You are about to delete ${staffName}. This can't be undone!`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `${API_URL}/${staffId}`, method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrfToken },
                    success: (response) => {
                        showNotification('success', 'Deleted!', response.message);
                        staffTable.ajax.reload(null, false);
                    },
                    error: (xhr) => handleAjaxError('Failed to delete staff.', xhr)
                });
            }
        });
    });

    $('#staffForm').on('submit', function (e) {
        e.preventDefault();
        clearValidationErrors();
        const staffId = $('#staffId').val();
        let url = staffId ? `${API_URL}/${staffId}` : API_URL;
        let formData = new FormData(this);
        if (staffId) formData.append('_method', 'PUT');

        $('#saveStaffBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Saving...');

        $.ajax({
            url: url, method: 'POST', data: formData, processData: false, contentType: false, headers: { 'X-CSRF-TOKEN': csrfToken },
            success: (response) => {
                showNotification('success', 'Success!', response.message);
                staffModal.hide();
                staffTable.ajax.reload();
            },
            error: (xhr) => {
                if (xhr.status === 422) displayValidationErrors(xhr.responseJSON.errors);
                else handleAjaxError('Failed to save staff data.', xhr);
            },
            complete: () => $('#saveStaffBtn').prop('disabled', false).text('Save Staff')
        });
    });
});
