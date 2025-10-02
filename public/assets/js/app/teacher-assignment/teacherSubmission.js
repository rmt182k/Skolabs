// public/assets/js/app/teacher-assignment/teacherSubmission.js
$(function () {

    function getAssignmentIdFromUrl() {
        const pathParts = window.location.pathname.split('/');
        const assignmentIdIndex = pathParts.indexOf('teacher-assignment');
        if (assignmentIdIndex > -1 && pathParts.length > assignmentIdIndex + 1) {
            const id = pathParts[assignmentIdIndex + 1];
            if (!isNaN(id)) { return id; }
        }
        return null;
    }

    const ASSIGNMENT_ID = getAssignmentIdFromUrl();
    if (!ASSIGNMENT_ID) {
        alert("Error: ID Tugas tidak valid atau tidak ditemukan di URL.");
        return;
    }

    const API_URL = `/api/teacher-assignment/${ASSIGNMENT_ID}/submissions`;

    // --- Inisialisasi DataTables untuk mode Client-Side ---
    $('#submissions-table').DataTable({
        // PERUBAHAN: Hapus 'serverSide: true' dan 'processing: true'

        ajax: {
            url: API_URL,
            // PERUBAHAN: 'dataSrc' memberitahu DataTables di mana letak array data
            // sekaligus kita gunakan untuk mengisi header & summary
            dataSrc: function (json) {
                if (json.success) {
                    updateHeaderInfo(json.assignment);
                    updateSummary(json.summary);
                    return json.data; // Kembalikan array data untuk tabel
                }
                return []; // Kembalikan array kosong jika ada error
            }
        },
        // 'columns' dan 'columnDefs' tidak berubah karena cara merender datanya sama
        columns: [
            { data: 'student_name' },
            { data: 'class_name' },
            { data: 'submitted_at' },
            { data: 'submission_id' },
            { data: 'total_grade' },
            { data: 'submission_id' }
        ],
        columnDefs: [
            {
                targets: 0,
                render: function (data, type, row) {
                    return `<div class="fw-bold">${row.student_name}</div><div class="small text-muted">NISN: ${row.nisn}</div>`;
                }
            },
            {
                targets: 2,
                render: function (data, type, row) {
                    if (!data) return '-';
                    const date = new Date(data);
                    return date.toLocaleString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
                }
            },
            {
                targets: 3,
                orderable: false,
                render: function (data, type, row) {
                    if (row.submission_id) {
                        return (row.total_grade !== null)
                            ? '<span class="badge bg-success">Sudah Dinilai</span>'
                            : '<span class="badge bg-warning text-dark">Belum Dinilai</span>';
                    }
                    return '<span class="badge bg-danger">Belum Mengumpulkan</span>';
                }
            },
            {
                targets: 4,
                className: 'text-center',
                render: function (data, type, row) {
                    return (data !== null) ? `<strong class="text-success">${data}</strong>` : '-';
                }
            },
            {
                targets: 5,
                orderable: false,
                className: 'text-center',
                render: function (data, type, row) {
                    if (row.submission_id) {
                        return (row.total_grade !== null)
                            ? `<a href="/teacher-submission/${row.submission_id}/grade" class="btn btn-sm btn-outline-success">Lihat</a>`
                            : `<a href="/teacher-submission/${row.submission_id}/grade" class="btn btn-sm btn-primary">Beri Nilai</a>`;
                    }
                    return `<button class="btn btn-sm btn-secondary" disabled>Tidak Ada Aksi</button>`;
                }
            }
        ],
        language: {
            url: "https://cdn.datatables.net/plug-ins/1.13.7/i18n/id.json"
        }
    });

    // --- Fungsi Helper (tidak berubah) ---
    function updateHeaderInfo(assignment) {
        if (!assignment) return;
        $('#assignment-title').text(assignment.title).removeClass('placeholder col-8');
        const dueDate = new Date(assignment.due_date);
        $('#assignment-due-date').text(`Tenggat Waktu: ${dueDate.toLocaleString('id-ID', { dateStyle: 'full', timeStyle: 'short' })}`).removeClass('placeholder col-6');
        $('#edit-assignment-link').attr('href', `/teacher/assignment/${assignment.id}/edit`).removeClass('disabled');
    }

    function updateSummary(summary) {
        if (!summary) return;
        $('#submitted-count').text(`${summary.submitted_count} / ${summary.total_students}`);
        $('#graded-count').text(`${summary.graded_count} / ${summary.submitted_count}`);
        $('#missing-count').text(`${summary.total_students - summary.submitted_count} / ${summary.total_students}`);
    }
});
