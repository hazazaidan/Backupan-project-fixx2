<?php

class ScanController extends Controller {

    public function __construct() {

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['user'])) {
            header('Location: ' . BASE_URL . '?url=auth/loginPage');
            exit;
        }
    }

    public function loginPage() {
        $this->index();
    }

    // ─── HALAMAN SCAN QR ────────────────────────────────────────────
    public function index() {

        $user        = $_SESSION['user'];
        $sekolah     = $_SESSION['sekolah'] ?? ['nama_sekolah' => 'MAN 2 Banyumas'];
        $scanHariIni = $this->getScanHariIni();
        $title       = 'Scan QR – Absensi';

        $this->view('guru/scan', compact('user', 'sekolah', 'scanHariIni', 'title'));
    }

    // ─── API SCAN QR ─────────────────────────────────────────────────
    public function api() {

        header('Content-Type: application/json');

        // Hanya POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method tidak diizinkan']);
            exit;
        }

        // Ambil qr_code dari POST
        $qrCode = trim($_POST['qr_code'] ?? '');

        // Validasi: QR Code tidak boleh kosong
        if (empty($qrCode)) {
            echo json_encode(['success' => false, 'message' => 'QR Code kosong']);
            exit;
        }

        // Set timezone
        date_default_timezone_set('Asia/Jakarta');

        // ── CARI SISWA ──
        // Coba cocokkan ke qr_image (UUID) dulu, fallback ke nis
        $siswaResult = Database::request(
            'GET',
            'students?select=id,nama,kelas,nis&qr_image=eq.' . urlencode($qrCode) . '&limit=1'
        );

        // Fallback: cari berdasarkan nis jika tidak ketemu via qr_image
        if (empty($siswaResult) || isset($siswaResult['error'])) {
            $siswaResult = Database::request(
                'GET',
                'students?select=id,nama,kelas,nis&nis=eq.' . urlencode($qrCode) . '&limit=1'
            );
        }

        // Jika masih tidak ditemukan
        if (empty($siswaResult) || isset($siswaResult['error'])) {
            echo json_encode([
                'success' => false,
                'message' => 'QR Code tidak dikenali. Silakan cek kembali atau hubungi administrator.'
            ]);
            exit;
        }

        $siswa     = $siswaResult[0];
        $namaKelas = $siswa['kelas'] ?? '-';
        $today     = date('Y-m-d');

        // ── CEK SUDAH ABSEN HARI INI ──
        $absenResult = Database::request(
            'GET',
            'kehadiran?siswa_id=eq.' . $siswa['id'] . '&tanggal=eq.' . $today . '&limit=1'
        );

        if (!empty($absenResult) && !isset($absenResult['error'])) {
            echo json_encode([
                'success' => false,
                'message' => htmlspecialchars($siswa['nama'] ?? 'Siswa') . ' sudah absen hari ini'
            ]);
            exit;
        }

        // ── TENTUKAN STATUS ──
        $waktuSekarang = date('H:i');
        $waktuMasuk    = date('H:i:s');
        $status        = ($waktuSekarang <= '07:30') ? 'hadir' : 'terlambat';

        // ── SIMPAN KE kehadiran ──
        $insertResult = Database::request(
            'POST',
            'kehadiran',
            [
                'siswa_id'    => $siswa['id'],
                'tanggal'     => $today,
                'waktu_masuk' => $waktuMasuk,
                'status'      => $status,
            ]
        );

        if (isset($insertResult['error'])) {
            echo json_encode([
                'success' => false,
                'message' => 'Gagal menyimpan absensi. Silakan coba lagi.'
            ]);
            exit;
        }

        // ── RESPONSE SUKSES ──
        if ($status === 'terlambat') {
            $pesan = htmlspecialchars($siswa['nama'] ?? 'Siswa') . ' hadir — Terlambat (melewati 07:30)';
        } else {
            $pesan = htmlspecialchars($siswa['nama'] ?? 'Siswa') . ' berhasil absen Hadir';
        }

        echo json_encode([
            'success' => true,
            'message' => $pesan,
            'siswa'   => [
                'nama'       => htmlspecialchars($siswa['nama'] ?? '-'),
                'nama_kelas' => htmlspecialchars($namaKelas),
            ],
            'waktu'   => $waktuSekarang,
            'status'  => $status,
        ]);
        exit;
    }

    // ─── DATA SCAN HARI INI ──────────────────────────────────────────
    private function getScanHariIni() {

        try {

            date_default_timezone_set('Asia/Jakarta');
            $today = date('Y-m-d');

            $result = Database::request(
                'GET',
                'kehadiran?select=tanggal,waktu_masuk,status,students(nama,kelas)&tanggal=eq.'
                    . $today . '&order=waktu_masuk.desc'
            );

            if (empty($result) || isset($result['error'])) {
                return [];
            }

            return array_map(function ($row) {
                return [
                    'nama'       => $row['students']['nama']  ?? '-',
                    'nama_kelas' => $row['students']['kelas'] ?? '-',
                    'waktu'      => substr($row['waktu_masuk'] ?? '', 0, 5),
                    'status'     => $row['status'] ?? '-',
                ];
            }, $result);

        } catch (Exception $e) {
            return [];
        }
    }
}