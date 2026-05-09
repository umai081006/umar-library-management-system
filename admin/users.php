<?php
// admin/users.php
// Halaman kelola pengguna (admin only)

require_once '../includes/auth_check.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

requireAdmin();

// --- HAPUS USER ---
if (isset($_GET['delete'])) {
    $deleteId = (int) $_GET['delete'];

    // Jangan hapus diri sendiri
    if ($deleteId === (int) $_SESSION['user_id']) {
        setFlashMessage('Anda tidak bisa menghapus akun Anda sendiri!', 'error');
    } else {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = $1 AND role != 'admin'");
        $stmt->execute([$deleteId]);
        setFlashMessage('Pengguna berhasil dihapus.', 'success');
    }
    header('Location: users.php');
    exit();
}

// --- UBAH ROLE USER ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_role'])) {
    $userId   = (int) $_POST['user_id'];
    $newRole  = in_array($_POST['new_role'], ['admin', 'member']) ? $_POST['new_role'] : 'member';

    if ($userId === (int) $_SESSION['user_id']) {
        setFlashMessage('Anda tidak bisa mengubah role diri sendiri!', 'error');
    } else {
        $stmt = $pdo->prepare("UPDATE users SET role = $1 WHERE id = $2");
        $stmt->execute([$newRole, $userId]);
        setFlashMessage('Role pengguna berhasil diubah.', 'success');
    }
    header('Location: users.php');
    exit();
}

// --- AMBIL DATA USER (dengan pagination) ---
$page    = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 10;
$offset  = ($page - 1) * $perPage;
$search  = sanitize($_GET['search'] ?? '');

if ($search) {
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE name ILIKE $1 OR email ILIKE $1");
    $countStmt->execute(["%$search%"]);
    $total = (int) $countStmt->fetchColumn();

    $stmt = $pdo->prepare("
        SELECT id, name, email, role, created_at
        FROM users
        WHERE name ILIKE $1 OR email ILIKE $1
        ORDER BY created_at DESC
        LIMIT $2 OFFSET $3
    ");
    $stmt->execute(["%$search%", $perPage, $offset]);
} else {
    $countStmt = $pdo->query("SELECT COUNT(*) FROM users");
    $total     = (int) $countStmt->fetchColumn();

    $stmt = $pdo->prepare("
        SELECT id, name, email, role, created_at
        FROM users
        ORDER BY created_at DESC
        LIMIT $1 OFFSET $2
    ");
    $stmt->execute([$perPage, $offset]);
}

$users     = $stmt->fetchAll();
$totalPages = ceil($total / $perPage);

// Set judul halaman
$pageTitle = 'Kelola Pengguna';
require_once '../includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Kelola Pengguna</h1>
            <p class="text-gray-500 text-sm mt-1">Total <?= $total ?> pengguna terdaftar</p>
        </div>
        <!-- Search -->
        <form method="GET" class="flex gap-2">
            <input
                type="text"
                name="search"
                value="<?= htmlspecialchars($search) ?>"
                placeholder="Cari nama atau email..."
                class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 w-64"
            >
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700 transition">
                Cari
            </button>
            <?php if ($search): ?>
                <a href="users.php" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm hover:bg-gray-300 transition">Reset</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Flash Message -->
    <?= showFlashMessage() ?>

    <!-- Tabel Pengguna -->
    <div class="bg-white rounded-xl shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">#</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nama</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Role</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Terdaftar</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-10 text-gray-400">
                            Tidak ada pengguna ditemukan.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($users as $i => $user): ?>
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 text-sm text-gray-500"><?= $offset + $i + 1 ?></td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <!-- Avatar Inisial -->
                                    <div class="w-9 h-9 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-sm">
                                        <?= strtoupper(substr($user['name'], 0, 1)) ?>
                                    </div>
                                    <span class="font-medium text-gray-800"><?= htmlspecialchars($user['name']) ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600"><?= htmlspecialchars($user['email']) ?></td>
                            <td class="px-6 py-4">
                                <?php if ($user['id'] === (int) $_SESSION['user_id']): ?>
                                    <span class="px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        <?= ucfirst($user['role']) ?> (Anda)
                                    </span>
                                <?php else: ?>
                                    <!-- Form ganti role inline -->
                                    <form method="POST" class="flex items-center gap-2">
                                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                        <select
                                            name="new_role"
                                            onchange="this.form.submit()"
                                            class="border border-gray-300 rounded-lg text-sm px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        >
                                            <option value="member" <?= $user['role'] === 'member' ? 'selected' : '' ?>>Member</option>
                                            <option value="admin"  <?= $user['role'] === 'admin'  ? 'selected' : '' ?>>Admin</option>
                                        </select>
                                        <input type="hidden" name="change_role" value="1">
                                    </form>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                <?= date('d/m/Y', strtotime($user['created_at'])) ?>
                            </td>
                            <td class="px-6 py-4">
                                <?php if ($user['id'] !== (int) $_SESSION['user_id'] && $user['role'] !== 'admin'): ?>
                                    <a
                                        href="users.php?delete=<?= $user['id'] ?>"
                                        class="text-red-600 hover:text-red-800 text-sm font-medium"
                                        onclick="return confirmDelete()"
                                    >
                                        Hapus
                                    </a>
                                <?php else: ?>
                                    <span class="text-gray-300 text-sm">—</span>
                                <?php endif; ?>
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
                <a
                    href="?page=<?= $p ?><?= $search ? '&search=' . urlencode($search) : '' ?>"
                    class="px-3 py-2 rounded-lg text-sm font-medium transition
                        <?= $p === $page
                            ? 'bg-blue-600 text-white'
                            : 'bg-white border border-gray-300 text-gray-700 hover:bg-gray-50' ?>"
                >
                    <?= $p ?>
                </a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>
