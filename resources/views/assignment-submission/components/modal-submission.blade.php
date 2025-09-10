<div class="modal fade" id="submissionModal" tabindex="-1" aria-labelledby="submissionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="submissionModalLabel">Submission Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <strong>Student:</strong> <span id="studentName"></span>
                </div>
                <div class="mb-3">
                    <strong>Submitted At:</strong> <span id="submittedAt"></span>
                </div>
                <div id="submissionContentContainer" class="mb-3">
                    <strong>Content:</strong>
                    <div class="p-3 border rounded bg-light" id="submissionContent"></div>
                </div>
                <div id="submissionFileContainer" class="mb-3">
                    <strong>File:</strong> <a href="#" id="submissionFileLink" target="_blank"></a>
                </div>

                <hr>

                <form id="gradingForm" name="gradingForm">
                    <input type="hidden" name="submission_id" id="submissionId">
                    <h5 class="mb-3">Grading</h5>
                    <div class="mb-3">
                        <label for="grade" class="form-label">Grade (0-100)</label>
                        <input type="number" class="form-control" id="grade" name="grade" min="0"
                            max="100" step="0.5">
                    </div>
                    <div class="mb-3">
                        <label for="feedback" class="form-label">Feedback</label>
                        <textarea class="form-control" id="feedback" name="feedback" rows="4"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary" form="gradingForm" id="saveGradeBtn">Save Grade</button>
            </div>
        </div>
    </div>
</div>
