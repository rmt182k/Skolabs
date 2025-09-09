<div class="modal fade" id="materialModal" tabindex="-1" aria-labelledby="materialModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="materialModalLabel">Add New Material</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="materialForm" name="materialForm" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <input type="hidden" id="materialId" name="id">

                    <div class="mb-3">
                        <label for="materialTitle" class="form-label">Material Title <span
                                class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="materialTitle" name="title"
                            placeholder="Enter material title" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="subjectId" class="form-label">Subject <span class="text-danger">*</span></label>
                            <select class="form-select" id="subjectId" name="subject_id" required>
                                <option value="">Select Subject</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="classId" class="form-label">Class <span class="text-danger">*</span></label>
                            <select class="form-select" id="classId" name="class_id" required>
                                <option value="">Select Class</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="materialDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="materialDescription" name="description" rows="3"
                            placeholder="Enter a brief description"></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="materialFile" class="form-label">Upload File <span
                                class="text-danger">*</span></label>
                        <input type="file" class="form-control" id="materialFile" name="file" required>
                        <small id="fileHelp" class="form-text text-muted">Max file size: 10MB. Allowed types: pdf,
                            docx, pptx, jpg, png, mp4.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="saveBtn">
                        <i class="fas fa-save me-1"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
