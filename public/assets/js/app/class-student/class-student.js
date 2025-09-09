document.addEventListener('DOMContentLoaded', function () {
    const API_URL = '/api/class-students';
    const CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');

    // Inisialisasi Select2
    $('#class_id').select2({
        dropdownParent: $('#assignStudentModal')
    });
    $('#student_ids').select2({
        placeholder: "Select students",
        dropdownParent: $('#assignStudentModal')
    });

    const table = $('#assignStudentsTable').DataTable({
        processing: true,
        serverSide: false,
        ajax: {
            url: API_URL,
            dataSrc: 'data'
        },
        columns: [
            { data: 'class.name' },
            { data: 'student.name' },
            { data: 'class.major.name', defaultContent: '-' },
            { data: 'class.teacher.name', defaultContent: '-' },
            {
                data: 'created_at',
                render: function (data) {
                    return new Date(data).toLocaleDateString();
                }
            },
            {
                data: null,
                orderable: false,
                searchable: false,
                render: function (data, type, row) {
                    return `
                        <button class="btn btn-sm btn-danger delete-btn" data-id="${row.id}"><i class="fas fa-trash"></i> Delete</button>
                    `;
                }
            }
        ]
    });

    // Fungsi untuk memuat data dropdown, mengembalikan promise
    function loadCreateData() {
        return $.ajax({
            url: `${API_URL}/create-data`,
            method: 'GET'
        });
    }

    // Handle tombol "Assign Student"
    $('#addAssignStudentBtn').on('click', function () {
        $('#assignStudentForm')[0].reset();
        $('#assignStudentId').val('');
        $('#class_id').val(null).trigger('change');
        $('#student_ids').val(null).trigger('change');
        $('#assignStudentModalLabel').text('Assign Student to Class');
        $('#saveAssignStudentBtn').text('Save').prop('disabled', false);

        loadCreateData().done(function (response) {
            if (response.success) {
                const { classes, students } = response.data;

                const classSelect = $('#class_id');
                classSelect.empty().append('<option value="">Select a Class</option>');
                classes.forEach(cls => classSelect.append(`<option value="${cls.id}">${cls.name}</option>`));

                const studentSelect = $('#student_ids');
                studentSelect.empty();
                students.forEach(student => studentSelect.append(`<option value="${student.id}">${student.name}</option>`));
                studentSelect.trigger('change');

                $('#assignStudentModal').modal('show');
            } else {
                Swal.fire('Error', 'Failed to load necessary data.', 'error');
            }
        });
    });

    // DIUBAH: Handle Simpan data menggunakan event click pada tombol
    $('#saveAssignStudentBtn').on('click', function () {
        $(this).text('Saving...').prop('disabled', true);

        $.ajax({
            url: API_URL,
            method: 'POST',
            data: $('#assignStudentForm').serialize(), // Ambil data dari form
            headers: { 'X-CSRF-TOKEN': CSRF_TOKEN },
            success: function (response) {
                if (response.success) {
                    $('#assignStudentModal').modal('hide');
                    Swal.fire('Success', response.message, 'success');
                    table.ajax.reload(null, false);
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            },
            error: function (xhr) {
                let errorMsg = 'An error occurred. Please try again.';
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    errorMsg = Object.values(xhr.responseJSON.errors).map(err => err[0]).join('<br>');
                }
                Swal.fire('Validation Error', errorMsg, 'error');
            },
            complete: function () {
                $('#saveAssignStudentBtn').text('Save').prop('disabled', false);
            }
        });
    });

    // Handle Hapus data
    $('#assignStudentsTable').on('click', '.delete-btn', function () {
        const assignmentId = $(this).data('id');

        Swal.fire({
            title: 'Are you sure?',
            text: 'You are about to remove this student from the class.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, remove it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `${API_URL}/${assignmentId}`,
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': CSRF_TOKEN },
                    success: function (response) {
                        if (response.success) {
                            Swal.fire('Removed!', response.message, 'success');
                            table.ajax.reload(null, false);
                        } else {
                            Swal.fire('Failed!', response.message, 'error');
                        }
                    },
                    error: function () {
                        Swal.fire('Error', 'An error occurred while removing the student.', 'error');
                    }
                });
            }
        });
    });
});

