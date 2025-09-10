document.addEventListener('DOMContentLoaded', function() {
    const API_URL = '/api/assignments';
    const CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');

    // Initialize Flatpickr for date pickers
    const startDatePicker = flatpickr("#startDate", {
        enableTime: true,
        dateFormat: "Y-m-d H:i",
    });
    const dueDatePicker = flatpickr("#dueDate", {
        enableTime: true,
        dateFormat: "Y-m-d H:i",
    });

    // Function to load dropdown data (subjects and classes)
    function loadCreateData() {
        return $.ajax({
            url: `${API_URL}/create-data`,
            method: 'GET'
        });
    }

    // Initialize DataTable
    const assignmentTable = $('#assignment-datatable').DataTable({
        processing: true,
        serverSide: false,
        ajax: {
            url: API_URL,
            dataSrc: 'data'
        },
        columns: [
            { data: 'id' },
            { data: 'title' },
            { data: 'subject.name', defaultContent: '-' },
            { data: 'class.name', defaultContent: '-' },
            { data: 'start_date' },
            { data: 'due_date' },
            {
                data: null,
                orderable: false,
                render: function(data, type, row) {
                    return `
                        <a href="/assignment/${row.id}/submissions" class="btn btn-sm btn-info view-btn" title="View Submissions"><i class="fas fa-eye"></i></a>
                        <button class="btn btn-sm btn-warning edit-btn" data-id="${row.id}" title="Edit"><i class="fas fa-edit"></i></button>
                        <button class="btn btn-sm btn-danger delete-btn" data-id="${row.id}" data-title="${row.title}" title="Delete"><i class="fas fa-trash"></i></button>
                    `;
                }
            }
        ]
    });

    // Handle "Add New Assignment" button click
    $('#assignmentAddBtn').on('click', function() {
        $('#assignmentForm')[0].reset();
        $('#assignmentId').val('');
        $('#assignmentModalLabel').text('Add New Assignment');
        $('#current-file-container').hide();
        $('#saveBtn').text('Save Assignment').prop('disabled', false);

        // Set default start date to now
        startDatePicker.setDate(new Date());
        dueDatePicker.clear();

        loadCreateData().done(function(response) {
            if (response.success) {
                const { subjects, classes } = response.data;
                const subjectSelect = $('#subjectId');
                const classSelect = $('#classId');

                subjectSelect.empty().append('<option value="">Select Subject</option>');
                subjects.forEach(subject => subjectSelect.append(`<option value="${subject.id}">${subject.name}</option>`));

                classSelect.empty().append('<option value="">Select Class</option>');
                classes.forEach(cls => classSelect.append(`<option value="${cls.id}">${cls.name}</option>`));

                $('#assignmentModal').modal('show');
            } else {
                Swal.fire('Error', 'Failed to load necessary data.', 'error');
            }
        });
    });

    // Handle Edit button click
    $('#assignment-datatable').on('click', '.edit-btn', function() {
        const assignmentId = $(this).data('id');
        $('#assignmentForm')[0].reset();
        $('#saveBtn').text('Save Changes').prop('disabled', false);

        loadCreateData().done(function(response) {
            if (response.success) {
                const { subjects, classes } = response.data;
                const subjectSelect = $('#subjectId');
                const classSelect = $('#classId');

                subjectSelect.empty().append('<option value="">Select Subject</option>');
                subjects.forEach(subject => subjectSelect.append(`<option value="${subject.id}">${subject.name}</option>`));

                classSelect.empty().append('<option value="">Select Class</option>');
                classes.forEach(cls => classSelect.append(`<option value="${cls.id}">${cls.name}</option>`));

                $.get(`${API_URL}/${assignmentId}`, function(res) {
                    if (res.success) {
                        const data = res.data;
                        $('#assignmentModalLabel').text('Edit Assignment');
                        $('#assignmentId').val(data.id);
                        $('#assignmentTitle').val(data.title);
                        $('#assignmentDescription').val(data.description);
                        $('#subjectId').val(data.subject_id);
                        $('#classId').val(data.class_id);

                        startDatePicker.setDate(data.start_date);
                        dueDatePicker.setDate(data.due_date);

                        if (data.file_name) {
                            $('#current-file-name').text(data.file_name);
                            $('#current-file-container').show();
                        } else {
                            $('#current-file-container').hide();
                        }

                        $('#assignmentModal').modal('show');
                    } else {
                        Swal.fire('Error', res.message, 'error');
                    }
                });
            }
        });
    });


    // Handle Form Submit (Create and Update)
    $('#assignmentForm').on('submit', function(e) {
        e.preventDefault();
        $('#saveBtn').text('Saving...').prop('disabled', true);

        const assignmentId = $('#assignmentId').val();
        const url = assignmentId ? `${API_URL}/${assignmentId}` : API_URL;
        const formData = new FormData(this);

        if (assignmentId) {
            formData.append('_method', 'PUT');
        }

        $.ajax({
            url: url,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: { 'X-CSRF-TOKEN': CSRF_TOKEN },
            success: function(response) {
                if (response.success) {
                    $('#assignmentModal').modal('hide');
                    Swal.fire('Success', response.message, 'success');
                    assignmentTable.ajax.reload(null, false);
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            },
            error: function(xhr) {
                let errorMsg = 'An error occurred. Please try again.';
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    errorMsg = Object.values(xhr.responseJSON.errors).map(err => err[0]).join('<br>');
                }
                Swal.fire('Validation Error', errorMsg, 'error');
            },
            complete: function() {
                $('#saveBtn').text(assignmentId ? 'Save Changes' : 'Save Assignment').prop('disabled', false);
            }
        });
    });

    // Handle Delete button click
    $('#assignment-datatable').on('click', '.delete-btn', function() {
        const assignmentId = $(this).data('id');
        const assignmentTitle = $(this).data('title');

        Swal.fire({
            title: 'Are you sure?',
            text: `You are about to delete "${assignmentTitle}". This action cannot be undone!`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `${API_URL}/${assignmentId}`,
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': CSRF_TOKEN },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Deleted!', response.message, 'success');
                            assignmentTable.ajax.reload(null, false);
                        } else {
                            Swal.fire('Failed!', response.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'An error occurred while deleting the assignment.', 'error');
                    }
                });
            }
        });
    });
});

