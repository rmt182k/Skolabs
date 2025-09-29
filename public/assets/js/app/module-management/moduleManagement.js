// ============================================================================
// TUNGGU SAMPAI HALAMAN SELESAI DIMUAT
// ============================================================================
$(document).ready(function () {

    // ========================================================================
    // VARIABEL GLOBAL
    // ========================================================================

    // Base URL untuk API
    var apiBaseUrl = '/api/module-management';

    // CSRF Token dari Laravel
    var csrfToken = $('meta[name="csrf-token"]').attr('content');

    // Variabel untuk menyimpan data
    var allRoles = [];      // Semua role/peran
    var allMenus = [];      // Semua menu
    var allPermissions = []; // Semua permission

    // Variabel untuk Bootstrap Modal
    var menuModal = null;

    // Flag untuk menandai mode tambah atau edit
    var isEditMode = false;

    // Queue untuk alert
    var alertQueue = [];
    var isShowingAlert = false;


    // ========================================================================
    // FUNGSI INISIALISASI
    // ========================================================================

    function initializePage() {
        console.log('🚀 Memulai inisialisasi halaman...');

        // 1. Inisialisasi Bootstrap Modal
        initializeModal();

        // 2. Inisialisasi Select2 untuk icon picker
        initializeSelect2();

        // 3. Muat semua data dari server
        loadAllDataFromServer();

        // 4. Setup event listeners (tombol, form, dll)
        setupAllEventListeners();

        console.log('✅ Inisialisasi selesai!');
    }


    // ========================================================================
    // INISIALISASI BOOTSTRAP MODAL
    // ========================================================================

    function initializeModal() {
        var modalElement = document.getElementById('menuModal');
        menuModal = new bootstrap.Modal(modalElement);
        console.log('✅ Bootstrap Modal diinisialisasi');
    }


    // ========================================================================
    // INISIALISASI SELECT2 (ICON PICKER)
    // ========================================================================

    function initializeSelect2() {
        $('#input-menu-icon').select2({
            data: iconList, // iconList dari file listIcon.js
            theme: 'bootstrap-5',
            placeholder: 'Pilih sebuah ikon',
            templateResult: formatIconForSelect2,
            templateSelection: formatIconForSelect2,
            dropdownParent: $('#menuModal')
        });
        console.log('✅ Select2 diinisialisasi');
    }

    // Fungsi untuk format tampilan icon di Select2
    function formatIconForSelect2(icon) {
        if (!icon.id) {
            return icon.text;
        }

        var iconClass = icon.id.startsWith('fa') ? icon.id : 'fas ' + icon.id;
        var $iconElement = $('<span><i class="' + iconClass + '" style="width: 20px;"></i> ' + icon.text + '</span>');

        return $iconElement;
    }


    // ========================================================================
    // MUAT SEMUA DATA DARI SERVER
    // ========================================================================

    function loadAllDataFromServer() {
        console.log('📡 Memuat data dari server...');

        // Tampilkan loading
        showLoadingState();

        // Muat data Roles
        loadRolesData();

        // Muat data Menus
        loadMenusData();

        // Muat data Permissions
        loadPermissionsData();
    }


    // ========================================================================
    // MUAT DATA ROLES (PERAN)
    // ========================================================================

    function loadRolesData() {
        $.ajax({
            url: '/api/roles',
            type: 'GET',
            dataType: 'json',
            success: function (response) {
                console.log('✅ Data Roles berhasil dimuat:', response);

                if (response.success) {
                    allRoles = response.data;

                    // Cek apakah semua data sudah dimuat
                    checkIfAllDataLoaded();
                } else {
                    showErrorMessage('Gagal memuat data roles: ' + response.message);
                }
            },
            error: function (xhr, status, error) {
                console.error('❌ Error memuat roles:', error);
                showErrorMessage('Gagal memuat data roles. Silakan refresh halaman.');
            }
        });
    }


    // ========================================================================
    // MUAT DATA MENUS
    // ========================================================================

    function loadMenusData() {
        $.ajax({
            url: '/api/menus',
            type: 'GET',
            dataType: 'json',
            success: function (response) {
                console.log('✅ Data Menus berhasil dimuat:', response);

                if (response.success) {
                    allMenus = response.data;

                    // Cek apakah semua data sudah dimuat
                    checkIfAllDataLoaded();
                } else {
                    showErrorMessage('Gagal memuat data menus: ' + response.message);
                }
            },
            error: function (xhr, status, error) {
                console.error('❌ Error memuat menus:', error);
                showErrorMessage('Gagal memuat data menus. Silakan refresh halaman.');
            }
        });
    }


    // ========================================================================
    // MUAT DATA PERMISSIONS (HAK AKSES)
    // ========================================================================

    function loadPermissionsData() {
        $.ajax({
            url: '/api/permissions',
            type: 'GET',
            dataType: 'json',
            success: function (response) {
                console.log('✅ Data Permissions berhasil dimuat:', response);

                if (response.success) {
                    if (Array.isArray(response.data) && response.data.length > 0) {
                        allPermissions = response.data;
                        renderPermissionTable(allPermissions);
                    } else {
                        allPermissions = [];
                        showNoDataState();
                    }

                    checkIfAllDataLoaded();
                } else {
                    showErrorMessage('Gagal memuat data permissions: ' + response.message);
                }

            },
            error: function (xhr, status, error) {
                console.error('❌ Error memuat permissions:', error);
                showErrorMessage('Gagal memuat data permissions. Silakan refresh halaman.');
            }
        });
    }


    // ========================================================================
    // CEK APAKAH SEMUA DATA SUDAH DIMUAT
    // ========================================================================

    function checkIfAllDataLoaded() {
        // Cek apakah semua data sudah tersedia
        if (allRoles.length > 0 && allMenus.length > 0 && allPermissions.length >= 0) {
            console.log('✅ Semua data berhasil dimuat!');

            // Render tabel permission
            renderPermissionTable();

            // Render tabel menu
            renderMenuTable();
        }
    }


    // ========================================================================
    // RENDER TABEL PERMISSION (ROLE & HAK AKSES)
    // ========================================================================

    function renderPermissionTable() {
        console.log('🎨 Rendering tabel permission...');

        // Cek apakah data tersedia
        if (allRoles.length === 0 || allMenus.length === 0) {
            $('#permission-table-body').html('<tr><td colspan="100%" class="text-center">Tidak ada data</td></tr>');
            return;
        }

        // Render header tabel
        renderPermissionTableHeader();

        // Render body tabel
        renderPermissionTableBody();

        console.log('✅ Tabel permission selesai dirender');
    }


    // ========================================================================
    // RENDER HEADER TABEL PERMISSION
    // ========================================================================

    function renderPermissionTableHeader() {
        var headerHtml = '';

        // Baris pertama: Nama Role
        headerHtml += '<tr>';
        headerHtml += '<th rowspan="2" class="text-center align-middle" style="width: 25%;">Menu Name</th>';

        // Loop setiap role
        for (var i = 0; i < allRoles.length; i++) {
            var role = allRoles[i];
            headerHtml += '<th colspan="4" class="text-center">' + role.name + '</th>';
        }

        headerHtml += '</tr>';

        // Baris kedua: View, Create, Update, Delete
        headerHtml += '<tr>';

        // Loop setiap role untuk permission columns
        for (var i = 0; i < allRoles.length; i++) {
            headerHtml += '<th class="text-center" style="width: 5%;">View</th>';
            headerHtml += '<th class="text-center" style="width: 5%;">Create</th>';
            headerHtml += '<th class="text-center" style="width: 5%;">Update</th>';
            headerHtml += '<th class="text-center" style="width: 5%;">Delete</th>';
        }

        headerHtml += '</tr>';

        // Masukkan ke dalam thead
        $('#permission-table-head').html(headerHtml);
    }


    // ========================================================================
    // RENDER BODY TABEL PERMISSION
    // ========================================================================

    function renderPermissionTableBody() {
        var bodyHtml = '';

        // Urutkan menu berdasarkan order
        var sortedMenus = allMenus.slice().sort(function (a, b) {
            return a.order - b.order;
        });

        // Loop setiap menu
        for (var i = 0; i < sortedMenus.length; i++) {
            var menu = sortedMenus[i];
            var isChildMenu = menu.parent_id !== 0;

            bodyHtml += '<tr>';

            // Kolom nama menu
            var menuClass = isChildMenu ? 'menu-child' : 'menu-parent';
            var menuPrefix = isChildMenu ? '└─ ' : '';
            bodyHtml += '<td class="' + menuClass + '">' + menuPrefix + menu.name + '</td>';

            // Loop setiap role untuk checkbox permissions
            for (var j = 0; j < allRoles.length; j++) {
                var role = allRoles[j];

                // Cari permission untuk menu dan role ini
                var permission = findPermission(menu.id, role.id);

                // Checkbox View
                var viewChecked = permission && permission.can_view ? 'checked' : '';
                bodyHtml += '<td class="text-center">';
                bodyHtml += '<div class="form-check">';
                bodyHtml += '<input class="form-check-input" type="checkbox" ';
                bodyHtml += 'data-menu-id="' + menu.id + '" ';
                bodyHtml += 'data-role-id="' + role.id + '" ';
                bodyHtml += 'data-permission="view" ';
                bodyHtml += viewChecked + '>';
                bodyHtml += '</div>';
                bodyHtml += '</td>';

                // Checkbox Create
                var createChecked = permission && permission.can_create ? 'checked' : '';
                bodyHtml += '<td class="text-center">';
                bodyHtml += '<div class="form-check">';
                bodyHtml += '<input class="form-check-input" type="checkbox" ';
                bodyHtml += 'data-menu-id="' + menu.id + '" ';
                bodyHtml += 'data-role-id="' + role.id + '" ';
                bodyHtml += 'data-permission="create" ';
                bodyHtml += createChecked + '>';
                bodyHtml += '</div>';
                bodyHtml += '</td>';

                // Checkbox Update
                var updateChecked = permission && permission.can_update ? 'checked' : '';
                bodyHtml += '<td class="text-center">';
                bodyHtml += '<div class="form-check">';
                bodyHtml += '<input class="form-check-input" type="checkbox" ';
                bodyHtml += 'data-menu-id="' + menu.id + '" ';
                bodyHtml += 'data-role-id="' + role.id + '" ';
                bodyHtml += 'data-permission="update" ';
                bodyHtml += updateChecked + '>';
                bodyHtml += '</div>';
                bodyHtml += '</td>';

                // Checkbox Delete
                var deleteChecked = permission && permission.can_delete ? 'checked' : '';
                bodyHtml += '<td class="text-center">';
                bodyHtml += '<div class="form-check">';
                bodyHtml += '<input class="form-check-input" type="checkbox" ';
                bodyHtml += 'data-menu-id="' + menu.id + '" ';
                bodyHtml += 'data-role-id="' + role.id + '" ';
                bodyHtml += 'data-permission="delete" ';
                bodyHtml += deleteChecked + '>';
                bodyHtml += '</div>';
                bodyHtml += '</td>';
            }

            bodyHtml += '</tr>';
        }

        // Masukkan ke dalam tbody
        $('#permission-table-body').html(bodyHtml);
    }


    // ========================================================================
    // FUNGSI HELPER: CARI PERMISSION
    // ========================================================================

    function findPermission(menuId, roleId) {
        for (var i = 0; i < allPermissions.length; i++) {
            var perm = allPermissions[i];
            if (perm.menu_id === menuId && perm.role_id === roleId) {
                return perm;
            }
        }
        return null;
    }


    // ========================================================================
    // RENDER TABEL MENU
    // ========================================================================

    function renderMenuTable() {
        console.log('🎨 Rendering tabel menu...');

        // Cek apakah data tersedia
        if (allMenus.length === 0) {
            $('#menu-table-body').html('<tr><td colspan="5" class="text-center">Tidak ada menu</td></tr>');
            updateParentMenuOptions(); // Update parent select kosong
            return;
        }

        var bodyHtml = '';

        // Urutkan menu berdasarkan order
        var sortedMenus = allMenus.slice().sort(function (a, b) {
            return a.order - b.order;
        });

        // Loop setiap menu
        for (var i = 0; i < sortedMenus.length; i++) {
            var menu = sortedMenus[i];
            var isChildMenu = menu.parent_id !== 0;
            var iconClass = menu.icon || 'fas fa-circle-notch';

            bodyHtml += '<tr data-menu-id="' + menu.id + '">';

            // Kolom Order
            bodyHtml += '<td>' + menu.order + '</td>';

            // Kolom Menu Name
            var menuClass = isChildMenu ? 'menu-child' : 'menu-parent';
            var menuPrefix = isChildMenu ? '└─ ' : '';
            bodyHtml += '<td class="' + menuClass + '">' + menuPrefix + menu.name + '</td>';

            // Kolom Route
            var routeText = menu.route || 'N/A';
            bodyHtml += '<td><code>' + routeText + '</code></td>';

            // Kolom Icon
            var iconText = menu.icon || 'N/A';
            bodyHtml += '<td><i class="' + iconClass + ' me-2"></i> ' + iconText + '</td>';

            // Kolom Actions
            bodyHtml += '<td>';
            bodyHtml += '<button class="btn btn-sm btn-info btn-edit-menu" data-menu-id="' + menu.id + '">';
            bodyHtml += '<i class="fas fa-edit"></i>';
            bodyHtml += '</button> ';
            bodyHtml += '<button class="btn btn-sm btn-danger btn-delete-menu" data-menu-id="' + menu.id + '">';
            bodyHtml += '<i class="fas fa-trash"></i>';
            bodyHtml += '</button>';
            bodyHtml += '</td>';

            bodyHtml += '</tr>';
        }

        // Masukkan ke dalam tbody
        $('#menu-table-body').html(bodyHtml);

        // Update dropdown parent menu
        updateParentMenuOptions();

        console.log('✅ Tabel menu selesai dirender');
    }


    // ========================================================================
    // UPDATE OPTIONS PARENT MENU
    // ========================================================================

    function updateParentMenuOptions() {
        var optionsHtml = '<option value="0">-- No Parent --</option>';

        // Loop menu yang parent_id = 0 (parent menu saja)
        for (var i = 0; i < allMenus.length; i++) {
            var menu = allMenus[i];
            if (menu.parent_id === 0) {
                optionsHtml += '<option value="' + menu.id + '">' + menu.name + '</option>';
            }
        }

        // Masukkan ke dalam select
        $('#input-menu-parent').html(optionsHtml);
    }


    // ========================================================================
    // SETUP EVENT LISTENERS (TOMBOL, FORM, DLL)
    // ========================================================================

    function setupAllEventListeners() {
        console.log('🎯 Setup event listeners...');

        // 1. Tombol Save Permissions
        $('#btn-save-permissions').on('click', function () {
            saveAllPermissions();
        });

        // 2. Tombol Add Menu
        $('#btn-add-menu').on('click', function () {
            openAddMenuModal();
        });

        // 3. Form Submit Menu
        $('#form-menu').on('submit', function (e) {
            e.preventDefault();
            submitMenuForm();
        });

        // 4. Tombol Edit Menu (delegated event)
        $('#menu-table-body').on('click', '.btn-edit-menu', function () {
            var menuId = parseInt($(this).data('menu-id'));
            openEditMenuModal(menuId);
        });

        // 5. Tombol Delete Menu (delegated event)
        $('#menu-table-body').on('click', '.btn-delete-menu', function () {
            var menuId = parseInt($(this).data('menu-id'));
            deleteMenu(menuId);
        });

        console.log('✅ Event listeners siap');
    }


    // ========================================================================
    // SIMPAN SEMUA PERMISSIONS
    // ========================================================================

    function saveAllPermissions() {
        console.log('💾 Menyimpan permissions...');

        // Array untuk menampung data permissions
        var permissionsData = [];

        // Ambil semua checkbox
        var checkboxes = $('#permission-table-body input[type="checkbox"]');

        // Loop setiap checkbox
        checkboxes.each(function () {
            var checkbox = $(this);
            var menuId = parseInt(checkbox.data('menu-id'));
            var roleId = parseInt(checkbox.data('role-id'));
            var permissionType = checkbox.data('permission'); // view, create, update, delete
            var isChecked = checkbox.is(':checked');

            // Cari apakah sudah ada data untuk menu_id dan role_id ini
            var existingData = null;
            for (var i = 0; i < permissionsData.length; i++) {
                if (permissionsData[i].menu_id === menuId && permissionsData[i].role_id === roleId) {
                    existingData = permissionsData[i];
                    break;
                }
            }

            // Jika belum ada, buat data baru
            if (!existingData) {
                existingData = {
                    menu_id: menuId,
                    role_id: roleId,
                    can_view: false,
                    can_create: false,
                    can_update: false,
                    can_delete: false
                };
                permissionsData.push(existingData);
            }

            // Set nilai permission sesuai checkbox
            if (permissionType === 'view') {
                existingData.can_view = isChecked;
            } else if (permissionType === 'create') {
                existingData.can_create = isChecked;
            } else if (permissionType === 'update') {
                existingData.can_update = isChecked;
            } else if (permissionType === 'delete') {
                existingData.can_delete = isChecked;
            }
        });

        console.log('📦 Data permissions yang akan disimpan:', permissionsData);

        // Kirim ke server via AJAX
        $.ajax({
            url: apiBaseUrl + '/permissions',
            type: 'POST',
            dataType: 'json',
            contentType: 'application/json',
            headers: {
                'X-CSRF-TOKEN': csrfToken
            },
            data: JSON.stringify({
                permissions: permissionsData
            }),
            success: function (response) {
                console.log('✅ Permissions berhasil disimpan:', response);

                if (response.success) {
                    showSuccessMessage(response.message);

                    // Reload data
                    loadAllDataFromServer();
                } else {
                    showErrorMessage('Gagal menyimpan: ' + response.message);
                }
            },
            error: function (xhr, status, error) {
                console.error('❌ Error menyimpan permissions:', error);
                showErrorMessage('Gagal menyimpan permissions. Silakan coba lagi.');
            }
        });
    }


    // ========================================================================
    // BUKA MODAL TAMBAH MENU
    // ========================================================================

    function openAddMenuModal() {
        console.log('➕ Membuka modal tambah menu...');

        // Set mode ke tambah
        isEditMode = false;

        // Ubah judul modal
        $('#menuModalLabel').text('Add New Menu');

        // Reset form
        $('#form-menu')[0].reset();
        $('#input-menu-id').val('');

        // Reset Select2
        $('#input-menu-icon').val(null).trigger('change');

        // Tampilkan modal
        menuModal.show();
    }


    // ========================================================================
    // BUKA MODAL EDIT MENU
    // ========================================================================

    function openEditMenuModal(menuId) {
        console.log('✏️ Membuka modal edit menu ID:', menuId);

        // Cari data menu berdasarkan ID
        var menu = null;
        for (var i = 0; i < allMenus.length; i++) {
            if (allMenus[i].id === menuId) {
                menu = allMenus[i];
                break;
            }
        }

        // Jika menu tidak ditemukan
        if (!menu) {
            showErrorMessage('Menu tidak ditemukan!');
            return;
        }

        // Set mode ke edit
        isEditMode = true;

        // Ubah judul modal
        $('#menuModalLabel').text('Edit Menu');

        // Isi form dengan data menu
        $('#input-menu-id').val(menu.id);
        $('#input-menu-name').val(menu.name);
        $('#input-menu-route').val(menu.route || '');
        $('#input-menu-parent').val(menu.parent_id || 0);
        $('#input-menu-order').val(menu.order);

        // Set Select2 icon
        $('#input-menu-icon').val(menu.icon).trigger('change');

        // Tampilkan modal
        menuModal.show();
    }


    // ========================================================================
    // SUBMIT FORM MENU (TAMBAH/EDIT)
    // ========================================================================

    function submitMenuForm() {
        console.log('📝 Submit form menu...');

        // Ambil data dari form
        var menuData = {
            name: $('#input-menu-name').val().trim(),
            route: $('#input-menu-route').val().trim() || null,
            icon: $('#input-menu-icon').val() || null,
            parent_id: parseInt($('#input-menu-parent').val()) || 0,
            order: parseInt($('#input-menu-order').val()) || 0
        };

        // Jika mode edit, tambahkan ID
        if (isEditMode) {
            menuData.id = parseInt($('#input-menu-id').val());
        }

        console.log('📦 Data menu:', menuData);

        // Validasi sederhana
        if (!menuData.name) {
            showErrorMessage('Nama menu harus diisi!');
            return;
        }

        // Tentukan URL dan method
        var url = apiBaseUrl + '/menus';
        var method = 'POST';

        if (isEditMode) {
            url = apiBaseUrl + '/menus/' + menuData.id;
            method = 'PUT';
        }

        // Kirim ke server via AJAX
        $.ajax({
            url: url,
            type: method,
            dataType: 'json',
            contentType: 'application/json',
            headers: {
                'X-CSRF-TOKEN': csrfToken
            },
            data: JSON.stringify(menuData),
            success: function (response) {
                console.log('✅ Menu berhasil disimpan:', response);

                if (response.success) {
                    showSuccessMessage(response.message);

                    // Tutup modal
                    menuModal.hide();

                    // Reload data
                    loadAllDataFromServer();
                } else {
                    showErrorMessage('Gagal menyimpan: ' + response.message);
                }
            },
            error: function (xhr, status, error) {
                console.error('❌ Error menyimpan menu:', error);
                showErrorMessage('Gagal menyimpan menu. Silakan coba lagi.');
            }
        });
    }


    // ========================================================================
    // HAPUS MENU
    // ========================================================================

    function deleteMenu(menuId) {
        console.log('🗑️ Menghapus menu ID:', menuId);

        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Menu yang dihapus tidak dapat dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                // Jika pengguna menekan "Ya, hapus!", kirim request DELETE
                $.ajax({
                    url: apiBaseUrl + '/menus/' + menuId,
                    type: 'DELETE',
                    dataType: 'json',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    },
                    success: function (response) {
                        console.log('✅ Menu berhasil dihapus:', response);
                        if (response.success) {
                            showSuccessMessage(response.message);
                            loadAllDataFromServer(); // Reload data
                        } else {
                            showErrorMessage('Gagal menghapus: ' + response.message);
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error('❌ Error menghapus menu:', xhr.responseText);
                        let errorMessage = 'Gagal menghapus menu. Silakan coba lagi.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        showErrorMessage(errorMessage);
                    }
                });
            } else {
                console.log('❌ Penghapusan dibatalkan');
            }
        });
    }


    // ========================================================================
    // TAMPILKAN LOADING STATE
    // ========================================================================

    function showLoadingState() {
        $('#permission-table-body').html('<tr><td colspan="100%" class="text-center">⏳ Loading data...</td></tr>');
        $('#menu-table-body').html('<tr><td colspan="5" class="text-center">⏳ Loading data...</td></tr>');
    }

    // ========================================================================
    // TAMPILKAN DATA STATE
    // ========================================================================

    function showNoDataState() {
        $('#permission-table-body').html('<tr><td colspan="100%" class="text-center text-muted">📭 No Data</td></tr>');
        $('#menu-table-body').html('<tr><td colspan="5" class="text-center text-muted">📭 No Data</td></tr>');
    }



    // ========================================================================
    // PROSES ALERT QUEUE
    // ========================================================================

    function processAlertQueue() {
        // Jika sedang menampilkan alert atau queue kosong, return
        if (isShowingAlert || alertQueue.length === 0) {
            return;
        }

        // Ambil alert pertama dari queue
        var alertData = alertQueue.shift();
        isShowingAlert = true;

        // Tampilkan alert
        Swal.fire(alertData).then(function () {
            // Setelah alert ditutup, set flag dan proses alert berikutnya
            isShowingAlert = false;
            processAlertQueue();
        });
    }


    // ========================================================================
    // TAMPILKAN PESAN SUKSES
    // ========================================================================

    function showSuccessMessage(message) {
        // Tambahkan ke queue
        alertQueue.push({
            icon: 'success',
            title: 'Berhasil!',
            text: message,
            timer: 2000,
            showConfirmButton: false
        });

        // Proses queue
        processAlertQueue();

        console.log('✅ Success:', message);
    }


    // ========================================================================
    // TAMPILKAN PESAN ERROR
    // ========================================================================

    function showErrorMessage(message) {
        // Tambahkan ke queue
        alertQueue.push({
            icon: 'error',
            title: 'Oops... Terjadi Kesalahan',
            text: message
        });

        // Proses queue
        processAlertQueue();

        console.error('❌ Error:', message);
    }


    // ========================================================================
    // JALANKAN INISIALISASI SAAT HALAMAN SIAP
    // ========================================================================

    initializePage();

}); // End of document ready
