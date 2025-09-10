// File: public/assets/js/app/admin/admin.js

document.addEventListener('DOMContentLoaded', function () {
    // --- KONFIGURASI & VARIABEL GLOBAL ---
    const API_URL = '/api/admins';
    const csrfToken = $('meta[name="csrf-token"]').attr('content');
    const adminModal = new bootstrap.Modal(document.getElementById('adminModal'));

    // --- INISIALISASI DATATABLE ---
    const adminTable = $('#admins-datatable').DataTable({
        processing: true,
        ajax: {
            url: API_URL,
            dataSrc: 'data',
            error: function (xhr) {
                handleAjaxError('Failed to fetch admin data.', xhr);
            }
        },
        columns: [
            { data: null, searchable: false, orderable: false, render: (data, type, row, meta) => meta.row + 1 },
            { data: 'name', defaultContent: '-' },
            { data: 'email', defaultContent: '-' },
            { data: 'job_title', defaultContent: '-' },
            {
                data: 'status',
                render: function (data) {
                    if (data === 'active') {
                        return '<span class="badge bg-success-subtle text-success">Active</span>';
                    }
                    return '<span class="badge bg-danger-subtle text-danger">Inactive</span>';
                }
            },
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
        ],
        columnDefs: [
            { "width": "5%", "targets": 0 }, // Atur lebar kolom nomor
            { "width": "15%", "targets": 5 } // Atur lebar kolom aksi
        ]
    });

    // --- FUNGSI BANTUAN (HELPER FUNCTIONS) ---

    const showNotification = (icon, title, text = '') => {
        Swal.fire({ icon, title, text, timer: 2000, showConfirmButton: false });
    };

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
            input.addClass('is-invalid');
            input.next('.invalid-feedback').text(errors[field][0]);
        }
    };

    const resetForm = () => {
        $('#adminForm')[0].reset();
        $('#adminId').val('');
        clearValidationErrors();
        $('#adminStatus').val('active'); // Set default value
    };

    // --- EVENT LISTENERS ---

    $('#adminAddBtn').on('click', function () {
        resetForm();
        $('#adminModalLabel').text('👤 Add New Admin');
        adminModal.show();
    });

    $('#admins-datatable').on('click', '.edit-btn', function () {
        const adminId = $(this).data('id');
        $.getJSON(`${API_URL}/${adminId}`)
            .done(response => {
                if (response.success) {
                    resetForm();
                    const data = response.data;
                    $('#adminModalLabel').text('✏️ Edit Admin');
                    $('#adminId').val(data.id);
                    $('#adminName').val(data.name);
                    $('#adminEmail').val(data.email);
                    $('#adminJobTitle').val(data.job_title);
                    $('#adminStatus').val(data.status);
                    $('#adminPassword').val('');
                    adminModal.show();
                } else {
                    showNotification('error', 'Error!', response.message);
                }
            })
            .fail(xhr => handleAjaxError('Failed to fetch admin data.', xhr));
    });

    $('#admins-datatable').on('click', '.delete-btn', function () {
        const adminId = $(this).data('id');
        const adminName = $(this).data('name');

        Swal.fire({
            title: 'Are you sure?',
            text: `You are about to delete ${adminName}. This action cannot be undone!`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `${API_URL}/${adminId}`,
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': csrfToken },
                    success: (response) => {
                        showNotification('success', 'Deleted!', response.message);
                        adminTable.ajax.reload(null, false);
                    },
                    error: (xhr) => handleAjaxError('Failed to delete admin.', xhr)
                });
            }
        });
    });

    $('#adminForm').on('submit', function (e) {
        e.preventDefault();
        clearValidationErrors();

        const adminId = $('#adminId').val();
        let url = adminId ? `${API_URL}/${adminId}` : API_URL;

        let formData = new FormData(this);
        if (adminId) {
            formData.append('_method', 'PUT');
        }

        $('#saveAdminBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Saving...');

        $.ajax({
            url: url,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: { 'X-CSRF-TOKEN': csrfToken },
            success: function (response) {
                showNotification('success', 'Success!', response.message);
                adminModal.hide();
                adminTable.ajax.reload();
            },
            error: function (xhr) {
                if (xhr.status === 422) {
                    displayValidationErrors(xhr.responseJSON.errors);
                } else {
                    handleAjaxError('Failed to save admin data.', xhr);
                }
            },
            complete: function () {
                $('#saveAdminBtn').prop('disabled', false).text('Save Admin');
            }
        });
    });
});
