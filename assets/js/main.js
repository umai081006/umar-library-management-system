// assets/js/main.js
// Vanilla JavaScript untuk interaksi umum di seluruh halaman

/**
 * Konfirmasi dialog sebelum menghapus data.
 * Gunakan di tombol hapus: onclick="return confirmDelete()"
 *
 * @returns {boolean} true jika dikonfirmasi, false jika dibatalkan
 */
function confirmDelete() {
    return confirm('Apakah Anda yakin ingin menghapus data ini?\nTindakan ini tidak dapat dibatalkan.');
}

/**
 * Preview gambar sebelum diupload.
 * Gunakan di input file: onchange="previewImage(this, 'preview-id')"
 *
 * @param {HTMLInputElement} input - Elemen input file
 * @param {string} previewId - ID elemen img tujuan preview
 */
function previewImage(input, previewId) {
    const preview = document.getElementById(previewId);
    if (!preview) return;

    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function (e) {
            preview.src = e.target.result;
            preview.classList.remove('hidden');
        };
        reader.readAsDataURL(input.files[0]);
    }
}

/**
 * Auto-hide flash message setelah beberapa detik.
 * Dipanggil otomatis saat halaman siap.
 */
document.addEventListener('DOMContentLoaded', function () {
    // Sembunyikan alert flash message secara otomatis setelah 5 detik
    const alerts = document.querySelectorAll('[role="alert"]');
    alerts.forEach(function (alert) {
        setTimeout(function () {
            alert.style.transition = 'opacity 0.5s ease';
            alert.style.opacity = '0';
            setTimeout(function () {
                alert.remove();
            }, 500);
        }, 5000);
    });
});
