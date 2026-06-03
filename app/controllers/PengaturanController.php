<?php

class PengaturanController extends Controller
{
    public function __construct()
    {
        // Cek session login
        if (!isset($_SESSION['user'])) {
            header('Location: ' . BASE_URL . '?url=auth/login');
            exit;
        }
    }

    // GET - Tampilkan halaman pengaturan
    public function index()
    {
        $user = $_SESSION['user'];
        $this->view('guru/pengaturan', ['user' => $user]);
    }

    // POST - Update profil (nama, email)
    public function updateProfil()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '?url=guru/pengaturan');
            exit;
        }

        $id    = $_SESSION['user']['id'];
        $nama  = trim($_POST['nama'] ?? '');
        $email = trim($_POST['email'] ?? '');

        if (empty($nama) || empty($email)) {
            $_SESSION['error'] = 'Nama dan email tidak boleh kosong.';
            header('Location: ' . BASE_URL . '?url=guru/pengaturan');
            exit;
        }

        $db = Database::getInstance();

        // Cek email duplikat (selain diri sendiri)
        $cek = $db->query(
            "SELECT id FROM guru WHERE email = ? AND id != ?",
            [$email, $id]
        )->fetch();

        if ($cek) {
            $_SESSION['error'] = 'Email sudah digunakan oleh akun lain.';
            header('Location: ' . BASE_URL . '?url=guru/pengaturan');
            exit;
        }

        $db->query(
            "UPDATE guru SET nama = ?, email = ? WHERE id = ?",
            [$nama, $email, $id]
        );

        // Update session
        $_SESSION['user']['nama']  = $nama;
        $_SESSION['user']['email'] = $email;

        $_SESSION['success'] = 'Profil berhasil diperbarui.';
        header('Location: ' . BASE_URL . '?url=guru/pengaturan');
        exit;
    }

    // POST - Ganti password
    public function gantiPassword()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '?url=guru/pengaturan');
            exit;
        }

        $id               = $_SESSION['user']['id'];
        $password_lama    = $_POST['password_lama']    ?? '';
        $password_baru    = $_POST['password_baru']    ?? '';
        $konfirmasi_pass  = $_POST['konfirmasi_pass']  ?? '';

        if (empty($password_lama) || empty($password_baru) || empty($konfirmasi_pass)) {
            $_SESSION['error'] = 'Semua field password harus diisi.';
            header('Location: ' . BASE_URL . '?url=guru/pengaturan');
            exit;
        }

        if ($password_baru !== $konfirmasi_pass) {
            $_SESSION['error'] = 'Konfirmasi password tidak cocok.';
            header('Location: ' . BASE_URL . '?url=guru/pengaturan');
            exit;
        }

        if (strlen($password_baru) < 6) {
            $_SESSION['error'] = 'Password baru minimal 6 karakter.';
            header('Location: ' . BASE_URL . '?url=guru/pengaturan');
            exit;
        }

        $db   = Database::getInstance();
        $guru = $db->query(
            "SELECT password FROM guru WHERE id = ?",
            [$id]
        )->fetch();

        if (!$guru || !password_verify($password_lama, $guru['password'])) {
            $_SESSION['error'] = 'Password lama tidak sesuai.';
            header('Location: ' . BASE_URL . '?url=guru/pengaturan');
            exit;
        }

        $hash = password_hash($password_baru, PASSWORD_DEFAULT);
        $db->query(
            "UPDATE guru SET password = ? WHERE id = ?",
            [$hash, $id]
        );

        $_SESSION['success'] = 'Password berhasil diperbarui.';
        header('Location: ' . BASE_URL . '?url=guru/pengaturan');
        exit;
    }
}