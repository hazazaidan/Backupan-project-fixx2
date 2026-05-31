<?php

class AuthController extends Controller {

    // =========================================================
    // LOGIN PAGE
    // =========================================================
    public function loginPage(): void {

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Jika sudah login
        if ($this->isLoggedIn()) {

            $role = $_SESSION['user']['role'] ?? 'guru';

            if ($role === 'admin') {
                $this->redirect('admin/dashboard');
            } else {
                $this->redirect('guru/dashboard');
            }

            return;
        }

        $this->view('auth/login', [
            'title' => 'Login – Absensi QR'
        ]);
    }

    // =========================================================
    // LOGIN PROCESS
    // =========================================================
    public function doLogin(): void {

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $role     = $_POST['role'] ?? 'guru';

        // Validasi role
        if (!in_array($role, ['guru', 'admin'])) {
            $role = 'guru';
        }

        // Validasi input kosong
        if (empty($email) || empty($password)) {

            $_SESSION['login_error'] =
                'Email dan password wajib diisi.';

            $this->redirect('login');
            return;
        }

        // =====================================================
        // AMBIL USER DARI DATABASE
        // =====================================================

        $table = ($role === 'admin')
            ? 'admin'
            : 'guru';

        $response = Database::request(
            'GET',
            $table .
            '?email=eq.' .
            urlencode($email) .
            '&limit=1'
        );

        // =====================================================
        // USER DITEMUKAN
        // =====================================================

        if (
            is_array($response) &&
            !isset($response['error']) &&
            !empty($response)
        ) {

            $user = $response[0];

            $storedPassword =
                $user['password']
                ?? $user['Password']
                ?? '';

            $passwordValid = false;

            // =================================================
            // VERIFIKASI PASSWORD
            // =================================================

            if (!empty($storedPassword)) {

                // Password hash
                if (
                    password_verify(
                        $password,
                        $storedPassword
                    )
                ) {
                    $passwordValid = true;
                }

                // Password plain text
                if ($storedPassword === $password) {
                    $passwordValid = true;
                }
            }

            // =================================================
            // LOGIN BERHASIL
            // =================================================

            if ($passwordValid) {

                // Cek status admin
                if ($role === 'admin') {

                    $status =
                        strtolower(
                            $user['status']
                            ?? 'aktif'
                        );

                    if ($status !== 'aktif') {

                        $_SESSION['login_error'] =
                            'Akun admin dinonaktifkan.';

                        $this->redirect('login');
                        return;
                    }
                }

                // Simpan session
                $_SESSION['user'] = [

                    'id' =>
                        $user['id']
                        ?? null,

                    'nama' =>
                        $user['nama']
                        ?? 'User',

                    'email' =>
                        $user['email']
                        ?? $email,

                    'role' => $role,

                    'foto' =>
                        $user['foto']
                        ?? null,

                    'status' =>
                        $user['status']
                        ?? 'aktif',

                    'login_at' =>
                        date('Y-m-d H:i:s')
                ];

                // =================================================
                // UPDATE LAST LOGIN ADMIN
                // =================================================

                if (
                    $role === 'admin' &&
                    !empty($user['id'])
                ) {

                    Database::request(
                        'PATCH',
                        'admin?id=eq.' . $user['id'],
                        [
                            'last_login' =>
                                date('c'),

                            'updated_at' =>
                                date('c')
                        ]
                    );
                }

                // =================================================
                // REDIRECT DASHBOARD
                // =================================================

                if ($role === 'admin') {

                    $this->redirect(
                        'admin/dashboard'
                    );

                } else {

                    $this->redirect(
                        'guru/dashboard'
                    );
                }

                return;
            }

            // =================================================
            // PASSWORD SALAH
            // =================================================

            $_SESSION['login_error'] =
                'Password salah.';

            $this->redirect('login');
            return;
        }

        // =====================================================
        // DUMMY USER (DEV ONLY)
        // =====================================================

        $dummyUsers = [

            'guru' => [

                'email' =>
                    'guru@guru.com',

                'password' =>
                    'guru123',

                'nama' =>
                    'Guru Demo',

                'role' =>
                    'guru'
            ],

            'admin' => [

                'email' =>
                    'admin@admin.com',

                'password' =>
                    'admin123',

                'nama' =>
                    'Administrator',

                'role' =>
                    'admin'
            ]
        ];

        $dummy = $dummyUsers[$role] ?? null;

        if (
            $dummy &&
            $dummy['email'] === $email &&
            $dummy['password'] === $password
        ) {

            $_SESSION['user'] = [

                'id' => 0,

                'nama' =>
                    $dummy['nama'],

                'email' =>
                    $dummy['email'],

                'role' =>
                    $dummy['role']
            ];

            if ($role === 'admin') {

                $this->redirect(
                    'admin/dashboard'
                );

            } else {

                $this->redirect(
                    'guru/dashboard'
                );
            }

            return;
        }

        // =====================================================
        // LOGIN GAGAL
        // =====================================================

        $_SESSION['login_error'] =
            'Email atau password salah.';

        $this->redirect('login');
    }

