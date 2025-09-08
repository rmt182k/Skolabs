document.addEventListener('DOMContentLoaded', function () {
    // Endpoint API
    const API_URL = '/api/students';
    const EDUCATIONAL_LEVELS_API_URL = '/api/educational-levels';
    const MAJORS_API_URL = '/api/majors';

    // Inisialisasi DataTable
    if ($.fn.DataTable.isDataTable('#studentTable')) {
        $('#studentTable').DataTable().destroy();
    }

    const studentTable = $('#studentTable').DataTable({
        // ...getTableConfig('#studentTable'), // Pastikan fungsi ini ada jika Anda menggunakannya
        processing: true,
        ajax: {
            url: API_URL,
            dataSrc: function (json) {
                if (!json.success) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: json.message || 'Failed to load student data.'
                    });
                    return [];
                }
                return json.data || [];
            },
            error: function (xhr, error, thrown) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to fetch student data: ' + (xhr.statusText || thrown),
                });
                console.error('DataTable AJAX error:', xhr, error, thrown);
            }
        },
        columns: [
            { data: null, render: (data, type, row, meta) => meta.row + 1 },
            { data: 'nisn', defaultContent: '-' },
            { data: 'name', defaultContent: '-' },
            { data: 'gender', defaultContent: '-' },
            { data: 'grade_level', defaultContent: '-' },
            { data: 'enrollment_date', defaultContent: '-' },
            {
                data: null,
                orderable: false,
                render: function (data, type, row) {
                    return `
                        <button class="btn btn-sm btn-warning edit-btn"
                            data-id="${row.id}">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button class="btn btn-sm btn-danger delete-btn"
                            data-id="${row.id}"
                            data-name="${row.name}">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    `;
                }
            }
        ]
    });

    // Menangani klik tombol "Add New Student"
    $('#studentAddBtn').on('click', function () {
        $('#studentModalLabel').text('👤 Add New Student');
        $('#studentId').val('');
        $('#createStudentForm')[0].reset();

        // Pastikan dropdown major kembali ke kondisi disabled
        $('#studentMajorField').prop('disabled', true).empty().append('<option value="">Select Major</option>');

        fetchEducationalLevels();
        $('#studentModal').modal('show');
    });

    // Menangani klik tombol "Edit"
    $(document).on('click', '.edit-btn', function () {
        const studentId = $(this).data('id');

        $.ajax({
            url: `${API_URL}/${studentId}`,
            method: 'GET',
            success: function (response) {
                if (response.success) {
                    const data = response.data;
                    $('#studentModalLabel').text('✏️ Edit Student');
                    $('#studentId').val(data.id);
                    $('#studentName').val(data.name);
                    $('#studentEmail').val(data.email);
                    $('#studentNisn').val(data.nisn);
                    $('#studentDateOfBirth').val(data.date_of_birth);
                    $('#studentGender').val(data.gender);
                    $('#studentPhoneNumber').val(data.phone_number);
                    $('#studentAddress').val(data.address);
                    $('#studentEnrollmentDate').val(data.enrollment_date);
                    $('#studentGradeLevel').val(data.grade_level);

                    if (data.major) {
                        fetchEducationalLevels(data.major.educational_level_id, data.major.id);
                    } else {
                        // Jika tidak ada major, panggil fetchEducationalLevels dengan level_id siswa
                        fetchEducationalLevels(data.educational_level_id);
                    }

                    $('#studentModal').modal('show');

                } else {
                    Swal.fire('Error!', response.message, 'error');
                }
            },
            error: function () {
                Swal.fire('Error!', 'Failed to fetch student data.', 'error');
            }
        });
    });

    // Handle Delete button
    $(document).on('click', '.delete-btn', function () {
        const studentId = $(this).data('id');
        const studentName = $(this).data('name');

        Swal.fire({
            title: 'Are you sure?',
            text: `You are about to delete ${studentName}. This action cannot be undone!`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `${API_URL}/${studentId}`,
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    success: function (response) {
                        if (response.success) {
                            Swal.fire('Deleted!', 'The student has been deleted.', 'success');
                            studentTable.ajax.reload(null, false);
                        } else {
                            Swal.fire('Failed!', response.message || 'Failed to delete student.', 'error');
                        }
                    },
                    error: function () {
                        Swal.fire('Error!', 'Failed to delete student.', 'error');
                    }
                });
            }
        });
    });

    // Handle Form Submit
    $('#createStudentForm').on('submit', function (e) {
        e.preventDefault();
        const studentId = $('#studentId').val();
        const method = studentId ? 'PUT' : 'POST';
        const url = studentId ? `${API_URL}/${studentId}` : API_URL;
        const formData = $(this).serialize();

        $.ajax({
            url: url,
            method: method,
            data: formData,
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function (response) {
                if (response.success) {
                    Swal.fire('Success!', response.message, 'success');
                    $('#studentModal').modal('hide');
                    studentTable.ajax.reload();
                } else {
                    Swal.fire('Error!', response.message, 'error');
                }
            },
            error: function (xhr) {
                const errors = xhr.responseJSON?.errors;
                let errorMsg = 'Please fix the following errors:\n';
                if (errors) {
                    Object.values(errors).forEach(err => errorMsg += `- ${err[0]}\n`);
                    Swal.fire('Validation Error!', errorMsg, 'error');
                } else {
                    Swal.fire('Error!', 'Failed to save student data.', 'error');
                }
            }
        });
    });

    // Fungsi untuk memuat level pendidikan ke dalam dropdown
    function fetchEducationalLevels(selectedLevelId = null, majorId = null) {
        $.ajax({
            url: EDUCATIONAL_LEVELS_API_URL,
            type: 'GET',
            dataType: 'json',
            success: function (res) {
                if (res.success) {
                    $('#studentMajorLevel').empty().append('<option value="">Select Level</option>');
                    $.each(res.data, function (i, level) {
                        $('#studentMajorLevel').append($('<option>', {
                            value: level.id,
                            text: level.name
                        }));
                    });

                    if (selectedLevelId) {
                        $('#studentMajorLevel').val(selectedLevelId);
                        // Langsung panggil fetchMajors setelah level dipilih
                        fetchMajors(selectedLevelId, majorId);
                    }
                } else {
                    $('#studentMajorLevel').empty().append(`<option value="">${res.message}</option>`);
                }
            },
            error: function () {
                $('#studentMajorLevel').empty().append('<option value="">Failed to load levels</option>');
            }
        });
    }

    // Event listener saat user mengganti pilihan educational level
    $('#studentMajorLevel').on('change', function () {
        const selectedLevelId = $(this).val();
        fetchMajors(selectedLevelId);
    });

    // Fungsi untuk memuat jurusan berdasarkan level pendidikan
    function fetchMajors(levelId, majorId = null) {
        // Jika user memilih "Select Level" (value kosong), disable dan reset major
        if (!levelId) {
            $('#studentMajorField').prop('disabled', true).empty().append('<option value="">Select Major</option>');
            return;
        }

        $.ajax({
            url: `${MAJORS_API_URL}?educational_level_id=${levelId}`,
            type: 'GET',
            dataType: 'json',
            success: function (res) {
                if (res.success) {
                    const hasMajors = res.data && res.data.length > 0;
                    $('#studentMajorField').empty().append('<option value="">Select Major</option>');

                    if (hasMajors) {
                        // Jika ada jurusan, AKTIFKAN dropdown
                        $('#studentMajorField').prop('disabled', false);
                        $.each(res.data, function (i, major) {
                            $('#studentMajorField').append($('<option>', {
                                value: major.id,
                                text: major.name
                            }));
                        });
                        // Jika ini mode edit, pilih major yang sesuai
                        if (majorId) {
                            $('#studentMajorField').val(majorId);
                        }
                    } else {
                        // Jika tidak ada jurusan, TETAP DISABLED dropdown
                        $('#studentMajorField').prop('disabled', true);
                        $('#studentMajorField').append('<option value="">No majors available</option>');
                    }
                } else {
                    // Jika API gagal (success: false), TETAP DISABLED dropdown
                    $('#studentMajorField').prop('disabled', true);
                    $('#studentMajorField').empty().append(`<option value="">${res.message}</option>`);
                }
            },
            error: function () {
                // Jika request AJAX error, TETAP DISABLED dropdown
                $('#studentMajorField').prop('disabled', true);
                $('#studentMajorField').empty().append('<option value="">Failed to load majors</option>');
            }
        });
    }

    // Panggil fungsi untuk memuat level pendidikan saat halaman dimuat
    fetchEducationalLevels();
});
