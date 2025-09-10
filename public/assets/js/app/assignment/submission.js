document.addEventListener('DOMContentLoaded', function() {
    // URL untuk mengambil semua data submission
    const API_URL_INDEX = '/api/assignment-submissions';
    // URL untuk detail dan grade tetap sama
    const API_URL_DETAIL = '/api/submissions';
    const CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');

    // Initialize DataTable
    const submissionTable = $('#all-submissions-datatable').DataTable({
        processing: true,
        serverSide: false,
        ajax: {
            url: API_URL_INDEX,
            dataSrc: 'data'
        },
        columns: [{
            data: 'id'
        }, {
            data: 'assignment.title', // Kolom baru untuk judul tugas
            defaultContent: '-'
        }, {
            data: 'student.name',
            defaultContent: '-'
        }, {
            data: 'submitted_at'
        }, {
            data: 'status'
        }, {
            data: 'grade'
        }, {
            data: null,
            orderable: false,
            render: function(data, type, row) {
                return `
                    <button class="btn btn-sm btn-primary view-grade-btn" data-id="${row.id}" title="View & Grade">
                        <i class="fas fa-search-plus me-1"></i> View & Grade
                    </button>
                `;
            }
        }]
    });

    // Kode untuk handle tombol "View & Grade" dan form submit SAMA PERSIS dengan
    // file submission.js sebelumnya, karena logikanya tidak berubah.
    // Cukup copy-paste dari file `public/assets/js/app/assignment/submission.js`

    // Handle "View & Grade" button click
    $('#all-submissions-datatable').on('click', '.view-grade-btn', function() {
        const submissionId = $(this).data('id');
        $('#gradingForm')[0].reset();
        $('#saveGradeBtn').text('Save Grade').prop('disabled', false);

        $.get(`${API_URL_DETAIL}/${submissionId}`, function(res) {
            if (res.success) {
                const data = res.data;
                $('#submissionModalLabel').text(`Details for Submission #${data.id}`);
                $('#submissionId').val(data.id);
                $('#studentName').text(data.student_name);
                $('#submittedAt').text(new Date(data.submitted_at).toLocaleString());

                if (data.content) {
                    $('#submissionContent').text(data.content);
                    $('#submissionContentContainer').show();
                } else {
                    $('#submissionContentContainer').hide();
                }

                if (data.file_path && data.file_name) {
                    $('#submissionFileLink').attr('href', data.file_url).text(data.file_name);
                    $('#submissionFileContainer').show();
                } else {
                    $('#submissionFileContainer').hide();
                }

                $('#grade').val(data.grade);
                $('#feedback').val(data.feedback);

                $('#submissionModal').modal('show');
            } else {
                Swal.fire('Error', res.message, 'error');
            }
        });
    });

    // Handle Grading Form Submit
    $('#gradingForm').on('submit', function(e) {
        e.preventDefault();
        $('#saveGradeBtn').text('Saving...').prop('disabled', true);

        const submissionId = $('#submissionId').val();
        const url = `${API_URL_DETAIL}/${submissionId}/grade`;
        const formData = $(this).serialize();

        $.ajax({
            url: url,
            method: 'POST',
            data: formData,
            headers: { 'X-CSRF-TOKEN': CSRF_TOKEN },
            success: function(response) {
                if (response.success) {
                    $('#submissionModal').modal('hide');
                    Swal.fire('Success', response.message, 'success');
                    submissionTable.ajax.reload(null, false);
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
                $('#saveGradeBtn').text('Save Grade').prop('disabled', false);
            }
        });
    });
});
