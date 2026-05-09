<?php
// index.php
// Halaman beranda — menampilkan daftar buku yang tersedia

require_once 'config/database.php';
require_once 'includes/auth_check.php';
require_once 'includes/functions.php';
require_once 'includes/header.php';

// Ambil semua buku yang tersedia (stock > 0) dengan prepared statement
$stmt = $pdo->prepare("SELECT * FROM books ORDER BY created_at DESC");
$stmt->execute();
$books = $stmt->fetchAll();
?>

<h2 class="text-2xl font-bold text-gray-800 mb-6">Katalog Buku</h2>

<?php showFlashMessage(); ?>

<!-- Grid Buku -->
<?php if (empty($books)): ?>
    <div class="text-center py-16 text-gray-400">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
            stroke="currentColor" class="w-16 h-16 mx-auto mb-4 opacity-50">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
        </svg>
        <p class="text-lg">Belum ada buku tersedia.</p>
    </div>
<?php else: ?>
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        <?php foreach ($books as $book): ?>
            <div class="bg-white rounded-xl shadow hover:shadow-lg transition duration-200 overflow-hidden flex flex-col">
                <!-- Cover Buku -->
                <div class="bg-gradient-to-br from-blue-400 to-indigo-500 h-48 flex items-center justify-center">
                    <?php if (!empty($book['cover_image']) && file_exists('uploads/' . $book['cover_image'])): ?>
                        <img src="/uploads/<?= e($book['cover_image']) ?>"
                            alt="Cover <?= e($book['title']) ?>"
                            class="h-full w-full object-cover">
                    <?php else: ?>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="1" stroke="white" class="w-20 h-20 opacity-75">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                        </svg>
                    <?php endif; ?>
                </div>

                <!-- Info Buku -->
                <div class="p-4 flex flex-col flex-grow">
                    <h3 class="font-semibold text-gray-800 text-sm leading-tight mb-1 line-clamp-2">
                        <?= e($book['title']) ?>
                    </h3>
                    <p class="text-xs text-gray-500 mb-1"><?= e($book['author']) ?></p>
                    <p class="text-xs text-gray-400 mb-3">Tahun: <?= e($book['published_year'] ?? '-') ?></p>

                    <!-- Stok -->
                    <div class="mt-auto flex items-center justify-between">
                        <span class="text-xs <?= $book['stock'] > 0 ? 'text-green-600 bg-green-100' : 'text-red-600 bg-red-100' ?> px-2 py-1 rounded-full font-medium">
                            <?= $book['stock'] > 0 ? 'Stok: ' . $book['stock'] : 'Habis' ?>
                        </span>
                        <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'member' && $book['stock'] > 0): ?>
                            <a href="/member/pinjam.php?book_id=<?= $book['id'] ?>"
                                class="text-xs bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded-lg transition">
                                Pinjam
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
