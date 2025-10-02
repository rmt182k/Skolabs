document.addEventListener('DOMContentLoaded', function () {
    // --- ELEMEN DOM ---
    const cardContainer = document.getElementById('assignment-card-view');
    const tableContainer = document.getElementById('assignment-table-view');
    const tableBody = document.getElementById('assignment-table-body');
    const viewCardBtn = document.getElementById('view-card-btn');
    const viewTableBtn = document.getElementById('view-table-btn');
    const searchInput = document.getElementById('search-input');
    const courseFilter = document.getElementById('course-filter');
    const classFilter = document.getElementById('class-filter');
    const typeFilter = document.getElementById('type-filter');

    let currentView = 'card';
    // Variabel untuk menyimpan URL API kita
    const API_URL = '/api/teacher/assignments'; // Sesuaikan jika path Anda berbeda
    const FILTER_DATA_URL = '/api/teacher/assignments/filters';

    // --- FUNGSI TAMPILAN (RENDER) ---
    // Fungsi render kartu dan tabel tidak perlu banyak diubah, hanya memastikan datanya dari server

    function getAssignmentInfo(assignment) {
        const now = new Date();
        const dueDate = new Date(assignment.dueDate);
        const isPastDue = now > dueDate && (now.setHours(0, 0, 0, 0) !== dueDate.setHours(0, 0, 0, 0));

        if (assignment.gradedCount == assignment.submittedCount && assignment.submittedCount > 0) {
            return { status: 'completed', text: 'Selesai Dinilai', class: 'bg-completed' };
        }
        if (assignment.submittedCount > assignment.gradedCount) {
            return { status: 'grading', text: 'Perlu Dinilai', class: 'bg-grading' };
        }
        if (isPastDue) {
            return { status: 'active', text: 'Batas Waktu Lewat', class: 'bg-overdue' };
        }
        return { status: 'active', text: 'Aktif', class: 'bg-active' };
    }

    function getTypeBadge(type) {
        // ... (Fungsi getTypeBadge Anda tetap sama, tidak perlu diubah)
        let iconClass = '', badgeClass = '';
        switch (type) {
            case 'task': iconClass = 'bi-file-earmark-text'; badgeClass = 'bg-primary'; break;
            case 'quiz': iconClass = 'bi-patch-question'; badgeClass = 'bg-success'; break;
            case 'exam': iconClass = 'bi-journal-check'; badgeClass = 'bg-danger'; break;
            default: iconClass = 'bi-tag'; badgeClass = 'bg-secondary';
        }
        const capitalizedType = type.charAt(0).toUpperCase() + type.slice(1);
        return `<span class="badge ${badgeClass} fw-normal"><i class="bi ${iconClass} me-1"></i>${capitalizedType}</span>`;
    }

    function renderAssignmentsAsCards(data) {
        // ... (Fungsi renderAssignmentsAsCards Anda tetap sama)
        cardContainer.innerHTML = '';
        if (data.length === 0) {
            cardContainer.innerHTML = `<div class="col-12"><div class="alert alert-info text-center">Tidak ada tugas yang sesuai dengan filter.</div></div>`;
            return;
        }
        data.forEach(assignment => {
            const info = getAssignmentInfo(assignment);
            const progress = assignment.totalStudents > 0 ? (assignment.submittedCount / assignment.totalStudents) * 100 : 0;
            const dueDate = new Date(assignment.dueDate);
            const typeBadge = getTypeBadge(assignment.type);
            const cardHTML = `
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card assignment-card status-${info.status}">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="card-title fw-bold mb-0 me-2">${assignment.title}</h5>
                                <span class="status-badge ${info.class}">${info.text}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="card-subtitle text-muted">${assignment.class || 'Belum ada kelas'} &middot; ${assignment.course}</h6>
                                ${typeBadge}
                            </div>
                            <hr>
                            <div class="progress-container">
                                <div class="d-flex justify-content-between small">
                                    <span>Terkumpul: <strong>${assignment.submittedCount}/${assignment.totalStudents}</strong></span>
                                    <span>Ternilai: <strong>${assignment.gradedCount}/${assignment.submittedCount}</strong></span>
                                </div>
                                <div class="progress mt-1" style="height: 10px;">
                                    <div class="progress-bar" role="progressbar" style="width: ${progress}%;" aria-valuenow="${progress}" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <p class="card-text mb-0 small text-muted">
                                    <i class="bi bi-calendar-check"></i> Batas: ${dueDate.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })}
                                </p>
                                <a href="/teacher-assignment/${assignment.id}/submissions" class="btn btn-primary btn-sm">Lihat Submission</a>
                            </div>
                        </div>
                    </div>
                </div>`;
            cardContainer.innerHTML += cardHTML;
        });
    }

    function renderAssignmentsAsTable(data) {
        // ... (Fungsi renderAssignmentsAsTable Anda tetap sama)
        tableBody.innerHTML = '';
        if (data.length === 0) {
            tableBody.innerHTML = `<tr><td colspan="6" class="text-center"><div class="alert alert-info mb-0">Tidak ada tugas yang sesuai dengan filter.</div></td></tr>`;
            return;
        }
        data.forEach(assignment => {
            const info = getAssignmentInfo(assignment);
            const progress = assignment.totalStudents > 0 ? (assignment.submittedCount / assignment.totalStudents) * 100 : 0;
            const dueDate = new Date(assignment.dueDate);
            const typeBadge = getTypeBadge(assignment.type);
            const rowHTML = `
                <tr>
                    <td>
                        <div class="fw-bold">${assignment.title}</div>
                        <div class="small text-muted">${info.text}</div>
                    </td>
                    <td>${typeBadge}</td>
                    <td>
                        <div class="fw-medium">${assignment.class || 'Belum ada kelas'}</div>
                        <div class="small text-muted">${assignment.course}</div>
                    </td>
                    <td>${dueDate.toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' })}</td>
                    <td>
                        <div class="d-flex align-items-center">
                            <span class="me-2">${assignment.submittedCount}/${assignment.totalStudents}</span>
                            <div class="progress flex-grow-1" style="height: 8px;">
                                <div class="progress-bar" role="progressbar" style="width: ${progress}%;" aria-valuenow="${progress}"></div>
                            </div>
                        </div>
                    </td>
                    <td class="text-center">
                        <div class="btn-group">
                             <a href="/teacher/assignment/${assignment.id}/submissions" class="btn btn-sm btn-outline-primary" title="Lihat Submission"><i class="bi bi-eye-fill"></i></a>
                             <a href="/teacher/assignment/${assignment.id}/edit" class="btn btn-sm btn-outline-secondary" title="Edit Tugas"><i class="bi bi-pencil-fill"></i></a>
                             <button class="btn btn-sm btn-outline-danger" title="Hapus Tugas"><i class="bi bi-trash-fill"></i></button>
                        </div>
                    </td>
                </tr>`;
            tableBody.innerHTML += rowHTML;
        });
    }

    // --- FUNGSI PENGAMBILAN DATA (AJAX) ---

    // Fungsi untuk mengambil data tugas dari backend berdasarkan filter
    async function fetchAndRenderAssignments() {
        // Tampilkan loading spinner/placeholder
        cardContainer.innerHTML = `<div class="col-12 text-center p-5"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>`;
        tableBody.innerHTML = `<tr><td colspan="6" class="text-center p-5"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></td></tr>`;

        // 1. Kumpulkan semua nilai filter
        const params = new URLSearchParams({
            search: searchInput.value,
            course_id: courseFilter.value,
            class_id: classFilter.value,
            type: typeFilter.value,
            status: document.querySelector('#pills-tab button.active').id.replace('-tab', '').replace('pills-', '')
        });

        // 2. Lakukan request ke API
        try {
            const response = await fetch(`${API_URL}?${params.toString()}`);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const result = await response.json();

            if (result.success) {
                // 3. Render data yang diterima
                if (currentView === 'card') {
                    renderAssignmentsAsCards(result.data);
                } else {
                    renderAssignmentsAsTable(result.data);
                }
            } else {
                throw new Error(result.message || 'Gagal mengambil data.');
            }
        } catch (error) {
            console.error("Fetch error:", error);
            // Tampilkan pesan error di UI
            cardContainer.innerHTML = `<div class="col-12"><div class="alert alert-danger">Terjadi kesalahan saat memuat data. Silakan coba lagi.</div></div>`;
            tableBody.innerHTML = `<tr><td colspan="6" class="text-center"><div class="alert alert-danger mb-0">Terjadi kesalahan saat memuat data.</div></td></tr>`;
        }
    }

    // Fungsi untuk mengisi dropdown filter dari backend
    async function populateFilters() {
        try {
            const response = await fetch(FILTER_DATA_URL);
            const result = await response.json();

            if (result.success) {
                // Isi filter Mata Pelajaran
                courseFilter.innerHTML = '<option value="">Semua Mata Pelajaran</option>';
                result.data.subjects.forEach(subject => {
                    courseFilter.innerHTML += `<option value="${subject.id}">${subject.name}</option>`;
                });
                // Isi filter Kelas
                classFilter.innerHTML = '<option value="">Semua Kelas</option>';
                result.data.classes.forEach(cls => {
                    classFilter.innerHTML += `<option value="${cls.id}">${cls.name}</option>`;
                });

                // --- TAMBAHKAN INI ---
                // Isi filter Tipe Tugas
                typeFilter.innerHTML = '<option value="">Semua Tipe</option>';
                if (result.data.types && result.data.types.length > 0) {
                    result.data.types.forEach(type => {
                        const capitalizedType = type.charAt(0).toUpperCase() + type.slice(1);
                        typeFilter.innerHTML += `<option value="${type}">${capitalizedType}</option>`;
                    });
                }
                // --- AKHIR TAMBAHAN ---

            } else {
                throw new Error(result.message || 'Gagal memuat data filter.');
            }
        } catch (error) {
            console.error("Filter populate error:", error);
            courseFilter.innerHTML = '<option value="">Gagal memuat</option>';
            classFilter.innerHTML = '<option value="">Gagal memuat</option>';
            // Tambahkan pesan error untuk type filter juga
            typeFilter.innerHTML = '<option value="">Gagal memuat</option>';
        }
    }


    // --- EVENT LISTENERS ---
    viewCardBtn.addEventListener('click', () => {
        if (currentView !== 'card') {
            currentView = 'card';
            tableContainer.classList.add('d-none');
            cardContainer.classList.remove('d-none');
            viewCardBtn.classList.add('active');
            viewTableBtn.classList.remove('active');
            fetchAndRenderAssignments(); // Render ulang dengan data terbaru
        }
    });

    viewTableBtn.addEventListener('click', () => {
        if (currentView !== 'table') {
            currentView = 'table';
            cardContainer.classList.add('d-none');
            tableContainer.classList.remove('d-none');
            viewTableBtn.classList.add('active');
            viewCardBtn.classList.remove('active');
            fetchAndRenderAssignments(); // Render ulang dengan data terbaru
        }
    });

    // Event listener untuk semua filter. Setiap ada perubahan, panggil API.
    [searchInput, courseFilter, classFilter, typeFilter].forEach(el => {
        // Gunakan 'change' untuk select, dan 'input' untuk search agar lebih efisien
        const eventType = el.tagName === 'SELECT' ? 'change' : 'input';
        el.addEventListener(eventType, fetchAndRenderAssignments);
    });

    document.querySelectorAll('#pills-tab button').forEach(tab => {
        tab.addEventListener('click', fetchAndRenderAssignments);
    });

    // --- INISIALISASI ---
    // 1. Isi dulu dropdown filter
    // 2. Setelah itu, ambil data tugas untuk pertama kali
    populateFilters().then(() => {
        fetchAndRenderAssignments();
    });
});
