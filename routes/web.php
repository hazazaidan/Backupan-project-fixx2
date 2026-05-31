<?php

$url = $_GET['url'] ?? 'login';

switch ($url) {

    // ── AUTH ───────────────────────────────────────────────────────
    case 'login':
    case 'admin/login':
    case 'auth/loginPage':
        require_once BASE_PATH . '/app/controllers/AuthController.php';
        (new AuthController())->loginPage();
        break;

    case 'login/process':
    case 'auth/doLogin':
    case 'admin/doLogin':
        require_once BASE_PATH . '/app/controllers/AuthController.php';
        (new AuthController())->doLogin();
        break;

    case 'logout':
    case 'auth/logout':
    case 'admin/logout':
        require_once BASE_PATH . '/app/controllers/AuthController.php';
        (new AuthController())->logout();
        break;

    case 'lupa-password':
    case 'auth/lupaPassword':
        require_once BASE_PATH . '/app/controllers/AuthController.php';
        (new AuthController())->lupaPassword();
        break;

    case 'lupa-password/proses':
    case 'auth/prosesLupaPassword':
        require_once BASE_PATH . '/app/controllers/AuthController.php';
        (new AuthController())->prosesLupaPassword();
        break;

    case 'register':
    case 'auth/registerPage':
        require_once BASE_PATH . '/app/controllers/AuthController.php';
        (new AuthController())->registerPage();
        break;

    case 'register/proses':
    case 'auth/doRegister':
        require_once BASE_PATH . '/app/controllers/AuthController.php';
        (new AuthController())->doRegister();
        break;

    // ── GURU ───────────────────────────────────────────────────────
    case 'guru/dashboard':
    case 'dashboard':
        require_once BASE_PATH . '/app/controllers/DashboardController.php';
        (new DashboardController())->index();
        break;

    case 'guru/kelas':
    case 'kelas':
        require_once BASE_PATH . '/app/controllers/AbsensiController.php';
        (new AbsensiController())->kelasPage();
        break;

    case 'guru/absensi':
    case 'absensi':
        require_once BASE_PATH . '/app/controllers/AbsensiController.php';
        (new AbsensiController())->absensiPage();
        break;

    case 'guru/absensi/submit':
    case 'absensi/submit':
        require_once BASE_PATH . '/app/controllers/AbsensiController.php';
        (new AbsensiController())->submitAbsensi();
        break;

    case 'scan':
    case 'absensi/scanPage':
    case 'guru/scan':
        require_once BASE_PATH . '/app/controllers/AbsensiController.php';
        (new AbsensiController())->scanPage();
        break;

    case 'scan/api':
    case 'absensi/apiAbsensi':
        require_once BASE_PATH . '/app/controllers/AbsensiController.php';
        (new AbsensiController())->apiAbsensi();
        break;

    case 'riwayat':
    case 'absensi/riwayat':
    case 'guru/riwayat':
        require_once BASE_PATH . '/app/controllers/AbsensiController.php';
        (new AbsensiController())->riwayat();
        break;

    case 'rekap':
    case 'absensi/rekap':
    case 'guru/rekap':
        require_once BASE_PATH . '/app/controllers/AbsensiController.php';
        (new AbsensiController())->rekap();
        break;

    case 'monitoring':
    case 'guru/monitoring':
        require_once BASE_PATH . '/app/controllers/MonitoringController.php';
        (new MonitoringController())->index();
        break;

    case 'pengaturan':
    case 'guru/pengaturan':
        require_once BASE_PATH . '/app/controllers/PengaturanController.php';
        (new PengaturanController())->index();
        break;

    case 'guru/jadwal':
    case 'guru/notifikasi':
        require_once BASE_PATH . '/app/controllers/DashboardController.php';
        (new DashboardController())->index();
        break;

    // ── ADMIN ──────────────────────────────────────────────────────
    case 'admin/dashboard':
        require_once BASE_PATH . '/app/controllers/AdminController.php';
        (new AdminController())->dashboard();
        break;

    case 'admin/siswa':
        require_once BASE_PATH . '/app/controllers/AdminController.php';
        (new AdminController())->siswa();
        break;

    // ── CRUD siswa ─────────────────────────────────────────────────
    case 'admin/siswa/store':
        require_once BASE_PATH . '/app/controllers/AdminController.php';
        (new AdminController())->storeSiswa();
        break;

    case 'admin/siswa/update':
        require_once BASE_PATH . '/app/controllers/AdminController.php';
        (new AdminController())->updateSiswa();
        break;

    case 'admin/siswa/destroy':
        require_once BASE_PATH . '/app/controllers/AdminController.php';
        (new AdminController())->destroySiswa();
        break;

    case 'admin/guru':
        require_once BASE_PATH . '/app/controllers/AdminController.php';
        (new AdminController())->guru();
        break;

    // ── CRUD guru ──────────────────────────────────────────────────
    case 'admin/guru/store':
        require_once BASE_PATH . '/app/controllers/AdminController.php';
        (new AdminController())->storeGuru();
        break;

    case 'admin/guru/update':
        require_once BASE_PATH . '/app/controllers/AdminController.php';
        (new AdminController())->updateGuru();
        break;

    case 'admin/guru/destroy':
        require_once BASE_PATH . '/app/controllers/AdminController.php';
        (new AdminController())->destroyGuru();
        break;

    case 'admin/kelas':
        require_once BASE_PATH . '/app/controllers/AdminController.php';
        (new AdminController())->kelas();
        break;

    // ── CRUD kelas ─────────────────────────────────────────────────
    case 'admin/kelas/store':
        require_once BASE_PATH . '/app/controllers/AdminController.php';
        (new AdminController())->storeKelas();
        break;

    case 'admin/kelas/update':
        require_once BASE_PATH . '/app/controllers/AdminController.php';
        (new AdminController())->updateKelas();
        break;

    case 'admin/kelas/destroy':
        require_once BASE_PATH . '/app/controllers/AdminController.php';
        (new AdminController())->destroyKelas();
        break;

    case 'admin/laporan':
        require_once BASE_PATH . '/app/controllers/AdminController.php';
        (new AdminController())->laporan();
        break;

    case 'admin/pengaturan':
        require_once BASE_PATH . '/app/controllers/AdminController.php';
        (new AdminController())->pengaturan();
        break;

    case 'admin/registrasi':
        require_once BASE_PATH . '/app/controllers/AdminController.php';
        (new AdminController())->registrasi();
        break;

    case 'admin/approveRegistrasi':
        require_once BASE_PATH . '/app/controllers/AdminController.php';
        (new AdminController())->approveRegistrasi();
        break;

    case 'admin/rejectRegistrasi':
        require_once BASE_PATH . '/app/controllers/AdminController.php';
        (new AdminController())->rejectRegistrasi();
        break;

    // ── 404 ────────────────────────────────────────────────────────
    default:
        http_response_code(404);
        echo "404 – Halaman tidak ditemukan: <code>" . htmlspecialchars($url) . "</code>";
        break;
}