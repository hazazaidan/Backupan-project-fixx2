<?php

require_once BASE_PATH . '/app/core/Controller.php';
require_once BASE_PATH . '/app/core/Database.php';

class App {

    protected $controller = 'AuthController';
    protected $method     = 'loginPage';
    protected $params     = [];

    public function __construct() {

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $url = $this->parseURL();

        $fullRouteMap = [

            // ── AUTH ──────────────────────────────────────────────
            'login'                   => ['AuthController', 'loginPage'],
            'auth/login'              => ['AuthController', 'loginPage'],
            'auth/loginPage'          => ['AuthController', 'loginPage'],
            'admin/login'             => ['AuthController', 'loginPage'],

            'login/process'           => ['AuthController', 'doLogin'],
            'auth/doLogin'            => ['AuthController', 'doLogin'],
            'admin/doLogin'           => ['AuthController', 'doLogin'],

            'logout'                  => ['AuthController', 'logout'],
            'auth/logout'             => ['AuthController', 'logout'],
            'admin/logout'            => ['AuthController', 'logout'],

            // ── REGISTER ──────────────────────────────────────────
            'register'                => ['AuthController', 'registerPage'],
            'auth/register'           => ['AuthController', 'registerPage'],
            'auth/registerPage'       => ['AuthController', 'registerPage'],
            'auth/doRegister'         => ['AuthController', 'doRegister'],

            // ── LUPA PASSWORD ─────────────────────────────────────
            'lupa-password'           => ['AuthController', 'lupaPassword'],
            'auth/lupaPassword'       => ['AuthController', 'lupaPassword'],
            'lupa-password/proses'    => ['AuthController', 'prosesLupaPassword'],
            'auth/prosesLupaPassword' => ['AuthController', 'prosesLupaPassword'],

            // ── ADMIN ─────────────────────────────────────────────
            'admin/dashboard'         => ['AdminController', 'dashboard'],
            'admin/siswa'             => ['AdminController', 'siswa'],
            'admin/siswa/store'       => ['AdminController', 'storeSiswa'],   // ← sudah ada
            'admin/siswa/update'      => ['AdminController', 'updateSiswa'],  // ← sudah ada
            'admin/siswa/destroy'     => ['AdminController', 'destroySiswa'], // ← sudah ada
            'admin/guru'              => ['AdminController', 'guru'],
            'admin/guru/store'        => ['AdminController', 'storeGuru'],    // ← sudah ada
            'admin/guru/update'       => ['AdminController', 'updateGuru'],   // ← sudah ada
            'admin/guru/destroy'      => ['AdminController', 'destroyGuru'],  // ← sudah ada
            'admin/kelas'             => ['AdminController', 'kelas'],
            'admin/kelas/store'       => ['AdminController', 'storeKelas'],   // ← FIX: ini yang kurang!
            'admin/kelas/update'      => ['AdminController', 'updateKelas'],  // ← FIX: ini yang kurang!
            'admin/kelas/destroy'     => ['AdminController', 'destroyKelas'], // ← FIX: ini yang kurang!
            'admin/laporan'           => ['AdminController', 'laporan'],
            'admin/pengaturan'        => ['AdminController', 'pengaturan'],
            'admin/registrasi'        => ['AdminController', 'registrasi'],
            'admin/approveRegistrasi' => ['AdminController', 'approveRegistrasi'],
            'admin/rejectRegistrasi'  => ['AdminController', 'rejectRegistrasi'],

            // ── KELUHAN ───────────────────────────────────────────
            'admin/keluhan'              => ['KeluhanController', 'index'],
            'admin/keluhan/updateStatus' => ['KeluhanController', 'updateStatus'],
            'admin/keluhan/chat'         => ['KeluhanController', 'chat'],
            'admin/keluhan/sendMessage'  => ['KeluhanController', 'sendMessage'],
            'admin/keluhan/getMessages'  => ['KeluhanController', 'getMessages'],
            'admin/keluhan/downloadFile' => ['KeluhanController', 'downloadFile'],

            // ── GURU DASHBOARD ────────────────────────────────────
            'dashboard'               => ['DashboardController', 'index'],
            'guru/dashboard'          => ['DashboardController', 'index'],

            // ── KELAS (BARU) ──────────────────────────────────────
            'kelas'                   => ['AbsensiController', 'kelasPage'],
            'guru/kelas'              => ['AbsensiController', 'kelasPage'],

            // ── ABSENSI (BARU) ────────────────────────────────────
            'absensi'                 => ['AbsensiController', 'absensiPage'],
            'guru/absensi'            => ['AbsensiController', 'absensiPage'],
            'absensi/submit'          => ['AbsensiController', 'submitAbsensi'],
            'guru/absensi/submit'     => ['AbsensiController', 'submitAbsensi'],

            // ── SCAN ──────────────────────────────────────────────
            'scan'                    => ['AbsensiController', 'scanPage'],
            'guru/scan'               => ['AbsensiController', 'scanPage'],
            'absensi/scanPage'        => ['AbsensiController', 'scanPage'],
            'scan/api'                => ['AbsensiController', 'apiAbsensi'],
            'absensi/apiAbsensi'      => ['AbsensiController', 'apiAbsensi'],

            // ── RIWAYAT ───────────────────────────────────────────
            'riwayat'                 => ['AbsensiController', 'riwayat'],
            'guru/riwayat'            => ['AbsensiController', 'riwayat'],
            'absensi/riwayat'         => ['AbsensiController', 'riwayat'],

            // ── REKAP ─────────────────────────────────────────────
            'rekap'                   => ['AbsensiController', 'rekap'],
            'guru/rekap'              => ['AbsensiController', 'rekap'],
            'absensi/rekap'           => ['AbsensiController', 'rekap'],

            // ── MONITORING ────────────────────────────────────────
            'monitoring'              => ['MonitoringController', 'index'],
            'guru/monitoring'         => ['MonitoringController', 'index'],

            // ── PENGATURAN ────────────────────────────────────────
            'pengaturan'              => ['PengaturanController', 'index'],
            'guru/pengaturan'         => ['PengaturanController', 'index'],

            // ── PLACEHOLDER ───────────────────────────────────────
            'guru/jadwal'             => ['DashboardController', 'index'],
            'guru/notifikasi'         => ['DashboardController', 'index'],
        ];

        $urlString = isset($_GET['url']) && $_GET['url'] !== ''
            ? trim($_GET['url'], '/')
            : 'login';

        if (isset($fullRouteMap[$urlString])) {
            [$controllerName, $methodName] = $fullRouteMap[$urlString];
        } else {
            $segment = strtolower($url[0] ?? '');

            $segmentMap = [
                'login'      => ['AuthController',      'loginPage'],
                'auth'       => ['AuthController',      'loginPage'],
                'register'   => ['AuthController',      'registerPage'],
                'admin'      => ['AuthController',      'loginPage'],
                'dashboard'  => ['DashboardController', 'index'],
                'scan'       => ['AbsensiController',   'scanPage'],
                'riwayat'    => ['AbsensiController',   'riwayat'],
                'rekap'      => ['AbsensiController',   'rekap'],
                'absensi'    => ['AbsensiController',   'absensiPage'],
                'kelas'      => ['AbsensiController',   'kelasPage'],
                'monitoring' => ['MonitoringController','index'],
                'pengaturan' => ['PengaturanController','index'],
            ];

            [$controllerName, $methodName] =
                $segmentMap[$segment]
                ?? ['AuthController', 'loginPage'];
        }

        $controllerPath = BASE_PATH . '/app/controllers/' . $controllerName . '.php';

        if (!file_exists($controllerPath)) {
            http_response_code(404);
            die("<h2 style='font-family:sans-serif'>Controller tidak ditemukan: <span style='color:red'>{$controllerName}</span></h2>");
        }

        require_once $controllerPath;

        $controllerObj = new $controllerName;

        if (!method_exists($controllerObj, $methodName)) {
            http_response_code(404);
            die("<h2 style='font-family:sans-serif'>Method tidak ditemukan: <span style='color:red'>{$methodName}</span></h2>");
        }

        call_user_func_array([$controllerObj, $methodName], $this->params);
    }

    public function parseURL(): array {
        if (isset($_GET['url']) && $_GET['url'] !== '') {
            $url = rtrim($_GET['url'], '/');
            $url = filter_var($url, FILTER_SANITIZE_URL);
            return explode('/', $url);
        }
        return [];
    }
}