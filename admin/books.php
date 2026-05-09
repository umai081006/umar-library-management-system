<?php
// admin/books.php
// Halaman kelola buku — CRUD (Create, Read, Update, Delete)

require_once '../includes/auth_check.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

requireAdmin();

// --- HAPUS BUKU ---
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    // Hapus file cover jika ada
    $stmt = $pdo->prepare("SELECT cover_image FROM books WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $book = $stmt->fetch();
    if ($book && $book['cover_image'] && file_exists('../uploads/' . $book['cover_image'])) {
        unlink('../uploads/' . $book['cover_image']);
    }
    $pdo->prepare("DELETE FROM books WHERE id = :id")->execute([':id' => $id]);
    $_SESSION['success'] = 'Buku berhasil dihapus.';
    header('Location: /admin/books.php');
    exit();
}

// --- TAMBAH / EDIT BUKU ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id            = (int) ($_POST['id'] ?? 0);
    $title         = trim($_POST['title'] ?? '');
    $author        = trim($_POST['author'] ?? '');
    $isbn          = trim($_POST['isbn'] ?? '');
    $published_year = (int) ($_POST['published_year'] ?? 0);
    $stock         = (int) ($_POST['stock'] ?? 0);
    $cover_image   = '';

    // Upload cover buku
    if (!empty($_FILES['cover_image']['name'])) {
        $ext         = pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION);
        $allowedExt  = ['jpg', 'jpeg', 'png', 'webp'];
        if (in_array(strtolower($ext), $allowedExt)) {
            $filename    = 'book_' . time() . '.' . $ext;
            move_uploaded_file($_FILES['cover_image']['tmp_name'], '../uploads/' . $filename);
            $cover_image = $filename;
        }
    }

    if (empty($title) || empty($author)) {
        $_SESSION['error'] = 'Judul dan Penulis wajib diisi.';
    } elseif ($id > 0) {
        // UPDATE
        $sql = "UPDATE books SET title=:title, author=:author, isbn=:isbn,
                published_year=:year, stock=:stock";
        $params = [':title' => $title, ':author' => $author, ':isbn' => $isbn,
                   ':year' => $published_year, ':stock' => $stock, ':id' => $id];
        if ($cover_image) {
            $sql .= ", cover_image=:cover";
            $params[':cover'] = $cover_image;
        }
        $sql .= " WHERE id=:id";
        $pdo->prepare($sql)->execute($params);
        $_SESSION['success'] = 'Buku berhasil diperbarui.';
    } else {
        // INSERT
        $pdo->prepare(
            "INSERT INTO books (title, author, isbn, published_year, stock, cover_image)
             VALUES (:title, :author, :isbn, :year, :stock, :cover)"
        )->execute([
            ':title' => $title, ':author' => $author, ':isbn' => $isbn,
            ':year' => $published_year, ':stock' => $stock, ':cover' => $cover_image,
        ]);
        $_SESSION['success'] = 'Buku baru berhasil ditambahkan.';
    }
    header('Location: /admin/books.php');
    exit();
}

// Ambil buku yang akan diedit (jika ada)
$editBook = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM books WHERE id = :id");
    $stmt->execute([':id' => (int) $_GET['edit']]);
    $editBook = $stmt->fetch();
}

// Ambil semua buku
$books = $pdo->query("SELECT * FROM books ORDER BY created_at DESC")->fetchAll();

require_once '../includes/header.php';
?>

<div class="flex items-center justify-between mb-6">
    <h2 class="text-2xl font-bold text-gray-800">Kelola Buku</h2>
    <a href="/admin/index.php" class="text-sm text-blue-600 hover:underline">← Kembali ke Dashboard</a>
</div>

