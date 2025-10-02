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
    if (isEdit) {
        populateSubjects().then(() => {
            populateClasses().then(() => {
                populateFormForEdit();
            });
        });
    } else {
        populateSubjects();
        populateClasses();
        const now = new Date();
        now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
        $('#startDate').val(now.toISOString().slice(0, 10));
    }

    updateUI();

    // ======================================================================
    // FUNGSI-FUNGSI AJAX
    // ======================================================================
    function populateSubjects() {
        return $.ajax({
            url: '/api/subjects', method: 'GET', success: function (response) {
                const dropdown = $('#subjectId');
                dropdown.empty().append($('<option>', {}));
                if (response.success && response.data.length > 0) {
                    $.each(response.data, (key, value) => {
                        dropdown.append($('<option>', { value: value.id, text: value.name }));
                    });
                }
                dropdown.select2({ placeholder: 'Search and select a subject', width: '100%' });
            }
        });
    }

    function populateClasses() {
        return $.ajax({
            url: '/api/class', method: 'GET', success: function (response) {
                const dropdown = $('#classId');
                dropdown.empty();
                if (response.success && response.data.length > 0) {
                    $.each(response.data, (key, value) => {
                        dropdown.append($('<option>', { value: value.id, text: value.name }));
                    });
                }
                dropdown.select2({ placeholder: 'Choose Class(es)', width: '100%' });
            }
        });
    }

    function populateFormForEdit() {
        $.get(`/api/assignments/${assignmentId}`, function (response) {
            if (response.success) {
                const data = response.data;
                $('#assignmentTitle').val(data.title);
                $('#assignmentType').val(data.assignment_type);
                $('#description').val(data.description);
                if (data.start_date) $('#startDate').val(data.start_date.split(' ')[0]);
                if (data.due_date) $('#dueDate').val(data.due_date.split(' ')[0]);

                $('#subjectId').val(data.subject_id).trigger('change');
                $('#classId').val(data.class_id).trigger('change');

                if (data.questions && data.questions.length > 0) {
                    data.questions.forEach(question => addQuestion(question));
                }
                updateUI();
            } else {
                alert('Gagal memuat data tugas.');
                console.error(response.message);
            }
        }).fail(function () {
            alert('Terjadi kesalahan saat mengambil data tugas.');
        });
    }

    // ======================================================================
    // FUNGSI-FUNGSI UNTUK MEMBANGUN FORM SOAL
    // ======================================================================
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

    function renderAnswerContainer(selectElement, data = null) {
        const questionCard = $(selectElement).closest('.question-card');
        const answerContainer = questionCard.find('.answer-container');
        const selectedType = $(selectElement).val();
        answerContainer.empty();

        if (selectedType === 'text') {
            const node = document.importNode(templates.answerText, true);
            if (data) $(node).find('.correct-answer-input').val(data.correct_answer);
            answerContainer.append(node);
        } else if (selectedType === 'essay') {
            const node = document.importNode(templates.answerEssay, true);
            if (data) $(node).find('.correct-answer-textarea').val(data.correct_answer);
            answerContainer.append(node);
        } else if (selectedType === 'multiple_choice') {
            const node = document.importNode(templates.answerMc, true);
            const mcAnswerWrapper = $(node.firstElementChild);

            const allowMultipleCb = mcAnswerWrapper.find('.allow-multiple-answers-cb');
            const addBtn = mcAnswerWrapper.find('.add-option-btn')[0];
            const optionsList = mcAnswerWrapper.find('.mc-options-list');

            // Set initial state of "allow multiple" checkbox
            const allowMultiple = data ? data.allow_multiple_answers == 1 : false;
            allowMultipleCb.prop('checked', allowMultiple);

            answerContainer.append(mcAnswerWrapper);

            if (data && data.options && data.options.length > 0) {
                data.options.forEach(opt => addMcOption(addBtn, opt));
            } else {
                addMcOption(addBtn); addMcOption(addBtn); // Default 2 options
            }
            // Update input types (radio/checkbox) after adding options
            updateMcInputType(optionsList, allowMultiple);
        }
    }

    function addMcOption(button, data = null) {
        const optionsList = $(button).siblings('.mc-options-list');
        const newOptionNode = document.importNode(templates.mcOption, true);
        const newOption = $(newOptionNode.firstElementChild);

        if (data) {
            newOption.find('.option-input').val(data.option_text);
            newOption.find('.correct-answer-selector').prop('checked', data.is_correct == 1);
        }

        optionsList.append(newOption);

        // Let renderAnswerContainer handle the input type update
        const allowMultiple = $(button).closest('.answer-container').find('.allow-multiple-answers-cb').is(':checked');
        updateMcInputType(optionsList, allowMultiple);
        reorderOptions(optionsList);
    }

    /**
     * Changes MC input types between 'radio' and 'checkbox'
     */
    function updateMcInputType(optionsList, allowMultiple) {
        const questionIndex = optionsList.closest('.question-card').index();
        const inputs = optionsList.find('.correct-answer-selector');

        if (allowMultiple) {
            inputs.attr('type', 'checkbox').attr('name', null);
        } else {
            // Uncheck all before switching to radio to prevent multiple checked
            if (inputs.filter(':checked').length > 1) {
                inputs.prop('checked', false);
            }
            inputs.attr('type', 'radio').attr('name', `correct_answer_q${questionIndex}`);
        }
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
    // VALIDASI FORM (sudah lengkap)
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
                if ($card.find('.correct-answer-selector:checked').length === 0) {
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
                allow_multiple_answers: false, // Default value
                options: [],
                correct_answer: null
            };

            if (questionData.type === 'multiple_choice') {
                questionData.allow_multiple_answers = $card.find('.allow-multiple-answers-cb').is(':checked');
                $card.find('.mc-option').each(function (optIndex) {
                    const optionText = $(this).find('.option-input').val().trim();
                    if (optionText) {
                        questionData.options.push({
                            option_letter: getOptionLetter(optIndex),
                            option_text: optionText,
                            is_correct: $(this).find('.correct-answer-selector').is(':checked')
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
    // EVENT LISTENERS
    // ======================================================================

    $('#saveBtn').on('click', function () {
        if (!validateForm()) return;

        const saveBtn = $(this);
        const originalText = saveBtn.html();
        saveBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Saving...');

        const payload = buildPayload();
        let url = '/api/assignments';
        let method = 'POST';

        if (isEdit) {
            url = `/api/assignments/${assignmentId}`;
            payload._method = 'PUT'; // Laravel method spoofing
        }

        $.ajax({
            url: url,
            method: 'POST', // Always POST for AJAX, use _method for PUT/PATCH
            contentType: 'application/json',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: JSON.stringify(payload),
            success: function (response) {
                alert(response.message);
                window.location.href = '/assignment';
            },
            error: function (xhr) {
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

    $('#add-question-btn').on('click', () => {
        addQuestion();
        updateUI();
    });
    questionBuilder.on('change', '.question-type-select', function () {
        renderAnswerContainer(this);
    });
    questionBuilder.on('click', '.btn-remove-question', function () {
        if (confirm('Are you sure you want to delete this question?')) {
            $(this).closest('.question-card').remove();
            updateUI();
        }
    });
    questionBuilder.on('click', '.add-option-btn', function () {
        addMcOption(this);
    });
    questionBuilder.on('click', '.btn-remove-option', function () {
        const option = $(this).closest('.mc-option');
        const optionsList = option.closest('.mc-options-list');
        option.remove();
        reorderOptions(optionsList);
    });

    // Listener untuk switch 'Allow multiple answers'
    questionBuilder.on('change', '.allow-multiple-answers-cb', function () {
        const isChecked = $(this).is(':checked');
        const optionsList = $(this).closest('.answer-container').find('.mc-options-list');
        updateMcInputType(optionsList, isChecked);
    });

});
