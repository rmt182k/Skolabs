document.addEventListener('DOMContentLoaded', function () {
    // API endpoint
    const API_URL = '/api/staffs';

    // Initialize DataTable
    const staffTable = $('#staff-datatable').DataTable({
        processing: true,
        ajax: {
            url: API_URL,
            dataSrc: function (json) {
                if (!json.success) {
                    Swal.fire({ icon: 'error', title: 'Error', text: json.message || 'Failed to load staff data.' });
                    return [];
                }
                return json.data || [];
            },
            error: function (xhr) {
                Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to fetch staff data: ' + xhr.statusText });
            }
        },
        columns: [
            { data: 'id' },
            { data: 'name', defaultContent: '-' },
            { data: 'email', defaultContent: '-' },
            { data: 'position', defaultContent: '-' },
            { data: 'status', defaultContent: '-' },
            {
                data: null,
                orderable: false,
                render: function (data, type, row) {
                    return `
                        <button class="btn btn-sm btn-warning edit-btn" data-id="${row.id}"><i class="fas fa-edit"></i> Edit</button>
                        <button class="btn btn-sm btn-danger delete-btn" data-id="${row.id}" data-name="${row.name}"><i class="fas fa-trash"></i> Delete</button>
                    `;
                }
            }
        ]
    });

    // Handle "Add New Staff" button click
    $('#staffAddBtn').on('click', function () {
        $('#staffForm')[0].reset();
        $('#staffId').val('');
        $('#staffModalLabel').text('👤 Add New Staff');
        $('#staffModal').modal('show');
    });

    // Handle "Edit" button click
    $('#staff-datatable').on('click', '.edit-btn', function () {
        const staffId = $(this).data('id');
        $.ajax({
            url: `${API_URL}/${staffId}`,
            method: 'GET',
            success: function (response) {
                if (response.success) {
                    const data = response.data;
                    $('#staffModalLabel').text('✏️ Edit Staff');
                    $('#staffId').val(data.id);
                    $('#staffName').val(data.name);
                    $('#staffEmail').val(data.email);
                    $('#staffPosition').val(data.position);
                    $('#staffStatus').val(data.status);
                    $('#staffPassword').val('');
                    $('#staffModal').modal('show');
                } else {
                    Swal.fire('Error!', response.message, 'error');
                }
            },
            error: function () {
                Swal.fire('Error!', 'Failed to fetch staff data.', 'error');
            }
        });
    });

    // Handle "Delete" button click
    $('#staff-datatable').on('click', '.delete-btn', function () {
        const staffId = $(this).data('id');
        const staffName = $(this).data('name');
        Swal.fire({
            title: 'Are you sure?',
            text: `You are about to delete ${staffName}. This action cannot be undone!`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `${API_URL}/${staffId}`,
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    success: function (response) {
                        if (response.success) {
                            Swal.fire('Deleted!', response.message, 'success');
                            staffTable.ajax.reload(null, false);
                        } else {
                            Swal.fire('Failed!', response.message, 'error');
                        }
                    },
                    error: function () {
                        Swal.fire('Error!', 'An error occurred while deleting the staff member.', 'error');
                    }
                });
            }
        });
    });

    // Handle Form Submit
    $('#staffForm').on('submit', function (e) {
        e.preventDefault();
        const staffId = $('#staffId').val();
        const method = staffId ? 'PUT' : 'POST';
        const url = staffId ? `${API_URL}/${staffId}` : API_URL;

        $.ajax({
            url: url,
            method: method,
            data: $(this).serialize(),
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function (response) {
                if (response.success) {
                    $('#staffModal').modal('hide');
                    Swal.fire('Success!', response.message, 'success');
                    staffTable.ajax.reload();
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
                    Swal.fire('Error!', 'Failed to save staff data.', 'error');
                }
            }
        });
    });
});
