<?php

require_once BASE_PATH . '/app/models/Kelas.php'; // [FIX] tambah require Kelas model

class MonitoringController extends Controller {

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['user'])) {
            header('Location: ' . BASE_URL . '?url=auth/loginPage');
            exit;
        }
    }

    public function index() {
        $user  = $_SESSION['user'];
        $title = 'Monitoring – Absensi QR';

        $today = date('Y-m-d');
        $dari  = date('Y-m-d', strtotime('-29 days'));

        // [FIX] Ambil kelas yang diampu guru ini
        $guruId        = $user['id'] ?? null;
        $kelasModel    = new Kelas();
        $kelasAmpu     = $kelasModel->getByGuru($guruId);
        $namaKelasAmpu = array_column($kelasAmpu, 'nama_kelas');

        // ── Ambil data kehadiran 30 hari terakhir ──────────────────────
        $kehadiran = Database::request('GET',
            'kehadiran?select=tanggal,waktu_masuk,status,students(nama,nis,kelas)' .
            '&tanggal=gte.' . $dari .
            '&tanggal=lte.' . $today .
            '&order=tanggal.desc,waktu_masuk.desc'
        );
        $kehadiran = (!empty($kehadiran) && !isset($kehadiran['error'])) ? $kehadiran : [];

        // [FIX] Filter kehadiran hanya dari kelas yang diampu
        $kehadiran = array_values(array_filter($kehadiran, fn($a) =>
            in_array($a['students']['kelas'] ?? '', $namaKelasAmpu)
        ));

        // ── Total siswa ────────────────────────────────────────────────
        // [FIX] Hanya siswa dari kelas yang diampu
        $siswaAll = [];
        foreach ($namaKelasAmpu as $nk) {
            $res = Database::request('GET', 'students?kelas=eq.' . urlencode($nk) . '&select=id,nama,nis,kelas');
            if (!empty($res) && !isset($res['error'])) {
                $siswaAll = array_merge($siswaAll, $res);
            }
        }
        $totalSiswa = count($siswaAll);

        // ── Rekap per hari untuk chart ─────────────────────────────────
        $rekapHari = [];
        foreach ($kehadiran as $a) {
            $tgl    = $a['tanggal'] ?? '';
            $status = strtolower($a['status'] ?? 'alpha');
            if (!$tgl) continue;
            if (!isset($rekapHari[$tgl])) {
                $rekapHari[$tgl] = ['hadir' => 0, 'terlambat' => 0, 'izin' => 0, 'alpha' => 0];
            }
            if (isset($rekapHari[$tgl][$status])) $rekapHari[$tgl][$status]++;
        }
        ksort($rekapHari);

        // ── Chart labels & data ────────────────────────────────────────
        $chartLabels    = [];
        $chartHadir     = [];
        $chartTerlambat = [];
        $chartAlpha     = [];
        foreach ($rekapHari as $tgl => $r) {
            $chartLabels[]    = date('d/m', strtotime($tgl));
            $chartHadir[]     = $r['hadir'] + $r['terlambat'];
            $chartTerlambat[] = $r['terlambat'];
            $chartAlpha[]     = $r['alpha'];
        }

        // ── Rekap hari ini ─────────────────────────────────────────────
        $hadirHariIni     = 0;
        $terlambatHariIni = 0;
        $izinHariIni      = 0;
        $alphaHariIni     = 0;

        foreach ($kehadiran as $a) {
            if (($a['tanggal'] ?? '') !== $today) continue;
            $s = strtolower($a['status'] ?? '');
            if      ($s === 'hadir')     $hadirHariIni++;
            elseif  ($s === 'terlambat') $terlambatHariIni++;
            elseif  ($s === 'izin')      $izinHariIni++;
            else                         $alphaHariIni++;
        }
        $belumAbsen = max(0, $totalSiswa - $hadirHariIni - $terlambatHariIni - $izinHariIni - $alphaHariIni);

        // ── Siswa terlambat hari ini ───────────────────────────────────
        $siswaTerlambat = [];
        foreach ($kehadiran as $a) {
            if (($a['tanggal'] ?? '') !== $today) continue;
            if (strtolower($a['status'] ?? '') !== 'terlambat') continue;
            $siswaTerlambat[] = [
                'nama'       => $a['students']['nama']  ?? '-',
                'nis'        => $a['students']['nis']   ?? '-',
                'nama_kelas' => $a['students']['kelas'] ?? '-',
                'waktu'      => $a['waktu_masuk']       ? substr($a['waktu_masuk'], 0, 5) : '-',
            ];
        }

        // ── Rekap per kelas hari ini ───────────────────────────────────
        // [FIX] Inisialisasi hanya dari kelas yang diampu
        $rekapKelas = [];
        foreach ($siswaAll as $s) {
            $kls = $s['kelas'] ?? 'Tanpa Kelas';
            if (!isset($rekapKelas[$kls])) {
                $rekapKelas[$kls] = ['total' => 0, 'hadir' => 0];
            }
            $rekapKelas[$kls]['total']++;
        }
        foreach ($kehadiran as $a) {
            if (($a['tanggal'] ?? '') !== $today) continue;
            $kls = $a['students']['kelas'] ?? 'Tanpa Kelas';
            $s   = strtolower($a['status'] ?? '');
            if (isset($rekapKelas[$kls]) && in_array($s, ['hadir', 'terlambat'])) {
                $rekapKelas[$kls]['hadir']++;
            }
        }
        ksort($rekapKelas);

        $this->view('guru/monitoring', compact(
            'user', 'title',
            'totalSiswa', 'hadirHariIni', 'terlambatHariIni',
            'izinHariIni', 'alphaHariIni', 'belumAbsen',
            'siswaTerlambat', 'rekapKelas',
            'chartLabels', 'chartHadir', 'chartTerlambat', 'chartAlpha'
        ));
    }
}