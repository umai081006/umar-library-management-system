<?php
// admin/reports.php
// Halaman laporan statistik perpustakaan (admin only)

require_once '../includes/auth_check.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

requireAdmin();

// --- STATISTIK UTAMA ---
$totalUsers  = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'member'")->fetchColumn();
$totalBooks  = (int) $pdo->query("SELECT COUNT(*) FROM books")->fetchColumn();
$totalLoans  = (int) $pdo->query("SELECT COUNT(*) FROM loans")->fetchColumn();
$activeLoans = (int) $pdo->query("SELECT COUNT(*) FROM loans WHERE status = 'dipinjam'")->fetchColumn();
$totalFine   = (int) $pdo->query("SELECT COALESCE(SUM(fine), 0) FROM loans WHERE status = 'dikembalikan'")->fetchColumn();
$overdueLoans = (int) $pdo->query("SELECT COUNT(*) FROM loans WHERE status = 'dipinjam' AND due_date < CURRENT_DATE")->fetchColumn();

// --- 5 BUKU PALING SERING DIPINJAM ---
$topBooks = $pdo->query("
    SELECT b.title, b.author, COUNT(l.id) AS total_loans
    FROM loans l
    JOIN books b ON l.book_id = b.id
    WHERE l.status IN ('dipinjam', 'dikembalikan')
    GROUP BY b.id, b.title, b.author
    ORDER BY total_loans DESC
    LIMIT 5
")->fetchAll();

// --- 5 MEMBER PALING AKTIF ---
$topMembers = $pdo->query("
    SELECT u.name, u.email, COUNT(l.id) AS total_loans
    FROM loans l
    JOIN users u ON l.user_id = u.id
    WHERE l.status IN ('dipinjam', 'dikembalikan')
    GROUP BY u.id, u.name, u.email
    ORDER BY total_loans DESC
    LIMIT 5
")->fetchAll();

// --- PEMINJAMAN PER BULAN (6 bulan terakhir) ---
$monthlyLoans = $pdo->query("
    SELECT TO_CHAR(created_at, 'Mon YYYY') AS bulan,
           TO_CHAR(created_at, 'YYYY-MM') AS bulan_sort,
           COUNT(*) AS total
    FROM loans
    WHERE created_at >= NOW() - INTERVAL '6 months'
    GROUP BY bulan, bulan_sort
    ORDER BY bulan_sort ASC
")->fetchAll();

$pageTitle = 'Laporan & Statistik';
require_once '../includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Laporan & Statistik</h1>
        <p class="text-gray-500 text-sm mt-1">Ringkasan aktivitas perpustakaan</p>
    </div>

    <!-- Kartu Statistik -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-8">
        <?php
        $stats = [
            ['label' => 'Total Member',     'value' => $totalUsers,   'color' => 'blue',   'icon' => '👥'],
            ['label' => 'Total Buku',        'value' => $totalBooks,   'color' => 'indigo', 'icon' => '📚'],
            ['label' => 'Total Peminjaman',  'value' => $totalLoans,   'color' => 'purple', 'icon' => '📋'],
            ['label' => 'Sedang Dipinjam',   'value' => $activeLoans,  'color' => 'yellow', 'icon' => '🔖'],
            ['label' => 'Terlambat',         'value' => $overdueLoans, 'color' => 'red',    'icon' => '⚠️'],
            ['label' => 'Total Denda',       'value' => 'Rp ' . number_format($totalFine, 0, ',', '.'), 'color' => 'green', 'icon' => '💰'],
        ];
        foreach ($stats as $s):
        ?>
            <div class="bg-white rounded-xl shadow p-4 text-center">
                <div class="text-2xl mb-1"><?= $s['icon'] ?></div>
                <div class="text-xl font-bold text-<?= $s['color'] ?>-600"><?= $s['value'] ?></div>
                <div class="text-xs text-gray-500 mt-1"><?= $s['label'] ?></div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Buku Paling Populer -->
        <div class="bg-white rounded-xl shadow p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">📚 Buku Paling Sering Dipinjam</h2>
            <?php if (empty($topBooks)): ?>
                <p class="text-gray-400 text-sm text-center py-6">Belum ada data peminjaman.</p>
            <?php else: ?>
                <div class="space-y-3">
                    <?php $maxLoans = max(array_column($topBooks, 'total_loans')); ?>
                    <?php foreach ($topBooks as $rank => $book): ?>
                        <div class="flex items-center gap-3">
                            <span class="w-7 h-7 rounded-full bg-blue-100 text-blue-700 text-sm font-bold flex items-center justify-center flex-shrink-0">
                                <?= $rank + 1 ?>
                            </span>
                            <div class="flex-1 min-w-0">
                                <p class="font-medium text-gray-800 text-sm truncate"><?= htmlspecialchars($book['title']) ?></p>
                                <p class="text-gray-400 text-xs"><?= htmlspecialchars($book['author']) ?></p>
                            </div>
                            <span class="text-sm font-semibold text-blue-600 flex-shrink-0">
                                <?= $book['total_loans'] ?>x
                            </span>
                        </div>
                        <!-- Bar visual -->
                        <div class="w-full bg-gray-100 rounded-full h-1.5 ml-10">
                            <div class="bg-blue-500 h-1.5 rounded-full"
                                 style="width: <?= $maxLoans > 0 ? round(($book['total_loans'] / $maxLoans) * 100) : 0 ?>%"></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Member Paling Aktif -->
        <div class="bg-white rounded-xl shadow p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">👥 Member Paling Aktif</h2>
            <?php if (empty($topMembers)): ?>
                <p class="text-gray-400 text-sm text-center py-6">Belum ada data peminjaman.</p>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($topMembers as $rank => $member): ?>
                        <div class="flex items-center gap-3">
                            <div class="w-7 h-7 rounded-full bg-green-100 text-green-700 text-sm font-bold flex items-center justify-center flex-shrink-0">
                                <?= strtoupper(substr($member['name'], 0, 1)) ?>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-medium text-gray-800 text-sm truncate"><?= htmlspecialchars($member['name']) ?></p>
                                <p class="text-gray-400 text-xs truncate"><?= htmlspecialchars($member['email']) ?></p>
                            </div>
                            <span class="text-sm font-semibold text-green-600 flex-shrink-0">
                                <?= $member['total_loans'] ?> buku
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Peminjaman Per Bulan -->
        <div class="bg-white rounded-xl shadow p-6 lg:col-span-2">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">📊 Peminjaman 6 Bulan Terakhir</h2>
            <?php if (empty($monthlyLoans)): ?>
                <p class="text-gray-400 text-sm text-center py-6">Belum ada data peminjaman.</p>
            <?php else: ?>
                <?php $maxMonthly = max(array_column($monthlyLoans, 'total')); ?>
                <div class="flex items-end gap-3 h-36">
                    <?php foreach ($monthlyLoans as $m): ?>
                        <?php $heightPct = $maxMonthly > 0 ? ($m['total'] / $maxMonthly) * 100 : 0; ?>
                        <div class="flex flex-col items-center flex-1 gap-1">
                            <span class="text-xs font-semibold text-blue-700"><?= $m['total'] ?></span>
                            <div class="w-full bg-blue-500 rounded-t-md transition-all duration-500"
                                 style="height: <?= max(4, $heightPct) ?>%"></div>
                            <span class="text-xs text-gray-500 whitespace-nowrap"><?= $m['bulan'] ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
