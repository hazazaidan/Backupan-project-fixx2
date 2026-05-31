<?php

/**
 * AdminAuthController.php
 * Handle login & logout untuk panel admin.
 * Lokasi: app/controllers/AdminAuthController.php
 */

class AdminAuthController
{
    // =========================================================
    //  HELPERS
    // =========================================================

    private function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

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
    //  LOGIN PAGE  →  GET ?url=admin/login
    // =========================================================

    public function loginPage(): void
    {
        $this->startSession();

        // Kalau sudah login, langsung ke dashboard
        if (!empty($_SESSION['admin'])) {
            header('Location: ' . BASE_URL . '?url=admin/dashboard');
            exit;
        }

        $this->view('auth/login');
    }

    // =========================================================
    //  DO LOGIN  →  POST ?url=admin/doLogin
    // =========================================================

    public function doLogin(): void
    {
        $this->startSession();

        // Kalau sudah login
        if (!empty($_SESSION['admin'])) {
            header('Location: ' . BASE_URL . '?url=admin/dashboard');
            exit;
        }

        // Hanya terima POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '?url=admin/login');
            exit;
        }

        $input    = trim($_POST['email']    ?? '');
        $password = trim($_POST['password'] ?? '');

        // Validasi input kosong
        if (empty($input) || empty($password)) {
            $_SESSION['login_error'] = 'Username/email dan password wajib diisi.';
            header('Location: ' . BASE_URL . '?url=admin/login');
            exit;
        }

        // ── Query ke Supabase (tabel: admin) ──────────────────
        $isEmail = filter_var($input, FILTER_VALIDATE_EMAIL);
        $field   = $isEmail ? 'email' : 'username';

        $apiUrl = SUPABASE_URL . '/rest/v1/admin?select=*&' . $field . '=eq.' . urlencode($input) . '&limit=1';

        $ch = curl_init($apiUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                'apikey: '        . SUPABASE_KEY,
                'Authorization: Bearer ' . SUPABASE_KEY,
                'Content-Type: application/json',
            ],
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            $_SESSION['login_error'] = 'Gagal menghubungi server. Coba lagi.';
            header('Location: ' . BASE_URL . '?url=admin/login');
            exit;
        }

        $rows = json_decode($response, true);

        // Tidak ditemukan
        if (empty($rows)) {
            $_SESSION['login_error'] = 'Username/email tidak ditemukan.';
            header('Location: ' . BASE_URL . '?url=admin/login');
            exit;
        }

        $admin = $rows[0];

        // Cek status aktif
        if (strtolower($admin['status'] ?? '') !== 'aktif') {
            $_SESSION['login_error'] = 'Akun ini tidak aktif. Hubungi Super Admin.';
            header('Location: ' . BASE_URL . '?url=admin/login');
            exit;
        }

        // Verifikasi password bcrypt
        if (!password_verify($password, $admin['password'])) {
            $_SESSION['login_error'] = 'Password salah.';
            header('Location: ' . BASE_URL . '?url=admin/login');
            exit;
        }

        // ── Login berhasil: simpan session ───────────────────
        $_SESSION['admin'] = [
            'id'       => $admin['id'],
            'nama'     => $admin['nama'],
            'username' => $admin['username'],
            'email'    => $admin['email'],
            'role'     => $admin['role'],
        ];

        // Update last_login ke Supabase
        $this->updateLastLogin($admin['id']);

        header('Location: ' . BASE_URL . '?url=admin/dashboard');
        exit;
    }

    // =========================================================
    //  LOGOUT  →  GET/POST ?url=admin/logout
    // =========================================================

    public function logout(): void
    {
        $this->startSession();
        $_SESSION = [];
        session_destroy();
        header('Location: ' . BASE_URL . '?url=admin/login');
        exit;
    }

    // =========================================================
    //  HELPER: Update last_login di Supabase
    // =========================================================

    private function updateLastLogin(int $id): void
    {
        $apiUrl = SUPABASE_URL . '/rest/v1/admin?id=eq.' . $id;
        $body   = json_encode(['last_login' => date('c')]);   // ISO 8601

        $ch = curl_init($apiUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => 'PATCH',
            CURLOPT_POSTFIELDS     => $body,
            CURLOPT_HTTPHEADER     => [
                'apikey: '        . SUPABASE_KEY,
                'Authorization: Bearer ' . SUPABASE_KEY,
                'Content-Type: application/json',
                'Prefer: return=minimal',
            ],
        ]);
        curl_exec($ch);
        curl_close($ch);
    }
}