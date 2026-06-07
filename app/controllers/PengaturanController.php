<?php

class PengaturanController extends Controller
{
    public function __construct()
    {
        if (!isset($_SESSION['user'])) {
            header('Location: ' . BASE_URL . '?url=login');
            exit;
        }
    }

    public function index()
    {
        $user = $_SESSION['user'];
        $this->view('guru/pengaturan', ['user' => $user]);
    }

    public function updateProfil()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '?url=pengaturan');
            exit;
        }

        $id    = $_SESSION['user']['id'];
        $nama  = trim($_POST['nama'] ?? '');
        $email = trim($_POST['email'] ?? '');

        if (empty($nama) || empty($email)) {
            $_SESSION['error'] = 'Nama dan email tidak boleh kosong.';
            header('Location: ' . BASE_URL . '?url=pengaturan');
            exit;
        }

        // Cek email duplikat via Supabase
        $cek = Database::request("GET", "guru?email=eq." . urlencode($email) . "&id=neq.$id");

        if (!empty($cek) && !isset($cek['error'])) {
            $_SESSION['error'] = 'Email sudah digunakan oleh akun lain.';
            header('Location: ' . BASE_URL . '?url=pengaturan');
            exit;
        }

        // Update ke Supabase
        Database::request("PATCH", "guru?id=eq.$id", [
            'nama'  => $nama,
            'email' => $email,
        ]);

        // Update session
        $_SESSION['user']['nama']  = $nama;
        $_SESSION['user']['email'] = $email;

        $_SESSION['success'] = 'Profil berhasil diperbarui.';
        header('Location: ' . BASE_URL . '?url=pengaturan');
        exit;
    }

    public function gantiPassword()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '?url=pengaturan');
            exit;
        }

        $id              = $_SESSION['user']['id'];
        $password_lama   = $_POST['password_lama']   ?? '';
        $password_baru   = $_POST['password_baru']   ?? '';
        $konfirmasi_pass = $_POST['konfirmasi_pass']  ?? '';

        if (empty($password_lama) || empty($password_baru) || empty($konfirmasi_pass)) {
            $_SESSION['error'] = 'Semua field password harus diisi.';
            header('Location: ' . BASE_URL . '?url=pengaturan');
            exit;
        }

        if ($password_baru !== $konfirmasi_pass) {
            $_SESSION['error'] = 'Konfirmasi password tidak cocok.';
            header('Location: ' . BASE_URL . '?url=pengaturan');
            exit;
        }

        if (strlen($password_baru) < 6) {
            $_SESSION['error'] = 'Password baru minimal 6 karakter.';
            header('Location: ' . BASE_URL . '?url=pengaturan');
            exit;
        }

        // Ambil data guru dari Supabase
        $guru = Database::request("GET", "guru?id=eq.$id");

        if (empty($guru) || !isset($guru[0]['password'])) {
            $_SESSION['error'] = 'Data akun tidak ditemukan.';
            header('Location: ' . BASE_URL . '?url=pengaturan');
            exit;
        }

        // Verifikasi password lama
        if (!password_verify($password_lama, $guru[0]['password'])) {
            $_SESSION['error'] = 'Password lama tidak sesuai.';
            header('Location: ' . BASE_URL . '?url=pengaturan');
            exit;
        }

        // Update password baru
        $hash = password_hash($password_baru, PASSWORD_DEFAULT);
        Database::request("PATCH", "guru?id=eq.$id", [
            'password' => $hash,
        ]);

        $_SESSION['success'] = 'Password berhasil diperbarui.';
        header('Location: ' . BASE_URL . '?url=pengaturan');
        exit;
    }
}