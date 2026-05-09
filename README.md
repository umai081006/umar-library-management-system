# Library Management System

Project Chapter 9 - Database
Nama: Umar Ramadhan
Kelas: Digirock Fullstack

Teknologi:
- PHP Native
- PostgreSQL
- HTML/CSS/JS

Sistem Manajemen Perpustakaan sederhana yang dibangun menggunakan PHP Native, PostgreSQL, dan Tailwind CSS. Proyek ini dirancang tanpa menggunakan framework besar untuk tujuan edukasi, agar mahasiswa dapat memahami alur kerja fundamental pengembangan aplikasi web, autentikasi, serta interaksi dengan database relasional (PostgreSQL).

## Fitur Utama

- **Autentikasi Aman:** Menggunakan session PHP, `password_hash()`, dan `password_verify()`.
- **Manajemen Peran (Role-based Access):** Memisahkan hak akses antara Admin dan Member.
- **Pencegahan SQL Injection:** Sepenuhnya menggunakan Prepared Statements (PDO).
- **Desain Responsif & Modern:** Menggunakan Tailwind CSS via CDN.

### Fitur Admin
- Dashboard statistik (Total buku, member, peminjaman, buku terpopuler).
- Kelola Buku (CRUD dengan upload cover gambar).
- Kelola Pengguna (Lihat daftar, hapus, ubah role).
- Kelola Peminjaman (Setujui, tolak, kembalikan buku, dan hitung denda keterlambatan).

### Fitur Member
- Dashboard dengan ringkasan aktivitas.
- Katalog Buku (Mencari dan meminjam buku).
- Riwayat Peminjaman (Melihat status buku yang dipinjam, dikembalikan, denda).

## Persyaratan Sistem

- PHP >= 7.4 (dengan ekstensi `pdo_pgsql`)
- PostgreSQL >= 10
- Web Server (Apache/Nginx) atau PHP Built-in Server

## Cara Instalasi

1. **Clone repositori** atau ekstrak file proyek ke direktori web server Anda (misal: `htdocs` atau `www`).

2. **Buat Database PostgreSQL**
   Buka terminal/PgAdmin dan buat database:
   ```sql
   CREATE DATABASE library_db;
   ```

3. **Impor Skema Database**
   Jalankan file `database/schema.sql` dan `database/seed.sql` ke dalam `library_db`:
   ```bash
   psql -U postgres -d library_db -f database/schema.sql
   psql -U postgres -d library_db -f database/seed.sql
   ```

4. **Konfigurasi Database**
   Buka file `config/database.php` dan sesuaikan kredensial koneksi (host, port, username, password).
   ```php
   $host = 'localhost';
   $port = '5432';
   $dbname = 'library_db';
   $user = 'postgres'; // Sesuaikan
   $password = 'password'; // Sesuaikan
   ```

5. **Jalankan Aplikasi**
   Jika menggunakan PHP built-in server, jalankan perintah ini di root folder proyek:
   ```bash
   php -S localhost:8000
   ```
   Lalu buka browser di `http://localhost:8000`.

## Akun Demo

| Role | Email | Password |
| --- | --- | --- |
| Admin | admin@library.com | admin123 |
| Member | member@library.com | member123 |

## Struktur Folder

```text
library-management-php-postgresql/
│
├── config/             # Konfigurasi aplikasi (koneksi database)
├── database/           # Skema dan data dummy (SQL)
├── admin/              # Halaman khusus administrator
├── member/             # Halaman khusus member
├── assets/             # File statis (CSS custom, JS)
│   ├── css/
│   └── js/
├── includes/           # File template & helper (header, footer, functions, auth_check)
├── uploads/            # Direktori upload (contoh: cover buku)
│   └── covers/
├── index.php           # Halaman utama (Katalog Buku)
├── login.php           # Halaman Login
├── register.php        # Halaman Registrasi
├── logout.php          # Proses Logout
└── README.md           # Dokumentasi Proyek
```
