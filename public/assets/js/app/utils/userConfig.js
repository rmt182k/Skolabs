document.getElementById('logout-button').addEventListener('click', function (event) {
    event.preventDefault();
    handleLogout();
});
function handleLogout() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    $.ajax({
        url: '/logout',
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken
        },
        success: function () {
            console.log("✅ Logout berhasil");
            window.location.href = '/login';
        },
        error: function (xhr, status, error) {
            console.error("❌ Gagal logout:", error);
            alert('Terjadi kesalahan saat logout. Coba lagi.');
        }
    });
}

