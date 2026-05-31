<?php
require_once BASE_PATH . '/app/models/Absensi.php';
require_once BASE_PATH . '/app/models/Siswa.php';
require_once BASE_PATH . '/app/models/Kelas.php';

class AbsensiController extends Controller {

    /* ── Halaman pilih kelas & mata pelajaran ─────────────── */
    public function kelasPage(): void {
        $this->requireRole('guru');

        $kelasModel = new Kelas();
        $guruId     = $_SESSION['user']['id'] ?? '';

        // AJAX: load mapel berdasarkan kelas yang dipilih
        if (isset($_GET['action']) && $_GET['action'] === 'mapel') {
            $kelas = trim($_GET['kelas'] ?? '');
            $mapel = $kelasModel->getMapelByGuruKelas($guruId, $kelas);
            $this->json($mapel);
            return;
        }

        // Ambil kelas yang diajar guru ini dari tabel jadwal
        $kelasList = $kelasModel->getByGuru($guruId);

        $this->view('guru/kelas', [
            'title'     => 'Pilih Kelas',
            'user'      => $_SESSION['user'],
            'kelasList' => $kelasList,
        ]);
    }

    /* ── Halaman absensi siswa ────────────────────────────── */
    public function absensiPage(): void {
        $this->requireRole('guru');

        $kelas    = trim($_GET['kelas']     ?? '');
        $jadwalId = trim($_GET['jadwal_id'] ?? '');
        $mapel    = trim($_GET['mapel']     ?? '');

        if (empty($kelas) || empty($jadwalId)) {
            header('Location: ?url=guru/kelas');
            exit;
        }

        $siswaModel = new Siswa();
        $siswaList  = $siswaModel->getByKelas($kelas);

        $this->view('guru/absensi', [
            'title'     => 'Absensi – ' . $kelas,
            'user'      => $_SESSION['user'],
            'kelas'     => $kelas,
            'jadwal_id' => $jadwalId,
            'mapel'     => $mapel,
            'siswaList' => $siswaList,
        ]);
    }

    /* ── Submit absensi (POST bulk) ───────────────────────── */
    public function submitAbsensi(): void {
        $this->requireRole('guru');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Method not allowed'], 405);
            return;
        }

        date_default_timezone_set('Asia/Jakarta');

        $jadwalId = trim($_POST['jadwal_id'] ?? '');
        $kelas    = trim($_POST['kelas']     ?? '');
        $mapel    = trim($_POST['mapel']     ?? '');
        $tanggal  = date('Y-m-d');
        $statuses = $_POST['status'] ?? [];

        if (empty($jadwalId) || empty($kelas) || empty($statuses)) {
            $this->json(['success' => false, 'message' => 'Data tidak lengkap']);
            return;
        }

        $absensiModel = new Absensi();

        if ($absensiModel->sudahAbsenJadwal($jadwalId, $tanggal)) {
            $this->json(['success' => false, 'message' => 'Absensi untuk sesi ini sudah pernah disubmit hari ini']);
            return;
        }

        $rows = [];
        foreach ($statuses as $siswaId => $status) {
            // ── PERUBAHAN: ucfirst agar tersimpan "Hadir"/"Izin"/"Alpha" di Supabase ──
            $statusValid = in_array(strtolower($status), ['hadir', 'izin', 'alpha'])
                           ? ucfirst(strtolower($status))
                           : 'Alpha';
            $rows[] = [
                'siswa_id'    => $siswaId,
                'jadwal_id'   => $jadwalId,
                'tanggal'     => $tanggal,
                'waktu_masuk' => date('H:i:s'),
                'status'      => $statusValid,
            ];
        }

        $ok = $absensiModel->bulkCreate($rows);