<?php showFlashMessage(); ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Form Tambah / Edit Buku -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded-xl shadow p-6">
            <h3 class="font-semibold text-gray-700 mb-4">
                <?= $editBook ? 'Edit Buku' : 'Tambah Buku Baru' ?>
            </h3>
            <form method="POST" action="/admin/books.php" enctype="multipart/form-data">
                <?php if ($editBook): ?>
                    <input type="hidden" name="id" value="<?= $editBook['id'] ?>">
                <?php endif; ?>

                <div class="mb-3">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Judul Buku <span class="text-red-500">*</span></label>
                    <input type="text" name="title"
                        value="<?= e($editBook['title'] ?? '') ?>"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none text-sm" required>
                </div>
                <div class="mb-3">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Penulis <span class="text-red-500">*</span></label>
                    <input type="text" name="author"
                        value="<?= e($editBook['author'] ?? '') ?>"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none text-sm" required>
                </div>
                <div class="mb-3">
                    <label class="block text-sm font-medium text-gray-700 mb-1">ISBN</label>
                    <input type="text" name="isbn"
                        value="<?= e($editBook['isbn'] ?? '') ?>"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none text-sm">
                </div>
                <div class="mb-3">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tahun Terbit</label>
                    <input type="number" name="published_year" min="1900" max="<?= date('Y') ?>"
                        value="<?= e($editBook['published_year'] ?? '') ?>"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none text-sm">
                </div>
                <div class="mb-3">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Stok</label>
                    <input type="number" name="stock" min="0"
                        value="<?= e($editBook['stock'] ?? 1) ?>"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none text-sm">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Cover Buku</label>
                    <input type="file" name="cover_image" accept="image/*"
                        onchange="previewImage(this, 'cover-preview')"
                        class="w-full text-sm text-gray-500 file:mr-3 file:py-1 file:px-3 file:rounded file:border-0 file:bg-blue-50 file:text-blue-600 hover:file:bg-blue-100">
                    <?php if (!empty($editBook['cover_image'])): ?>
                        <img src="/uploads/<?= e($editBook['cover_image']) ?>" id="cover-preview"
                            class="mt-2 h-24 w-auto rounded object-cover">
                    <?php else: ?>
                        <img id="cover-preview" class="mt-2 h-24 w-auto rounded object-cover hidden">
                    <?php endif; ?>
                </div>

                <div class="flex gap-2">
                    <button type="submit"
                        class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-lg text-sm font-medium transition">
                        <?= $editBook ? 'Simpan Perubahan' : 'Tambah Buku' ?>
                    </button>
                    <?php if ($editBook): ?>
                        <a href="/admin/books.php"
                            class="flex-1 text-center bg-gray-200 hover:bg-gray-300 text-gray-700 py-2 px-4 rounded-lg text-sm font-medium transition">
                            Batal
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabel Daftar Buku -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-xl shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-700">Daftar Buku (<?= count($books) ?>)</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-gray-700">
                    <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                        <tr>
                            <th class="px-4 py-3">Judul</th>
                            <th class="px-4 py-3">Penulis</th>
                            <th class="px-4 py-3">Stok</th>
                            <th class="px-4 py-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if (empty($books)): ?>
                            <tr>
                                <td colspan="4" class="text-center py-8 text-gray-400">Belum ada buku.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($books as $book): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 font-medium"><?= e($book['title']) ?></td>
                                    <td class="px-4 py-3"><?= e($book['author']) ?></td>
                                    <td class="px-4 py-3">
                                        <span class="<?= $book['stock'] > 0 ? 'text-green-600' : 'text-red-500' ?> font-semibold">
                                            <?= $book['stock'] ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex gap-2">
                                            <a href="/admin/books.php?edit=<?= $book['id'] ?>"
                                                class="text-xs bg-yellow-100 hover:bg-yellow-200 text-yellow-700 px-2 py-1 rounded transition">Edit</a>
                                            <a href="/admin/books.php?delete=<?= $book['id'] ?>"
                                                onclick="return confirmDelete()"
                                                class="text-xs bg-red-100 hover:bg-red-200 text-red-700 px-2 py-1 rounded transition">Hapus</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
