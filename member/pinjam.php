<?php
// member/pinjam.php
// Memproses peminjaman buku oleh member

require_once '../includes/auth_check.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

requireMember();

$bookId = (int) ($_GET['book_id'] ?? 0);
$userId = (int) $_SESSION['user_id'];

if ($bookId <= 0) {
    header('Location: /index.php');
    exit();
}

// 1. Cek apakah ada denda yang belum dibayar
$unpaidStmt = $pdo->prepare("SELECT COUNT(*) FROM loans WHERE user_id = ? AND fine > 0 AND fine_status = 'unpaid'");
$unpaidStmt->execute([$userId]);
$hasUnpaidFines = (int) $unpaidStmt->fetchColumn() > 0;

if ($hasUnpaidFines) {
    $_SESSION['error'] = 'Anda tidak dapat meminjam buku karena memiliki denda yang belum dibayar.';
    header('Location: /index.php');
    exit();
}

// 2. Cek apakah member sedang meminjam 3 buku
$activeLoansStmt = $pdo->prepare("SELECT COUNT(*) FROM loans WHERE user_id = ? AND status IN ('pending', 'dipinjam')");
$activeLoansStmt->execute([$userId]);
$activeLoans = (int) $activeLoansStmt->fetchColumn();

if ($activeLoans >= 3) {
    $_SESSION['error'] = 'Maksimal peminjaman adalah 3 buku secara bersamaan. Kembalikan buku untuk meminjam lagi.';
    header('Location: /index.php');
    exit();
}

// 3. Cek apakah member sudah meminjam buku ini (pending/dipinjam)
$alreadyBorrowedStmt = $pdo->prepare("SELECT COUNT(*) FROM loans WHERE user_id = ? AND book_id = ? AND status IN ('pending', 'dipinjam')");
$alreadyBorrowedStmt->execute([$userId, $bookId]);
if ((int) $alreadyBorrowedStmt->fetchColumn() > 0) {
    $_SESSION['error'] = 'Anda sedang meminjam buku ini. Tunggu persetujuan atau kembalikan terlebih dahulu.';
    header('Location: /index.php');
    exit();
}

// 4. Cek ketersediaan stok
$stockStmt = $pdo->prepare("SELECT stock, title FROM books WHERE id = ?");
$stockStmt->execute([$bookId]);
$book = $stockStmt->fetch();

if (!$book || $book['stock'] <= 0) {
    $_SESSION['error'] = 'Maaf, stok buku ini sedang kosong.';
    header('Location: /index.php');
    exit();
}

// 5. Masukkan ke database sebagai status pending
$pdo->prepare("INSERT INTO loans (user_id, book_id, status) VALUES (?, ?, 'pending')")->execute([$userId, $bookId]);

$_SESSION['success'] = 'Permintaan peminjaman buku "' . e($book['title']) . '" berhasil dikirim dan menunggu persetujuan admin.';
header('Location: /member/index.php');
exit();
?>
