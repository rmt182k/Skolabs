document.addEventListener('DOMContentLoaded', function () {
    // --- KONFIGURASI & VARIABEL GLOBAL ---
    const API_URL = '/api/subjects';
    const TEACHERS_API_URL = '/api/teachers';
    const csrfToken = $('meta[name="csrf-token"]').attr('content');
    const subjectModal = new bootstrap.Modal(document.getElementById('subjectModal'));

    // --- INISIALISASI ---

    // Inisialisasi Select2
    $('#teacherIds').select2({
        placeholder: "Select one or more teachers",
        dropdownParent: $('#subjectModal'), // Penting agar Select2 berfungsi di dalam modal
        width: '100%'
    });

    // Inisialisasi DataTable (Mode Client-Side)
    const subjectsTable = $('#subjectsTable').DataTable({
        processing: true,
        ajax: {
            url: API_URL,
            dataSrc: 'data', // Menunjuk ke array 'data' dalam respons JSON
            error: (xhr) => handleAjaxError('Failed to fetch subject data.', xhr)
        },
        columns: [
            // Kolom Nomor Urut
            { data: null, searchable: false, orderable: false, render: (data, type, row, meta) => meta.row + 1 },
            // Kolom Nama Mata Pelajaran
            { data: 'name', defaultContent: '-' },
            // Kolom Kode Mata Pelajaran
            { data: 'code', defaultContent: '-' },
            // Kolom Guru (custom render)
            {
                data: 'teachers',
                name: 'teachers',
                orderable: false,
                searchable: false,
                render: function (data) {
                    if (!data || data.length === 0) {
                        return '<span class="badge bg-secondary-subtle text-secondary">No Teacher Assigned</span>';
                    }
                    return data.map(teacher => `<span class="badge bg-info-subtle text-info me-1">${teacher.name}</span>`).join(' ');
                }
            },
            // Kolom Aksi (Tombol Edit & Delete)
            {
                data: null,
                orderable: false,
                searchable: false,
                render: (data) => `
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-warning edit-btn" data-id="${data.id}"><i class="fas fa-edit"></i> Edit</button>
                        <button class="btn btn-sm btn-danger delete-btn" data-id="${data.id}" data-name="${data.name}"><i class="fas fa-trash"></i> Delete</button>
                    </div>
                `
            }
        ]
    });

    // --- FUNGSI BANTUAN ---
    const showNotification = (icon, title, text = '') => Swal.fire({ icon, title, text, timer: 2000, showConfirmButton: false });
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
            // Menangani error untuk array teacher_ids.*
            const fieldName = field.split('.')[0];
            const input = $(`[name^="${fieldName}"]`); // Menggunakan `name^=` untuk cocok dengan `teacher_ids[]`
            input.addClass('is-invalid');

            // Menampilkan pesan error setelah elemen Select2
            if (fieldName === 'teacher_ids') {
                input.next('.select2-container').next('.invalid-feedback').text(errors[field][0]);
            } else {
                input.next('.invalid-feedback').text(errors[field][0]);
            }
        }
    };
    const resetForm = () => {
        $('#subjectForm')[0].reset();
        $('#subjectId').val('');
        $('#teacherIds').val(null).trigger('change'); // Reset Select2
        clearValidationErrors();
    };

    // --- FUNGSI UTAMA ---
    function fetchTeachers(selectedTeacherIds = []) {
        $.getJSON(TEACHERS_API_URL)
            .done(response => {
                if (response.success) {
                    const teacherSelect = $('#teacherIds');
                    teacherSelect.html(''); // Kosongkan opsi lama
                    response.data.forEach(teacher => {
                        teacherSelect.append(new Option(teacher.name, teacher.id, false, false));
                    });
                    if (selectedTeacherIds.length > 0) {
                        teacherSelect.val(selectedTeacherIds).trigger('change');
                    }
                }
            })
            .fail(xhr => handleAjaxError('Failed to load teacher data.', xhr));
    }


    // --- EVENT LISTENERS ---
    $('#addSubjectBtn').on('click', () => {
        resetForm();
        $('#subjectModalLabel').text('📚 Add New Subject');
        fetchTeachers(); // Muat daftar guru
        subjectModal.show();
    });

    $('#subjectsTable').on('click', '.edit-btn', function () {
        const subjectId = $(this).data('id');
        $.getJSON(`${API_URL}/${subjectId}`)
            .done(response => {
                if (response.success) {
                    resetForm();
                    const data = response.data;
                    $('#subjectModalLabel').text('✏️ Edit Subject');
                    $('#subjectId').val(data.id);
                    $('#subjectName').val(data.name);
                    $('#subjectCode').val(data.code);

                    // Ambil ID guru yang terkait dan panggil fetchTeachers
                    const selectedTeacherIds = data.teachers.map(teacher => teacher.id);
                    fetchTeachers(selectedTeacherIds);

                    subjectModal.show();
                } else {
                    showNotification('error', 'Error!', response.message);
                }
            })
            .fail(xhr => handleAjaxError('Failed to fetch subject data.', xhr));
    });

    $('#subjectsTable').on('click', '.delete-btn', function () {
        const subjectId = $(this).data('id');
        const subjectName = $(this).data('name');
        Swal.fire({
            title: 'Are you sure?',
            text: `You are about to delete ${subjectName}.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `${API_URL}/${subjectId}`,
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': csrfToken },
                    success: (response) => {
                        showNotification('success', 'Deleted!', response.message);
                        subjectsTable.ajax.reload(null, false); // false agar tidak kembali ke halaman pertama
                    },
                    error: (xhr) => handleAjaxError('Failed to delete subject.', xhr)
                });
            }
        });
    });

    $('#subjectForm').on('submit', function (e) {
        e.preventDefault();
        clearValidationErrors();
        const subjectId = $('#subjectId').val();
        let url = subjectId ? `${API_URL}/${subjectId}` : API_URL;
        let formData = new FormData(this);
        if (subjectId) {
            formData.append('_method', 'PUT');
        }

        $('#saveSubjectBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Saving...');

        $.ajax({
            url: url,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: { 'X-CSRF-TOKEN': csrfToken },
            success: (response) => {
                showNotification('success', 'Success!', response.message);
                subjectModal.hide();
                subjectsTable.ajax.reload();
            },
            error: (xhr) => {
                if (xhr.status === 422) {
                    displayValidationErrors(xhr.responseJSON.errors);
                } else {
                    handleAjaxError('Failed to save subject data.', xhr);
                }
            },
            complete: () => $('#saveSubjectBtn').prop('disabled', false).text('Save Subject')
        });
    });
});
