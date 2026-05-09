<?php
// member/index.php
// Dashboard Member — menampilkan ringkasan akun dan buku yang sedang dipinjam

require_once '../includes/auth_check.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

requireMember();

$userId = (int) $_SESSION['user_id'];

// --- STATISTIK MEMBER ---
$stmtDipinjam = $pdo->prepare("SELECT COUNT(*) FROM loans WHERE user_id = ? AND status = 'dipinjam'");
$stmtDipinjam->execute([$userId]);
$totalDipinjam = (int) $stmtDipinjam->fetchColumn();

$stmtDikembalikan = $pdo->prepare("SELECT COUNT(*) FROM loans WHERE user_id = ? AND status = 'dikembalikan'");
$stmtDikembalikan->execute([$userId]);
$totalDikembalikan = (int) $stmtDikembalikan->fetchColumn();

$stmtDenda = $pdo->prepare("SELECT COALESCE(SUM(fine), 0) FROM loans WHERE user_id = ? AND fine_status = 'unpaid' AND status = 'dikembalikan'");
$stmtDenda->execute([$userId]);
$totalDenda = (int) $stmtDenda->fetchColumn();

// --- BUKU YANG SEDANG DIPINJAM ---
$activeLoansStmt = $pdo->prepare("
    SELECT l.*, b.title, b.author, b.cover_image
    FROM loans l
    JOIN books b ON l.book_id = b.id
    WHERE l.user_id = ? AND l.status IN ('pending', 'dipinjam')
    ORDER BY l.created_at DESC
");
$activeLoansStmt->execute([$userId]);
$activeLoans = $activeLoansStmt->fetchAll();

// --- BUKU REKOMENDASI (Buku terbaru yang tersedia) ---
$recentBooksStmt = $pdo->query("
    SELECT id, title, author, cover_image, stock
    FROM books
    WHERE stock > 0
    ORDER BY created_at DESC
    LIMIT 4
");
$recentBooks = $recentBooksStmt->fetchAll();

$pageTitle = 'Dashboard Member';
require_once '../includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Selamat datang, <?= htmlspecialchars($_SESSION['user_name']) ?>!</h1>
        <p class="text-gray-500 text-sm mt-1">Ini adalah ringkasan aktivitas peminjaman buku Anda.</p>
    </div>

    <?= showFlashMessage() ?>

    <!-- Kartu Statistik -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow p-6 border-l-4 border-blue-500 flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500">Sedang Dipinjam</p>
                <p class="text-3xl font-bold text-gray-900 mt-1"><?= $totalDipinjam ?></p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center text-blue-600 text-xl">
                🔖
            </div>
        </div>
        <div class="bg-white rounded-xl shadow p-6 border-l-4 border-green-500 flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500">Sudah Dikembalikan</p>
                <p class="text-3xl font-bold text-gray-900 mt-1"><?= $totalDikembalikan ?></p>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center text-green-600 text-xl">
                ✅
            </div>
        </div>
        <div class="bg-white rounded-xl shadow p-6 border-l-4 border-red-500 flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500">Total Denda Dibayar</p>
                <p class="text-3xl font-bold text-gray-900 mt-1">Rp <?= number_format($totalDenda, 0, ',', '.') ?></p>
            </div>
            <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center text-red-600 text-xl">
                💰
            </div>
        </div>
    </div>

    <!-- Konten Utama: 2 Kolom -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Kolom Kiri: Peminjaman Aktif (2/3 lebar) -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-xl shadow overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                    <h2 class="text-lg font-semibold text-gray-800">Aktivitas Saat Ini</h2>
                    <a href="/member/loans.php" class="text-sm text-blue-600 hover:underline">Lihat Semua</a>
                </div>
                <div class="p-0">
                    <?php if (empty($activeLoans)): ?>
                        <div class="p-8 text-center">
                            <div class="text-4xl mb-3">📚</div>
                            <p class="text-gray-500">Anda tidak memiliki buku yang sedang dipinjam.</p>
                            <a href="/" class="mt-4 inline-block bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700 transition">
                                Cari Buku
                            </a>
                        </div>
                    <?php else: ?>
                        <ul class="divide-y divide-gray-100">
                            <?php foreach ($activeLoans as $loan): 
                                $isOverdue = $loan['status'] === 'dipinjam' && !empty($loan['due_date']) && strtotime($loan['due_date']) < time();
                            ?>
                                <li class="p-6 hover:bg-gray-50 transition">
                                    <div class="flex items-start gap-4">
                                        <!-- Cover Buku -->
                                        <div class="w-16 h-24 bg-gray-200 rounded-md overflow-hidden flex-shrink-0">
                                            <?php if ($loan['cover_image']): ?>
                                                <img src="/uploads/covers/<?= htmlspecialchars($loan['cover_image']) ?>" alt="Cover" class="w-full h-full object-cover">
                                            <?php else: ?>
                                                <div class="w-full h-full flex items-center justify-center text-gray-400 text-xs text-center p-1">No Cover</div>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <!-- Info Buku -->
                                        <div class="flex-1 min-w-0">
                                            <div class="flex justify-between items-start mb-1">
                                                <h3 class="text-base font-bold text-gray-900 line-clamp-1">
                                                    <?= htmlspecialchars($loan['title']) ?>
                                                </h3>
                                                <?= loanStatusBadge($loan['status']) ?>
                                            </div>
                                            <p class="text-sm text-gray-500 mb-2"><?= htmlspecialchars($loan['author']) ?></p>
                                            
                                            <div class="grid grid-cols-2 gap-2 text-sm">
                                                <div class="bg-gray-50 p-2 rounded">
                                                    <span class="block text-xs text-gray-400">Tgl Pinjam</span>
                                                    <span class="font-medium text-gray-700">
                                                        <?= $loan['loan_date'] ? date('d/m/Y', strtotime($loan['loan_date'])) : 'Menunggu' ?>
                                                    </span>
                                                </div>
                                                <div class="bg-gray-50 p-2 rounded">
                                                    <span class="block text-xs text-gray-400">Batas Kembali</span>
                                                    <span class="font-medium <?= $isOverdue ? 'text-red-600' : 'text-gray-700' ?>">
                                                        <?= $loan['due_date'] ? date('d/m/Y', strtotime($loan['due_date'])) : 'Menunggu' ?>
                                                        <?= $isOverdue ? ' ⚠️' : '' ?>
                                                    </span>
                                                </div>
                                            </div>
                                            <?php if ($isOverdue): ?>
                                                <p class="text-xs text-red-500 mt-2 flex items-center gap-1">
                                                    <span>⚠️</span> Harap segera kembalikan buku untuk menghindari denda bertambah.
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Rekomendasi (1/3 lebar) -->
        <div class="space-y-6">
            <div class="bg-white rounded-xl shadow p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Buku Terbaru</h2>
                
                <?php if (empty($recentBooks)): ?>
                    <p class="text-sm text-gray-500">Belum ada buku baru.</p>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($recentBooks as $book): ?>
                            <a href="/?search=<?= urlencode($book['title']) ?>" class="flex items-center gap-3 group">
                                <div class="w-12 h-16 bg-gray-200 rounded overflow-hidden flex-shrink-0 group-hover:ring-2 ring-blue-500 transition">
                                    <?php if ($book['cover_image']): ?>
                                        <img src="/uploads/covers/<?= htmlspecialchars($book['cover_image']) ?>" class="w-full h-full object-cover">
                                    <?php else: ?>
                                        <div class="w-full h-full flex items-center justify-center text-gray-400 text-[10px]">No Cover</div>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <h3 class="text-sm font-medium text-gray-900 group-hover:text-blue-600 transition line-clamp-2">
                                        <?= htmlspecialchars($book['title']) ?>
                                    </h3>
                                    <p class="text-xs text-gray-500 mt-1"><?= htmlspecialchars($book['author']) ?></p>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <div class="mt-6">
                    <a href="/" class="block w-full text-center bg-gray-50 hover:bg-gray-100 text-gray-700 font-medium py-2 px-4 rounded-lg transition text-sm">
                        Eksplorasi Katalog
                    </a>
                </div>
            </div>
        </div>
        
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
