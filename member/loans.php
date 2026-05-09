<?php
// member/loans.php
// Riwayat peminjaman member

require_once '../includes/auth_check.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

requireMember();

$userId = (int) $_SESSION['user_id'];

// --- PAGINATION ---
$page    = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 10;
$offset  = ($page - 1) * $perPage;

$totalStmt = $pdo->prepare("SELECT COUNT(*) FROM loans WHERE user_id = ?");
$totalStmt->execute([$userId]);
$total = (int) $totalStmt->fetchColumn();

$stmt = $pdo->prepare("
    SELECT l.*, b.title, b.author
    FROM loans l
    JOIN books b ON l.book_id = b.id
    WHERE l.user_id = ?
    ORDER BY l.created_at DESC
    LIMIT ? OFFSET ?
");
$stmt->execute([$userId, $perPage, $offset]);
$loans = $stmt->fetchAll();
$totalPages = ceil($total / $perPage);

$pageTitle = 'Riwayat Peminjaman';
require_once '../includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Riwayat Peminjaman</h1>
            <p class="text-gray-500 text-sm mt-1">Semua aktivitas peminjaman buku Anda</p>
        </div>
        <a href="/member" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
            &larr; Kembali ke Dashboard
        </a>
    </div>

    <!-- Tabel Riwayat -->
    <div class="bg-white rounded-xl shadow overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">#</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Buku</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tgl Pinjam</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Batas Kembali</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tgl Dikembalikan</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Denda</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if (empty($loans)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-10 text-gray-400">Belum ada riwayat peminjaman.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($loans as $i => $loan): 
                        $isOverdue = $loan['status'] === 'dipinjam' && !empty($loan['due_date']) && strtotime($loan['due_date']) < time();
                    ?>
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 text-sm text-gray-500"><?= $offset + $i + 1 ?></td>
                            <td class="px-6 py-4">
                                <p class="font-medium text-gray-800 text-sm line-clamp-1"><?= htmlspecialchars($loan['title']) ?></p>
                                <p class="text-gray-400 text-xs"><?= htmlspecialchars($loan['author']) ?></p>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                <?= $loan['loan_date'] ? date('d/m/Y', strtotime($loan['loan_date'])) : '—' ?>
                            </td>
                            <td class="px-6 py-4 text-sm <?= $isOverdue ? 'text-red-600 font-semibold' : 'text-gray-600' ?>">
                                <?= $loan['due_date'] ? date('d/m/Y', strtotime($loan['due_date'])) : '—' ?>
                                <?= $isOverdue ? ' ⚠️' : '' ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                <?php if ($loan['fine'] > 0): ?>
                                    <div class="font-medium">Rp <?= number_format($loan['fine'], 0, ',', '.') ?></div>
                                    <?php if ($loan['fine_status'] === 'paid'): ?>
                                        <span class="text-[10px] bg-green-100 text-green-700 px-1 py-0.5 rounded uppercase">Lunas</span>
                                    <?php else: ?>
                                        <span class="text-[10px] bg-red-100 text-red-700 px-1 py-0.5 rounded uppercase">Belum Lunas</span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4">
                                <?= loanStatusBadge($loan['status']) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div class="flex justify-center mt-6 gap-2">
            <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                <a href="?page=<?= $p ?>" 
                   class="px-3 py-2 rounded-lg text-sm font-medium transition <?= $p === $page ? 'bg-blue-600 text-white' : 'bg-white border border-gray-300 text-gray-700 hover:bg-gray-50' ?>">
                    <?= $p ?>
                </a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>
