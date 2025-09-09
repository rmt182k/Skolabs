document.addEventListener('DOMContentLoaded', function () {
    const API_URL = '/api/learning-materials';

    function loadCreateData() {
        return $.ajax({
            url: `${API_URL}/create-data`,
            method: 'GET'
        });
    }

    // Initialize DataTable
    const materialTable = $('#materials-datatable').DataTable({
        processing: true,
        serverSide: false,
        ajax: {
            url: API_URL,
            dataSrc: function (json) {
                if (!json.success) {
                    Swal.fire({ icon: 'error', title: 'Error', text: json.message || 'Failed to load data.' });
                    return [];
                }
                return json.data || [];
            }
        },
        columns: [
            { data: 'id' },
            { data: 'title' },
            { data: 'subject.name', defaultContent: '-' },
            { data: 'class.name', defaultContent: '-' },
            { data: 'teacher.name', defaultContent: '-' },
            { data: 'file_type' },
            {
                data: null,
                orderable: false,
                render: function (data, type, row) {
                    return `
                        <a href="${API_URL}/download/${row.id}" class="btn btn-sm btn-success" title="Download"><i class="fas fa-download"></i></a>
                        <button class="btn btn-sm btn-warning edit-btn" data-id="${row.id}" title="Edit"><i class="fas fa-edit"></i></button>
                        <button class="btn btn-sm btn-danger delete-btn" data-id="${row.id}" data-title="${row.title}" title="Delete"><i class="fas fa-trash"></i></button>
                    `;
                }
            }
        ]
    });

    $('#materialAddBtn').on('click', function () {
        $('#materialForm')[0].reset();
        $('#materialId').val('');
        $('#materialModalLabel').text('🚀 Add New Material');
        $('#materialFile').prop('required', true);

        loadCreateData().done(function (response) {
            if (response.success) {
                const { subjects, classes } = response.data;
                const subjectSelect = $('#subjectId');
                const classSelect = $('#classId');

                subjectSelect.empty().append('<option value="">Select Subject</option>');
                subjects.forEach(subject => subjectSelect.append(`<option value="${subject.id}">${subject.name}</option>`));

                classSelect.empty().append('<option value="">Select Class</option>');
                classes.forEach(cls => classSelect.append(`<option value="${cls.id}">${cls.name}</option>`));

                $('#materialModal').modal('show');
            } else {
                Swal.fire('Error', 'Failed to load necessary data for the form.', 'error');
            }
        });
    });

    $('#materials-datatable').on('click', '.edit-btn', function () {
        const materialId = $(this).data('id');
        $('#materialForm')[0].reset();
        $('#materialModalLabel').text('✏️ Edit Material');
        $('#materialFile').prop('required', false);

        loadCreateData().done(function (response) {
            if (response.success) {
                const { subjects, classes } = response.data;
                const subjectSelect = $('#subjectId');
                const classSelect = $('#classId');

                subjectSelect.empty().append('<option value="">Select Subject</option>');
                subjects.forEach(subject => subjectSelect.append(`<option value="${subject.id}">${subject.name}</option>`));

                classSelect.empty().append('<option value="">Select Class</option>');
                classes.forEach(cls => classSelect.append(`<option value="${cls.id}">${cls.name}</option>`));

                $.get(`${API_URL}/${materialId}`, function (res) {
                    if (res.success) {
                        const data = res.data;
                        $('#materialId').val(data.id);
                        $('#materialTitle').val(data.title);
                        $('#subjectId').val(data.subject_id);
                        $('#classId').val(data.class_id);
                        $('#materialDescription').val(data.description);
                        $('#materialModal').modal('show');
                    }
                });
            }
        });
    });

    $('#materialForm').on('submit', function (e) {
        e.preventDefault();

        const materialId = $('#materialId').val();
        const url = materialId ? `${API_URL}/${materialId}` : API_URL;

        const formData = new FormData(this);
        if (materialId) {
            formData.append('_method', 'PUT');
        }

        $.ajax({
            url: url,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function (response) {
                if (response.success) {
                    $('#materialModal').modal('hide');
                    Swal.fire('Success!', response.message, 'success');
                    materialTable.ajax.reload(null, false);
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
                    Swal.fire('Error!', 'An unexpected error occurred.', 'error');
                }
            }
        });
    });

    $('#materials-datatable').on('click', '.delete-btn', function () {
        const materialId = $(this).data('id');
        const materialTitle = $(this).data('title');

        Swal.fire({
            title: 'Are you sure?',
            text: `You are about to delete "${materialTitle}". This action cannot be undone!`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `${API_URL}/${materialId}`,
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    success: function (response) {
                        if (response.success) {
                            Swal.fire('Deleted!', response.message, 'success');
                            materialTable.ajax.reload(null, false);
                        } else {
                            Swal.fire('Failed!', response.message, 'error');
                        }
                    },
                    error: function () {
                        Swal.fire('Error!', 'An error occurred while deleting the material.', 'error');
                    }
                });
            }
        });
    });
});
