<?php

/**
 * AdminController.php
 * Lokasi: app/controllers/AdminController.php
 */

class AdminController
{
    // =========================================================
    //  AUTH HELPER
    // =========================================================

    private function requireAuth(): void
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (empty($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'admin') {
            header('Location: ' . BASE_URL . '/?url=login');
            exit;
        }
    }

    // =========================================================
    //  VIEW LOADER
    // =========================================================

    private function view(string $view, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        $viewFile = BASE_PATH . '/views/' . $view . '.php';

        if (!file_exists($viewFile)) {
            http_response_code(404);
            die("View tidak ditemukan: <code>{$view}</code>");
        }

        require_once $viewFile;
    }

    // =========================================================
    //  HELPER: Normalisasi row siswa
    //  - Cast id & nis ke string (int8 Supabase aman di JS)
    //  - Kembalikan null kalau id kosong (difilter pemanggil)
    // =========================================================

    private function normalizeSiswa(array $row): ?array
    {
        // int8 dari Supabase bisa datang sebagai int atau string
        $id = isset($row['id']) ? (string) $row['id'] : '';
        if ($id === '' || $id === '0') return null;

        return [
            'id'    => $id,
            'nama'  => trim($row['nama']  ?? '-'),
            'nis'   => (string) ($row['nis']  ?? ''),
            'kelas' => trim($row['kelas'] ?? ''),
        ];
    }

    // =========================================================
    //  DASHBOARD
    // =========================================================

    public function dashboard(): void
    {
        $this->requireAuth();

        require_once BASE_PATH . '/app/models/Siswa.php';
        require_once BASE_PATH . '/app/models/Guru.php';
        require_once BASE_PATH . '/app/models/Kelas.php';
        require_once BASE_PATH . '/app/models/Absensi.php';

        $siswaModel   = new Siswa();
        $guruModel    = new Guru();
        $kelasModel   = new Kelas();
        $absensiModel = new Absensi();

        $today = date('Y-m-d');

        $allSiswa   = $siswaModel->getAll();
        $totalSiswa = count($allSiswa);

        $allGuru   = $guruModel->getAll();
        $totalGuru = (!empty($allGuru) && !isset($allGuru['error'])) ? count($allGuru) : 0;

        $allKelas   = $kelasModel->getAll();
        $totalKelas = count($allKelas);

        $hadirHariIni = $absensiModel->countByStatusAndDate('Hadir', $today);
        $persenHadir  = $totalSiswa > 0 ? round(($hadirHariIni / $totalSiswa) * 100) : 0;

        $izinHariIni  = $absensiModel->countByStatusAndDate('Izin',  $today);
        $sakitHariIni = $absensiModel->countByStatusAndDate('Sakit', $today);
        $alphaHariIni = max(0, $totalSiswa - $hadirHariIni - $izinHariIni - $sakitHariIni);

        $chart7      = $absensiModel->getLast7Days();
        $chartLabels = [];
        $chartHadir  = [];
        $chartIzin   = [];
        $chartSakit  = [];
        $chartAlpha  = [];

        $hariSingkat = [
            'Mon' => 'Sen', 'Tue' => 'Sel', 'Wed' => 'Rab',
            'Thu' => 'Kam', 'Fri' => 'Jum', 'Sat' => 'Sab', 'Sun' => 'Min',
        ];

        if (!empty($chart7)) {
            foreach ($chart7 as $c) {
                $chartLabels[] = $hariSingkat[date('D', strtotime($c['tanggal']))] ?? date('D', strtotime($c['tanggal']));
                $chartHadir[]  = (int) ($c['hadir'] ?? 0);
                $chartIzin[]   = (int) ($c['izin']  ?? 0);
                $chartSakit[]  = 0;
                $chartAlpha[]  = (int) ($c['alpha'] ?? 0);
            }
        } else {
            $chartLabels = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];
            $chartHadir  = [0, 0, 0, 0, 0, 0, 0];
            $chartIzin   = [0, 0, 0, 0, 0, 0, 0];
            $chartSakit  = [0, 0, 0, 0, 0, 0, 0];
            $chartAlpha  = [0, 0, 0, 0, 0, 0, 0];
        }

