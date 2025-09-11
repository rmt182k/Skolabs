// File: public/assets/js/app/class/class.js
$(document).ready(function () {
    // --- KONFIGURASI & VARIABEL GLOBAL ---
    const API_URL = '/api/class';
    const MAJORS_API_URL = '/api/majors';
    const CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
    const classModal = new bootstrap.Modal(document.getElementById('classModal'));

    // --- INISIALISASI DATATABLE ---
    const table = $('#classesTable').DataTable({
        processing: true,
        ajax: {
            url: API_URL,
            dataSrc: 'data'
        },
        columns: [{
            data: 'name'
        }, {
            data: 'grade_level',
            defaultContent: '-'
        }, {
            data: 'educational_level.name',
            defaultContent: '<span class="text-muted">N/A</span>'
        }, {
            data: 'major.name',
            defaultContent: '<span class="text-muted">N/A</span>'
        }, {
            data: 'teacher.name',
            defaultContent: '<span class="text-muted">N/A</span>'
        }, {
            data: 'id',
            orderable: false,
            searchable: false,
            render: function (data, type, row) {
                return `
                        <button class="btn btn-sm btn-primary edit-btn" data-id="${data}"><i class="fas fa-edit"></i> Edit</button>
                        <button class="btn btn-sm btn-danger delete-btn" data-id="${data}"><i class="fas fa-trash"></i> Delete</button>
                    `;
            }
        }]
    });

    const showNotification = (icon, title) => Swal.fire({
        icon,
        title,
        timer: 2000,
        showConfirmButton: false
    });

    const populateDropdown = (selector, data, defaultOptionText, valueKey = 'id', textKey = 'name') => {
        const dropdown = $(selector);
        dropdown.html(`<option value="">${defaultOptionText}</option>`);
        data.forEach(item => dropdown.append($('<option>', {
            value: item[valueKey],
            text: item[textKey]
        })));
    };

    const resetForm = () => {
        $('#classForm')[0].reset();
        $('#classId').val('');
        $('#generatedClassName').val('');
        $('#majorId').prop('disabled', true).html('<option value="">Select Level First</option>');
        $('.form-control, .form-select').removeClass('is-invalid');
    };

    const updateGeneratedClassName = () => {
        const gradeText = $('#gradeLevel option:selected').text();
        const levelText = $('#educationalLevelId option:selected').text();
        const majorText = $('#majorId option:selected').text();
        const majorSelect = $('#majorId');
        let className = '';
        if (gradeText && gradeText !== 'Select Grade' && levelText && levelText !== 'Select Level') {
            if (!majorSelect.prop('disabled') && majorText && majorText !== 'Select Major') {
                className = `${gradeText} ${majorText}`;
            } else {
                className = `${gradeText} ${levelText}`;
            }
        }
        $('#generatedClassName').val(className.trim());
    };

    // --- FUNGSI AJAX ---

    // DIUBAH: Tambahkan parameter 'gradeLevel'
    function fetchInitialData(levelId = null, majorId = null, teacherId = null, gradeLevel = null) {
        $.getJSON(`${API_URL}/create-data`).done(response => {
            if (response.success) {
                const {
                    teachers,
                    educational_levels
                } = response.data;
                const gradeSelect = $('#gradeLevel');
                gradeSelect.html('<option value="">Select Grade</option>');
                for (let i = 1; i <= 12; i++) {
                    gradeSelect.append(`<option value="${i}">${i}</option>`);
                }

                populateDropdown('#educationalLevelId', educational_levels, 'Select Level');
                populateDropdown('#teacherId', teachers, 'Select a Teacher');

                // DIUBAH: Logika untuk memilih nilai dipindahkan ke sini
                // Ini akan dieksekusi SETELAH semua dropdown terisi
                if (gradeLevel) {
                    $('#gradeLevel').val(gradeLevel);
                }
                if (levelId) {
                    $('#educationalLevelId').val(levelId).trigger('change', [majorId]);
                }
                if (teacherId) {
                    $('#teacherId').val(teacherId);
                }
            }
        }).fail(() => showNotification('error', 'Failed to load initial data'));
    }

    function fetchMajors(levelId, selectedMajorId = null) {
        const majorSelect = $('#majorId');
        if (!levelId) {
            majorSelect.prop('disabled', true).html('<option value="">Select Level First</option>');
            return;
        }
        $.getJSON(`${MAJORS_API_URL}?educational_level_id=${levelId}`).done(response => {
            if (response.success && response.data.length > 0) {
                populateDropdown('#majorId', response.data, 'Select Major');
                majorSelect.prop('disabled', false);
                if (selectedMajorId) {
                    majorSelect.val(selectedMajorId);
                }
            } else {
                majorSelect.prop('disabled', true).html('<option value="">No majors available</option>');
            }
            updateGeneratedClassName();
        }).fail(() => showNotification('error', 'Failed to load majors'));
    }

    // --- EVENT LISTENERS ---
    $('#classAddBtn').on('click', function () {
        resetForm();
        $('#classModalLabel').text('Add New Class');
        fetchInitialData(); // Panggil tanpa parameter untuk form tambah
        classModal.show();
    });

    $('#educationalLevelId').on('change', function (event, majorIdToSelect) {
        fetchMajors($(this).val(), majorIdToSelect);
    });

    $('#gradeLevel, #majorId, #educationalLevelId').on('change', updateGeneratedClassName);

    $('#classForm').on('submit', function (e) {
        e.preventDefault();
        updateGeneratedClassName();
        $('#saveClassBtn').text('Saving...').prop('disabled', true);
        const classId = $('#classId').val();
        let method = classId ? 'PUT' : 'POST';
        let url = classId ? `${API_URL}/${classId}` : API_URL;
        let formData = $(this).serialize();
        if (method === 'PUT') formData += '&_method=PUT';

        $.ajax({
            url: url,
            method: 'POST',
            data: formData,
            headers: { 'X-CSRF-TOKEN': CSRF_TOKEN },
            success: (response) => {
                if (response.success) {
                    classModal.hide();
                    showNotification('success', response.message);
                    table.ajax.reload(null, false);
                }
            },
            error: () => showNotification('error', 'An error occurred'),
            complete: () => $('#saveClassBtn').text('Save changes').prop('disabled', false)
        });
    });

    $('#classesTable').on('click', '.edit-btn', function () {
        const classId = $(this).data('id');
        $.getJSON(`${API_URL}/${classId}`).done(response => {
            if (response.success) {
                resetForm();
                const data = response.data;
                $('#classModalLabel').text('Edit Class');
                $('#classId').val(data.id);

                // DIUBAH: Kirim 'data.grade_level' sebagai parameter ke fungsi fetchInitialData
                fetchInitialData(data.educational_level_id, data.major_id, data.teacher_id, data.grade_level);

                // DIUBAH: Baris ini dihapus dari sini karena sudah ditangani di dalam fetchInitialData
                // $('#gradeLevel').val(data.grade_level);

                classModal.show();
            }
        }).fail(() => showNotification('error', 'Failed to fetch class data.'));
    });

    $('#classesTable').on('click', '.delete-btn', function () {
        const classId = $(this).data('id');
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `${API_URL}/${classId}`,
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': CSRF_TOKEN },
                    success: (response) => {
                        showNotification('success', response.message);
                        table.ajax.reload(null, false);
                    },
                    error: () => showNotification('error', 'Failed to delete the class.')
                });
            }
        });
    });
});
