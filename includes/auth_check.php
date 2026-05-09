<?php
// includes/auth_check.php
// Helper untuk memeriksa status login dan role pengguna

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Cek apakah user sudah login.
 * Jika belum, redirect ke halaman login.
 */
function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: /login.php');
        exit();
    }
}

/**
 * Cek apakah user adalah admin.
 * Jika bukan, redirect ke halaman member.
 */
function requireAdmin() {
    requireLogin();
    if ($_SESSION['role'] !== 'admin') {
        header('Location: /member/index.php');
        exit();
    }
}

/**
 * Cek apakah user adalah member.
 * Jika sudah login sebagai admin, redirect ke dashboard admin.
 */
function requireMember() {
    requireLogin();
    if ($_SESSION['role'] === 'admin') {
        header('Location: /admin/index.php');
        exit();
    }
}

/**
 * Redirect jika sudah login (untuk halaman login/register).
 */
function redirectIfLoggedIn() {
    if (isset($_SESSION['user_id'])) {
        if ($_SESSION['role'] === 'admin') {
            header('Location: /admin/index.php');
        } else {
            header('Location: /member/index.php');
        }
        exit();
    }
}
?>
