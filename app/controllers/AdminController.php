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

        $role = strtolower($_SESSION['user']['role'] ?? '');

        if (empty($_SESSION['user']) || $role !== 'admin') {

            $contentType = $_SERVER['HTTP_CONTENT_TYPE'] ?? $_SERVER['CONTENT_TYPE'] ?? '';
            $isAjax      = str_contains($contentType, 'application/json') ||
                           !empty($_SERVER['HTTP_X_REQUESTED_WITH']);

            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Session expired']);
                exit;
            }

            header('Location: ' . rtrim(BASE_URL, '/') . '/?url=login');
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
    // =========================================================

    private function normalizeSiswa(array $row): ?array
    {
        $id = isset($row['id']) ? (string) $row['id'] : '';
        if ($id === '' || $id === '0') return null;

        return [
            'id'       => $id,
            'nama'     => trim($row['nama']     ?? '-'),
            'nis'      => (string) ($row['nis'] ?? ''),
            'kelas'    => trim($row['kelas']    ?? ''),
            'qr_image' => trim($row['qr_image'] ?? ''),
        ];
    }

    // =========================================================
    //  HELPER: Cek apakah response dari Database::request sukses
    // =========================================================

    private function isSuccess($response): bool
    {
        if (is_null($response))    return true;
        if (!is_array($response))  return true;
        if (isset($response['error'])) return false;
        return true;
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

        $resp = Database::request('GET', 'students?select=id,nama,nis,kelas,qr_image&order=kelas.asc,nama.asc');

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

        $body  = json_decode(file_get_contents('php://input'), true) ?? [];
        $nama  = trim($body['nama']     ?? '');
        $nis   = trim($body['nisn']     ?? $body['nis'] ?? '');
        $kelas = trim($body['kelas']    ?? '');
        $qr    = trim($body['qr_image'] ?? '');

        if (!$nama || !$nis || !$kelas) {
            echo json_encode(['success' => false, 'message' => 'Nama, NIS, dan Kelas wajib diisi']);
            exit;
        }

        if (!ctype_digit($nis)) {
            echo json_encode(['success' => false, 'message' => 'NIS harus berupa angka']);
            exit;
        }

        $cek = Database::request('GET', 'students?nis=eq.' . urlencode($nis) . '&select=id&limit=1');
        if (is_array($cek) && count($cek) > 0 && !isset($cek['error'])) {
            echo json_encode(['success' => false, 'message' => 'NIS sudah terdaftar']);
            exit;
        }

        $payload = [
            'nama'     => $nama,
            'nis'      => (int) $nis,
            'kelas'    => $kelas,
            'password' => null,
        ];
        if (!empty($qr)) $payload['qr_image'] = $qr;

        $insert = Database::request('POST', 'students', $payload);

        if (!$this->isSuccess($insert)) {
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
        $id    = trim($body['id']       ?? '');
        $nama  = trim($body['nama']     ?? '');
        $nis   = preg_replace('/\D/', '', trim($body['nisn'] ?? $body['nis'] ?? ''));
        $kelas = trim($body['kelas']    ?? '');
        $qr    = trim($body['qr_image'] ?? '');

        if (!$id || !$nama || !$nis || !$kelas) {
            echo json_encode(['success' => false, 'message' => 'Data tidak lengkap']);
            exit;
        }

        if (!ctype_digit($nis)) {
            echo json_encode(['success' => false, 'message' => 'NIS harus berupa angka']);
            exit;
        }

        $cekNis = Database::request('GET', 'students?nis=eq.' . urlencode($nis) . '&id=neq.' . urlencode($id) . '&select=id&limit=1');
        if (is_array($cekNis) && !isset($cekNis['error']) && count($cekNis) > 0) {
            echo json_encode(['success' => false, 'message' => 'NIS ' . $nis . ' sudah digunakan siswa lain']);
            exit;
        }

        $payload = [
            'nama'  => $nama,
            'nis'   => (int) $nis,
            'kelas' => $kelas,
        ];
        if (!empty($qr)) $payload['qr_image'] = $qr;

        $update = Database::request('PATCH', 'students?id=eq.' . urlencode($id), $payload);

        if (!$this->isSuccess($update)) {
            $errMsg = 'Gagal mengupdate data siswa';
            if (isset($update['response']['message'])) {
                $errMsg .= ': ' . $update['response']['message'];
            } elseif (isset($update['response']['hint'])) {
                $errMsg .= ': ' . $update['response']['hint'];
            }
            echo json_encode([
                'success' => false,
                'message' => $errMsg,
                'debug'   => $update,
            ]);
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

        $siswaData = Database::request('GET', 'students?id=eq.' . urlencode($id) . '&select=nis&limit=1');
        $nis = null;
        if (!empty($siswaData) && !isset($siswaData['error']) && isset($siswaData[0]['nis'])) {
            $nis = $siswaData[0]['nis'];
        }

        Database::request('DELETE', 'kehadiran?siswa_id=eq.' . urlencode($id));
        if ($nis !== null) {
            Database::request('DELETE', 'reports?student_nis=eq.'        . urlencode($nis));
            Database::request('DELETE', 'parent_reports?student_nis=eq.' . urlencode($nis));
        }

        $delete = Database::request('DELETE', 'students?id=eq.' . urlencode($id));

        if (!$this->isSuccess($delete)) {
            echo json_encode(['success' => false, 'message' => 'Gagal menghapus data siswa']);
            exit;
        }

        echo json_encode(['success' => true, 'message' => 'Siswa berhasil dihapus']);
        exit;
    }

    public function updateStatusKehadiran(): void
    {
        $this->requireAuth();
        header('Content-Type: application/json');

        $body   = json_decode(file_get_contents('php://input'), true) ?? [];
        $id     = trim($body['id']     ?? '');
        $status = trim($body['status'] ?? '');

        $validStatus = ['Hadir', 'Izin', 'Alpha', 'Terlambat', 'Sakit'];

        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID tidak valid']);
            exit;
        }

        if (!in_array($status, $validStatus)) {
            echo json_encode(['success' => false, 'message' => 'Status tidak valid']);
            exit;
        }

        $update = Database::request('PATCH', 'kehadiran?id=eq.' . urlencode($id), [
            'status' => $status,
        ]);

        if (!$this->isSuccess($update)) {
            echo json_encode(['success' => false, 'message' => 'Gagal mengupdate status']);
            exit;
        }

        echo json_encode(['success' => true, 'message' => 'Status berhasil diubah ke ' . $status]);
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

        if (!$this->isSuccess($insert)) {
            echo json_encode([
                'success' => false,
                'message' => 'Gagal menyimpan data guru',
                'debug'   => $insert,
            ]);
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

        if (!$this->isSuccess($update)) {
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

        if (!$this->isSuccess($delete)) {
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
        $tingkat   = strtoupper(trim($body['tingkat']      ?? ''));
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

        if (!$this->isSuccess($insert)) {
            echo json_encode([
                'success' => false,
                'message' => 'Gagal menyimpan data kelas: ' . json_encode($insert['response'] ?? $insert),
            ]);
            exit;
        }

        if (!empty($waliKelas)) {
            Database::request('PATCH', 'guru?nama=eq.' . urlencode($waliKelas), [
                'wali_kelas' => $namaKelas
            ]);
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
        $tingkat   = strtoupper(trim($body['tingkat']      ?? ''));
        $waliKelas = trim($body['wali_kelas']   ?? '');
        $tahun     = trim($body['tahun_ajaran'] ?? '');
        $kapasitas = (int) ($body['kapasitas']  ?? 0);

        if (!$id || !$namaKelas) {
            echo json_encode(['success' => false, 'message' => 'ID dan nama kelas wajib diisi']);
            exit;
        }

        $kelasLama = Database::request('GET', 'kelas?id=eq.' . urlencode($id) . '&select=nama_kelas,wali_kelas&limit=1');
        $waliLama  = $kelasLama[0]['wali_kelas'] ?? null;

        $payload = [
            'nama_kelas'   => $namaKelas,
            'tingkat'      => !empty($tingkat) ? $tingkat : null,
            'wali_kelas'   => !empty($waliKelas) ? $waliKelas : null,
            'tahun_ajaran' => $tahun ?: null,
        ];
        if ($kapasitas > 0) $payload['kapasitas'] = $kapasitas;

        $update = Database::request('PATCH', 'kelas?id=eq.' . urlencode($id), $payload);

        if (!$this->isSuccess($update)) {
            echo json_encode(['success' => false, 'message' => 'Gagal mengupdate data kelas']);
            exit;
        }

        if (!empty($waliLama) && $waliLama !== $waliKelas) {
            Database::request('PATCH', 'guru?nama=eq.' . urlencode($waliLama), [
                'wali_kelas' => null
            ]);
        }
        // 2. Set wali_kelas baru ke guru yang dipilih
        if (!empty($waliKelas)) {
            Database::request('PATCH', 'guru?nama=eq.' . urlencode($waliKelas), [
                'wali_kelas' => $namaKelas
            ]);
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

        $kelasData = Database::request('GET', 'kelas?id=eq.' . urlencode($id) . '&select=wali_kelas&limit=1');
        $waliLama  = $kelasData[0]['wali_kelas'] ?? null;
        if (!empty($waliLama)) {
            Database::request('PATCH', 'guru?nama=eq.' . urlencode($waliLama), [
                'wali_kelas' => null
            ]);
        }

        $delete = Database::request('DELETE', 'kelas?id=eq.' . urlencode($id));

        if (!$this->isSuccess($delete)) {
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

        require_once BASE_PATH . '/app/models/Absensi.php';

        $absensiModel = new Absensi();

        $respKelas   = Database::request('GET', 'kelas?select=nama_kelas&order=nama_kelas.asc');
        $daftarKelas = (!empty($respKelas) && !isset($respKelas['error']) && is_array($respKelas))
                       ? array_values(array_filter(array_column($respKelas, 'nama_kelas')))
                       : [];

        $tglMulai = $_GET['tgl_mulai'] ?? date('Y-m-d', strtotime('-6 days'));
        $tglAkhir = $_GET['tgl_akhir'] ?? date('Y-m-d');
        $kelas    = $_GET['kelas']     ?? '';
        $status   = $_GET['status']    ?? '';

        $laporanData = $absensiModel->getByRange($tglMulai, $tglAkhir, $kelas, $status);

        $totalHadir     = count(array_filter($laporanData, fn($r) => $r['status'] === 'Hadir'));
        $totalTerlambat = count(array_filter($laporanData, fn($r) => $r['status'] === 'Terlambat'));
        $totalIzinSakit = count(array_filter($laporanData, fn($r) => in_array($r['status'], ['Izin', 'Sakit'])));
        $totalAlpha     = count(array_filter($laporanData, fn($r) => $r['status'] === 'Alpha'));

        $this->view('admin/laporan', [
            'pageTitle'      => 'Laporan Kehadiran',
            'laporanData'    => $laporanData,
            'daftarKelas'    => $daftarKelas,
            'totalHadir'     => $totalHadir,
            'totalTerlambat' => $totalTerlambat,
            'totalIzinSakit' => $totalIzinSakit,
            'totalAlpha'     => $totalAlpha,
            'total'          => count($laporanData),
            'filterTglMulai' => $tglMulai,
            'filterTglAkhir' => $tglAkhir,
            'filterKelas'    => $kelas,
            'filterStatus'   => $status,
        ]);
    }

    // =========================================================
    //  PENGATURAN
    // =========================================================

    public function pengaturan(): void
    {
        $this->requireAuth();

        $configPath = BASE_PATH . '/storage/pengaturan.json';
        $config     = [];
        if (file_exists($configPath)) {
            $decoded = json_decode(file_get_contents($configPath), true);
            if (is_array($decoded)) $config = $decoded;
        }

        $respLibur = Database::request('GET', 'hari_libur?order=tanggal.asc');
        $hariLibur = (!empty($respLibur) && !isset($respLibur['error']) && is_array($respLibur))
                     ? $respLibur
                     : [];

        $data = [
            'pageTitle' => 'Pengaturan Sistem',
            'config'    => $config,
            'hariLibur' => $hariLibur,
            'sekolah'   => [
                'nama'       => $config['nama_sekolah'] ?? 'SMA Negeri 1 Yogyakarta',
                'npsn'       => '20403280',
                'alamat'     => $config['alamat_sekolah'] ?? 'Jl. HOS Cokroaminoto No. 10, Pakuncen, Wirobrajan, Yogyakarta',
                'telepon'    => '(0274) 513448',
                'email'      => 'info@sman1yk.sch.id',
                'website'    => 'https://sman1yogya.sch.id',
                'kepala'     => $config['kepala_sekolah'] ?? 'Drs. H. Mujiyono, M.Pd.',
                'tahun_ajar' => $config['tahun_ajaran']   ?? '2024/2025',
                'semester'   => $config['semester']        ?? 'Genap',
                'logo'       => 'assets/img/logo-sekolah.png',
            ],
            'absensi' => [
                'jam_masuk'        => $config['batas_tepat']      ?? '07:00',
                'jam_toleransi'    => $config['batas_terlambat']  ?? '07:15',
                'jam_pulang'       => $config['jam_mulai_pulang'] ?? '14:30',
                'hari_aktif'       => ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'],
                'qr_expired_menit' => 30,
                'notif_alpha_wa'   => $config['notif_wa']   ?? true,
                'auto_alpha'       => $config['auto_alpha'] ?? true,
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
    //  PENGATURAN — SAVE
    // =========================================================

    public function savePengaturan(): void
    {
        $this->requireAuth();
        header('Content-Type: application/json');

        $body = json_decode(file_get_contents('php://input'), true) ?? [];

        if (empty($body)) {
            echo json_encode(['success' => false, 'message' => 'Data kosong']);
            exit;
        }

        $configPath = BASE_PATH . '/storage/pengaturan.json';
        $dir        = dirname($configPath);

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $saved = file_put_contents($configPath, json_encode($body, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        if ($saved === false) {
            echo json_encode(['success' => false, 'message' => 'Gagal menulis file konfigurasi. Pastikan folder storage/ writable.']);
            exit;
        }

        echo json_encode(['success' => true, 'message' => 'Pengaturan berhasil disimpan']);
        exit;
    }

    // =========================================================
    //  PENGATURAN — TAMBAH HARI LIBUR
    // =========================================================

    public function tambahLibur(): void
    {
        $this->requireAuth();
        header('Content-Type: application/json');

        $body    = json_decode(file_get_contents('php://input'), true) ?? [];
        $tanggal = trim($body['tanggal']    ?? '');
        $ket     = trim($body['keterangan'] ?? '');

        if (!$tanggal || !$ket) {
            echo json_encode(['success' => false, 'message' => 'Tanggal dan keterangan wajib diisi']);
            exit;
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) {
            echo json_encode(['success' => false, 'message' => 'Format tanggal tidak valid']);
            exit;
        }

        $cek = Database::request('GET', 'hari_libur?tanggal=eq.' . urlencode($tanggal) . '&select=id&limit=1');
        if (is_array($cek) && !isset($cek['error']) && count($cek) > 0) {
            echo json_encode(['success' => false, 'message' => 'Tanggal ' . $tanggal . ' sudah terdaftar sebagai hari libur']);
            exit;
        }

        $insert = Database::request('POST', 'hari_libur', [
            'tanggal'    => $tanggal,
            'keterangan' => $ket,
        ]);

        if (!$this->isSuccess($insert)) {
            echo json_encode(['success' => false, 'message' => 'Gagal menyimpan hari libur', 'debug' => $insert]);
            exit;
        }

        echo json_encode(['success' => true, 'message' => 'Hari libur berhasil ditambahkan']);
        exit;
    }

    // =========================================================
    //  PENGATURAN — HAPUS HARI LIBUR
    // =========================================================

    public function hapusLibur(): void
    {
        $this->requireAuth();
        header('Content-Type: application/json');

        $body = json_decode(file_get_contents('php://input'), true) ?? [];
        $id   = trim($body['id'] ?? '');

        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID tidak valid']);
            exit;
        }

        $delete = Database::request('DELETE', 'hari_libur?id=eq.' . urlencode($id));

        if (!$this->isSuccess($delete)) {
            echo json_encode(['success' => false, 'message' => 'Gagal menghapus hari libur']);
            exit;
        }

        echo json_encode(['success' => true, 'message' => 'Hari libur berhasil dihapus']);
        exit;
    }

    // =========================================================
    //  PENGATURAN — GET HARI LIBUR
    // =========================================================

    public function getLibur(): void
    {
        $this->requireAuth();
        header('Content-Type: application/json');

        $resp = Database::request('GET', 'hari_libur?order=tanggal.asc');
        $data = (!empty($resp) && !isset($resp['error']) && is_array($resp)) ? $resp : [];

        echo json_encode(['success' => true, 'data' => $data]);
        exit;
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
            echo json_encode(['success' => false, 'message' => 'ID tidak valid']);
            exit;
        }

        $reg = Database::request('GET', 'registrasi?id=eq.' . $id . '&limit=1');
        if (empty($reg) || isset($reg['error'])) {
            echo json_encode(['success' => false, 'message' => 'Data tidak ditemukan']);
            exit;
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

        if (!$this->isSuccess($insert)) {
            echo json_encode(['success' => false, 'message' => 'Gagal membuat akun']);
            exit;
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
            echo json_encode(['success' => false, 'message' => 'ID tidak valid']);
            exit;
        }

        Database::request('PATCH', 'registrasi?id=eq.' . $id, ['status' => 'rejected']);
        echo json_encode(['success' => true, 'message' => 'Registrasi ditolak']);
        exit;
    }
}