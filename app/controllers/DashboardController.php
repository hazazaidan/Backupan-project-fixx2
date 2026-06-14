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

        $today  = date('Y-m-d');
        $guruId = $_SESSION['user']['id'] ?? null;

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

        // [FIX] Ambil kelas yang diampu guru ini
        $kelasAmpu     = $kelasModel->getByGuru($guruId);
        $namaKelasAmpu = array_column($kelasAmpu, 'nama_kelas');

        // [FIX] Semua query pakai filter kelas yang diampu
        $totalSiswa   = $absensiModel->countSiswaByKelas($namaKelasAmpu);
        $hadirHariIni = $absensiModel->countHadirByKelas($today, $namaKelasAmpu);
        $izinSakit    = $absensiModel->countIzinSakitByKelas($today, $namaKelasAmpu);
        $belumAbsen   = max(0, $totalSiswa - $hadirHariIni - $izinSakit);

        // [FIX] Kehadiran per kelas hanya kelas yang diampu
        $allKelasList = $kelasModel->getWithKehadiran($today);
        $kelasList    = array_values(array_filter($allKelasList, fn($k) => in_array($k['nama_kelas'] ?? '', $namaKelasAmpu)));

        // [FIX] Chart dan aktivitas hanya dari kelas yang diampu
        $chart7Hari = $absensiModel->getLast7DaysByKelas($namaKelasAmpu);
        $aktivitas  = $absensiModel->getRecentByKelas($namaKelasAmpu, 5);

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