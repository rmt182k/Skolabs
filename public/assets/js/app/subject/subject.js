$(document).ready(function () {
    const API_URL = '/api/subjects'; // Sesuaikan dengan URL API Anda
    const TEACHER_API_URL = '/api/teachers'; // API untuk mengambil data guru
    const CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');

    // 1. Inisialisasi Select2 untuk dropdown multi-pilihan guru
    $('#teacherIds').select2({
        placeholder: "Select one or more teachers",
        dropdownParent: $('#subjectModal'), // Penting agar Select2 berfungsi di dalam modal
        width: '100%'
    });

    // 2. Inisialisasi DataTable untuk menampilkan data mata pelajaran
    const table = $('#subjectsTable').DataTable({ // Pastikan tabel Anda punya id="subjectsTable"
        processing: true,
        serverSide: true, // Sebaiknya serverSide untuk data besar, tapi bisa diubah
        ajax: API_URL,
        columns: [
            { data: 'name', name: 'name' },
            { data: 'code', name: 'code' },
            {
                data: 'teachers',
                name: 'teachers',
                orderable: false,
                searchable: false,
                render: function (data, type, row) {
                    if (!data || data.length === 0) {
                        return '<span class="badge bg-secondary">No Teacher</span>';
                    }
                    // Tampilkan nama guru sebagai badge
                    return data.map(teacher => `<span class="badge bg-info me-1">${teacher.name}</span>`).join(' ');
                }
            },
            {
                data: 'id',
                orderable: false,
                searchable: false,
                render: function (data, type, row) {
                    // Tombol Aksi (Edit & Delete)
                    return `
                        <button class="btn btn-sm btn-primary edit-btn" data-id="${data}"><i class="fas fa-edit"></i> Edit</button>
                        <button class="btn btn-sm btn-danger delete-btn" data-id="${data}"><i class="fas fa-trash"></i> Delete</button>
                    `;
                }
            }
        ]
    });

    // 3. Fungsi untuk memuat data guru ke dalam Select2
    function loadTeachers() {
        const teacherSelect = $('#teacherIds');
        teacherSelect.empty(); // Kosongkan pilihan lama

        $.ajax({
            url: TEACHER_API_URL,
            method: 'GET',
            success: function (response) {
                if (response.data) {
                    response.data.forEach(teacher => {
                        teacherSelect.append(new Option(teacher.name, teacher.id, false, false));
                    });
                }
                teacherSelect.trigger('change');
            },
            error: function () {
                Swal.fire('Error', 'Failed to load teacher data.', 'error');
            }
        });
    }


    // 4. Handle tombol "Add New Subject"
    $('#addSubjectBtn').on('click', function () { // Pastikan tombol Anda punya id="addSubjectBtn"
        $('#subjectForm')[0].reset();
        $('#subjectId').val('');
        $('#teacherIds').val(null).trigger('change'); // Reset Select2
        $('#subjectModalLabel').text('Add New Subject');
        $('#saveSubjectBtn').text('Save').prop('disabled', false);

        loadTeachers(); // Muat data guru ke dropdown

        $('#subjectModal').modal('show');
    });

    // 5. Handle form submission (untuk Create dan Update)
    $('#subjectForm').on('submit', function (e) {
        e.preventDefault();
        $('#saveSubjectBtn').text('Saving...').prop('disabled', true);

        const subjectId = $('#subjectId').val();
        let method = subjectId ? 'PUT' : 'POST';
        let url = subjectId ? `${API_URL}/${subjectId}` : API_URL;

        // Untuk method PUT, kita perlu menambahkan _method
        let formData = $(this).serialize();
        if (method === 'PUT') {
            formData += '&_method=PUT';
        }

        $.ajax({
            url: url,
            method: 'POST', // Method tetap POST untuk mengakomodasi _method=PUT
            data: formData,
            headers: { 'X-CSRF-TOKEN': CSRF_TOKEN },
            success: function (response) {
                if (response.success) {
                    $('#subjectModal').modal('hide');
                    Swal.fire('Success', response.message, 'success');
                    table.ajax.reload(null, false); // Reload tabel tanpa reset paging
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
                $('#saveSubjectBtn').text('Save changes').prop('disabled', false);
            }
        });
    });

    // 6. Handle tombol "Edit"
    $('#subjectsTable').on('click', '.edit-btn', function () {
        const subjectId = $(this).data('id');

        // Ambil data subject yang spesifik
        $.get(`${API_URL}/${subjectId}`, function (response) {
            if (response.data) {
                const subject = response.data;
                $('#subjectModalLabel').text('Edit Subject');
                $('#subjectId').val(subject.id);
                $('#subjectName').val(subject.name);
                $('#subjectCode').val(subject.code);

                // Muat semua guru, lalu pilih guru yang terkait dengan subject ini
                loadTeachers();

                // Dapatkan ID guru yang sudah terpilih
                const selectedTeacherIds = subject.teachers.map(teacher => teacher.id);
                $('#teacherIds').val(selectedTeacherIds).trigger('change');

                $('#subjectModal').modal('show');
            }
        });
    });

    // 7. Handle tombol "Delete"
    $('#subjectsTable').on('click', '.delete-btn', function () {
        const subjectId = $(this).data('id');
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `${API_URL}/${subjectId}`,
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': CSRF_TOKEN },
                    success: function (response) {
                        if (response.success) {
                            Swal.fire('Deleted!', response.message, 'success');
                            table.ajax.reload(null, false);
                        } else {
                            Swal.fire('Failed!', response.message, 'error');
                        }
                    },
                    error: function () {
                        Swal.fire('Error', 'An error occurred while deleting the subject.', 'error');
                    }
                });
            }
        });
    });
});