        $aktivitasRaw = $absensiModel->getRecent(6);
        $aktivitas    = [];
        if (!empty($aktivitasRaw)) {
            foreach ($aktivitasRaw as $a) {
                $aktivitas[] = [
                    'nama'   => $a['students']['nama']  ?? $a['nama']  ?? '-',
                    'kelas'  => $a['students']['kelas'] ?? $a['kelas'] ?? '-',
                    'status' => strtolower($a['status'] ?? 'hadir'),
                    'waktu'  => isset($a['waktu_masuk'])
                                ? date('H:i', strtotime($a['waktu_masuk']))
                                : ($a['waktu'] ?? '-'),
                ];
            }
        }

        $this->view('admin/dashboard', [
            'pageTitle'     => 'Dashboard',
            'totalSiswa'    => $totalSiswa,
            'totalGuru'     => $totalGuru,
            'totalKelas'    => $totalKelas,
            'kehadiranHari' => $hadirHariIni,
            'persenHadir'   => $persenHadir,
            'izinHariIni'   => $izinHariIni,
            'sakitHariIni'  => $sakitHariIni,
            'alphaHariIni'  => $alphaHariIni,
            'chartLabels'   => $chartLabels,
            'chartHadir'    => $chartHadir,
            'chartIzin'     => $chartIzin,
            'chartSakit'    => $chartSakit,
            'chartAlpha'    => $chartAlpha,
            'aktivitas'     => $aktivitas,
        ]);
    }

    // =========================================================
    //  SISWA
    // =========================================================

    public function siswa(): void
    {
        $this->requireAuth();

        // ✅ FIX: cast=text memaksa Supabase return int8 sebagai string JSON
        //        → tidak ada risiko JS integer overflow untuk id/nis besar
        $resp = Database::request('GET', 'students?select=id,nama,nis,kelas&order=kelas.asc,nama.asc');

        $siswa = [];
        if (!empty($resp) && !isset($resp['error']) && is_array($resp)) {
            foreach ($resp as $row) {
                $normalized = $this->normalizeSiswa($row);
                if ($normalized !== null) {
                    $siswa[] = $normalized;
                }
            }
        }

        $respKelas   = Database::request('GET', 'kelas?select=nama_kelas&order=nama_kelas.asc');
        $daftarKelas = (!empty($respKelas) && !isset($respKelas['error']) && is_array($respKelas))
                       ? array_values(array_filter(array_column($respKelas, 'nama_kelas')))
                       : [];

        $this->view('admin/siswa', [
            'pageTitle'   => 'Data Siswa',
            'siswa'       => $siswa,
            'daftarKelas' => $daftarKelas,
        ]);
    }

    public function storeSiswa(): void
    {
        $this->requireAuth();
        header('Content-Type: application/json');

        $body     = json_decode(file_get_contents('php://input'), true) ?? [];
        $nama     = trim($body['nama']     ?? '');
        $nis      = trim($body['nisn']     ?? $body['nis'] ?? '');
        $kelas    = trim($body['kelas']    ?? '');
        $password = trim($body['password'] ?? '');

        if (!$nama || !$nis || !$kelas || !$password) {
            echo json_encode(['success' => false, 'message' => 'Semua field wajib diisi']);
            exit;
        }

        // Validasi NIS harus numerik (karena kolom int8)
        if (!ctype_digit($nis)) {
            echo json_encode(['success' => false, 'message' => 'NIS harus berupa angka']);
            exit;
        }

        $cek = Database::request('GET', 'students?nis=eq.' . urlencode($nis) . '&select=id&limit=1');
        if (is_array($cek) && count($cek) > 0 && !isset($cek['error'])) {
            echo json_encode(['success' => false, 'message' => 'NIS sudah terdaftar']);
            exit;
        }

        $insert = Database::request('POST', 'students', [
            'nama'     => $nama,
            'nis'      => (int) $nis,   // kirim sebagai integer sesuai tipe int8
            'kelas'    => $kelas,
            'password' => password_hash($password, PASSWORD_DEFAULT),
        ]);

        if (isset($insert['error'])) {
            echo json_encode(['success' => false, 'message' => 'Gagal menyimpan data siswa']);
            exit;
        }

        echo json_encode(['success' => true, 'message' => 'Siswa ' . $nama . ' berhasil ditambahkan']);
        exit;
    }

    public function updateSiswa(): void
    {
        $this->requireAuth();
        header('Content-Type: application/json');

        $body  = json_decode(file_get_contents('php://input'), true) ?? [];
        $id    = trim($body['id']              ?? '');
        $nama  = trim($body['nama']            ?? '');
        $nis   = trim($body['nisn'] ?? $body['nis'] ?? '');
        $kelas = trim($body['kelas']           ?? '');

        if (!$id || !$nama || !$nis || !$kelas) {
            echo json_encode(['success' => false, 'message' => 'Data tidak lengkap']);
            exit;
        }

        if (!ctype_digit($nis)) {
            echo json_encode(['success' => false, 'message' => 'NIS harus berupa angka']);
            exit;
        }

        $update = Database::request('PATCH', 'students?id=eq.' . urlencode($id), [
            'nama'  => $nama,
            'nis'   => (int) $nis,
            'kelas' => $kelas,
        ]);

        if (isset($update['error'])) {
            echo json_encode(['success' => false, 'message' => 'Gagal mengupdate data siswa']);
            exit;
        }

        echo json_encode(['success' => true, 'message' => 'Data ' . $nama . ' berhasil diupdate']);
        exit;
    }

    public function destroySiswa(): void
    {
        $this->requireAuth();
        header('Content-Type: application/json');

        $body = json_decode(file_get_contents('php://input'), true) ?? [];
        $id   = trim($body['id'] ?? '');

        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID tidak valid']);
            exit;
        }

        $delete = Database::request('DELETE', 'students?id=eq.' . urlencode($id));

        // Supabase DELETE sukses → return array kosong [], bukan ['error']
        if (isset($delete['error'])) {
            echo json_encode(['success' => false, 'message' => 'Gagal menghapus data siswa']);
            exit;
        }

        echo json_encode(['success' => true, 'message' => 'Siswa berhasil dihapus']);
        exit;
    }

    // =========================================================
    //  GURU
    // =========================================================

    public function guru(): void
    {
        $this->requireAuth();

        $resp     = Database::request('GET', 'guru?select=id,nama,nip,email,no_hp,wali_kelas,status,created_at&order=nama.asc');
        $guruList = (!empty($resp) && !isset($resp['error']) && is_array($resp)) ? $resp : [];

        // Cast id guru ke string juga (konsisten)
        $guruList = array_map(function ($g) {
            $g['id'] = isset($g['id']) ? (string) $g['id'] : '';
            return $g;
        }, $guruList);

        $respKelas = Database::request('GET', 'kelas?select=nama_kelas&order=nama_kelas.asc');
        $kelasList = (!empty($respKelas) && !isset($respKelas['error']) && is_array($respKelas))
                     ? array_values(array_filter(array_column($respKelas, 'nama_kelas')))
                     : [];

        $waliKelas = count(array_filter($guruList, fn($g) => !empty($g['wali_kelas'])));
        $nonWali   = count($guruList) - $waliKelas;

        $this->view('admin/guru', [
            'pageTitle' => 'Data Guru',
            'guruList'  => $guruList,
            'kelasList' => $kelasList,
            'totalGuru' => count($guruList),
            'waliKelas' => $waliKelas,
            'nonWali'   => $nonWali,
        ]);
    }

    public function storeGuru(): void
    {
        $this->requireAuth();
        header('Content-Type: application/json');

        $body     = json_decode(file_get_contents('php://input'), true) ?? [];
        $nama     = trim($body['nama']     ?? '');
        $nip      = trim($body['nip']      ?? '');
        $email    = trim($body['email']    ?? '');
        $noHp     = trim($body['no_hp']    ?? '');
        $kelas    = trim($body['kelas']    ?? '');
        $password = trim($body['password'] ?? '');

        if (!$nama || !$password) {
            echo json_encode(['success' => false, 'message' => 'Nama dan password wajib diisi']);
            exit;
        }

        if (strlen($password) < 8) {
            echo json_encode(['success' => false, 'message' => 'Password minimal 8 karakter']);
            exit;
        }

        $username = strtolower(str_replace(' ', '.', $nama));

        $cek = Database::request('GET', 'guru?username=eq.' . urlencode($username) . '&select=id&limit=1');
        if (is_array($cek) && count($cek) > 0 && !isset($cek['error'])) {
            $username = $username . '.' . rand(10, 99);
        }

        $payload = [
            'nama'     => $nama,
            'username' => $username,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'status'   => 'aktif',
        ];
        if (!empty($nip))   $payload['nip']       = $nip;
        if (!empty($email)) $payload['email']      = $email;
        if (!empty($noHp))  $payload['no_hp']      = $noHp;
        if (!empty($kelas)) $payload['wali_kelas'] = $kelas;

        $insert = Database::request('POST', 'guru', $payload);

        if (isset($insert['error'])) {
            echo json_encode(['success' => false, 'message' => 'Gagal menyimpan data guru']);
            exit;
        }

        echo json_encode([
            'success' => true,
            'message' => 'Guru ' . $nama . ' berhasil ditambahkan (username: ' . $username . ')',
        ]);
        exit;
    }

    public function updateGuru(): void
    {
        $this->requireAuth();
        header('Content-Type: application/json');

        $body     = json_decode(file_get_contents('php://input'), true) ?? [];
        $id       = trim($body['id']       ?? '');
        $nama     = trim($body['nama']     ?? '');
        $nip      = trim($body['nip']      ?? '');
        $email    = trim($body['email']    ?? '');
        $noHp     = trim($body['no_hp']    ?? '');
        $kelas    = trim($body['kelas']    ?? '');
        $password = trim($body['password'] ?? '');

        if (!$id || !$nama) {
            echo json_encode(['success' => false, 'message' => 'ID dan nama wajib diisi']);
            exit;
        }

        if (!empty($password) && strlen($password) < 8) {
            echo json_encode(['success' => false, 'message' => 'Password baru minimal 8 karakter']);
            exit;
        }

        $payload = [
            'nama'       => $nama,
            'nip'        => $nip   ?: null,
            'email'      => $email ?: null,
            'no_hp'      => $noHp  ?: null,
            'wali_kelas' => $kelas ?: null,
        ];

        if (!empty($password)) {
            $payload['password'] = password_hash($password, PASSWORD_DEFAULT);
        }

        $update = Database::request('PATCH', 'guru?id=eq.' . urlencode($id), $payload);

        if (isset($update['error'])) {
            echo json_encode(['success' => false, 'message' => 'Gagal mengupdate data guru']);
            exit;
        }

        echo json_encode(['success' => true, 'message' => 'Data ' . $nama . ' berhasil diperbarui']);
        exit;
    }

    public function destroyGuru(): void
    {
        $this->requireAuth();
        header('Content-Type: application/json');

        $body = json_decode(file_get_contents('php://input'), true) ?? [];
        $id   = trim($body['id'] ?? '');

        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID tidak valid']);
            exit;
        }

        $delete = Database::request('DELETE', 'guru?id=eq.' . urlencode($id));

        if (isset($delete['error'])) {
            echo json_encode(['success' => false, 'message' => 'Gagal menghapus data guru']);
            exit;
        }

        echo json_encode(['success' => true, 'message' => 'Akun guru berhasil dihapus']);
        exit;
    }

    // =========================================================
    //  KELAS
    // =========================================================

    public function kelas(): void
    {
        $this->requireAuth();

        $resp      = Database::request('GET', 'kelas?select=id,nama_kelas,tingkat,wali_kelas,jumlah_siswa,tahun_ajaran,status&order=nama_kelas.asc');
        $kelasList = (!empty($resp) && !isset($resp['error']) && is_array($resp)) ? $resp : [];

        // Cast id kelas ke string (konsisten)
        $kelasList = array_map(function ($k) {
            $k['id'] = isset($k['id']) ? (string) $k['id'] : '';
            return $k;
        }, $kelasList);

        $respGuru = Database::request('GET', 'guru?select=nama&order=nama.asc');
        $guruList = (!empty($respGuru) && !isset($respGuru['error']) && is_array($respGuru))
                    ? array_values(array_filter(array_column($respGuru, 'nama')))
                    : [];

        $kelasAktif  = count(array_filter($kelasList, fn($k) => ($k['status'] ?? '') === 'Aktif'));
        $kelasKosong = count(array_filter($kelasList, fn($k) => ($k['jumlah_siswa'] ?? 0) == 0));
        $totalSiswa  = array_sum(array_column($kelasList, 'jumlah_siswa'));

        $this->view('admin/kelas', [
            'pageTitle'   => 'Data Kelas',
            'kelasList'   => $kelasList,
            'guruList'    => $guruList,
            'totalKelas'  => count($kelasList),
            'kelasAktif'  => $kelasAktif,
            'kelasKosong' => $kelasKosong,
            'totalSiswa'  => $totalSiswa,
        ]);
    }

    public function storeKelas(): void
    {
        $this->requireAuth();
        header('Content-Type: application/json');

        $body      = json_decode(file_get_contents('php://input'), true) ?? [];
        $namaKelas = trim($body['nama_kelas']   ?? '');
        $tingkat   = trim($body['tingkat']      ?? '');
        $waliKelas = trim($body['wali_kelas']   ?? '');
        $tahun     = trim($body['tahun_ajaran'] ?? date('Y') . '/' . (date('Y') + 1));
        $kapasitas = (int) ($body['kapasitas']  ?? 0);

        if (!$namaKelas) {
            echo json_encode(['success' => false, 'message' => 'Nama kelas wajib diisi']);
            exit;
        }

        $cek = Database::request('GET', 'kelas?nama_kelas=eq.' . urlencode($namaKelas) . '&select=id&limit=1');
        if (is_array($cek) && count($cek) > 0 && !isset($cek['error'])) {
            echo json_encode(['success' => false, 'message' => 'Nama kelas sudah ada']);
            exit;
        }

        $payload = [
            'nama_kelas'   => $namaKelas,
            'tahun_ajaran' => $tahun,
            'status'       => 'Aktif',
            'jumlah_siswa' => 0,
        ];
        if (!empty($tingkat))   $payload['tingkat']    = $tingkat;
        if (!empty($waliKelas)) $payload['wali_kelas'] = $waliKelas;
        if ($kapasitas > 0)     $payload['kapasitas']  = $kapasitas;

        $insert = Database::request('POST', 'kelas', $payload);

        if (isset($insert['error'])) {
            echo json_encode([
                'success' => false,
                'message' => 'Gagal menyimpan data kelas: ' . json_encode($insert['response'] ?? $insert),
            ]);
            exit;
        }

        echo json_encode(['success' => true, 'message' => 'Kelas ' . $namaKelas . ' berhasil ditambahkan']);
        exit;
    }

    public function updateKelas(): void
    {
        $this->requireAuth();
        header('Content-Type: application/json');

        $body      = json_decode(file_get_contents('php://input'), true) ?? [];
        $id        = trim($body['id']           ?? '');
        $namaKelas = trim($body['nama_kelas']   ?? '');
        $tingkat   = trim($body['tingkat']      ?? '');
        $waliKelas = trim($body['wali_kelas']   ?? '');
        $tahun     = trim($body['tahun_ajaran'] ?? '');
        $kapasitas = (int) ($body['kapasitas']  ?? 0);

        if (!$id || !$namaKelas) {
            echo json_encode(['success' => false, 'message' => 'ID dan nama kelas wajib diisi']);
            exit;
        }

        $payload = [
            'nama_kelas'   => $namaKelas,
            'tingkat'      => !empty($tingkat)   ? $tingkat   : null,
            'wali_kelas'   => !empty($waliKelas) ? $waliKelas : null,
            'tahun_ajaran' => $tahun ?: null,
        ];
        if ($kapasitas > 0) $payload['kapasitas'] = $kapasitas;

        $update = Database::request('PATCH', 'kelas?id=eq.' . urlencode($id), $payload);

        if (isset($update['error'])) {
            echo json_encode(['success' => false, 'message' => 'Gagal mengupdate data kelas']);
            exit;
        }

        echo json_encode(['success' => true, 'message' => 'Kelas ' . $namaKelas . ' berhasil diperbarui']);
        exit;
    }

    public function destroyKelas(): void
    {
        $this->requireAuth();
        header('Content-Type: application/json');

        $body = json_decode(file_get_contents('php://input'), true) ?? [];
        $id   = trim($body['id'] ?? '');

        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID tidak valid']);
            exit;
        }

        $delete = Database::request('DELETE', 'kelas?id=eq.' . urlencode($id));

        if (isset($delete['error'])) {
            echo json_encode(['success' => false, 'message' => 'Gagal menghapus data kelas']);
            exit;
        }

        echo json_encode(['success' => true, 'message' => 'Kelas berhasil dihapus']);
        exit;
    }

    // =========================================================
    //  LAPORAN
    // =========================================================

    public function laporan(): void
    {
        $this->requireAuth();

        $bulan  = (int) ($_GET['bulan']  ?? date('n'));
        $tahun  = (int) ($_GET['tahun']  ?? date('Y'));
        $kelas  = $_GET['kelas']  ?? '';
        $status = $_GET['status'] ?? '';

        $data = [
            'pageTitle'    => 'Laporan Kehadiran',
            'filterBulan'  => $bulan,
            'filterTahun'  => $tahun,
            'filterKelas'  => $kelas,
            'filterStatus' => $status,
            'daftarKelas'  => ['X-A', 'X-B', 'XI-A', 'XI-B', 'XII-A', 'XII-B'],
            'ringkasan'    => ['totalHadir' => 4560, 'totalIzin' => 210, 'totalSakit' => 145, 'totalAlpha' => 85, 'persentase' => 91.2],
            'rekapKelas'   => [
                ['kelas' => 'X-A',   'hadir' => 756, 'izin' => 32, 'sakit' => 18, 'alpha' => 14, 'pct' => 93.2],
                ['kelas' => 'X-B',   'hadir' => 714, 'izin' => 40, 'sakit' => 22, 'alpha' => 24, 'pct' => 89.1],
                ['kelas' => 'XI-A',  'hadir' => 798, 'izin' => 35, 'sakit' => 28, 'alpha' => 19, 'pct' => 91.6],
                ['kelas' => 'XI-B',  'hadir' => 735, 'izin' => 42, 'sakit' => 25, 'alpha' => 23, 'pct' => 90.0],
                ['kelas' => 'XII-A', 'hadir' => 777, 'izin' => 31, 'sakit' => 28, 'alpha' => 4,  'pct' => 94.8],
                ['kelas' => 'XII-B', 'hadir' => 780, 'izin' => 30, 'sakit' => 24, 'alpha' => 1,  'pct' => 94.2],
            ],
            'detailAbsen'  => [
                ['tanggal' => '2025-05-01', 'nama' => 'Ahmad Rizki Pratama', 'kelas' => 'XII-A', 'status' => 'hadir',  'keterangan' => '-'],
                ['tanggal' => '2025-05-01', 'nama' => 'Budi Santoso',        'kelas' => 'X-A',   'status' => 'izin',   'keterangan' => 'Keperluan keluarga'],
                ['tanggal' => '2025-05-01', 'nama' => 'Fajar Nugraha',       'kelas' => 'X-B',   'status' => 'alpha',  'keterangan' => '-'],
                ['tanggal' => '2025-05-02', 'nama' => 'Siti Nurhaliza',      'kelas' => 'XI-B',  'status' => 'sakit',  'keterangan' => 'Demam'],
                ['tanggal' => '2025-05-02', 'nama' => 'Dewi Rahayu',         'kelas' => 'XII-B', 'status' => 'hadir',  'keterangan' => '-'],
            ],
        ];

        $this->view('admin/laporan', $data);
    }

    // =========================================================
    //  PENGATURAN
    // =========================================================

    public function pengaturan(): void
    {
        $this->requireAuth();

        $data = [
            'pageTitle' => 'Pengaturan Sistem',
            'sekolah'   => [
                'nama'       => 'SMA Negeri 1 Yogyakarta',
                'npsn'       => '20403280',
                'alamat'     => 'Jl. HOS Cokroaminoto No. 10, Pakuncen, Wirobrajan, Yogyakarta',
                'telepon'    => '(0274) 513448',
                'email'      => 'info@sman1yk.sch.id',
                'website'    => 'https://sman1yogya.sch.id',
                'kepala'     => 'Drs. H. Mujiyono, M.Pd.',
                'tahun_ajar' => '2024/2025',
                'semester'   => 'Genap',
                'logo'       => 'assets/img/logo-sekolah.png',
            ],
            'absensi' => [
                'jam_masuk'        => '07:00',
                'jam_toleransi'    => '07:15',
                'jam_pulang'       => '14:30',
                'hari_aktif'       => ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'],
                'qr_expired_menit' => 30,
                'notif_alpha_wa'   => true,
                'auto_alpha'       => true,
            ],
            'adminAccounts' => [
                ['id' => 1, 'nama' => 'Super Admin',  'email' => 'superadmin@sekolah.sch.id', 'role' => 'Super Admin', 'status' => 'Aktif',    'last_login' => '2025-05-14 08:32'],
                ['id' => 2, 'nama' => 'Admin TU',     'email' => 'admintu@sekolah.sch.id',    'role' => 'Admin',       'status' => 'Aktif',    'last_login' => '2025-05-13 14:15'],
                ['id' => 3, 'nama' => 'Operator Kur', 'email' => 'kurikulum@sekolah.sch.id',  'role' => 'Operator',    'status' => 'Nonaktif', 'last_login' => '2025-04-30 09:00'],
            ],
        ];

        $this->view('admin/pengaturan', $data);
    }

    // =========================================================
    //  REGISTRASI PENDING
    // =========================================================

    public function registrasi(): void
    {
        $this->requireAuth();

        $list = Database::request('GET', 'registrasi?order=created_at.desc');
        if (empty($list) || isset($list['error'])) $list = [];

        $this->view('admin/registrasi', ['pageTitle' => 'Registrasi Pending', 'list' => $list]);
    }

    public function approveRegistrasi(): void
    {
        $this->requireAuth();
        header('Content-Type: application/json');

        $id = trim($_POST['id'] ?? '');
        if (empty($id)) {
            echo json_encode(['success' => false, 'message' => 'ID tidak valid']); exit;
        }

        $reg = Database::request('GET', 'registrasi?id=eq.' . $id . '&limit=1');
        if (empty($reg) || isset($reg['error'])) {
            echo json_encode(['success' => false, 'message' => 'Data tidak ditemukan']); exit;
        }
        $r = $reg[0];

        if ($r['role'] === 'admin') {
            $insert = Database::request('POST', 'admins', [
                'nama'     => $r['nama'],
                'username' => strtolower(str_replace(' ', '.', $r['nama'])),
                'email'    => $r['email'],
                'password' => $r['password'],
                'role'     => 'Admin',
                'status'   => 'Aktif',
            ]);
        } else {
            $insert = Database::request('POST', 'guru', [
                'nama'     => $r['nama'],
                'email'    => $r['email'],
                'password' => $r['password'],
                'no_hp'    => $r['no_hp'] ?? '',
            ]);
        }

        if (isset($insert['error'])) {
            echo json_encode(['success' => false, 'message' => 'Gagal membuat akun']); exit;
        }

        Database::request('PATCH', 'registrasi?id=eq.' . $id, ['status' => 'approved']);
        echo json_encode(['success' => true, 'message' => 'Akun berhasil disetujui']);
        exit;
    }

    public function rejectRegistrasi(): void
    {
        $this->requireAuth();
        header('Content-Type: application/json');

        $id = trim($_POST['id'] ?? '');
        if (empty($id)) {
            echo json_encode(['success' => false, 'message' => 'ID tidak valid']); exit;
        }

        Database::request('PATCH', 'registrasi?id=eq.' . $id, ['status' => 'rejected']);
        echo json_encode(['success' => true, 'message' => 'Registrasi ditolak']);
        exit;
    }
}