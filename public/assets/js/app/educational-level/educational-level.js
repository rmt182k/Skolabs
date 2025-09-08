$(document).ready(function () {
    // Inisialisasi DataTable
    var table = $('#educationalLevelsTable').DataTable({
        ajax: {
            url: '/api/educational-levels',
            dataSrc: 'data'
        },
        columns: [
            { data: 'name' },
            { data: 'duration_years' },
            { data: 'description' },
            {
                data: 'created_at',
                render: function (data) {
                    return new Date(data).toLocaleDateString();
                }
            },
            {
                data: 'updated_at',
                render: function (data) {
                    return new Date(data).toLocaleDateString();
                }
            },
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
    $('#addEducationalLevelBtn').click(function () {
        $('#educationalLevelForm')[0].reset();
        $('#educationalLevelId').val('');
        $('#educationalLevelModalLabel').text('Add Educational Level');
        $('#educationalLevelModal').modal('show');
    });

    // Simpan atau update data
    $('#saveEducationalLevelBtn').click(function () {
        let id = $('#educationalLevelId').val();
        let url = id ? `/api/educational-levels/${id}` : '/api/educational-levels';
        let method = id ? 'PUT' : 'POST';

        $.ajax({
            url: url,
            method: method,
            data: $('#educationalLevelForm').serialize(),
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                if (response.success) {
                    table.ajax.reload();
                    $('#educationalLevelModal').modal('hide');
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
            url: `/api/educational-levels/${id}`,
            method: 'GET',
            success: function (response) {
                if (response.success) {
                    let data = response.data;
                    $('#educationalLevelId').val(data.id);
                    $('#name').val(data.name);
                    $('#duration_years').val(data.duration_years);
                    $('#description').val(data.description);
                    $('#educationalLevelModalLabel').text('Edit Educational Level');
                    $('#educationalLevelModal').modal('show');
                }
            },
            error: function (xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Failed to fetch educational level data',
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
            text: 'You are about to delete this educational level. This action cannot be undone!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/api/educational-levels/${id}`,
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
                            text: 'Failed to delete educational level',
                            confirmButtonColor: '#d33'
                        });
                    }
                });
            }
        });
    });
});
