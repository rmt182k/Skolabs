document.addEventListener('DOMContentLoaded', function () {
    // API endpoint
    const API_URL = '/api/class';

    // Function to load majors and teachers into select dropdowns
    function loadCreateData() {
        return $.ajax({
            url: `${API_URL}/create-data`,
            method: 'GET'
        });
    }

    // Initialize DataTable
    const classTable = $('#class-datatable').DataTable({
        processing: true,
        ajax: {
            url: API_URL,
            dataSrc: function (json) {
                if (!json.success) {
                    Swal.fire({ icon: 'error', title: 'Error', text: json.message || 'Failed to load class data.' });
                    return [];
                }
                return json.data || [];
            },
            error: function (xhr) {
                Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to fetch class data: ' + xhr.statusText });
            }
        },
        columns: [
            { data: 'id' },
            { data: 'name', defaultContent: '-' },
            { data: 'major.name', defaultContent: '-' },
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

    // Handle "Add New Class" button click
    $('#classAddBtn').on('click', function () {
        $('#classForm')[0].reset();
        $('#classId').val('');
        $('#classModalLabel').text('👤 Add New Class');
        loadCreateData().done(function(response) {
            if (response.success) {
                const majors = response.data.majors;
                const teachers = response.data.teachers;
                const majorSelect = $('#majorId');
                const teacherSelect = $('#teacherId');

                majorSelect.empty().append('<option value="">Select Major</option>');
                majors.forEach(function (major) {
                    majorSelect.append(`<option value="${major.id}">${major.name}</option>`);
                });

                teacherSelect.empty().append('<option value="">Select Teacher</option>');
                teachers.forEach(function (teacher) {
                    teacherSelect.append(`<option value="${teacher.id}">${teacher.name}</option>`);
                });
            }
        });
        $('#classModal').modal('show');
    });

    // Handle "Edit" button click
    $('#class-datatable').on('click', '.edit-btn', function () {
        const classId = $(this).data('id');
        loadCreateData().done(function(response) {
            if (response.success) {
                const majors = response.data.majors;
                const teachers = response.data.teachers;
                const majorSelect = $('#majorId');
                const teacherSelect = $('#teacherId');

                majorSelect.empty().append('<option value="">Select Major</option>');
                majors.forEach(function (major) {
                    majorSelect.append(`<option value="${major.id}">${major.name}</option>`);
                });

                teacherSelect.empty().append('<option value="">Select Teacher</option>');
                teachers.forEach(function (teacher) {
                    teacherSelect.append(`<option value="${teacher.id}">${teacher.name}</option>`);
                });

                $.ajax({
                    url: `${API_URL}/${classId}`,
                    method: 'GET',
                    success: function (response) {
                        if (response.success) {
                            const data = response.data;
                            $('#classModalLabel').text('✏️ Edit Class');
                            $('#classId').val(data.id);
                            $('#className').val(data.name);
                            $('#majorId').val(data.major_id);
                            $('#teacherId').val(data.teacher_id);
                            $('#classModal').modal('show');
                        } else {
                            Swal.fire('Error!', response.message, 'error');
                        }
                    },
                    error: function () {
                        Swal.fire('Error!', 'Failed to fetch class data.', 'error');
                    }
                });
            }
        });
    });

    // Handle "Delete" button click
    $('#class-datatable').on('click', '.delete-btn', function () {
        const classId = $(this).data('id');
        const className = $(this).data('name');
        Swal.fire({
            title: 'Are you sure?',
            text: `You are about to delete ${className}. This action cannot be undone!`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `${API_URL}/${classId}`,
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    success: function (response) {
                        if (response.success) {
                            Swal.fire('Deleted!', response.message, 'success');
                            classTable.ajax.reload(null, false);
                        } else {
                            Swal.fire('Failed!', response.message, 'error');
                        }
                    },
                    error: function () {
                        Swal.fire('Error!', 'An error occurred while deleting the class.', 'error');
                    }
                });
            }
        });
    });

    // Handle Form Submit
    $('#classForm').on('submit', function (e) {
        e.preventDefault();
        const classId = $('#classId').val();
        const method = classId ? 'PUT' : 'POST';
        const url = classId ? `${API_URL}/${classId}` : API_URL;

        $.ajax({
            url: url,
            method: method,
            data: $(this).serialize(),
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function (response) {
                if (response.success) {
                    $('#classModal').modal('hide');
                    Swal.fire('Success!', response.message, 'success');
                    classTable.ajax.reload();
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
                    Swal.fire('Error!', 'Failed to save class data.', 'error');
                }
            }
        });
    });
});
