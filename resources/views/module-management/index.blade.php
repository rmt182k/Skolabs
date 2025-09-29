@extends('layouts.auth')

@section('title', 'Module Management')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="sha512-1ycn6IcaQQ40/MKBW2W4Rhis/DbILU74C1vSrLJxCq57o941Ym01SwNsOMqvEBFlcgUa6xLiPY/NS5R+E6ztJQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
    <link rel="stylesheet" href="{{ asset('assets/css/app/module-management/module-management.css') }}">
@endpush

@section('content')
    <div class="container-fluid">
        @include('layouts.components.breadcrumb')

        {{-- SECTION 1: TABEL PERMISSION (Role & Hak Akses) --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header">
                <h4 class="card-title mb-0">🔑 Role & Permission Management</h4>
            </div>
            <div class="card-body">
                <p class="card-text text-muted">
                    Atur hak akses (View, Create, Update, Delete) untuk setiap peran pada menu yang tersedia.
                </p>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" style="min-width: 800px;">
                        <thead class="table-light" id="permission-table-head">
                            {{-- Header akan diisi oleh JavaScript --}}
                        </thead>
                        <tbody id="permission-table-body">
                            <tr>
                                <td colspan="100%" class="text-center">Loading data...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-end mt-3">
                    <button type="button" id="btn-save-permissions" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Save Changes
                    </button>
                </div>
            </div>
        </div>

        {{-- SECTION 2: TABEL MENU --}}
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">📄 Menu Management</h4>
                <button type="button" class="btn btn-success" id="btn-add-menu">
                    <i class="fas fa-plus me-2"></i>Add New Menu
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Order</th>
                                <th>Menu Name</th>
                                <th>Route</th>
                                <th>Icon</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="menu-table-body">
                            <tr>
                                <td colspan="5" class="text-center">Loading data...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL: Tambah / Edit Menu --}}
    <div class="modal fade" id="menuModal" tabindex="-1" aria-labelledby="menuModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="menuModalLabel">Add New Menu</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="form-menu">
                    <div class="modal-body">
                        {{-- Hidden field untuk menyimpan ID menu saat edit --}}
                        <input type="hidden" id="input-menu-id">

                        <div class="mb-3">
                            <label for="input-menu-name" class="form-label">Menu Name</label>
                            <input type="text" class="form-control" id="input-menu-name" placeholder="e.g., Dashboard" required>
                        </div>

                        <div class="mb-3">
                            <label for="input-menu-route" class="form-label">Route</label>
                            <input type="text" class="form-control" id="input-menu-route" placeholder="e.g., dashboard.index">
                        </div>

                        <div class="mb-3">
                            <label for="input-menu-icon" class="form-label">Icon</label>
                            <select class="form-control" id="input-menu-icon" style="width: 100%;">
                                {{-- Options akan diisi oleh Select2 dari listIcon.js --}}
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="input-menu-parent" class="form-label">Parent Menu</label>
                            <select class="form-select" id="input-menu-parent">
                                {{-- Options akan diisi oleh JavaScript --}}
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="input-menu-order" class="form-label">Order</label>
                            <input type="number" class="form-control" id="input-menu-order" value="0" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Menu</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="{{ asset('assets/js/app/module-management/listIcon.js') }}"></script>

<script src="{{ asset('assets/js/app/module-management/moduleManagement.js') }}"></script>
@endpush
