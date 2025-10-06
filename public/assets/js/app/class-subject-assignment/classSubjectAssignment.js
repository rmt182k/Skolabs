document.addEventListener('DOMContentLoaded', function() {
    // --- KONFIGURASI & VARIABEL GLOBAL ---
    const API_URL = '/api/class-subjects';
    const CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
    const assignmentModal = new bootstrap.Modal(document.getElementById('assignmentModal'));

    // --- ELEMEN DOM ---
    const tableContainer = $('#assignment-table-view');
    const cardContainer = $('#assignment-card-view');
    const viewTableBtn = $('#view-table-btn');
    const viewCardBtn = $('#view-card-btn');
    let currentView = 'table';
    let assignmentsTable;

    // --- ELEMEN DOM BARU UNTUK FILTER ---
    const filterClass = $('#filter-class');
    const filterSubject = $('#filter-subject');
    const filterTeacher = $('#filter-teacher');
    const resetFiltersBtn = $('#reset-filters-btn');

    // --- INISIALISASI ---
    $('#classId, #subjectId, #teacherId').select2({
        dropdownParent: $('#assignmentModal'),
        width: '100%'
    });

    filterClass.select2({ placeholder: 'All Classes', allowClear: true });
    filterSubject.select2({ placeholder: 'All Subjects', allowClear: true });
    filterTeacher.select2({ placeholder: 'All Teachers', allowClear: true });

    // --- FUNGSI BARU: Mengisi Dropdown Filter ---
    function populateFilters() {
        $.getJSON(`${API_URL}/filters`)
            .done(response => {
                if (response.success) {
                    populateSelect('#filter-class', response.data.classes, 'All Classes');
                    populateSelect('#filter-subject', response.data.subjects, 'All Subjects');
                    populateSelect('#filter-teacher', response.data.teachers, 'All Teachers');
                }
            })
            .fail(xhr => handleAjaxError('Failed to load filter data.', xhr));
    }

    function initializeDataTable() {
        const filters = {
            class_id: filterClass.val(),
            subject_id: filterSubject.val(),
            teacher_id: filterTeacher.val(),
        };
        const queryString = $.param(filters);

        if ($.fn.DataTable.isDataTable('#assignmentsTable')) {
            assignmentsTable.ajax.url(`${API_URL}/data?${queryString}`).load();
        } else {
            assignmentsTable = $('#assignmentsTable').DataTable({
                processing: true,
                ajax: {
                    url: `${API_URL}/data?${queryString}`,
                    // GAYA PENULISAN: Ubah dataSrc jadi fungsi untuk proses response
                    dataSrc: function(json) {
                        // Tampilkan academic year dari response
                        if (json.academic_year) {
                            $('#academic-year-display').text(`(A.Y. ${json.academic_year})`);
                        } else {
                            $('#academic-year-display').text('');
                        }
                        // Kembalikan data utama untuk tabel
                        return json.data;
                    }
                },
                columns: [{
                    data: 'class.name',
                    defaultContent: '-'
                }, {
                    data: 'subject.name',
                    defaultContent: '-'
                }, {
                    data: 'teacher.name',
                    defaultContent: '-'
                }, {
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: (data) => `
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-warning edit-btn" data-id="${data.id}"><i class="fas fa-edit"></i> Edit</button>
                            <button class="btn btn-sm btn-danger delete-btn" data-id="${data.id}"><i class="fas fa-trash"></i> Delete</button>
                        </div>
                    `
                }],
                destroy: true
            });
        }
    }

    function renderAssignmentsAsCards(groupedData) {
        cardContainer.html('');
        if (Object.keys(groupedData).length === 0) {
            cardContainer.html('<div class="alert alert-info text-center">No teaching assignments found matching your criteria.</div>');
            return;
        }

        let content = '<div class="row g-3">';
        for (const className in groupedData) {
            const assignments = groupedData[className];
            content += `
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="card class-group-card h-100">
                        <div class="card-header bg-light fw-bold">
                            <i class="bi bi-person-workspace me-2"></i>${className}
                        </div>
                        <div class="list-group list-group-flush">
            `;
            assignments.forEach(assignment => {
                content += `
                    <div class="list-group-item d-flex justify-content-between align-items-center assignment-item">
                        <div class="me-auto">
                            <div class="fw-semibold">${assignment.subject_name || 'No Subject'}</div>
                            <small class="text-muted"><i class="fas fa-chalkboard-teacher me-1"></i>${assignment.teacher_name || 'No Teacher'}</small>
                        </div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-outline-warning edit-btn" title="Edit" data-id="${assignment.id}"><i class="fas fa-edit"></i>Edit</button>
                            <button class="btn btn-sm btn-outline-danger delete-btn" title="Delete" data-id="${assignment.id}"><i class="fas fa-trash"></i>Delete</button>
                        </div>
                    </div>
                `;
            });
            content += `</div></div></div>`;
        }
        content += '</div>';
        cardContainer.html(content);
    }

    function loadDataForView() {
        if (currentView === 'table') {
            tableContainer.removeClass('d-none');
            cardContainer.addClass('d-none');
            initializeDataTable();
        } else {
            tableContainer.addClass('d-none');
            cardContainer.removeClass('d-none');
            cardContainer.html('<div class="text-center p-5"><span class="spinner-border"></span> Loading...</div>');
            const filters = {
                class_id: filterClass.val(),
                subject_id: filterSubject.val(),
                teacher_id: filterTeacher.val(),
            };
            const queryString = $.param(filters);

            $.getJSON(`${API_URL}/data-grouped?${queryString}`)
                .done(response => {
                    // GAYA PENULISAN: Tambahkan logika untuk menampilkan academic year
                    if (response.academic_year) {
                        $('#academic-year-display').text(`(A.Y. ${response.academic_year})`);
                    } else {
                        $('#academic-year-display').text('');
                    }

                    if (response.success) {
                        renderAssignmentsAsCards(response.data);
                    } else {
                        handleAjaxError(response.message);
                    }
                })
                .fail(xhr => handleAjaxError('Failed to load card data.', xhr));
        }
    }

    // --- FUNGSI-FUNGSI BANTUAN ---
    const showNotification = (icon, title, text = '') => Swal.fire({ icon, title, text, timer: 2000, showConfirmButton: false });
    const handleAjaxError = (defaultMessage, xhr = null) => {
        const message = xhr?.responseJSON?.message || defaultMessage;
        showNotification('error', 'An Error Occurred', message);
    };
    const resetForm = () => {
        $('#assignmentForm')[0].reset();
        $('#assignmentId').val('');
        $('#classId, #subjectId, #teacherId').val(null).trigger('change');
        $('#teacherId').prop('disabled', true).html('<option value="">Select Subject First</option>');
    };
    const populateSelect = (selector, data, placeholder, valueKey = 'id', textKey = 'name') => {
        const select = $(selector);
        const currentValue = select.val();
        select.html(`<option value="">${placeholder}</option>`);
        data.forEach(item => {
            select.append(new Option(item[textKey], item[valueKey], false, false));
        });
        select.val(currentValue);
    };

    function loadInitialData(classId = null, subjectId = null, teacherId = null) {
        $.getJSON(`${API_URL}/create-data`)
            .done(response => {
                if (response.success) {
                    populateSelect('#classId', response.data.classes, 'Select a Class');
                    populateSelect('#subjectId', response.data.subjects, 'Select a Subject');
                    if (classId) $('#classId').val(classId).trigger('change');
                    if (subjectId) $('#subjectId').val(subjectId).trigger('change', [teacherId]);
                }
            })
            .fail(xhr => handleAjaxError('Failed to load initial data.', xhr));
    }

    function loadTeachersForSubject(subjectId, selectedTeacherId = null) {
        const teacherSelect = $('#teacherId');
        if (!subjectId) {
            teacherSelect.prop('disabled', true).html('<option value="">Select Subject First</option>');
            return;
        }
        teacherSelect.prop('disabled', true).html('<option value="">Loading teachers...</option>');

        $.getJSON(`/api/subjects/${subjectId}/teachers`)
            .done(response => {
                if (response.success) {
                    populateSelect('#teacherId', response.data, 'Select a Teacher');
                    teacherSelect.prop('disabled', false);
                    if (selectedTeacherId) {
                        teacherSelect.val(selectedTeacherId).trigger('change');
                    }
                }
            })
            .fail(xhr => handleAjaxError('Failed to load teachers.', xhr));
    }

    // --- EVENT LISTENERS ---
    viewTableBtn.on('click', () => { if (currentView !== 'table') { currentView = 'table'; viewCardBtn.removeClass('active'); viewTableBtn.addClass('active'); loadDataForView(); } });
    viewCardBtn.on('click', () => { if (currentView !== 'card') { currentView = 'card'; viewTableBtn.removeClass('active'); viewCardBtn.addClass('active'); loadDataForView(); } });

    filterClass.on('change', loadDataForView);
    filterSubject.on('change', loadDataForView);
    filterTeacher.on('change', loadDataForView);

    resetFiltersBtn.on('click', () => {
        filterClass.val(null).trigger('change');
        filterSubject.val(null).trigger('change');
        filterTeacher.val(null).trigger('change');
    });

    $('#addAssignmentBtn').on('click', () => {
        resetForm();
        $('#assignmentModalLabel').text('🗓️ Add New Teaching Assignment');
        loadInitialData();
        assignmentModal.show();
    });

    $('#subjectId').on('change', function(event, teacherIdToSelect) { loadTeachersForSubject($(this).val(), teacherIdToSelect); });

    $('#assignmentForm').on('submit', function(e) {
        e.preventDefault();
        const assignmentId = $('#assignmentId').val();
        const url = assignmentId ? `${API_URL}/${assignmentId}` : API_URL;
        const formData = $(this).serialize();
        $('#saveAssignmentBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Saving...');

        $.ajax({
            url: url,
            method: 'POST',
            data: formData + (assignmentId ? '&_method=PUT' : ''),
            headers: { 'X-CSRF-TOKEN': CSRF_TOKEN },
            success: (response) => {
                showNotification('success', 'Success!', response.message);
                assignmentModal.hide();
                loadDataForView();
                populateFilters();
            },
            error: (xhr) => handleAjaxError('Failed to save assignment.', xhr),
            complete: () => $('#saveAssignmentBtn').prop('disabled', false).text('Save Assignment')
        });
    });

    $(document).on('click', '.edit-btn', function() {
        const assignmentId = $(this).data('id');
        $.getJSON(`${API_URL}/${assignmentId}`)
            .done(response => {
                if (response.success) {
                    resetForm();
                    const data = response.data;
                    $('#assignmentModalLabel').text('✏️ Edit Teaching Assignment');
                    $('#assignmentId').val(data.id);
                    loadInitialData(data.class_id, data.subject_id, data.teacher_id);
                    assignmentModal.show();
                }
            })
            .fail(xhr => handleAjaxError('Failed to fetch assignment data.', xhr));
    });

    $(document).on('click', '.delete-btn', function() {
        const assignmentId = $(this).data('id');
        Swal.fire({
            title: 'Are you sure?',
            text: `You are about to delete this teaching assignment.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `${API_URL}/${assignmentId}`,
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': CSRF_TOKEN },
                    success: (response) => {
                        showNotification('success', 'Deleted!', response.message);
                        loadDataForView();
                        populateFilters();
                    },
                    error: (xhr) => handleAjaxError('Failed to delete assignment.', xhr)
                });
            }
        });
    });

    // --- PEMUATAN AWAL ---
    populateFilters();
    loadDataForView();
});
