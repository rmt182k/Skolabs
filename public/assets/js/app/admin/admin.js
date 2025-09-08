document.addEventListener('DOMContentLoaded', function () {
    // API endpoint
    const API_URL = '/api/admins';

    // Initialize DataTable
    const adminTable = $('#admins-datatable').DataTable({
        processing: true,
        ajax: {
            url: API_URL,
            dataSrc: function (json) {
                if (!json.success) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: json.message || 'Failed to load admin data.'
                    });
                    return [];
                }
                return json.data || [];
            },
            error: function (xhr, error, thrown) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to fetch admin data: ' + (xhr.statusText || thrown),
                });
            }
        },
        columns: [{
            data: 'id'
        },
        {
            data: 'name',
            defaultContent: '-'
        },
        {
            data: 'email',
            defaultContent: '-'
        },
        {
            data: 'job_title',
            defaultContent: '-'
        },
        {
            data: 'status',
            defaultContent: '-'
        },
        {
            data: null,
            orderable: false,
            render: function (data, type, row) {
                return `
    <button class="btn btn-sm btn-warning edit-btn" data-id="${row.id}">
        <i class="fas fa-edit"></i> Edit
    </button>
    <button class="btn btn-sm btn-danger delete-btn" data-id="${row.id}" data-name="${row.name}">
        <i class="fas fa-trash"></i> Delete
    </button>
    `;
            }
        }
        ]
    });

    // Handle "Add New Admin" button click
    $('#adminAddBtn').on('click', function () {
        $('#adminForm')[0].reset();
        $('#adminId').val('');
        $('#adminModalLabel').text('👤 Add New Admin');
        $('#adminModal').modal('show');
    });

    // Handle "Edit" button click
    $(document).on('click', '.edit-btn', function () {
        const adminId = $(this).data('id');

        $.ajax({
            url: `${API_URL}/${adminId}`,
            method: 'GET',
            success: function (response) {
                if (response.success) {
                    const data = response.data;
                    $('#adminModalLabel').text('✏️ Edit Admin');
                    $('#adminId').val(data.id);
                    $('#adminName').val(data.name);
                    $('#adminEmail').val(data.email);
                    $('#adminJobTitle').val(data.job_title);
                    $('#adminStatus').val(data.status);
                    $('#adminPassword').val(''); // Clear password field
                    $('#adminModal').modal('show');
                } else {
                    Swal.fire('Error!', response.message || 'Admin data not found.',
                        'error');
                }
            },
            error: function () {
                Swal.fire('Error!', 'Failed to fetch admin data.', 'error');
            }
        });
    });

    // Handle "Delete" button click
    $(document).on('click', '.delete-btn', function () {
        const adminId = $(this).data('id');
        const adminName = $(this).data('name');

        Swal.fire({
            title: 'Are you sure?',
            text: `You are about to delete ${adminName}. This action cannot be undone!`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `${API_URL}/${adminId}`,
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (response) {
                        if (response.success) {
                            Swal.fire('Deleted!', 'The admin has been deleted.',
                                'success');
                            adminTable.ajax.reload(null, false);
                        } else {
                            Swal.fire('Failed!', response.message ||
                                'Failed to delete admin.', 'error');
                        }
                    },
                    error: function () {
                        Swal.fire('Error!',
                            'An error occurred while deleting the admin.',
                            'error');
                    }
                });
            }
        });
    });

    // Handle Form Submit
    $('#adminForm').on('submit', function (e) {
        e.preventDefault();
        const adminId = $('#adminId').val();
        const method = adminId ? 'PUT' : 'POST';
        const url = adminId ? `${API_URL}/${adminId}` : API_URL;

        $.ajax({
            url: url,
            method: method,
            data: $(this).serialize(),
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                if (response.success) {
                    $('#adminModal').modal('hide');
                    Swal.fire('Success!', response.message, 'success');
                    adminTable.ajax.reload();
                } else {
                    Swal.fire('Error!', response.message, 'error');
                }
            },
            error: function (xhr) {
                const errors = xhr.responseJSON?.errors;
                if (errors) {
                    let errorMsg = 'Please fix the following errors:\n';
                    Object.values(errors).forEach(err => errorMsg += `- ${err[0]}\n`);
                    Swal.fire('Validation Error!', errorMsg, 'error');
                } else {
                    Swal.fire('Error!', 'Failed to save admin data.', 'error');
                }
            }
        });
    });
});
