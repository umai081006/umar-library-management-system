<?php
// config/database.php
// Konfigurasi koneksi ke database PostgreSQL
//
// CARA SETUP:
// 1. Salin file ini: cp config/database.example.php config/database.php
// 2. Isi nilai $user dan $password sesuai PostgreSQL lokal Anda

$host = 'localhost';
$port = '5432';
$dbname = 'library_db';
$user = 'postgres'; // Ganti dengan username PostgreSQL Anda
$password = 'your_password_here'; // Ganti dengan password PostgreSQL Anda

try {
    // Membuat koneksi PDO ke PostgreSQL
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
    $pdo = new PDO($dsn, $user, $password);

    // Set error mode ke exception agar mudah di-debug
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Set mode fetch default ke associative array
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // Tampilkan pesan error jika koneksi gagal
    die("Koneksi database gagal: " . $e->getMessage());
}
?>
