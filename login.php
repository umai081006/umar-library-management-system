<?php
// login.php
// Halaman login untuk semua pengguna

require_once 'includes/auth_check.php';
require_once 'config/database.php';
require_once 'includes/functions.php';

// Jika sudah login, redirect sesuai role
redirectIfLoggedIn();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validasi input dasar
    if (empty($email) || empty($password)) {
        $error = 'Email dan password wajib diisi.';
    } else {
        // Gunakan prepared statement untuk mencegah SQL Injection
        $stmt = $pdo->prepare("SELECT id, name, email, password, role FROM users WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        // Verifikasi password dengan password_verify()
        if ($user && password_verify($password, $user['password'])) {
            // Login berhasil — simpan data ke session
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['email']     = $user['email'];
            $_SESSION['role']      = $user['role'];

            // Redirect berdasarkan role
            if ($user['role'] === 'admin') {
                header('Location: /admin/index.php');
            } else {
                header('Location: /member/index.php');
            }
            exit();
        } else {
            $error = 'Email atau password salah.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Perpustakaan Online</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="min-h-screen bg-gradient-to-br from-blue-600 to-indigo-800 flex items-center justify-center px-4">

<div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-8">
    <!-- Logo / Judul -->
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-100 rounded-full mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                stroke="currentColor" class="w-8 h-8 text-blue-600">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.315 48.315 0 0012 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75z" />
            </svg>
        </div>
        <h1 class="text-2xl font-bold text-gray-800">Perpustakaan Online</h1>
        <p class="text-gray-500 text-sm mt-1">Masuk ke akun Anda</p>
    </div>

    <!-- Pesan Error -->
    <?php if ($error): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-5 text-sm">
            <?= e($error) ?>
        </div>
    <?php endif; ?>

    <!-- Pesan sukses dari register -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-5 text-sm">
            <?= e($_SESSION['success']) ?>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <!-- Form Login -->
    <form method="POST" action="/login.php" novalidate>
        <div class="mb-5">
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <input type="email" id="email" name="email"
                value="<?= e($_POST['email'] ?? '') ?>"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition"
                placeholder="contoh@email.com" required autofocus>
        </div>

        <div class="mb-6">
            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
            <input type="password" id="password" name="password"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition"
                placeholder="Masukkan password" required>
        </div>

        <button type="submit"
            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition duration-200 shadow">
            Masuk
        </button>
    </form>

    <p class="text-center text-sm text-gray-500 mt-6">
        Belum punya akun?
        <a href="/register.php" class="text-blue-600 hover:underline font-medium">Daftar Sekarang</a>
    </p>
</div>

</body>
</html>
