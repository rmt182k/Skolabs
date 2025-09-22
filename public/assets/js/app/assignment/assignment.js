document.addEventListener('DOMContentLoaded', function () {
    const API_URL = '/api/assignments';
    const CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');

    // <-- PERBAIKAN 1: Ambil URL dasar dari atribut data-edit-url pada tabel
    const editBaseUrl = $('#assignment-datatable').data('edit-url');

    // Inisialisasi DataTable
    const assignmentTable = $('#assignment-datatable').DataTable({
        processing: true,
        responsive: true,
        ajax: {
            url: API_URL,
            dataSrc: 'data'
        },
        columns: [
            { data: null, searchable: false, orderable: false, className: 'text-center', render: (d, t, r, m) => m.row + 1 },
            { data: 'title' },
            { data: 'subject_name', defaultContent: '-' },
            { data: 'class_names', defaultContent: '-' },
            { data: 'assignment_type', render: data => data.charAt(0).toUpperCase() + data.slice(1) },
            { data: 'due_date', render: data => data ? new Date(data).toLocaleDateString('id-ID', { year: 'numeric', month: 'long', day: 'numeric' }) : '-' },
            {
                data: 'id',
                searchable: false,
                orderable: false,
                className: 'text-center',
                render: function(data, type, row) {
                    // <-- PERBAIKAN 2: Gunakan variabel editBaseUrl untuk membuat URL yang benar
                    const editUrl = `${editBaseUrl}/${data}/edit`;

                    return `
                        <div class="d-flex justify-content-center">
                            <a href="${editUrl}" class="btn btn-sm btn-info me-2" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button class="btn btn-sm btn-danger delete-btn" data-id="${data}" data-title="${row.title}" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    `;
                }
            }
        ]
    });

    // Handle Delete button click
    $('#assignment-datatable tbody').on('click', '.delete-btn', function () {
        const assignmentId = $(this).data('id');
        const assignmentTitle = $(this).data('title');

        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: `Tugas "${assignmentTitle}" akan dihapus permanen!`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `${API_URL}/${assignmentId}`,
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': CSRF_TOKEN },
                    success: function (response) {
                        Swal.fire('Dihapus!', response.message, 'success');
                        assignmentTable.ajax.reload(null, false); // Reload tabel tanpa kembali ke halaman pertama
                    },
                    error: function () {
                        Swal.fire('Gagal!', 'Terjadi kesalahan saat menghapus.', 'error');
                    }
                });
            }
        });
    });
});
