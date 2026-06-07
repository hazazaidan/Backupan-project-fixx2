<?php

class GuruController extends Controller {

    // ─── Auth check + ambil $user dari session ───────────────────────────────
    private function authCheck(): array {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'guru') {
            header('Location: ?url=auth/loginPage');
            exit;
        }
        return $_SESSION['user'];
    }

    // ─── Routes ──────────────────────────────────────────────────────────────
    public function index() {
        $this->dashboard();
    }

    public function dashboard() {
        $user = $this->authCheck();
        $this->view('guru/dashboard', compact('user'));
    }

    public function scan() {
        $user = $this->authCheck();
        $this->view('guru/scan', compact('user'));
    }

    public function riwayat() {
        $user = $this->authCheck();
        $this->view('guru/riwayat', compact('user'));
    }

    public function rekap() {
        $user = $this->authCheck();
        $this->view('guru/rekap', compact('user'));
    }

    public function monitoring() {
        $user = $this->authCheck();
        $this->view('guru/monitoring', compact('user'));
    }

    public function pengaturan() {
        $user = $this->authCheck();
        $this->view('guru/pengaturan', compact('user'));
    }

    // ─── TAMBAHAN: Kelas & Mapel ──────────────────────────────────────────────
    public function kelas() {
        $user    = $this->authCheck();
        $guruId  = $user['id'] ?? null;

        $kelasModel = new Kelas();

        // Ambil daftar kelas yang diajar guru ini (by wali_kelas)
        $kelasList = $guruId ? $kelasModel->getByGuru($guruId) : [];

        // Jika ada AJAX request untuk mapel
        if (isset($_GET['action']) && $_GET['action'] === 'mapel') {
            $namaKelas = $_GET['kelas'] ?? '';
            $mapelList = $guruId
                ? $kelasModel->getMapelByGuruKelas($guruId, $namaKelas)
                : [];
            header('Content-Type: application/json');
            echo json_encode($mapelList);
            exit;
        }

        // Ambil semua mapel untuk kelas pertama (default) jika ada
        $mapelList = [];
        if (!empty($kelasList) && $guruId) {
            $mapelList = $kelasModel->getMapelByGuruKelas(
                $guruId,
                $kelasList[0]['nama'] ?? ''
            );
        }

        $this->view('guru/kelas', compact('user', 'kelasList', 'mapelList'));
    }
}