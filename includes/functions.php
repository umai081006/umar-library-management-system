<?php
// includes/functions.php
// Kumpulan fungsi-fungsi pembantu (helper functions)

/**
 * Tampilkan pesan flash (session message) dan hapus dari session.
 * Gunakan di template setelah header.
 */
function showFlashMessage() {
    if (isset($_SESSION['success'])) {
        echo '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4" role="alert">';
        echo '<span>' . htmlspecialchars($_SESSION['success']) . '</span>';
        echo '</div>';
        unset($_SESSION['success']);
    }
    if (isset($_SESSION['error'])) {
        echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4" role="alert">';
        echo '<span>' . htmlspecialchars($_SESSION['error']) . '</span>';
        echo '</div>';
        unset($_SESSION['error']);
    }
}

/**
 * Sanitasi output HTML untuk mencegah XSS.
 * 
 * @param string $data Data yang ingin ditampilkan
 * @return string Data yang sudah di-escape
 */
function e(string $data): string {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

/**
 * Format tanggal dari format database ke format Indonesia.
 * 
 * @param string $date Tanggal dari database (Y-m-d)
 * @return string Tanggal format Indonesia (d F Y)
 */
function formatTanggal(string $date): string {
    if (!$date) return '-';
    $bulan = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    $timestamp = strtotime($date);
    return date('d', $timestamp) . ' ' . $bulan[(int)date('m', $timestamp)] . ' ' . date('Y', $timestamp);
}

/**
 * Hitung denda keterlambatan (Rp 1.000 per hari).
 * 
 * @param string $returnDate Tanggal pengembalian yang direncanakan
 * @return int Jumlah denda dalam rupiah
 */
function hitungDenda(string $returnDate): int {
    $today = new DateTime();
    $due   = new DateTime($returnDate);
    if ($today <= $due) return 0;
    $diff = $today->diff($due);
    return $diff->days * 1000; // Rp 1.000 per hari
}

/**
 * Badge status peminjaman dengan Tailwind CSS.
 * 
 * @param string $status
 * @return string HTML Badge
 */
function loanStatusBadge(string $status): string {
    switch ($status) {
        case 'pending':
            return '<span class="px-2 py-1 bg-yellow-100 text-yellow-800 text-xs font-semibold rounded-full">Menunggu</span>';
        case 'dipinjam':
            return '<span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs font-semibold rounded-full">Dipinjam</span>';
        case 'dikembalikan':
            return '<span class="px-2 py-1 bg-green-100 text-green-800 text-xs font-semibold rounded-full">Dikembalikan</span>';
        case 'ditolak':
            return '<span class="px-2 py-1 bg-red-100 text-red-800 text-xs font-semibold rounded-full">Ditolak</span>';
        default:
            return '<span class="px-2 py-1 bg-gray-100 text-gray-800 text-xs font-semibold rounded-full">' . e($status) . '</span>';
    }
}

/**
 * Sanitasi string input dasar.
 * 
 * @param string $data
 * @return string
 */
function sanitize(string $data): string {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}
?>
