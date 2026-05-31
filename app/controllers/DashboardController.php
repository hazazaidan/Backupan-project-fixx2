<?php
require_once BASE_PATH . '/app/core/Controller.php';
require_once BASE_PATH . '/app/models/Absensi.php';
require_once BASE_PATH . '/app/models/Siswa.php';
require_once BASE_PATH . '/app/models/Kelas.php';

class DashboardController extends Controller {

    public function index(): void {

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user'])) {
            header('Location: ' . BASE_URL . '?url=login');
            exit;
        }

        if (method_exists($this, 'requireRole')) {
            $this->requireRole('guru');
        }

        $absensiModel = new Absensi();
        $siswaModel   = new Siswa();
        $kelasModel   = new Kelas();

        $today         = date('Y-m-d');
        $guruId        = $_SESSION['user']['id'] ?? null;

        $hariMap = [
            'Monday'    => 'Senin',
            'Tuesday'   => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday'  => 'Kamis',
            'Friday'    => 'Jumat',
            'Saturday'  => 'Sabtu',
            'Sunday'    => 'Minggu',
        ];
        $hariIndonesia = $hariMap[date('l')] ?? date('l');

        $jadwalHariIni = Database::request("GET", "jadwal?guru_id=eq." . $guruId . "&hari=eq." . urlencode($hariIndonesia) . "&order=jam_mulai.asc");
        if (empty($jadwalHariIni) || isset($jadwalHariIni['error'])) $jadwalHariIni = [];

        $totalSiswa   = $siswaModel->countAll();
        $hadirHariIni = $absensiModel->countByStatusAndDate('Hadir', $today);
        $izinSakit    = $absensiModel->countIzinSakitToday();
        $belumAbsen   = max(0, $totalSiswa - $hadirHariIni - $izinSakit);

        $kelasList    = $kelasModel->getWithKehadiran($today);
        $chart7Hari   = $absensiModel->getLast7Days();
        $aktivitas    = $absensiModel->getRecent(5);

        $this->view('guru/dashboard', [
            'title'         => 'Dashboard Guru',
            'user'          => $_SESSION['user'] ?? null,
            'totalSiswa'    => $totalSiswa,
            'hadirHariIni'  => $hadirHariIni,
            'izinSakit'     => $izinSakit,
            'belumAbsen'    => $belumAbsen,
            'kelasList'     => $kelasList,
            'chart7Hari'    => $chart7Hari,
            'aktivitas'     => $aktivitas,
            'jadwalHariIni' => $jadwalHariIni,
        ]);
    }
}