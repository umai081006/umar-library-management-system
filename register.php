<?php
// register.php
// Halaman registrasi untuk pengguna baru (role: member)

require_once 'includes/auth_check.php';
require_once 'config/database.php';
require_once 'includes/functions.php';

// Jika sudah login, redirect
redirectIfLoggedIn();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    // --- Validasi Input ---
    if (empty($name) || empty($email) || empty($password) || empty($confirm)) {
        $error = 'Semua field wajib diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid.';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter.';
    } elseif ($password !== $confirm) {
        $error = 'Konfirmasi password tidak cocok.';
    } else {
        // Cek apakah email sudah terdaftar (prepared statement)
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);

        if ($stmt->fetch()) {
            $error = 'Email sudah terdaftar. Gunakan email lain.';
        } else {
            // Hash password dengan bcrypt (password_hash)
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

            // Simpan user baru (prepared statement)
            $insert = $pdo->prepare(
                "INSERT INTO users (name, email, password, role) VALUES (:name, :email, :password, 'member')"
            );
            $insert->execute([
                ':name'     => $name,
                ':email'    => $email,
                ':password' => $hashedPassword,
            ]);

            $_SESSION['success'] = 'Registrasi berhasil! Silakan login.';
            header('Location: /login.php');
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - Perpustakaan Online</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="min-h-screen bg-gradient-to-br from-blue-600 to-indigo-800 flex items-center justify-center px-4 py-10">

<div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-8">
    <!-- Logo / Judul -->
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-100 rounded-full mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                stroke="currentColor" class="w-8 h-8 text-blue-600">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM4 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 0110.374 21c-2.331 0-4.512-.645-6.374-1.766z" />
            </svg>
        </div>
        <h1 class="text-2xl font-bold text-gray-800">Buat Akun Baru</h1>
        <p class="text-gray-500 text-sm mt-1">Daftar sebagai member perpustakaan</p>
    </div>

    <!-- Pesan Error -->
    <?php if ($error): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-5 text-sm">
            <?= e($error) ?>
        </div>
    <?php endif; ?>

    <!-- Form Registrasi -->
    <form method="POST" action="/register.php" novalidate>
        <div class="mb-4">
            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
            <input type="text" id="name" name="name"
                value="<?= e($_POST['name'] ?? '') ?>"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition"
                placeholder="Nama Lengkap Anda" required autofocus>
        </div>

        <div class="mb-4">
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <input type="email" id="email" name="email"
                value="<?= e($_POST['email'] ?? '') ?>"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition"
                placeholder="contoh@email.com" required>
        </div>

        <div class="mb-4">
            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
            <input type="password" id="password" name="password"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition"
                placeholder="Minimal 6 karakter" required>
        </div>

        <div class="mb-6">
            <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password</label>
            <input type="password" id="confirm_password" name="confirm_password"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition"
                placeholder="Ulangi password" required>
        </div>

        <button type="submit"
            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition duration-200 shadow">
            Daftar Sekarang
        </button>
    </form>

    <p class="text-center text-sm text-gray-500 mt-6">
        Sudah punya akun?
        <a href="/login.php" class="text-blue-600 hover:underline font-medium">Login di sini</a>
    </p>
</div>

</body>
</html>
