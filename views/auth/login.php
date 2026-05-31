<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login – Absensi QR</title>

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <style>
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --blue: #2563eb;
            --blue-dark: #1d4ed8;
            --blue-soft: #eff6ff;
            --text: #0f172a;
            --muted: #64748b;
            --border: #e2e8f0;
            --bg: #f8fafc;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            min-height: 100vh;
            display: flex;
            background: var(--bg);
            overflow: auto;
        }

        .left-panel {
            flex: 1;
            position: relative;
            display: none;
        }

        @media(min-width:1024px){
            .left-panel {
                display: block;
            }
        }

        .left-panel img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .left-panel::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(
                135deg,
                rgba(15,23,42,0.72) 0%,
                rgba(37,99,235,0.45) 100%
            );
        }

        .left-badge {
            position: absolute;
            bottom: 48px;
            left: 48px;
            z-index: 10;
            color: white;
        }

        .left-badge h2 {
            font-size: 32px;
            font-weight: 800;
            line-height: 1.2;
            margin-bottom: 8px;
        }

        .left-badge p {
            font-size: 15px;
            opacity: 0.8;
            max-width: 340px;
            line-height: 1.6;
        }

        .right-panel {
            width: 100%;
            max-width: 520px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 32px 48px;
            background: white;
            box-shadow: -8px 0 40px rgba(0,0,0,0.08);
        }

        .form-wrap {
            width: 100%;
        }

        .logo-ring {
            width: 68px;
            height: 68px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--blue-dark), #60a5fa);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 14px;
            box-shadow: 0 8px 24px rgba(37,99,235,0.35);
        }

        .logo-ring i {
            font-size: 26px;
            color: white;
        }

        .form-title {
            text-align: center;
            font-size: 24px;
            font-weight: 800;
            color: var(--text);
            margin-bottom: 4px;
        }

        .form-sub {
            text-align: center;
            font-size: 13px;
            color: var(--muted);
            margin-bottom: 20px;
        }

        .tabs {
            display: flex;
            background: var(--bg);
            border-radius: 12px;
            padding: 4px;
            margin-bottom: 18px;
            border: 1px solid var(--border);
        }

        .tab-btn {
            flex: 1;
            padding: 10px;
            border: none;
            background: none;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            color: var(--muted);
        }

        .tab-btn.active {
            background: white;
            color: var(--blue);
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .role-selector {
            display: flex;
            gap: 12px;
            margin-bottom: 16px;
        }

        .role-card {
            flex: 1;
            border: 2px solid var(--border);
            border-radius: 12px;
            padding: 12px;
            text-align: center;
            cursor: pointer;
            transition: .2s;
        }

        .role-card.selected {
            border-color: var(--blue);
            background: var(--blue-soft);
        }

        .role-card input {
            display: none;
        }

        .role-card i {
            display: block;
            margin-bottom: 6px;
            font-size: 20px;
        }

        .input-group {
            position: relative;
            margin-bottom: 14px;
        }

        .input-group i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
        }

        .input-group input {
            width: 100%;
            padding: 12px 14px 12px 42px;
            border: 1.5px solid var(--border);
            border-radius: 12px;
            background: var(--bg);
            outline: none;
            font-size: 14px;
        }

        .input-group input:focus {
            border-color: var(--blue);
            background: white;
        }

        .toggle-pass {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            border: none;
            background: none;
            cursor: pointer;
            color: #94a3b8;
        }

        .row-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }

        .check-label {
            font-size: 13px;
            color: var(--muted);
        }

        .link-muted {
            font-size: 13px;
            color: var(--blue);
            text-decoration: none;
            font-weight: 600;
        }

        .btn-submit {
            width: 100%;
            padding: 13px;
            border: none;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--blue-dark), var(--blue));
            color: white;
            font-weight: 700;
            cursor: pointer;
            font-size: 15px;
        }

        .btn-submit:hover {
            opacity: .95;
        }

        #formRegister,
        #formLupa {
            display: none;
        }

        .alert-error,
        .alert-success {
            padding: 12px 14px;
            border-radius: 10px;
            margin-bottom: 14px;
            font-size: 13px;
        }

        .alert-error {
            background: #fee2e2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }

        .alert-success {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
        }

        .info-banner {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 10px;
            padding: 12px;
            font-size: 12px;
            color: #1d4ed8;
            margin-bottom: 14px;
        }

        .form-footer {
            margin-top: 20px;
            text-align: center;
            font-size: 11px;
            color: #94a3b8;
        }
    </style>
</head>
<body>

<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$baseUrl = rtrim(BASE_URL, '/') . '/';
?>

<div class="left-panel">
    <img src="<?= $baseUrl ?>assets/image/sekolah.jpeg" alt="Sekolah">

    <div class="left-badge">
        <h2>Sistem Absensi<br>Digital MAN 2</h2>

        <p>
            Catat kehadiran siswa secara real-time
            dengan teknologi QR Code.
        </p>
    </div>
</div>

<div class="right-panel">
<div class="form-wrap">

    <div class="logo-ring">
        <i class="fa fa-qrcode"></i>
    </div>

    <h1 class="form-title">ABSENSI QR</h1>
    <p class="form-sub">Sistem Absensi Digital Sekolah</p>

    <div class="tabs">
        <button type="button" class="tab-btn active" id="tabLogin" onclick="switchTab('login')">
            <i class="fa fa-right-to-bracket"></i> Masuk
        </button>

        <button type="button" class="tab-btn" id="tabRegister" onclick="switchTab('register')">
            <i class="fa fa-user-plus"></i> Registrasi
        </button>
    </div>

    <?php if (!empty($_SESSION['login_error'])): ?>
        <div class="alert-error">
            <?= htmlspecialchars($_SESSION['login_error']) ?>
        </div>
        <?php unset($_SESSION['login_error']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['reg_success'])): ?>
        <div class="alert-success">
            <?= htmlspecialchars($_SESSION['reg_success']) ?>
        </div>
        <?php unset($_SESSION['reg_success']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['reg_error'])): ?>
        <div class="alert-error">
            <?= htmlspecialchars($_SESSION['reg_error']) ?>
        </div>
        <?php unset($_SESSION['reg_error']); ?>
    <?php endif; ?>

    <!-- LOGIN -->
    <form id="formLogin" action="<?= $baseUrl ?>?url=auth/doLogin" method="POST">

        <div class="role-selector">

            <label class="role-card selected" id="cardGuru">
                <input
                    type="radio"
                    name="role"
                    value="guru"
                    checked
                    onchange="selectRole('guru')"
                >

                <i class="fa fa-chalkboard-user"></i>
                <span>Guru</span>
            </label>

            <label class="role-card" id="cardAdmin">
                <input
                    type="radio"
                    name="role"
                    value="admin"
                    onchange="selectRole('admin')"
                >

                <i class="fa fa-shield-halved"></i>
                <span>Admin</span>
            </label>

        </div>

        <div class="input-group">
            <i class="fa fa-user"></i>

            <input
                type="text"
                name="email"
                placeholder="Email"
                required
            >
        </div>

        <div class="input-group">
            <i class="fa fa-lock"></i>

            <input
                type="password"
                name="password"
                id="loginPass"
                placeholder="Password"
                required
            >

            <button type="button" class="toggle-pass" onclick="togglePass('loginPass', this)">
                <i class="fa fa-eye"></i>
            </button>
        </div>

        <div class="row-meta">
            <label class="check-label">
                <input type="checkbox" name="remember">
                Ingat saya
            </label>

            <a href="#" class="link-muted" onclick="switchTab('lupa'); return false;">
                Lupa password?
            </a>
        </div>

        <button type="submit" class="btn-submit">
            <i class="fa fa-right-to-bracket"></i>
            Masuk Sekarang
        </button>

    </form>

    <!-- REGISTER -->
    <form id="formRegister" action="<?= $baseUrl ?>?url=auth/doRegister" method="POST">

        <div class="info-banner">
            Akun akan diverifikasi admin terlebih dahulu.
        </div>

        <div class="input-group">
            <i class="fa fa-id-card"></i>
            <input type="text" name="nama" placeholder="Nama Lengkap" required>
        </div>

        <div class="input-group">
            <i class="fa fa-envelope"></i>
            <input type="email" name="email" placeholder="Email" required>
        </div>

        <div class="input-group">
            <i class="fa fa-phone"></i>
            <input type="text" name="no_hp" placeholder="No HP">
        </div>

        <div class="role-selector">

            <label class="role-card selected" id="regCardGuru">
                <input type="radio" name="role" value="guru" checked onchange="selectRegRole('guru')">
                <i class="fa fa-chalkboard-user"></i>
                <span>Guru</span>
            </label>

            <label class="role-card" id="regCardAdmin">
                <input type="radio" name="role" value="admin" onchange="selectRegRole('admin')">
                <i class="fa fa-shield-halved"></i>
                <span>Admin</span>
            </label>

        </div>

        <div class="input-group">
            <i class="fa fa-lock"></i>

            <input
                type="password"
                name="password"
                id="regPass"
                placeholder="Password"
                required
            >

            <button type="button" class="toggle-pass" onclick="togglePass('regPass', this)">
                <i class="fa fa-eye"></i>
            </button>
        </div>

        <div class="input-group">
            <i class="fa fa-lock"></i>

            <input
                type="password"
                name="konfirmasi_password"
                id="regPass2"
                placeholder="Konfirmasi Password"
                required
            >

            <button type="button" class="toggle-pass" onclick="togglePass('regPass2', this)">
                <i class="fa fa-eye"></i>
            </button>
        </div>

        <button type="submit" class="btn-submit">
            Daftar Sekarang
        </button>

    </form>

    <!-- LUPA PASSWORD -->
    <form id="formLupa" action="<?= $baseUrl ?>?url=auth/prosesLupaPassword" method="POST">

        <div class="info-banner">
            Masukkan email untuk reset password.
        </div>

        <div class="input-group">
            <i class="fa fa-envelope"></i>

            <input
                type="email"
                name="email"
                placeholder="Email"
                required
            >
        </div>

        <button type="submit" class="btn-submit">
            Kirim Link Reset
        </button>

    </form>

    <p class="form-footer">
        © 2026 Sistem Absensi Sekolah · MAN 2 Banyumas
    </p>

</div>
</div>

<script>

const BASE_URL = '<?= $baseUrl ?>';

function switchTab(tab) {

    document.getElementById('formLogin').style.display =
        tab === 'login' ? 'block' : 'none';

    document.getElementById('formRegister').style.display =
        tab === 'register' ? 'block' : 'none';

    document.getElementById('formLupa').style.display =
        tab === 'lupa' ? 'block' : 'none';

    document.getElementById('tabLogin').classList.toggle(
        'active',
        tab === 'login'
    );

    document.getElementById('tabRegister').classList.toggle(
        'active',
        tab === 'register'
    );
}

function selectRole(role) {

    document.getElementById('cardGuru').classList.toggle(
        'selected',
        role === 'guru'
    );

    document.getElementById('cardAdmin').classList.toggle(
        'selected',
        role === 'admin'
    );
}

function selectRegRole(role) {

    document.getElementById('regCardGuru').classList.toggle(
        'selected',
        role === 'guru'
    );

    document.getElementById('regCardAdmin').classList.toggle(
        'selected',
        role === 'admin'
    );
}

function togglePass(id, btn) {

    const input = document.getElementById(id);
    const icon  = btn.querySelector('i');

    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'fa fa-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'fa fa-eye';
    }
}

</script>

</body>
</html>