<?php
// includes/header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - Perpustakaan Online' : 'Perpustakaan Online' ?></title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="bg-gray-50 flex flex-col min-h-screen">

<!-- Navbar -->
<nav class="bg-blue-600 text-white shadow-md">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center">
                <a href="/index.php" class="text-xl font-bold flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.315 48.315 0 0012 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75z" />
                    </svg>
                    Perpustakaan Online
                </a>
            </div>
            <div class="flex items-center space-x-4">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <a href="/admin/index.php" class="hover:text-blue-200 text-sm">Dashboard</a>
                        <a href="/admin/books.php" class="hover:text-blue-200 text-sm hidden md:inline">Buku</a>
                        <a href="/admin/loans.php" class="hover:text-blue-200 text-sm hidden md:inline">Peminjaman</a>
                        <a href="/admin/reports.php" class="hover:text-blue-200 text-sm hidden md:inline">Laporan</a>
                    <?php else: ?>
                        <a href="/member/index.php" class="hover:text-blue-200 text-sm">Dashboard</a>
                        <a href="/member/loans.php" class="hover:text-blue-200 text-sm hidden md:inline">Riwayat</a>
                    <?php endif; ?>
                    <span class="text-blue-200 hidden sm:inline-block text-sm">| <?= htmlspecialchars($_SESSION['user_name']) ?></span>
                    <a href="/logout.php" class="bg-red-500 hover:bg-red-600 px-3 py-1.5 rounded-md transition duration-200 text-sm">Logout</a>
                <?php else: ?>
                    <a href="/login.php" class="hover:text-blue-200 text-sm">Login</a>
                    <a href="/register.php" class="bg-white text-blue-600 hover:bg-blue-50 px-3 py-1.5 rounded-md transition duration-200 text-sm font-medium">Daftar</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<!-- Main Content Area -->
<main class="flex-grow max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full">
