// File: public/assets/js/app/student-assigment/studentAssignmentForm.js

document.addEventListener('DOMContentLoaded', function () {

    // --- Elemen DOM ---
    const loadingSpinner = document.getElementById('loading-spinner');
    const assignmentContent = document.getElementById('assignment-content');
    const questionContainer = document.getElementById('question-container');
    const submissionForm = document.getElementById('submissionForm');

    // --- Template ---
    const templates = {
        text: document.getElementById('short-answer-question-template')?.content,
        essay: document.getElementById('essay-question-template')?.content,
        multiple_choice: document.getElementById('multiple-choice-question-template')?.content,
    };

    /**
     * Mengambil ID tugas dari URL halaman.
     */
    const getAssignmentIdFromUrl = () => {
        const pathSegments = window.location.pathname.split('/');
        // Mengambil segmen sebelum 'take' sebagai ID
        const takeIndex = pathSegments.indexOf('take');
        if (takeIndex > 0 && pathSegments[takeIndex - 1]) {
            const id = parseInt(pathSegments[takeIndex - 1], 10);
            return !isNaN(id) ? id : null;
        }
        return null;
    };

    /**
     * [FUNGSI UTAMA YANG DIPERBARUI]
     * Merender seluruh halaman tugas berdasarkan data dari API.
     * Sekarang menangani `allow_multiple_answers` untuk soal pilihan ganda.
     */
    const renderAssignment = (data) => {
        // 1. Isi detail tugas
        document.getElementById('assignment-title').textContent = data.title;
        document.getElementById('assignment-description').innerHTML = data.description || 'Tidak ada deskripsi.';

        const teacherEl = document.getElementById('assignment-teacher');
        const subjectEl = document.getElementById('assignment-subject');
        const dueDateEl = document.getElementById('assignment-due-date');

        if (teacherEl && data.teacher) teacherEl.textContent = `Guru: ${data.teacher.name}`;
        if (subjectEl && data.subject) subjectEl.textContent = `Mapel: ${data.subject.name}`;
        if (dueDateEl && data.due_date) {
            const dueDate = new Date(data.due_date);
            // Opsi untuk format tanggal yang lebih baik dan informatif
            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' };
            dueDateEl.textContent = `Batas Waktu: ${dueDate.toLocaleDateString('id-ID', options)}`;
        }

        // 2. Render setiap soal
        data.questions.forEach((question, index) => {
            const questionTemplate = templates[question.type];
            if (!questionTemplate) {
                console.warn(`Template untuk tipe soal '${question.type}' tidak ditemukan.`);
                return;
            }

            const questionCard = document.importNode(questionTemplate, true);

            // Set data umum soal
            questionCard.querySelector('.question-card').dataset.questionId = question.id;
            questionCard.querySelector('.question-number').textContent = `Soal #${index + 1}`;
            questionCard.querySelector('.question-score').textContent = `${question.score} Poin`;
            questionCard.querySelector('.question-text').innerHTML = question.question_text;

            // ========================================================== //
            // --- LOGIKA BARU UNTUK PILIHAN GANDA (JAWABAN TUNGGAL/GANDA) --- //
            if (question.type === 'multiple_choice') {
                const optionsContainer = questionCard.querySelector('.options-container');

                // Tentukan tipe input berdasarkan flag dari database
                const inputType = question.allow_multiple_answers ? 'checkbox' : 'radio';

                if (optionsContainer) {
                    question.options.forEach(option => {
                        const optionDiv = document.createElement('div');
                        optionDiv.className = 'form-check mb-2';

                        // Gunakan `inputType` yang sudah ditentukan
                        optionDiv.innerHTML = `
                            <input class="form-check-input student-answer"
                                   type="${inputType}"
                                   name="answer_${question.id}"
                                   value="${option.option_letter}"
                                   id="q${question.id}_opt${option.id}">
                            <label class="form-check-label" for="q${question.id}_opt${option.id}">
                                <strong>${option.option_letter}.</strong> ${option.option_text}
                            </label>
                        `;
                        optionsContainer.appendChild(optionDiv);
                    });

                    // Tambahkan instruksi tambahan jika jawaban boleh lebih dari satu
                    if (question.allow_multiple_answers) {
                        const instruction = document.createElement('small');
                        instruction.className = 'text-muted d-block mt-2';
                        instruction.textContent = 'Anda dapat memilih lebih dari satu jawaban.';
                        optionsContainer.insertAdjacentElement('beforebegin', instruction);
                    }
                }
            }
            // ========================================================== //

            questionContainer.appendChild(questionCard);
        });
    };

    /**
     * Mengumpulkan semua jawaban siswa menjadi format JSON untuk dikirim.
     */
    const buildSubmissionPayload = () => {
        const answers = [];
        document.querySelectorAll('.question-card').forEach(card => {
            const questionId = card.dataset.questionId;
            const answerInputs = card.querySelectorAll('.student-answer');
            let studentAnswer = null;

            // Cek tipe input untuk menentukan cara pengambilan data
            const inputType = answerInputs[0]?.type;

            if (inputType === 'checkbox' || inputType === 'radio') {
                studentAnswer = [];
                answerInputs.forEach(input => {
                    if (input.checked) {
                        studentAnswer.push(input.value);
                    }
                });
                // Jika radio button dan tidak ada yg dipilih, hasilnya array kosong.
                // Jika hanya satu jawaban (radio), kita ubah jadi string.
                if (inputType === 'radio') {
                    studentAnswer = studentAnswer.length > 0 ? studentAnswer[0] : null;
                }

            } else { // Short Answer & Essay (textarea atau input text)
                studentAnswer = answerInputs[0]?.value.trim() || null;
            }

            answers.push({
                question_id: questionId,
                answer: studentAnswer
            });
        });
        return { answers: answers };
    };

    /**
     * Menangani proses submit jawaban.
     */
    const handleSubmit = async (e) => {
        e.preventDefault();

        const result = await Swal.fire({
            title: 'Kumpulkan Tugas?',
            text: "Pastikan semua jawaban sudah terisi dengan benar.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Kumpulkan!',
            cancelButtonText: 'Batal'
        });

        if (result.isConfirmed) {
            const payload = buildSubmissionPayload();
            const assignmentId = getAssignmentIdFromUrl();

            // Tampilkan loading
            Swal.fire({
                title: 'Mengirim Jawaban...',
                text: 'Mohon tunggu sebentar.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            try {
                const response = await fetch(`/api/student-assignments/${assignmentId}/submit`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    },
                    body: JSON.stringify(payload)
                });

                const result = await response.json();
                if (!response.ok) throw new Error(result.message || 'Gagal mengirim jawaban.');

                await Swal.fire('Berhasil!', 'Jawaban Anda telah berhasil dikumpulkan.', 'success');
                // Arahkan ke halaman daftar tugas siswa
                window.location.href = '/student-assignment';

            } catch (error) {
                Swal.fire('Oops!', error.message, 'error');
            }
        }
    };

    /**
     * Memulai pengambilan data dan rendering halaman.
     */
    const initializePage = async () => {
        const assignmentId = getAssignmentIdFromUrl();
        if (!assignmentId) {
            assignmentContent.innerHTML = '<div class="alert alert-danger">ID Tugas tidak valid atau tidak ditemukan di URL.</div>';
            loadingSpinner.classList.add('d-none');
            assignmentContent.classList.remove('d-none');
            return;
        }

        try {
            const apiUrl = `/api/student-assignments/${assignmentId}/show`;
            const response = await fetch(apiUrl);

            if (!response.ok) {
                const errorResult = await response.json().catch(() => ({ message: 'Gagal memuat data tugas. Server mungkin sedang bermasalah.' }));
                throw new Error(errorResult.message);
            }

            const result = await response.json();
            if (!result.success) throw new Error(result.message);

            renderAssignment(result.data);

        } catch (error) {
            assignmentContent.innerHTML = `<div class="alert alert-danger text-center"><strong>Oops! Terjadi Kesalahan</strong><br>${error.message}</div>`;
        } finally {
            loadingSpinner.classList.add('d-none');
            assignmentContent.classList.remove('d-none');
        }
    };

    // --- Jalankan Semua ---
    initializePage();
    if (submissionForm) {
        submissionForm.addEventListener('submit', handleSubmit);
    }
});
