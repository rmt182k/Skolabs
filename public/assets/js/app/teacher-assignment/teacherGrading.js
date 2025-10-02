$(function () {
    // ======================================================================
    // PENGATURAN AWAL
    // ======================================================================
    const questionsContainer = $('#questions-container');
    const templates = {
        questionCard: $('#question-card-template')[0].content,
        textEssay: $('#text-essay-template')[0].content,
        multipleChoice: $('#multiple-choice-template')[0].content,
        mcOption: $('#mc-option-template')[0].content,
    };

    // ======================================================================
    // FUNGSI UTAMA & PENGAMBILAN DATA
    // ======================================================================

    /**
     * Mengambil ID pengumpulan tugas dari URL halaman saat ini.
     * @returns {string|null} ID pengumpulan tugas atau null jika tidak ditemukan.
     */
    function getSubmissionIdFromUrl() {
        const pathParts = window.location.pathname.split('/');
        // Untuk URL /teacher-submission/2/grade, pathParts akan menjadi ["", "teacher-submission", "2", "grade"]
        // ID pengumpulan adalah elemen sebelum 'grade'.
        const gradeIndex = pathParts.indexOf('grade');
        if (gradeIndex > 1) { // Pastikan 'grade' ada dan ada sesuatu sebelumnya
            return pathParts[gradeIndex - 1];
        }

        console.error("Submission ID not found in URL. Expected format: /teacher-submission/{id}/grade");
        return null;
    }

    /**
     * Fungsi utama untuk mengambil data penilaian dari server dan memulai proses render.
     */
    function loadGradingData() {
        const submissionId = getSubmissionIdFromUrl();
        if (!submissionId) {
            showError('Tidak dapat menemukan ID pengumpulan tugas di URL.');
            return;
        }

        $.ajax({
            url: `/api/teacher-submissions/${submissionId}/grade`, // Route API untuk mengambil data
            method: 'GET',
            dataType: 'json',
            success: function (response) {
                if (response.success && response.data) {
                    renderGradingPage(response.data);
                } else {
                    showError(response.message || 'Gagal memuat data penilaian dari server.');
                }
            },
            error: function (jqXHR) {
                const errorMsg = jqXHR.responseJSON?.message || 'Terjadi kesalahan saat menghubungi server.';
                showError(errorMsg);
            }
        });
    }

    /**
     * Menampilkan pesan error di halaman jika data gagal dimuat.
     * @param {string} message - Pesan error yang akan ditampilkan.
     */
    function showError(message) {
        questionsContainer.empty(); // Hapus placeholder
        const alertHtml = `
            <div class="alert alert-danger mx-3">
                <h4><i class="fas fa-exclamation-triangle me-2"></i> Terjadi Kesalahan</h4>
                <p>${message}</p>
                <p>Silakan muat ulang halaman atau kembali ke halaman sebelumnya.</p>
            </div>`;
        questionsContainer.html(alertHtml);
        $('.sticky-top .card').hide(); // Sembunyikan panel skor
    }

    /**
     * Merender seluruh komponen halaman setelah data diterima dari API.
     * @param {object} data - Objek data lengkap dari API yang strukturnya mirip dummyData.
     */
    function renderGradingPage(data) {
        // Hapus placeholder/loading state
        questionsContainer.empty();
        $('.placeholder-glow .placeholder').remove();

        // Simpan ID penting di body untuk digunakan saat menyimpan
        $('body').data('submission-id', data.submission.id);
        $('body').data('assignment-id', data.assignment.id);

        renderBreadcrumbs(data.assignment);
        renderSummaryPanel(data);
        renderQuestions(data.results);
        calculateAndUpdateScore(); // Hitung skor awal
    }


    // ======================================================================
    // FUNGSI-FUNGSI UNTUK MERENDER SETIAP BAGIAN
    // ======================================================================

    function renderBreadcrumbs(assignment) {
        const assignmentLink = `<a href="/teacher/assignment/${assignment.id}/submissions">${assignment.title}</a>`;
        $('#breadcrumb-assignment-link').html(assignmentLink);
        $('#breadcrumb-action').text('Penilaian');
    }

    function renderSummaryPanel(data) {
        $('#student-name').text(data.student.name);
        $('#student-nisn').text(`NISN: ${data.student.nisn}`);
        $('#submission-time').text(new Date(data.submission.submittedAt).toLocaleString('id-ID'));

        const statusBadge = data.submission.status === 'Tepat Waktu' ?
            `<span class="badge bg-success">${data.submission.status}</span>` :
            `<span class="badge bg-warning text-dark">${data.submission.status}</span>`;
        $('#submission-status').html(statusBadge);


        const actionButtonContainer = $('#action-button-container');
        if (data.submission.isGraded) {
            actionButtonContainer.html('<button id="update-grade-btn" class="btn btn-warning btn-lg w-100"><i class="fas fa-save me-2"></i> Perbarui Nilai</button>');
        } else {
            actionButtonContainer.html('<button id="save-grade-btn" class="btn btn-primary btn-lg w-100"><i class="fas fa-check-circle me-2"></i> Simpan Penilaian</button>');
        }
    }

    function renderQuestions(results) {
        results.forEach((result, index) => {
            const questionCard = $(templates.questionCard.cloneNode(true));
            const question = result.question;

            // Simpan ID jawaban di elemen kartu soal untuk digunakan saat menyimpan
            questionCard.find('.question-card').data('answer-id', result.answerId);

            // Isi informasi dasar kartu soal
            questionCard.find('.question-number').text(`Soal #${index + 1}`);
            questionCard.find('.question-points').text(`(${question.score} Poin)`);
            questionCard.find('.question-type').text(question.type.replace('_', ' ').toUpperCase());
            questionCard.find('.question-text').html(question.text);

            // Isi bagian jawaban berdasarkan tipe soal
            const answerWrapper = questionCard.find('.answer-content-wrapper');
            if (question.type === 'text' || question.type === 'essay') {
                renderTextOrEssay(answerWrapper, question, result);
            } else if (question.type === 'multiple_choice') {
                renderMultipleChoice(answerWrapper, question, result);
            }

            // Isi bagian footer (feedback dan skor)
            questionCard.find('.feedback-input').val(result.feedback);
            const pointsAwarded = result.pointsAwarded === null ? '' : result.pointsAwarded;
            questionCard.find('.score-input').val(pointsAwarded).attr('max', question.score);
            questionCard.find('.max-score-text').text(`/ ${question.score}`);

            questionsContainer.append(questionCard);
        });
    }

    function renderTextOrEssay(wrapper, question, result) {
        const content = $(templates.textEssay.cloneNode(true));
        content.find('.student-answer-content').text(result.studentAnswer || '(Siswa tidak menjawab)');

        const correctAnswerContent = question.modelAnswer || question.correctAnswer;
        if (correctAnswerContent) {
            const correctAnswerTitle = question.type === 'essay' ? 'Kunci Jawaban / Rubrik' : 'Kunci Jawaban';
            content.find('.correct-answer-title').text(correctAnswerTitle);
            content.find('.correct-answer-content').text(correctAnswerContent);
            content.find('.correct-answer-box').show();
        }

        wrapper.append(content);
    }

    function renderMultipleChoice(wrapper, question, result) {
        const content = $(templates.multipleChoice.cloneNode(true));
        content.find('.correct-answer-content').text(question.correctAnswer.join(', '));
        const mcContainer = content.find('.mc-options-container');

        question.options.forEach(option => {
            const optionEl = $(templates.mcOption.cloneNode(true));
            const isCorrect = question.correctAnswer.includes(option.letter);
            const isStudentChoice = result.studentAnswer === option.letter;

            optionEl.find('.option-text').text(`${option.letter}. ${option.text}`);

            let iconHtml = '<i class="far fa-circle text-muted"></i>'; // Default
            if (isCorrect) {
                optionEl.addClass('correct-answer');
                iconHtml = '<i class="fas fa-check-circle text-success"></i>';
            }
            if (isStudentChoice) {
                optionEl.addClass('student-choice');
                if (!isCorrect) {
                    optionEl.addClass('wrong-answer');
                    iconHtml = '<i class="fas fa-times-circle text-danger"></i>';
                }
            }
            optionEl.find('.icon').html(iconHtml);
            mcContainer.append(optionEl);
        });
        wrapper.append(content);
    }

    // ======================================================================
    // FUNGSI UNTUK KALKULASI DAN INTERAKSI
    // ======================================================================

    function calculateAndUpdateScore() {
        let totalPointsAwarded = 0;
        let maxTotalPoints = 0;

        $('.question-card').each(function () {
            const scoreInput = $(this).find('.score-input');
            const maxScore = parseInt(scoreInput.attr('max'), 10);
            maxTotalPoints += isNaN(maxScore) ? 0 : maxScore;

            let awarded = parseInt(scoreInput.val(), 10);
            totalPointsAwarded += isNaN(awarded) ? 0 : awarded;
        });

        const finalScore = maxTotalPoints > 0 ? Math.round((totalPointsAwarded / maxTotalPoints) * 100) : 0;

        $('#final-score-display').text(finalScore);
        $('#final-points-display').text(`(${totalPointsAwarded} / ${maxTotalPoints} Poin)`);
    }

    /**
     * Mengumpulkan data nilai dari form dan mengirimkannya ke server via AJAX.
     */
    function saveGradingData() {
        const submissionId = $('body').data('submission-id');
        const assignmentId = $('body').data('assignment-id');
        const button = $('#action-button-container').find('button');
        const originalButtonHtml = button.html();

        let gradesPayload = {};
        $('.question-card').each(function () {
            const answerId = $(this).data('answer-id');
            const score = $(this).find('.score-input').val();
            const feedback = $(this).find('.feedback-input').val();

            if (answerId) {
                gradesPayload[answerId] = {
                    score: score === '' ? null : parseInt(score, 10),
                    feedback: feedback
                };
            }
        });

        $.ajax({
            url: `/api/teacher-submissions/${submissionId}/grade`, // Route API untuk menyimpan data
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                grades: gradesPayload
            }),
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function () {
                button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Menyimpan...');
            },
            success: function (response) {
                if (response.success) {
                    alert(response.message); // Bisa diganti dengan notifikasi yang lebih baik (misal: SweetAlert)
                    window.location.href = response.redirect_url || `/teacher/assignment/${assignmentId}/submissions`;
                } else {
                    alert('Gagal menyimpan: ' + (response.message || 'Error tidak diketahui.'));
                    button.prop('disabled', false).html(originalButtonHtml);
                }
            },
            error: function (jqXHR) {
                const errorMsg = jqXHR.responseJSON?.message || 'Terjadi kesalahan saat menyimpan data.';
                alert('Error: ' + errorMsg);
                button.prop('disabled', false).html(originalButtonHtml);
            }
        });
    }

    // ======================================================================
    // EVENT LISTENERS
    // ======================================================================

    questionsContainer.on('input', '.score-input', function () {
        const max = parseInt($(this).attr('max'), 10);
        const value = parseInt($(this).val(), 10);
        if (value > max) {
            $(this).val(max);
        } else if (value < 0) {
            $(this).val(0);
        }
        calculateAndUpdateScore();
    });

    $('#action-button-container').on('click', '#save-grade-btn, #update-grade-btn', function (e) {
        e.preventDefault();
        saveGradingData();
    });

    // ======================================================================
    // INISIALISASI HALAMAN
    // ======================================================================
    loadGradingData();

});
