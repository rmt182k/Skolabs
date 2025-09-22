$(function () {
    // ======================================================================
    // PENGATURAN AWAL & DETEKSI MODE (CREATE/EDIT)
    // ======================================================================
    const form = $('#assignmentForm');
    const isEdit = form.data('is-edit') === true;
    const assignmentId = form.data('assignment-id');

    const questionBuilder = $('#question-builder');
    const emptyState = $('#empty-state');
    const questionCounter = $('#question-counter');

    // Ambil semua template dari HTML
    const templates = {
        question: $('#question-template')[0].content,
        answerText: $('#answer-text-template')[0].content,
        answerEssay: $('#answer-essay-template')[0].content,
        answerMc: $('#answer-mc-template')[0].content,
        mcOption: $('#mc-option-template')[0].content,
    };

    // ======================================================================
    // INISIALISASI HALAMAN
    // ======================================================================

    // Jalankan logika utama berdasarkan mode
    if (isEdit) {
        // --- MODE EDIT ---
        // 1. Isi dropdown subject & class
        populateSubjects().then(() => {
            populateClasses().then(() => {
                // 2. Setelah dropdown siap, ambil data dari API dan isi form
                populateFormForEdit();
            });
        });
    } else {
        // --- MODE CREATE ---
        // Cukup isi dropdown dan set tanggal default
        populateSubjects();
        populateClasses();
        const now = new Date();
        now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
        $('#startDate').val(now.toISOString().slice(0, 10));
    }

    // Panggil updateUI saat halaman pertama kali dimuat
    updateUI();


    // ======================================================================
    // FUNGSI-FUNGSI AJAX
    // ======================================================================

    /**
     * Mengisi dropdown Subjects. Mengembalikan Promise agar bisa ditunggu.
     */
    function populateSubjects() {
        return $.ajax({
            url: '/api/subjects',
            method: 'GET',
            success: function (response) {
                const dropdown = $('#subjectId');
                dropdown.empty().append($('<option>', {}));
                if (response.success && response.data.length > 0) {
                    $.each(response.data, (key, value) => {
                        dropdown.append($('<option>', { value: value.id, text: value.name }));
                    });
                }
                dropdown.select2({
                    placeholder: 'Search and select a subject',
                    width: '100%'
                });
            }
        });
    }

    /**
     * Mengisi dropdown Classes. Mengembalikan Promise agar bisa ditunggu.
     */
    function populateClasses() {
        return $.ajax({
            url: '/api/class',
            method: 'GET',
            success: function (response) {
                const dropdown = $('#classId');
                dropdown.empty();
                if (response.success && response.data.length > 0) {
                    $.each(response.data, (key, value) => {
                        dropdown.append($('<option>', { value: value.id, text: value.name }));
                    });
                }
                dropdown.select2({
                    placeholder: 'Choose Class(es)',
                    width: '100%'
                });
            }
        });
    }

    /**
     * Mengambil data assignment (dari API) dan mengisi seluruh form dalam mode EDIT.
     */
    function populateFormForEdit() {
        // Ambil data assignment dari API menggunakan method show() di controller
        $.get(`/api/assignments/${assignmentId}`, function(response) {
            if (response.success) {
                const data = response.data;

                // Isi dropdown Select2
                $('#subjectId').val(data.subject_id).trigger('change');
                $('#classId').val(data.class_id).trigger('change');

                // Bangun kembali (rebuild) semua pertanyaan
                if (data.questions && data.questions.length > 0) {
                    data.questions.forEach(question => {
                        addQuestion(question);
                    });
                }
                updateUI(); // Perbarui UI setelah semua pertanyaan dimuat
            } else {
                alert('Gagal memuat data tugas.');
                console.error(response.message);
            }
        }).fail(function() {
            alert('Terjadi kesalahan saat mengambil data tugas.');
        });
    }


    // ======================================================================
    // FUNGSI-FUNGSI UNTUK MEMBANGUN FORM SOAL (QUESTION BUILDER)
    // ======================================================================

    /**
     * Menambah kartu pertanyaan baru. Bisa diisi dengan data jika dalam mode edit.
     * @param {object|null} data - Data pertanyaan yang sudah ada.
     */
    function addQuestion(data = null) {
        const newQuestionFragment = document.importNode(templates.question, true);
        const questionCard = $(newQuestionFragment.querySelector('.question-card'));

        if (data) {
            questionCard.find('.question-text').val(data.question_text);
            questionCard.find('.question-type-select').val(data.type);
            questionCard.find('.question-score').val(data.score);
        }

        questionBuilder.append(questionCard);

        const selectElement = questionCard.find('.question-type-select')[0];
        renderAnswerContainer(selectElement, data);
    }

    /**
     * Merender container jawaban sesuai tipe soal. Bisa diisi dengan data.
     * @param {HTMLElement} selectElement - Elemen <select> tipe soal.
     * @param {object|null} data - Data pertanyaan yang sudah ada.
     */
    function renderAnswerContainer(selectElement, data = null) {
        const questionCard = $(selectElement).closest('.question-card');
        const answerContainer = questionCard.find('.answer-container');
        const selectedType = $(selectElement).val();
        answerContainer.empty();

        if (selectedType === 'text') {
            const textAnswerNode = document.importNode(templates.answerText, true);
            if (data) $(textAnswerNode).find('.correct-answer-input').val(data.correct_answer);
            answerContainer.append(textAnswerNode);
        } else if (selectedType === 'essay') {
            const essayAnswerNode = document.importNode(templates.answerEssay, true);
            if (data) $(essayAnswerNode).find('.correct-answer-textarea').val(data.correct_answer);
            answerContainer.append(essayAnswerNode);
        } else if (selectedType === 'multiple_choice') {
            const mcAnswerNode = document.importNode(templates.answerMc, true);
            const mcAnswer = $(mcAnswerNode);
            answerContainer.append(mcAnswer);
            const addBtn = mcAnswer.find('.add-option-btn')[0];

            if (data && data.options && data.options.length > 0) {
                data.options.forEach(opt => addMcOption(addBtn, opt));
            } else {
                addMcOption(addBtn);
                addMcOption(addBtn);
            }
        }
    }

    /**
     * Menambah opsi jawaban pilihan ganda. Bisa diisi dengan data.
     * @param {HTMLElement} button - Tombol 'Add Option'.
     * @param {object|null} data - Data opsi yang sudah ada.
     */
    function addMcOption(button, data = null) {
        const optionsList = $(button).siblings('.mc-options-list');
        const newOptionNode = document.importNode(templates.mcOption, true);
        const newOption = $(newOptionNode);

        if (data) {
            newOption.find('.option-input').val(data.option_text);
            newOption.find('.correct-answer-checkbox').prop('checked', data.is_correct == 1);
        }

        optionsList.append(newOption);
        reorderOptions(optionsList);
    }

    // ======================================================================
    // FUNGSI BANTUAN (HELPERS) & UI
    // ======================================================================
    function updateUI() {
        const questionsCount = $('.question-card').length;
        questionCounter.text(`${questionsCount} Question${questionsCount !== 1 ? 's' : ''}`);
        emptyState.toggle(questionsCount === 0);
        $('.question-card').each((index, el) => $(el).find('.question-number').text(index + 1));
    }

    function reorderOptions(optionsList) {
        const options = optionsList.find('.mc-option');
        options.each((index, el) => $(el).find('.option-label').text(String.fromCharCode(65 + index)));
        options.find('.btn-remove-option').toggle(options.length > 2);
    }

    function getOptionLetter(index) {
        return String.fromCharCode(65 + index);
    }

    // ======================================================================
    // VALIDASI FORM
    // ======================================================================
    function validateForm() {
        let isValid = true;
        const errors = [];
        $('.is-invalid').removeClass('is-invalid');
        $('#subjectId, #classId').next('.select2-container').removeClass('is-invalid');

        if (!$('#assignmentTitle').val().trim()) {
            errors.push('Assignment title is required');
            $('#assignmentTitle').addClass('is-invalid');
            isValid = false;
        }
        if (!$('#assignmentType').val()) {
            errors.push('Assignment type is required');
            $('#assignmentType').addClass('is-invalid');
            isValid = false;
        }
        if (!$('#subjectId').val()) {
            errors.push('Subject is required');
            $('#subjectId').next('.select2-container').addClass('is-invalid');
            isValid = false;
        }
        if (!$('#classId').val() || $('#classId').val().length === 0) {
            errors.push('At least one Class is required');
            $('#classId').next('.select2-container').addClass('is-invalid');
            isValid = false;
        }

        const startDate = $('#startDate').val();
        const dueDate = $('#dueDate').val();
        if (startDate && dueDate && new Date(startDate) > new Date(dueDate)) {
            errors.push('Start Date cannot be after Due Date.');
            $('#startDate, #dueDate').addClass('is-invalid');
            isValid = false;
        }

        if ($('.question-card').length === 0) {
            errors.push('At least one question is required');
            isValid = false;
        }

        $('.question-card').each(function (index) {
            const $card = $(this);
            const qText = $card.find('.question-text');
            const qScore = $card.find('.question-score');
            const qType = $card.find('.question-type-select').val();

            if (!qText.val().trim()) {
                errors.push(`Question ${index + 1}: Text is required.`);
                qText.addClass('is-invalid');
                isValid = false;
            }
            if (!qScore.val() || parseInt(qScore.val(), 10) < 1) {
                errors.push(`Question ${index + 1}: Score must be a positive number.`);
                qScore.addClass('is-invalid');
                isValid = false;
            }
            if (qType === 'multiple_choice') {
                if ($card.find('.option-input').filter((i, el) => $(el).val().trim()).length < 2) {
                    errors.push(`Question ${index + 1}: At least 2 options are required.`);
                    isValid = false;
                }
                if ($card.find('.correct-answer-checkbox:checked').length === 0) {
                    errors.push(`Question ${index + 1}: At least one correct answer must be selected.`);
                    isValid = false;
                }
            } else if (qType === 'text') {
                const correctAnswerInput = $card.find('.correct-answer-input');
                if (!correctAnswerInput.val().trim()) {
                    errors.push(`Question ${index + 1}: A correct answer is required.`);
                    correctAnswerInput.addClass('is-invalid');
                    isValid = false;
                }
            }
        });

        if (!isValid && errors.length > 0) {
            alert('Please fix the following error:\n' + errors[0]);
        }
        return isValid;
    }

    // ======================================================================
    // MEMBUAT PAYLOAD DATA UNTUK DIKIRIM KE SERVER
    // ======================================================================
    function buildPayload() {
        const payload = {
            title: $('#assignmentTitle').val().trim(),
            assignment_type: $('#assignmentType').val(),
            subject_id: $('#subjectId').val(),
            class_id: $('#classId').val(),
            start_date: $('#startDate').val() || null,
            due_date: $('#dueDate').val() || null,
            description: $('#description').val().trim(),
            questions: []
        };

        $('.question-card').each(function (index) {
            const $card = $(this);
            const questionData = {
                order: index + 1,
                question_text: $card.find('.question-text').val().trim(),
                type: $card.find('.question-type-select').val(),
                score: parseInt($card.find('.question-score').val(), 10) || 0,
                options: [],
                correct_answer: null
            };

            if (questionData.type === 'multiple_choice') {
                $card.find('.mc-option').each(function (optIndex) {
                    const optionText = $(this).find('.option-input').val().trim();
                    if (optionText) {
                        questionData.options.push({
                            option_letter: getOptionLetter(optIndex),
                            option_text: optionText,
                            is_correct: $(this).find('.correct-answer-checkbox').is(':checked')
                        });
                    }
                });
            } else if (questionData.type === 'text') {
                questionData.correct_answer = $card.find('.correct-answer-input').val().trim();
            } else if (questionData.type === 'essay') {
                questionData.correct_answer = $card.find('.correct-answer-textarea').val().trim();
            }
            payload.questions.push(questionData);
        });
        return payload;
    }


    // ======================================================================
    // EVENT LISTENERS UNTUK TOMBOL DAN INTERAKSI
    // ======================================================================

    // Listener untuk tombol simpan/update
    $('#saveBtn').on('click', function () {
        if (!validateForm()) return;

        const saveBtn = $(this);
        const originalText = saveBtn.html();
        saveBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Saving...');

        const payload = buildPayload();
        let url = '/api/assignments';
        let method = 'POST'; // AJAX method is always POST

        if (isEdit) {
            url = `/api/assignments/${assignmentId}`;
            payload._method = 'PUT'; // Use method spoofing for Laravel update
        }

        $.ajax({
            url: url,
            method: method,
            contentType: 'application/json',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: JSON.stringify(payload),
            success: function(response) {
                alert(response.message);
                window.location.href = '/assignment';
            },
            error: function(xhr) {
                saveBtn.prop('disabled', false).html(originalText);
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    let errorMessages = 'Please fix the following errors:\n';
                    $.each(errors, (key, value) => {
                        errorMessages += `- ${value[0]}\n`;
                    });
                    alert(errorMessages);
                } else {
                    console.error("Server Error: ", xhr.responseText);
                    alert('An unexpected error occurred. Please try again.');
                }
            }
        });
    });

    // Listeners untuk interaksi question builder
    $('#add-question-btn').on('click', () => {
        addQuestion();
        updateUI();
    });
    questionBuilder.on('change', '.question-type-select', function() {
        renderAnswerContainer(this);
    });
    questionBuilder.on('click', '.btn-remove-question', function() {
        if (confirm('Are you sure you want to delete this question?')) {
            $(this).closest('.question-card').remove();
            updateUI();
        }
    });
    questionBuilder.on('click', '.add-option-btn', function() {
        addMcOption(this);
    });
    questionBuilder.on('click', '.btn-remove-option', function() {
        const option = $(this).closest('.mc-option');
        const optionsList = option.closest('.mc-options-list');
        option.remove();
        reorderOptions(optionsList);
    });
    questionBuilder.on('change', '.correct-answer-checkbox', function () {
        // (Fungsi validasi checkbox bisa ditambahkan di sini jika perlu)
    });
});
