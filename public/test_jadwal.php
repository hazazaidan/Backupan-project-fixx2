<?php
// Taruh file ini di public/test_jadwal.php
// Akses: localhost:8080/absensi-qr2/public/test_jadwal.php
// HAPUS setelah selesai!

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/core/Database.php';

session_start();

// Ambil 3 row pertama dari tabel jadwal
$jadwal = Database::request("GET", "jadwal?limit=3");
echo "<h3>Struktur tabel jadwal (3 row):</h3>";
echo "<pre>" . json_encode($jadwal, JSON_PRETTY_PRINT) . "</pre>";

// Ambil 1 row dari tabel guru (lihat kolom apa aja)
$guru = Database::request("GET", "guru?limit=3");
echo "<h3>Struktur tabel guru:</h3>";
echo "<pre>" . json_encode($guru, JSON_PRETTY_PRINT) . "</pre>";

// Lihat session user
echo "<h3>Session user saat ini:</h3>";
echo "<pre>" . json_encode($_SESSION['user'] ?? 'tidak ada session', JSON_PRETTY_PRINT) . "</pre>";