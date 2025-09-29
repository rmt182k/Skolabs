document.addEventListener('DOMContentLoaded', function () {
    // --- ELEMEN DOM ---
    const cardContainer = document.getElementById('assignment-card-view');
    const tableContainer = document.getElementById('assignment-table-view');
    const tableBody = document.getElementById('assignment-table-body');
    const viewCardBtn = document.getElementById('view-card-btn');
    const viewTableBtn = document.getElementById('view-table-btn');
    const searchInput = document.getElementById('search-input');
    const courseFilter = document.getElementById('course-filter');
    const loadingSpinner = document.getElementById('loading-spinner'); // Tambahkan spinner di HTML

    let currentView = 'card';
    let allAssignments = []; // Variabel untuk menyimpan data dari API

    // --- FUNGSI UNTUK MENGAMBIL DATA TUGAS DARI API ---
    async function fetchAssignments() {
        // Tampilkan spinner saat loading
        if (loadingSpinner) loadingSpinner.classList.remove('d-none');

        try {
            const response = await fetch('/api/student-assignments'); // Ganti URL ini jika perlu
            if (!response.ok) {
                throw new Error(`Gagal mengambil data: ${response.statusText}`);
            }
            const result = await response.json();

            // Simpan data ke variabel global jika request berhasil
            if (result.success && Array.isArray(result.data)) {
                allAssignments = result.data;
            } else {
                console.error('Format data dari API tidak sesuai:', result);
                allAssignments = []; // Kosongkan jika format salah
            }

        } catch (error) {
            console.error('Terjadi kesalahan saat fetch data:', error);
            // Tampilkan pesan error ke pengguna
            cardContainer.innerHTML = `<div class="col-12"><div class="alert alert-danger text-center">Tidak dapat memuat data tugas. Silakan coba lagi nanti.</div></div>`;
            tableBody.innerHTML = `<tr><td colspan="5" class="text-center"><div class="alert alert-danger mb-0">Tidak dapat memuat data tugas.</div></td></tr>`;
        } finally {
            // Sembunyikan spinner setelah selesai
            if (loadingSpinner) loadingSpinner.classList.add('d-none');
        }
    }


    // --- FUNGSI UNTUK POPULASI FILTER MATA PELAJARAN ---
    function populateCourseFilter() {
        // Hapus opsi lama sebelum mengisi yang baru
        courseFilter.innerHTML = '<option value="">Semua Mata Pelajaran</option>';

        // Gunakan data dari allAssignments
        const courses = [...new Set(allAssignments.map(ass => ass.subject_name))];
        courses.sort().forEach(course => {
            const option = document.createElement('option');
            option.value = course;
            option.textContent = course;
            courseFilter.appendChild(option);
        });
    }

    // --- FUNGSI UNTUK MENDAPATKAN STATUS ---
    // Menggunakan status yang sudah dikirim dari backend
    function getAssignmentStatus(assignment) {
        const status = assignment.status.toLowerCase().replace(' ', ''); // cth: "not submitted" -> "notsubmitted"

        if (status === 'submitted') {
            return { text: 'Selesai', class: 'bg-completed', status: 'completed' };
        }
        if (status === 'overdue') {
            return { text: 'Terlambat', class: 'bg-overdue', status: 'overdue' };
        }
        // Default untuk "Not Submitted" atau status lain
        return { text: 'Belum Dikerjakan', class: 'bg-pending', status: 'pending' };
    }


    // --- FUNGSI RENDER UTAMA ---
    function renderAssignmentsAsCards(data) {
        cardContainer.innerHTML = '';
        if (data.length === 0) {
            cardContainer.innerHTML = `<div class="col-12"><div class="alert alert-warning text-center">Tugas yang Anda cari tidak ditemukan.</div></div>`;
            return;
        }
        data.forEach(assignment => {
            const statusInfo = getAssignmentStatus(assignment);
            const dueDate = new Date(assignment.due_date);
            const cardHTML =
                `<div class="col-md-6 col-lg-4 mb-4">
                    <div class="card assignment-card status-${statusInfo.status}">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <h5 class="card-title fw-bold">${assignment.title}</h5>
                                <span class="status-badge ${statusInfo.class}">${statusInfo.text}</span>
                            </div>
                            <h6 class="card-subtitle mb-2 text-muted">${assignment.subject_name}</h6>
                            <p class="card-text small text-muted">Oleh: ${assignment.teacher_name}</p>
                            <hr>
                            <div class="d-flex justify-content-between align-items-center">
                                <p class="card-text mb-0">
                                    <i class="bi bi-calendar-check"></i> Batas Waktu: <strong>${dueDate.toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' })}</strong>
                                </p>
                                <a href="/student/assignment/${assignment.id}" class="btn btn-primary btn-sm">
                                    Lihat Detail <i class="bi bi-arrow-right-short"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>`;
            cardContainer.innerHTML += cardHTML;
        });
    }

    function renderAssignmentsAsTable(data) {
        tableBody.innerHTML = '';
        if (data.length === 0) {
            tableBody.innerHTML = `<tr><td colspan="5" class="text-center"><div class="alert alert-warning mb-0">Tugas yang Anda cari tidak ditemukan.</div></td></tr>`;
            return;
        }
        data.forEach(assignment => {
            const statusInfo = getAssignmentStatus(assignment);
            const dueDate = new Date(assignment.due_date);
            const rowHTML =
                `<tr>
                    <td>
                        <div class="fw-bold">${assignment.title}</div>
                        <div class="small text-muted">${assignment.teacher_name}</div>
                    </td>
                    <td>${assignment.subject_name}</td>
                    <td>${dueDate.toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' })}</td>
                    <td><span class="status-badge ${statusInfo.class}">${statusInfo.text}</span></td>
                    <td class="text-end">
                        <a href="/student/assignment/${assignment.id}" class="btn btn-primary btn-sm">Lihat Detail</a>
                    </td>
                </tr>`;
            tableBody.innerHTML += rowHTML;
        });
    }

    // --- FUNGSI FILTER DATA ---
    function getFilteredData() {
        const statusFilterMap = {
            'pills-all-tab': 'all',
            'pills-pending-tab': 'pending',
            'pills-completed-tab': 'completed',
            'pills-overdue-tab': 'overdue'
        };
        const activeTabId = document.querySelector('#pills-tab button.active').id;
        const statusFilter = statusFilterMap[activeTabId] || 'all';

        const searchTerm = searchInput.value.toLowerCase();
        const selectedCourse = courseFilter.value;

        let filtered = allAssignments;

        // 1. Filter berdasarkan status (tab)
        if (statusFilter !== 'all') {
            filtered = filtered.filter(ass => getAssignmentStatus(ass).status === statusFilter);
        }

        // 2. Filter berdasarkan pencarian judul
        if (searchTerm) {
            filtered = filtered.filter(ass => ass.title.toLowerCase().includes(searchTerm));
        }

        // 3. Filter berdasarkan mata pelajaran
        if (selectedCourse) {
            filtered = filtered.filter(ass => ass.subject_name === selectedCourse);
        }

        return filtered;
    }

    // --- FUNGSI RENDER KONTEN (MEMUTUSKAN TAMPILAN CARD/TABLE) ---
    function renderContent() {
        const data = getFilteredData();
        if (currentView === 'card') {
            renderAssignmentsAsCards(data);
        } else {
            renderAssignmentsAsTable(data);
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
            renderContent();
        }
    });

    viewTableBtn.addEventListener('click', () => {
        if (currentView !== 'table') {
            currentView = 'table';
            cardContainer.classList.add('d-none');
            tableContainer.classList.remove('d-none');
            viewTableBtn.classList.add('active');
            viewCardBtn.classList.remove('active');
            renderContent();
        }
    });

    [searchInput, courseFilter].forEach(el => el.addEventListener('input', renderContent));

    document.querySelectorAll('#pills-tab button').forEach(tab => {
        tab.addEventListener('click', renderContent);
    });

    // --- INISIALISASI ---
    async function initializePage() {
        await fetchAssignments(); // Tunggu data selesai diambil
        populateCourseFilter(); // Baru isi filter
        renderContent();      // Baru render konten awal
    }

    initializePage(); // Panggil fungsi inisialisasi
});
