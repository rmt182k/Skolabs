@extends('layouts.auth')

@section('title', 'Module Management')

@section('content')
    <div class="container-fluid">
        {{-- Breadcrumb (sesuai yang sudah kamu include) --}}
        @include('layouts.components.breadcrumb')

        {{-- Card Utama untuk Manajemen Peran & Hak Akses --}}
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
                            {{-- Header tabel akan di-generate oleh JavaScript --}}
                        </thead>
                        <tbody id="permission-table-body">
                            {{-- Baris tabel akan di-generate oleh JavaScript --}}
                            <tr>
                                <td colspan="100%" class="text-center">Loading data...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-end mt-3">
                    <button type="button" id="save-permissions-btn" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Save Changes
                    </button>
                </div>
            </div>
        </div>

        {{-- Card untuk Manajemen Menu --}}
        <div class="card shadow-sm">
             <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">📄 Menu Management</h4>
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#menuModal">
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
                            {{-- Data menu akan di-generate oleh JavaScript --}}
                             <tr>
                                <td colspan="5" class="text-center">Loading data...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal untuk Tambah & Edit Menu --}}
    <div class="modal fade" id="menuModal" tabindex="-1" aria-labelledby="menuModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="menuModalLabel">Add New Menu</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="menuForm">
                    <div class="modal-body">
                        <input type="hidden" id="menuId">
                        <div class="mb-3">
                            <label for="menuName" class="form-label">Menu Name</label>
                            <input type="text" class="form-control" id="menuName" placeholder="e.g., Dashboard" required>
                        </div>
                        <div class="mb-3">
                            <label for="menuRoute" class="form-label">Route</label>
                            <input type="text" class="form-control" id="menuRoute" placeholder="e.g., dashboard.index">
                        </div>
                         <div class="mb-3">
                            <label for="menuIcon" class="form-label">Icon</label>
                            <input type="text" class="form-control" id="menuIcon" placeholder="e.g., fa-tachometer-alt">
                            <small class="form-text text-muted">Gunakan class dari Font Awesome.</small>
                        </div>
                        <div class="mb-3">
                            <label for="menuParent" class="form-label">Parent Menu</label>
                            <select class="form-select" id="menuParent">
                                {{-- Opsi parent akan di-generate oleh JavaScript --}}
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="menuOrder" class="form-label">Order</label>
                            <input type="number" class="form-control" id="menuOrder" value="0" required>
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

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="sha512-1ycn6IcaQQ40/MKBW2W4Rhis/DbILU74C1vSrLJxCq57o941Ym01SwNsOMqvEBFlcgUa6xLiPY/NS5R+E6ztJQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        .table th, .table td { vertical-align: middle; }
        .table .form-check { display: flex; justify-content: center; align-items: center; min-height: 24px; }
        .table .form-check-input { margin: 0; }
        .menu-parent { font-weight: bold; }
        .menu-child { padding-left: 2rem !important; }
    </style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // =======================================================
    // DUMMY DATA (Nantinya, data ini akan diambil dari API)
    // =======================================================
    const dummyRoles = [
        { id: 1, name: 'Admin' },
        { id: 2, name: 'Manager' },
        { id: 3, name: 'User' }
    ];

    const dummyMenus = [
        { id: 1, name: 'Dashboard', route: 'dashboard.index', icon: 'fa-home', parent_id: 0, order: 1 },
        { id: 2, name: 'User Management', route: null, icon: 'fa-users-cog', parent_id: 0, order: 2 },
        { id: 3, name: 'Users List', route: 'users.index', icon: 'fa-users', parent_id: 2, order: 3 },
        { id: 4, name: 'Roles & Permissions', route: 'roles.index', icon: 'fa-user-shield', parent_id: 2, order: 4 },
        { id: 5, name: 'System Management', route: 'system.index', icon: 'fa-cogs', parent_id: 0, order: 5 },
    ];

    // Data pivot dummy untuk hak akses
    const dummyPermissions = [
        { menu_id: 1, role_id: 1, can_view: true, can_create: true, can_update: true, can_delete: true },
        { menu_id: 1, role_id: 2, can_view: true, can_create: false, can_update: false, can_delete: false },
        { menu_id: 1, role_id: 3, can_view: true, can_create: false, can_update: false, can_delete: false },
        { menu_id: 3, role_id: 2, can_view: true, can_create: true, can_update: false, can_delete: false },
    ];


    // =======================================================
    // RENDER FUNGSI
    // =======================================================

    function renderPermissionTable() {
        const thead = document.getElementById('permission-table-head');
        const tbody = document.getElementById('permission-table-body');

        // --- Render Header ---
        let headerHtml = `
            <tr>
                <th rowspan="2" class="text-center align-middle" style="width: 25%;">Menu Name</th>
                ${dummyRoles.map(role => `<th colspan="4" class="text-center">${role.name}</th>`).join('')}
            </tr>
            <tr>
                ${dummyRoles.map(() => `
                    <th class="text-center" style="width: 5%;">View</th>
                    <th class="text-center" style="width: 5%;">Create</th>
                    <th class="text-center" style="width: 5%;">Update</th>
                    <th class="text-center" style="width: 5%;">Delete</th>
                `).join('')}
            </tr>
        `;
        thead.innerHTML = headerHtml;

        // --- Render Body ---
        let bodyHtml = '';
        const sortedMenus = dummyMenus.sort((a, b) => a.order - b.order);

        sortedMenus.forEach(menu => {
            const isChild = menu.parent_id !== 0;
            bodyHtml += `<tr>`;
            bodyHtml += `<td class="${isChild ? 'menu-child' : 'menu-parent'}">
                            ${isChild ? '└─ ' : ''}${menu.name}
                         </td>`;

            dummyRoles.forEach(role => {
                const perm = dummyPermissions.find(p => p.menu_id === menu.id && p.role_id === role.id) || {};
                bodyHtml += `
                    <td class="text-center"><div class="form-check"><input class="form-check-input" type="checkbox" data-menu-id="${menu.id}" data-role-id="${role.id}" data-permission="view" ${perm.can_view ? 'checked' : ''}></div></td>
                    <td class="text-center"><div class="form-check"><input class="form-check-input" type="checkbox" data-menu-id="${menu.id}" data-role-id="${role.id}" data-permission="create" ${perm.can_create ? 'checked' : ''}></div></td>
                    <td class="text-center"><div class="form-check"><input class="form-check-input" type="checkbox" data-menu-id="${menu.id}" data-role-id="${role.id}" data-permission="update" ${perm.can_update ? 'checked' : ''}></div></td>
                    <td class="text-center"><div class="form-check"><input class="form-check-input" type="checkbox" data-menu-id="${menu.id}" data-role-id="${role.id}" data-permission="delete" ${perm.can_delete ? 'checked' : ''}></div></td>
                `;
            });
            bodyHtml += `</tr>`;
        });
        tbody.innerHTML = bodyHtml || '<tr><td colspan="100%" class="text-center">No menus found.</td></tr>';
    }

    function renderMenuTable() {
        const tbody = document.getElementById('menu-table-body');
        const parentSelect = document.getElementById('menuParent');

        let bodyHtml = '';
        let parentOptionsHtml = '<option value="0">-- No Parent --</option>';

        const sortedMenus = dummyMenus.sort((a, b) => a.order - b.order);

        sortedMenus.forEach(menu => {
            const isChild = menu.parent_id !== 0;
            bodyHtml += `
                <tr data-menu-id="${menu.id}">
                    <td>${menu.order}</td>
                    <td class="${isChild ? 'menu-child' : 'menu-parent'}">
                        ${isChild ? '└─ ' : ''}${menu.name}
                    </td>
                    <td><code>${menu.route || 'N/A'}</code></td>
                    <td><i class="fas ${menu.icon || 'fa-circle-notch'} me-2"></i> ${menu.icon || 'N/A'}</td>
                    <td>
                        <button class="btn btn-sm btn-info edit-menu-btn" data-bs-toggle="modal" data-bs-target="#menuModal"><i class="fas fa-edit"></i></button>
                        <button class="btn btn-sm btn-danger delete-menu-btn"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
            `;

            // Hanya menu parent yang bisa jadi parent
            if(menu.parent_id === 0) {
                parentOptionsHtml += `<option value="${menu.id}">${menu.name}</option>`;
            }
        });

        tbody.innerHTML = bodyHtml || '<tr><td colspan="5" class="text-center">No menus found.</td></tr>';
        parentSelect.innerHTML = parentOptionsHtml;
    }


    // =======================================================
    // INISIALISASI
    // =======================================================
    renderPermissionTable();
    renderMenuTable();

});
</script>
@endpush
