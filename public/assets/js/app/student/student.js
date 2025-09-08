document.addEventListener('DOMContentLoaded', function () {
    // Endpoint API
    const API_URL = '/api/students';

    // Inisialisasi DataTable
    if ($.fn.DataTable.isDataTable('#studentTable')) {
        $('#studentTable').DataTable().destroy();
    }

    const studentTable = $('#studentTable').DataTable({
        // ... (konfigurasi DataTable lainnya)
        ...getTableConfig('#studentTable'),
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
                    const majorLevel = row.major ? row.major.level : '';
                    return `
                        <button class="btn btn-sm btn-warning edit-btn"
                            data-id="${row.id}"
                            data-name="${row.name}"
                            data-email="${row.email}"
                            data-nisn="${row.nisn}"
                            data-date-of-birth="${row.date_of_birth}"
                            data-gender="${row.gender}"
                            data-phone-number="${row.phone_number}"
                            data-address="${row.address}"
                            data-enrollment-date="${row.enrollment_date}"
                            data-grade-level = "${row.grade_level}"
                            data-major-id = "${row.major ? row.major.id : ''}"
                            data-major-level = "${majorLevel}"
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
        $('#studentId').val(''); // Hapus ID yang tersembunyi
        $('#createStudentForm')[0].reset(); // Reset form

        // Secara default, field Major dinonaktifkan saat menambah siswa baru
        $('#studentMajorField').prop('disabled', true);
        // Pastikan grup field major terlihat, tidak disembunyikan
        $('#studentMajorFieldGroup').removeClass('d-none');

        $('#studentModal').modal('show');
    });

    // Menangani klik tombol "Edit"
    $(document).on('click', '.edit-btn', function () {
        const data = $(this).data();
        $('#studentModalLabel').text('✏️ Edit Student');
        $('#studentId').val(data.id);
        $('#studentName').val(data.name);
        $('#studentEmail').val(data.email);
        $('#studentNisn').val(data.nisn);
        $('#studentPassword').val('');
        $('#studentDateOfBirth').val(data.dateOfBirth);
        $('#studentGender').val(data.gender);
        $('#studentPhoneNumber').val(data.phoneNumber);
        $('#studentAddress').val(data.address);
        $('#studentEnrollmentDate').val(data.enrollmentDate);
        $('#studentGradeLevel').val(data.gradeLevel);

        const majorId = data.majorId;
        const majorLevel = data.majorLevel;
        $('#studentMajorLevel').val(majorLevel);

        // Pastikan grup field major terlihat
        $('#studentMajorFieldGroup').removeClass('d-none');

        // Atur status disabled berdasarkan level
        if (majorLevel && (majorLevel === 'senior_high_general' || majorLevel === 'senior_high_vocational')) {
            $('#studentMajorField').prop('disabled', false); // Aktifkan jika SMA/SMK
            fetchMajors(majorLevel, majorId);
        } else {
            $('#studentMajorField').prop('disabled', true); // Nonaktifkan jika bukan SMA/SMK
            $('#studentMajorField').empty().append('<option value="">Select Major</option>');
        }

        $('#studentModal').modal('show');
    });

    // ... (kode untuk delete, form submit)
    // Handle Delete button - Fixed class selector
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
                fetch(`${API_URL}/${studentId}`, {
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
                                'The student has been deleted.',
                                'success'
                            );
                            studentTable.ajax.reload(null, false);
                        } else {
                            Swal.fire(
                                'Failed!',
                                data.message || 'Failed to delete student.',
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
                        console.error('Error deleting student:', error);
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
                    $('#studentModal').modal('hide');
                    studentTable.ajax.reload();
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
                    Swal.fire('Error!', 'Failed to save student data.', 'error');
                }
            }
        });
    });

    $('#studentMajorLevel').on('change', function (e) {
        const selectedLevel = $(this).val();

        const isHighSchool = selectedLevel === 'senior_high_general' || selectedLevel === 'senior_high_vocational';
        $('#studentMajorField').prop('disabled', !isHighSchool);

        $('#studentMajorFieldGroup').removeClass('d-none');

        if (isHighSchool) {
            fetchMajors(selectedLevel);
        } else {
            $('#studentMajorField').empty().append('<option value="">Select Major</option>');
        }
    });

    function fetchMajors(level, majorId = null) {
        $.ajax({
            url: `/api/majors?level=${level}`,
            type: 'GET',
            dataType: 'json',
            success: function (res) {
                if (res.success) {
                    $('#studentMajorField').empty().append('<option value="">Select Major</option>');
                    $.each(res.data, function (i, major) {
                        $('#studentMajorField').append($('<option>', {
                            value: major.id,
                            text: major.name
                        }));
                    });

                    if (majorId) {
                        $('#studentMajorField').val(majorId);
                    }
                } else {
                    $('#studentMajorField').empty().append(`<option value="">${res.message}</option>`);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.error('Failed to fetch majors:', textStatus, errorThrown);
                $('#studentMajorField').empty().append('<option value="">Failed to load majors</option>');
            }
        });
    }

});
