$(document).ready(function () {
    // Inisialisasi DataTable
    var table = $('#majorTable').DataTable({
        ajax: {
            url: '/api/majors',
            dataSrc: 'data'
        },
        columns: [
            { data: null, render: function (data, type, row, meta) { return meta.row + 1; } },
            { data: 'level' },
            { data: 'name' },
            { data: 'description' },
            { data: 'created_at', render: function (data) { return new Date(data).toLocaleDateString(); } },
            {
                data: null,
                render: function (data, type, row) {
                    return `
                        <button class="btn btn-sm btn-warning edit-btn" data-id="${row.id}"><i class="fas fa-edit"></i> Edit</button>
                        <button class="btn btn-sm btn-danger delete-btn" data-id="${row.id}"><i class="fas fa-trash"></i> Delete</button>
                    `;
                }
            }
        ]
    });

    // Reset form dan modal untuk tambah data
    $('#majorAddBtn').click(function () {
        $('#majorForm')[0].reset();
        $('#majorId').val('');
        $('#majorModalLabel').text('Add Major'); // Fixed: Changed .description to .text
        $('#majorModal').modal('show');
    });

    // Simpan atau update data
    $('#saveMajorBtn').click(function () {
        let id = $('#majorId').val();
        let url = id ? `/api/majors/${id}` : '/api/majors';
        let method = id ? 'PUT' : 'POST';

        $.ajax({
            url: url,
            method: method,
            data: $('#majorForm').serialize(),
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                if (response.success) {
                    table.ajax.reload();
                    $('#majorModal').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                        confirmButtonColor: '#3085d6'
                    });
                }
            },
            error: function (xhr) {
                let errorMsg = xhr.responseJSON?.message || 'An error occurred';
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: errorMsg,
                    confirmButtonColor: '#d33'
                });
            }
        });
    });

    // Edit data
    $(document).on('click', '.edit-btn', function () {
        let id = $(this).data('id');
        $.ajax({
            url: `/api/majors/${id}`,
            method: 'GET',
            success: function (response) {
                if (response.success) {
                    let data = response.data;
                    $('#majorId').val(data.id);
                    $('#level').val(data.level);
                    $('#name').val(data.name);
                    $('#description').val(data.description);
                    $('#majorModalLabel').text('Edit Major'); // Fixed: Changed .description to .text
                    $('#majorModal').modal('show');
                }
            },
            error: function (xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Failed to fetch major data',
                    confirmButtonColor: '#d33'
                });
            }
        });
    });

    // Hapus data
    $(document).on('click', '.delete-btn', function () {
        let id = $(this).data('id');
        Swal.fire({
            title: 'Are you sure?',
            text: 'You are about to delete this major. This action cannot be undone!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/api/majors/${id}`,
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (response) {
                        if (response.success) {
                            table.ajax.reload();
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: response.message,
                                confirmButtonColor: '#3085d6'
                            });
                        }
                    },
                    error: function (xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Failed to delete major',
                            confirmButtonColor: '#d33'
                        });
                    }
                });
            }
        });
    });
});
