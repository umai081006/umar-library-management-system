<?php
// admin/loans.php
// Halaman kelola peminjaman buku (admin only)

require_once '../includes/auth_check.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

requireAdmin();

// --- PROSES APPROVE / RETURN / TOLAK / PAY FINE ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $loanId = (int) ($_POST['loan_id'] ?? 0);
    $action = $_POST['action'] ?? '';

    if ($loanId > 0) {
        switch ($action) {
            case 'approve':
                $stmt = $pdo->prepare("
                    UPDATE loans
                    SET status    = 'dipinjam',
                        loan_date = CURRENT_DATE,
                        due_date  = CURRENT_DATE + INTERVAL '7 days'
                    WHERE id = $1 AND status = 'pending'
                ");
                $stmt->execute([$loanId]);
                // Kurangi stok buku
                $pdo->prepare("
                    UPDATE books SET stock = stock - 1
                    WHERE id = (SELECT book_id FROM loans WHERE id = $1) AND stock > 0
                ")->execute([$loanId]);
                setFlashMessage('Peminjaman berhasil disetujui.', 'success');
                break;

            case 'return':
                $loanStmt = $pdo->prepare("SELECT * FROM loans WHERE id = $1");
                $loanStmt->execute([$loanId]);
                $loan = $loanStmt->fetch();

                if ($loan && $loan['status'] === 'dipinjam') {
                    $returnDate = new DateTime();
                    $dueDate    = new DateTime($loan['due_date']);
                    $fine       = 0;
                    if ($returnDate > $dueDate) {
                        $diffDays = (int) $returnDate->diff($dueDate)->days;
                        $fine     = $diffDays * 1000; // Rp 1.000/hari
                    }
                    $pdo->prepare("
                        UPDATE loans
                        SET status = 'dikembalikan', return_date = CURRENT_DATE, fine = $1
                        WHERE id = $2
                    ")->execute([$fine, $loanId]);
                    // Kembalikan stok
                    $pdo->prepare("
                        UPDATE books SET stock = stock + 1
                        WHERE id = (SELECT book_id FROM loans WHERE id = $1)
                    ")->execute([$loanId]);
                    $msg = 'Buku berhasil dikembalikan.';
                    if ($fine > 0) {
                        $msg .= ' Denda keterlambatan: Rp ' . number_format($fine, 0, ',', '.');
                    }
                    setFlashMessage($msg, 'success');
                }
                break;

            case 'reject':
                $pdo->prepare("UPDATE loans SET status = 'ditolak' WHERE id = $1 AND status = 'pending'")
                    ->execute([$loanId]);
                setFlashMessage('Peminjaman ditolak.', 'error');
                break;
                
            case 'pay_fine':
                $pdo->prepare("UPDATE loans SET fine_status = 'paid' WHERE id = $1 AND fine > 0 AND status = 'dikembalikan'")
                    ->execute([$loanId]);
                setFlashMessage('Denda berhasil dilunasi.', 'success');
                break;
        }
    }
    header('Location: loans.php');
    exit();
}

// --- FILTER STATUS ---
$statusFilter  = $_GET['status'] ?? 'all';
$validStatuses = ['all', 'pending', 'dipinjam', 'dikembalikan', 'ditolak'];
if (!in_array($statusFilter, $validStatuses)) {
    $statusFilter = 'all';
}

$page    = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 10;
$offset  = ($page - 1) * $perPage;

if ($statusFilter !== 'all') {
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM loans WHERE status = $1");
    $countStmt->execute([$statusFilter]);
    $total = (int) $countStmt->fetchColumn();

    $stmt = $pdo->prepare("
        SELECT l.*, u.name AS user_name, u.email AS user_email,
               b.title AS book_title, b.author AS book_author
        FROM loans l
        JOIN users u ON l.user_id = u.id
        JOIN books b ON l.book_id = b.id
        WHERE l.status = $1
        ORDER BY l.created_at DESC
        LIMIT $2 OFFSET $3
    ");
    $stmt->execute([$statusFilter, $perPage, $offset]);
} else {
    $total = (int) $pdo->query("SELECT COUNT(*) FROM loans")->fetchColumn();
    $stmt  = $pdo->prepare("
        SELECT l.*, u.name AS user_name, u.email AS user_email,
               b.title AS book_title, b.author AS book_author
        FROM loans l
        JOIN users u ON l.user_id = u.id
        JOIN books b ON l.book_id = b.id
        ORDER BY l.created_at DESC
        LIMIT $1 OFFSET $2
    ");
    $stmt->execute([$perPage, $offset]);
}

$loans      = $stmt->fetchAll();
$totalPages = ceil($total / $perPage);
$pageTitle  = 'Kelola Peminjaman';
require_once '../includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Kelola Peminjaman</h1>
        <p class="text-gray-500 text-sm mt-1">Setujui, kembalikan, atau tolak permintaan peminjaman</p>
    </div>

    <?= showFlashMessage() ?>

    <!-- Filter Tab -->
    <div class="flex gap-2 mb-6 flex-wrap">
        <?php
        $tabs = [
            'all' => 'Semua', 'pending' => 'Menunggu',
            'dipinjam' => 'Dipinjam', 'dikembalikan' => 'Dikembalikan', 'ditolak' => 'Ditolak',
        ];
        $colors = [
            'all' => 'bg-gray-800', 'pending' => 'bg-yellow-500',
            'dipinjam' => 'bg-blue-600', 'dikembalikan' => 'bg-green-600', 'ditolak' => 'bg-red-600',
        ];
        foreach ($tabs as $val => $label):
            $cls = ($statusFilter === $val)
                ? $colors[$val] . ' text-white'
                : 'bg-white border border-gray-300 text-gray-700 hover:bg-gray-50';
        ?>
            <a href="?status=<?= $val ?>" class="px-4 py-2 rounded-lg text-sm font-medium transition <?= $cls ?>">
                <?= $label ?>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Tabel -->
    <div class="bg-white rounded-xl shadow overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">#</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Peminjam</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Buku</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Tgl Pinjam</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Batas Kembali</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Denda</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if (empty($loans)): ?>
                    <tr>
                        <td colspan="8" class="text-center py-10 text-gray-400">Tidak ada data peminjaman.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($loans as $i => $loan):
                        $isOverdue = $loan['status'] === 'dipinjam' && !empty($loan['due_date'])
                            && strtotime($loan['due_date']) < time();
                    ?>
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-4 py-3 text-sm text-gray-500"><?= $offset + $i + 1 ?></td>
                            <td class="px-4 py-3">
                                <p class="font-medium text-gray-800 text-sm"><?= htmlspecialchars($loan['user_name']) ?></p>
                                <p class="text-gray-400 text-xs"><?= htmlspecialchars($loan['user_email']) ?></p>
                            </td>
                            <td class="px-4 py-3">
                                <p class="font-medium text-gray-800 text-sm line-clamp-1"><?= htmlspecialchars($loan['book_title']) ?></p>
                                <p class="text-gray-400 text-xs"><?= htmlspecialchars($loan['book_author']) ?></p>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">
                                <?= $loan['loan_date'] ? date('d/m/Y', strtotime($loan['loan_date'])) : '—' ?>
                            </td>
                            <td class="px-4 py-3 text-sm <?= $isOverdue ? 'text-red-600 font-semibold' : 'text-gray-600' ?>">
                                <?= $loan['due_date'] ? date('d/m/Y', strtotime($loan['due_date'])) : '—' ?>
                                <?= $isOverdue ? ' ⚠️' : '' ?>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">
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
                            <td class="px-4 py-3"><?= loanStatusBadge($loan['status']) ?></td>
                            <td class="px-4 py-3">
                                <form method="POST" class="flex gap-2 flex-wrap">
                                    <input type="hidden" name="loan_id" value="<?= $loan['id'] ?>">
                                    <?php if ($loan['status'] === 'pending'): ?>
                                        <button name="action" value="approve"
                                            class="bg-green-600 text-white text-xs px-3 py-1 rounded-lg hover:bg-green-700">Setujui</button>
                                        <button name="action" value="reject"
                                            onclick="return confirm('Tolak peminjaman ini?')"
                                            class="bg-red-600 text-white text-xs px-3 py-1 rounded-lg hover:bg-red-700">Tolak</button>
                                    <?php elseif ($loan['status'] === 'dipinjam'): ?>
                                        <button name="action" value="return"
                                            onclick="return confirm('Konfirmasi pengembalian buku?')"
                                            class="bg-blue-600 text-white text-xs px-3 py-1 rounded-lg hover:bg-blue-700">Kembalikan</button>
                                    <?php elseif ($loan['status'] === 'dikembalikan' && $loan['fine'] > 0 && $loan['fine_status'] === 'unpaid'): ?>
                                        <button name="action" value="pay_fine"
                                            onclick="return confirm('Konfirmasi pelunasan denda?')"
                                            class="bg-purple-600 text-white text-xs px-3 py-1 rounded-lg hover:bg-purple-700">Lunasi Denda</button>
                                    <?php else: ?>
                                        <span class="text-gray-300 text-xs">—</span>
                                    <?php endif; ?>
                                </form>
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
                <a href="?status=<?= $statusFilter ?>&page=<?= $p ?>"
                   class="px-3 py-2 rounded-lg text-sm font-medium transition <?= $p === $page ? 'bg-blue-600 text-white' : 'bg-white border border-gray-300 text-gray-700 hover:bg-gray-50' ?>">
                    <?= $p ?>
                </a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>
