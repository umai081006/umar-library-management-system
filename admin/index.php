<?php
// admin/index.php
// Dashboard Admin — menampilkan ringkasan statistik

require_once '../includes/auth_check.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

requireAdmin(); // Hanya admin yang bisa akses

// --- Ambil statistik ---
$totalBuku    = (int) $pdo->query("SELECT COUNT(*) FROM books")->fetchColumn();
$totalMember  = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'member'")->fetchColumn();
$dipinjam     = (int) $pdo->query("SELECT COUNT(*) FROM loans WHERE status = 'dipinjam'")->fetchColumn();
$pending      = (int) $pdo->query("SELECT COUNT(*) FROM loans WHERE status = 'pending'")->fetchColumn();

// Ambil 5 peminjaman terbaru
$stmt = $pdo->prepare(
    "SELECT l.id, u.name AS member_name, b.title AS book_title,
            l.loan_date, l.due_date, l.status
     FROM loans l
     JOIN users u ON u.id = l.user_id
     JOIN books b ON b.id = l.book_id
     ORDER BY l.created_at DESC
     LIMIT 5"
);
$stmt->execute();
$recentLoans = $stmt->fetchAll();

$pageTitle = 'Dashboard Admin';
require_once '../includes/header.php';
?>

<div class="mb-6">
    <h2 class="text-2xl font-bold text-gray-800">Dashboard Admin</h2>
    <p class="text-gray-500 text-sm mt-1">Selamat datang, <?= e($_SESSION['user_name']) ?>!</p>
</div>

<?php showFlashMessage(); ?>

<!-- Kartu Statistik -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Total Buku -->
    <div class="bg-white rounded-xl shadow p-6 flex items-center gap-4">
        <div class="bg-blue-100 text-blue-600 p-3 rounded-full">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
            </svg>
        </div>
        <div>
            <p class="text-2xl font-bold text-gray-800"><?= $totalBuku ?></p>
            <p class="text-sm text-gray-500">Total Buku</p>
        </div>
    </div>

    <!-- Total Member -->
    <div class="bg-white rounded-xl shadow p-6 flex items-center gap-4">
        <div class="bg-green-100 text-green-600 p-3 rounded-full">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
            </svg>
        </div>
        <div>
            <p class="text-2xl font-bold text-gray-800"><?= $totalMember ?></p>
            <p class="text-sm text-gray-500">Total Member</p>
        </div>
    </div>

    <!-- Sedang Dipinjam -->
    <div class="bg-white rounded-xl shadow p-6 flex items-center gap-4">
        <div class="bg-yellow-100 text-yellow-600 p-3 rounded-full">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <div>
            <p class="text-2xl font-bold text-gray-800"><?= $dipinjam ?></p>
            <p class="text-sm text-gray-500">Sedang Dipinjam</p>
        </div>
    </div>

    <!-- Menunggu Persetujuan -->
    <div class="bg-white rounded-xl shadow p-6 flex items-center gap-4">
        <div class="bg-indigo-100 text-indigo-600 p-3 rounded-full">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <div>
            <p class="text-2xl font-bold text-gray-800"><?= $pending ?></p>
            <p class="text-sm text-gray-500">Menunggu Acc</p>
        </div>
    </div>
</div>

<!-- Menu Cepat -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
    <a href="/admin/books.php" class="bg-blue-600 hover:bg-blue-700 text-white rounded-xl p-5 flex items-center gap-3 transition shadow">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 flex-shrink-0">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
        </svg>
        <span class="font-semibold">Kelola Buku</span>
    </a>
    <a href="/admin/users.php" class="bg-green-600 hover:bg-green-700 text-white rounded-xl p-5 flex items-center gap-3 transition shadow">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 flex-shrink-0">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
        </svg>
        <span class="font-semibold">Kelola Member</span>
    </a>
    <a href="/admin/loans.php" class="bg-yellow-500 hover:bg-yellow-600 text-white rounded-xl p-5 flex items-center gap-3 transition shadow">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 flex-shrink-0">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span class="font-semibold">Data Peminjaman</span>
    </a>
</div>

<!-- Tabel Peminjaman Terbaru -->
<div class="bg-white rounded-xl shadow overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-100">
        <h3 class="font-semibold text-gray-700">Peminjaman Terbaru</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm text-gray-700">
            <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                <tr>
                    <th class="px-6 py-3">Member</th>
                    <th class="px-6 py-3">Judul Buku</th>
                    <th class="px-6 py-3 text-left">Tanggal Pinjam</th>
                    <th class="px-6 py-3 text-left">Batas Kembali</th>
                    <th class="px-6 py-3 text-left">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if (empty($recentLoans)): ?>
                    <tr>
                        <td colspan="5" class="text-center py-6 text-gray-400">Belum ada data peminjaman.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($recentLoans as $loan): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-3"><?= e($loan['member_name']) ?></td>
                            <td class="px-6 py-3"><?= e($loan['book_title']) ?></td>
                            <td class="px-6 py-3"><?= $loan['loan_date'] ? formatTanggal($loan['loan_date']) : '—' ?></td>
                            <td class="px-6 py-3"><?= $loan['due_date'] ? formatTanggal($loan['due_date']) : '—' ?></td>
                            <td class="px-6 py-3"><?= loanStatusBadge($loan['status']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
