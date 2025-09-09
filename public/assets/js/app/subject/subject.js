document.addEventListener('DOMContentLoaded', function () {
    // API endpoint
    const API_URL = '/api/subjects';

    // Function to load teachers into select dropdown
    function loadCreateData() {
        return $.ajax({
            url: `${API_URL}/create-data`,
            method: 'GET'
        });
    }

    // Initialize DataTable
    const subjectTable = $('#subject-datatable').DataTable({
        processing: true,
        ajax: {
            url: API_URL,
            dataSrc: function (json) {
                if (!json.success) {
                    Swal.fire({ icon: 'error', title: 'Error', text: json.message || 'Failed to load subject data.' });
                    return [];
                }
                return json.data || [];
            },
            error: function (xhr) {
                Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to fetch subject data: ' + xhr.statusText });
            }
        },
        columns: [
            { data: 'id' },
            { data: 'name', defaultContent: '-' },
            { data: 'code', defaultContent: '-' },
            { data: 'teacher.name', defaultContent: '-' },
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

    // Handle "Add New Subject" button click
    $('#subjectAddBtn').on('click', function () {
        $('#subjectForm')[0].reset();
        $('#subjectId').val('');
        $('#subjectModalLabel').text('👤 Add New Subject');
        loadCreateData().done(function(response) {
            if (response.success) {
                const teachers = response.data.teachers;
                const teacherSelect = $('#teacherId');

                teacherSelect.empty().append('<option value="">Select Teacher</option>');
                teachers.forEach(function (teacher) {
                    teacherSelect.append(`<option value="${teacher.id}">${teacher.name}</option>`);
                });
            }
        });
        $('#subjectModal').modal('show');
    });

    // Handle "Edit" button click
    $('#subject-datatable').on('click', '.edit-btn', function () {
        const subjectId = $(this).data('id');
        loadCreateData().done(function(response) {
            if (response.success) {
                const teachers = response.data.teachers;
                const teacherSelect = $('#teacherId');

                teacherSelect.empty().append('<option value="">Select Teacher</option>');
                teachers.forEach(function (teacher) {
                    teacherSelect.append(`<option value="${teacher.id}">${teacher.name}</option>`);
                });

                $.ajax({
                    url: `${API_URL}/${subjectId}`,
                    method: 'GET',
                    success: function (response) {
                        if (response.success) {
                            const data = response.data;
                            $('#subjectModalLabel').text('✏️ Edit Subject');
                            $('#subjectId').val(data.id);
                            $('#subjectName').val(data.name);
                            $('#subjectCode').val(data.code);
                            $('#teacherId').val(data.teacher_id);
                            $('#subjectModal').modal('show');
                        } else {
                            Swal.fire('Error!', response.message, 'error');
                        }
                    },
                    error: function () {
                        Swal.fire('Error!', 'Failed to fetch subject data.', 'error');
                    }
                });
            }
        });
    });

    // Handle "Delete" button click
    $('#subject-datatable').on('click', '.delete-btn', function () {
        const subjectId = $(this).data('id');
        const subjectName = $(this).data('name');
        Swal.fire({
            title: 'Are you sure?',
            text: `You are about to delete ${subjectName}. This action cannot be undone!`, 
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `${API_URL}/${subjectId}`,
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    success: function (response) {
                        if (response.success) {
                            Swal.fire('Deleted!', response.message, 'success');
                            subjectTable.ajax.reload(null, false);
                        } else {
                            Swal.fire('Failed!', response.message, 'error');
                        }
                    },
                    error: function () {
                        Swal.fire('Error!', 'An error occurred while deleting the subject.', 'error');
                    }
                });
            }
        });
    });

    // Handle Form Submit
    $('#subjectForm').on('submit', function (e) {
        e.preventDefault();
        const subjectId = $('#subjectId').val();
        const method = subjectId ? 'PUT' : 'POST';
        const url = subjectId ? `${API_URL}/${subjectId}` : API_URL;

        $.ajax({
            url: url,
            method: method,
            data: $(this).serialize(),
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function (response) {
                if (response.success) {
                    $('#subjectModal').modal('hide');
                    Swal.fire('Success!', response.message, 'success');
                    subjectTable.ajax.reload();
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
                    Swal.fire('Error!', 'Failed to save subject data.', 'error');
                }
            }
        });
    });
});