    // =========================================================
    // LOGOUT
    // =========================================================
    public function logout(): void {

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION = [];

        if (ini_get("session.use_cookies")) {

            $params =
                session_get_cookie_params();

            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        session_destroy();

        $this->redirect('login');
    }

    // =========================================================
    // LUPA PASSWORD PAGE
    // =========================================================
    public function lupaPassword(): void {

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->view(
            'auth/lupa_password',
            [
                'title' =>
                    'Lupa Password – Absensi QR'
            ]
        );
    }

    // =========================================================
    // PROSES LUPA PASSWORD
    // =========================================================
    public function prosesLupaPassword(): void {

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $email =
            trim($_POST['email'] ?? '');

        // Validasi email
        if (
            empty($email) ||
            !filter_var(
                $email,
                FILTER_VALIDATE_EMAIL
            )
        ) {

            $_SESSION['lupa_error'] =
                'Format email tidak valid.';

            $this->redirect(
                'lupa-password'
            );

            return;
        }

        // Cari email
        $foundGuru = Database::request(
            'GET',
            'guru?email=eq.' .
            urlencode($email) .
            '&limit=1'
        );

        $foundAdmin = Database::request(
            'GET',
            'admin?email=eq.' .
            urlencode($email) .
            '&limit=1'
        );

        $_SESSION['lupa_success'] = true;

        // Jika ditemukan
        if (
            (
                !empty($foundGuru) &&
                !isset($foundGuru['error'])
            ) ||
            (
                !empty($foundAdmin) &&
                !isset($foundAdmin['error'])
            )
        ) {

            $token =
                bin2hex(
                    random_bytes(32)
                );

            $_SESSION['reset_token'] =
                $token;

            $_SESSION['dev_reset_link'] =
                "http://" .
                $_SERVER['HTTP_HOST'] .
                BASE_URL .
                "/?url=reset-password&token=" .
                $token;
        }

        $this->redirect('lupa-password');
    }

    // =========================================================
    // REGISTER PAGE
    // =========================================================
    public function registerPage(): void {

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if ($this->isLoggedIn()) {

            $role =
                $_SESSION['user']['role']
                ?? 'guru';

            $this->redirect(
                $role . '/dashboard'
            );

            return;
        }

        $this->view('auth/register', [
            'title' =>
                'Register – Absensi QR'
        ]);
    }

    // =========================================================
    // REGISTER PROCESS
    // =========================================================
    public function doRegister(): void {

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $role =
            $_POST['role']
            ?? 'guru';

        $nama =
            trim($_POST['nama'] ?? '');

        $email =
            trim($_POST['email'] ?? '');

        $noHp =
            trim($_POST['no_hp'] ?? '');

        $password =
            $_POST['password'] ?? '';

        $konfirm =
            $_POST['konfirm']
            ?? $_POST['konfirmasi_password']
            ?? '';

        // =====================================================
        // VALIDASI ROLE
        // =====================================================

        if (!in_array($role, ['guru', 'admin'])) {
            $role = 'guru';
        }

        // =====================================================
        // VALIDASI FIELD KOSONG
        // =====================================================

        if (
            empty($nama) ||
            empty($email) ||
            empty($password)
        ) {

            $_SESSION['reg_error'] =
                'Semua field wajib diisi.';

            $this->redirect('login');
            return;
        }

        // =====================================================
        // VALIDASI EMAIL
        // =====================================================

        if (
            !filter_var(
                $email,
                FILTER_VALIDATE_EMAIL
            )
        ) {

            $_SESSION['reg_error'] =
                'Format email tidak valid.';

            $this->redirect('login');
            return;
        }

        // =====================================================
        // VALIDASI PASSWORD
        // =====================================================

        if (strlen($password) < 6) {

            $_SESSION['reg_error'] =
                'Password minimal 6 karakter.';

            $this->redirect('login');
            return;
        }

        // =====================================================
        // KONFIRMASI PASSWORD
        // =====================================================

        if ($password !== $konfirm) {

            $_SESSION['reg_error'] =
                'Konfirmasi password tidak cocok.';

            $this->redirect('login');
            return;
        }

        // =====================================================
        // CEK EMAIL SUDAH ADA
        // =====================================================

        $cekReg = Database::request(
            'GET',
            'registrasi?email=eq.' .
            urlencode($email) .
            '&limit=1'
        );

        $cekGuru = Database::request(
            'GET',
            'guru?email=eq.' .
            urlencode($email) .
            '&limit=1'
        );

        $cekAdmin = Database::request(
            'GET',
            'admin?email=eq.' .
            urlencode($email) .
            '&limit=1'
        );

        if (
            (
                !empty($cekReg) &&
                !isset($cekReg['error'])
            ) ||
            (
                !empty($cekGuru) &&
                !isset($cekGuru['error'])
            ) ||
            (
                !empty($cekAdmin) &&
                !isset($cekAdmin['error'])
            )
        ) {

            $_SESSION['reg_error'] =
                'Email sudah digunakan atau menunggu verifikasi.';

            $this->redirect('login');
            return;
        }

        // =====================================================
        // SIMPAN REGISTRASI
        // =====================================================

        $result = Database::request(
            'POST',
            'registrasi',
            [

                'nama' =>
                    $nama,

                'email' =>
                    $email,

                'no_hp' =>
                    $noHp,

                'role' =>
                    $role,

                'password' =>
                    password_hash(
                        $password,
                        PASSWORD_BCRYPT
                    ),

                'status' =>
                    'pending',

                'created_at' =>
                    date('c'),

                'updated_at' =>
                    date('c')
            ]
        );

        // =====================================================
        // JIKA GAGAL
        // =====================================================

        if (
            isset($result['error']) ||
            empty($result)
        ) {

            $_SESSION['reg_error'] =
                'Gagal melakukan registrasi.';

            $this->redirect('login');
            return;
        }

        // =====================================================
        // SUCCESS
        // =====================================================

        $_SESSION['reg_success'] =
            'Registrasi berhasil! Tunggu verifikasi admin.';

        $this->redirect('login');
    }

    // =========================================================
    // CHECK LOGIN
    // =========================================================
    protected function isLoggedIn(): bool {

        return isset($_SESSION['user']);
    }
}