        if ($ok) {
            $this->json([
                'success' => true,
                'message' => 'Absensi berhasil disimpan',
                'kelas'   => $kelas,
                'mapel'   => $mapel,
                'tanggal' => date('d M Y'),
                'total'   => count($rows),
            ]);
        } else {
            $this->json(['success' => false, 'message' => 'Gagal menyimpan absensi']);
        }
    }

    /* ── Halaman Scan QR (lama, dipertahankan) ───────────────────── */
    public function scanPage(): void {
        $this->requireRole('guru');

        $absensiModel = new Absensi();
        $scanHariIni  = $absensiModel->getByDate(date('Y-m-d'), 10);

        $this->view('guru/scan', [
            'title'       => 'Scan QR – Absensi',
            'user'        => $_SESSION['user'],
            'scanHariIni' => $scanHariIni,
        ]);
    }

    /* ── API: proses scan QR (AJAX POST) ────────────── */
    public function apiAbsensi(): void {
        if (!$this->isLoggedIn()) {
            $this->json(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        date_default_timezone_set('Asia/Jakarta');

        $qrCode = trim($_POST['qr_code'] ?? '');
        if (empty($qrCode)) {
            $this->json(['success' => false, 'message' => 'QR Code kosong']);
            return;
        }

        $siswaModel   = new Siswa();
        $absensiModel = new Absensi();

        $siswa = $siswaModel->findByQR($qrCode);
        if (!$siswa) {
            $this->json(['success' => false, 'message' => 'Siswa tidak ditemukan: ' . $qrCode]);
            return;
        }

        $sudahAbsen = $absensiModel->sudahAbsenHariIni($siswa['id'], date('Y-m-d'));
        if ($sudahAbsen) {
            $this->json([
                'success' => false,
                'message' => ($siswa['nama'] ?? 'Siswa') . ' sudah absen hari ini',
                'siswa'   => $siswa,
            ]);
            return;
        }

        $waktuMasuk  = date('H:i:s');
        $waktuTampil = substr($waktuMasuk, 0, 5);
        $status      = ($waktuMasuk > '07:30:00') ? 'alpha' : 'hadir';

        $absensiModel->create([
            'siswa_id'    => $siswa['id'],
            'tanggal'     => date('Y-m-d'),
            'waktu_masuk' => $waktuMasuk,
            'status'      => $status,
        ]);

        $this->json([
            'success' => true,
            'message' => ($status === 'alpha')
                ? ($siswa['nama'] ?? '') . ' dianggap Alpha — melewati batas waktu (07:30)'
                : ($siswa['nama'] ?? '') . ' berhasil absen Hadir',
            'siswa'  => array_merge($siswa, [
                'nama_kelas' => $siswa['kelas'] ?? '–',
            ]),
            'waktu'   => $waktuTampil,
            'tanggal' => date('d M Y'),
            'status'  => $status,
        ]);
    }

    /* ── Halaman Riwayat Absensi ─────────────────────── */
    public function riwayat(): void {
        $this->requireRole('guru');

        $absensiModel = new Absensi();
        $kelasModel   = new Kelas();

        $tanggal = $_GET['tanggal'] ?? date('Y-m-d');
        $kelas   = $_GET['kelas']   ?? '';
        $status  = $_GET['status']  ?? '';
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 10;

        $data      = $absensiModel->getFiltered($tanggal, $kelas, $status, $page, $perPage);
        $total     = $absensiModel->countFiltered($tanggal, $kelas, $status);
        $kelasList = $kelasModel->getAll();

        $this->view('guru/riwayat', [
            'title'     => 'Riwayat Absensi',
            'user'      => $_SESSION['user'],
            'data'      => $data,
            'total'     => $total,
            'page'      => $page,
            'perPage'   => $perPage,
            'tanggal'   => $tanggal,
            'kelas'     => $kelas,
            'status'    => $status,
            'kelasList' => $kelasList,
        ]);
    }

    /* ── Halaman Rekap Kelas ─────────────────────────── */
    public function rekap(): void {
        $this->requireRole('guru');

        $kelasModel   = new Kelas();
        $absensiModel = new Absensi();

        $bulan     = $_GET['bulan'] ?? date('Y-m');
        $kelasList = $kelasModel->getRekapBulanan($bulan);
        $summary   = $absensiModel->getSummaryBulanan($bulan);

        $this->view('guru/rekap', [
            'title'     => 'Rekap Kelas',
            'user'      => $_SESSION['user'],
            'kelasList' => $kelasList,
            'summary'   => $summary,
            'bulan'     => $bulan,
        ]);
    }

    /* ── API: riwayat (AJAX) ─────────────────────────── */
    public function apiRiwayat(): void {
        if (!$this->isLoggedIn()) {
            $this->json(['success' => false], 401);
            return;
        }

        $absensiModel = new Absensi();
        $tanggal = $_GET['tanggal'] ?? date('Y-m-d');
        $data    = $absensiModel->getByDate($tanggal);
        $this->json(['success' => true, 'data' => $data]);
    }

    /* ── POST scan (form fallback) ───────────────────── */
    public function doScan(): void {
        $this->apiAbsensi();
    }
}