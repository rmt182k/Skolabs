document.addEventListener('DOMContentLoaded', function () {
    // API endpoint
    const API_URL = '/api/teachers';

    // Initialize DataTable
    if ($.fn.DataTable.isDataTable('#teachers-datatable')) {
        $('#teachers-datatable').DataTable().destroy();
    }

    const teacherTable = $('#teachers-datatable').DataTable({
        // ... (other DataTable configurations)
        ...getTableConfig('#teachers-datatable'),
        processing: true,
        serverSide: true,
        ajax: {
            url: API_URL,
            dataSrc: function (json) {
                if (!json.success) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: json.message || 'Failed to load teacher data.'
                    });
                    return [];
                }
                return json.data || [];
            },
            error: function (xhr, error, thrown) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to fetch teacher data: ' + (xhr.statusText || thrown),
                });
                console.error('DataTable AJAX error:', xhr, error, thrown);
            }
        },
        columns: [
            { data: 'id', name: 'id' },
            { data: 'user.name', name: 'user.name', defaultContent: '-' },
            { data: 'employee_id', name: 'employee_id', defaultContent: '-' },
            { data: 'date_of_birth', name: 'date_of_birth', defaultContent: '-' },
            { data: 'phone_number', name: 'phone_number', defaultContent: '-' },
            { data: 'gender', name: 'gender', defaultContent: '-' },
            { data: 'status', name: 'status', defaultContent: '-' },
            {
                data: null,
                orderable: false,
                render: function (data, type, row) {
                    return `
                        <button class="btn btn-sm btn-warning edit-btn"
                            data-id="${row.id}"
                            data-user-id="${row.user_id}"
                            data-employee-id="${row.employee_id}"
                            data-date-of-birth="${row.date_of_birth}"
                            data-phone-number="${row.phone_number}"
                            data-address="${row.address}"
                            data-gender="${row.gender}"
                            data-status="${row.status}">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button class="btn btn-sm btn-danger delete-btn"
                            data-id="${row.id}"
                            data-name="${row.user ? row.user.name : 'Unknown'}">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    `;
                }
            }
        ]
    });

    // Handle "Add Teacher" button click
    $('#teacherAddBtn').on('click', function () {
        $('#teacherModalLabel').text('👤 Add New Teacher');
        $('#teacher_id').val(''); // Clear hidden ID
        $('#teacherForm')[0].reset(); // Reset the form
        $('#teacherModal').modal('show');
    });

    // Handle "Edit" button click
    $(document).on('click', '.edit-btn', function () {
        const data = $(this).data();
        $('#teacherModalLabel').text('✏️ Edit Teacher');
        $('#teacher_id').val(data.id);
        $('#user_id').val(data.userId);
        $('#employee_id').val(data.employeeId);
        $('#date_of_birth').val(data.dateOfBirth);
        $('#phone_number').val(data.phoneNumber);
        $('#address').val(data.address);
        $('#gender').val(data.gender);
        $('#status').val(data.status);

        $('#teacherModal').modal('show');
    });

    // Handle "Delete" button click
    $(document).on('click', '.delete-btn', function () {
        const teacherId = $(this).data('id');
        const teacherName = $(this).data('name');

        Swal.fire({
            title: 'Are you sure?',
            text: `You are about to delete ${teacherName}. This action cannot be undone!`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`${API_URL}/${teacherId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                        'Accept': 'application/json'
                    },
                })
                .then(response => {
                    if (!response.ok) {
                        return response.text().then(text => {
                            throw new Error(`Network response was not ok: ${response.status} ${text}`);
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        Swal.fire(
                            'Deleted!',
                            'The teacher has been deleted.',
                            'success'
                        );
                        teacherTable.ajax.reload(null, false);
                    } else {
                        Swal.fire(
                            'Failed!',
                            data.message || 'Failed to delete teacher.',
                            'error'
                        );
                    }
                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: `An error occurred: ${error.message}`,
                    });
                    console.error('Error deleting teacher:', error);
                });
            }
        });
    });

    // Handle Form Submit
    $('#teacherForm').on('submit', function (e) {
        e.preventDefault();

        const teacherId = $('#teacher_id').val();
        const method = teacherId ? 'PUT' : 'POST';
        const url = teacherId ? `${API_URL}/${teacherId}` : API_URL;

        $.ajax({
            url: url,
            method: method,
            data: $(this).serialize(),
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                if (response.success) {
                    Swal.fire('Success!', response.message, 'success');
                    $('#teacherModal').modal('hide');
                    teacherTable.ajax.reload();
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
                    Swal.fire('Error!', 'Failed to save teacher data.', 'error');
                }
            }
        });
    });
});
