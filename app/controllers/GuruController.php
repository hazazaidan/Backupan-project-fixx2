<?php

class GuruController extends Controller {

    // ─── Auth check + ambil $user dari session ───────────────────────────────
    private function authCheck(): array {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'guru') {
            header('Location: ?url=auth/loginPage');
            exit;
        }
        return $_SESSION['user']; // return data user langsung
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
}