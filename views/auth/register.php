<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$error   = $_SESSION['reg_error']   ?? null; unset($_SESSION['reg_error']);
$success = $_SESSION['reg_success'] ?? null; unset($_SESSION['reg_success']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register – Absensi QR</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            min-height: 100vh;
            background: linear-gradient(135deg, #0f1729 0%, #1e3a8a 50%, #1e1b4b 100%);
            display: flex; align-items: center; justify-content: center;
            padding: 20px;
        }
        .register-wrap {
            display: flex;
            width: 100%; max-width: 980px;
            min-height: 600px;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 30px 80px rgba(0,0,0,0.4);
        }
        /* LEFT PANEL */
        .reg-left {
            width: 380px; flex-shrink: 0;
            background: linear-gradient(160deg, #2563eb 0%, #1d4ed8 40%, #1e1b4b 100%);
            padding: 48px 40px;
            display: flex; flex-direction: column;
            justify-content: space-between;
            position: relative; overflow: hidden;
        }
        .reg-left::before {
            content: ''; position: absolute;
            top: -60px; right: -60px;
            width: 220px; height: 220px;
            border-radius: 50%;
            background: rgba(255,255,255,0.06);
        }
        .reg-left::after {
            content: ''; position: absolute;
            bottom: -40px; left: -40px;
            width: 180px; height: 180px;
            border-radius: 50%;
            background: rgba(255,255,255,0.04);
        }
        .brand { display: flex; align-items: center; gap: 12px; position: relative; z-index: 1; }
        .brand-icon {
            width: 44px; height: 44px;
            background: rgba(255,255,255,0.2);
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 20px; color: white;
        }
        .brand-name { font-size: 16px; font-weight: 800; color: white; }
        .brand-sub  { font-size: 11px; color: rgba(255,255,255,0.55); }
        .left-hero { position: relative; z-index: 1; }
        .left-hero h2 { font-size: 26px; font-weight: 800; color: white; line-height: 1.3; margin-bottom: 12px; }
        .left-hero p  { font-size: 13px; color: rgba(255,255,255,0.65); line-height: 1.7; }
        .left-features { position: relative; z-index: 1; }
        .feature-item {
            display: flex; align-items: center; gap: 10px;
            margin-bottom: 10px;
            font-size: 13px; color: rgba(255,255,255,0.8);
        }
        .feature-item i { color: #60a5fa; font-size: 14px; }

        /* RIGHT PANEL */
        .reg-right {
            flex: 1;
            background: white;
            padding: 40px 44px;
            overflow-y: auto;
        }
        .reg-right h3 { font-size: 22px; font-weight: 800; color: #1e1b4b; margin-bottom: 4px; }
        .reg-right .sub { font-size: 13px; color: #6b7280; margin-bottom: 24px; }

        /* ROLE TABS */
        .role-tabs { display: flex; gap: 0; margin-bottom: 22px; border: 1.5px solid #e5e7eb; border-radius: 10px; overflow: hidden; }
        .role-tab {
            flex: 1; padding: 10px;
            border: none; background: white;
            font-size: 13px; font-weight: 600;
            color: #6b7280; cursor: pointer;
            display: flex; align-items: center; justify-content: center; gap: 7px;
            transition: all 0.2s;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        .role-tab.active { background: #2563eb; color: white; }
        .role-tab:first-child { border-right: 1.5px solid #e5e7eb; }

        /* FORM */
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
        .form-group { margin-bottom: 14px; }
        .form-label { display: block; font-size: 11.5px; font-weight: 700; color: #374151; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.4px; }
        .input-wrap { position: relative; }
        .input-wrap i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #9ca3af; font-size: 14px; }
        .form-input {
            width: 100%; padding: 10px 12px 10px 36px;
            border: 1.5px solid #e5e7eb;
            border-radius: 10px; font-size: 13px;
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: #111827; outline: none;
            transition: border-color 0.15s, box-shadow 0.15s;
        }
        .form-input:focus { border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,0.1); }
        .form-input.no-icon { padding-left: 12px; }
        .pw-toggle {
            position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
            background: none; border: none; cursor: pointer;
            color: #9ca3af; font-size: 14px;
        }

        /* STRENGTH BAR */
        .strength-bar { height: 4px; border-radius: 99px; background: #e5e7eb; margin-top: 6px; overflow: hidden; }
        .strength-fill { height: 100%; border-radius: 99px; transition: width 0.3s, background 0.3s; width: 0%; }
        .strength-text { font-size: 11px; margin-top: 4px; font-weight: 600; }

        /* ALERT */
        .alert {
            padding: 11px 14px; border-radius: 10px;
            font-size: 13px; font-weight: 500;
            display: flex; align-items: center; gap: 9px;
            margin-bottom: 16px;
        }
        .alert-error   { background: #fee2e2; color: #dc2626; }
        .alert-success { background: #dcfce7; color: #16a34a; }

        /* SUBMIT BTN */
        .btn-register {
            width: 100%; padding: 12px;
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: white; border: none; border-radius: 10px;
            font-size: 14px; font-weight: 700;
            cursor: pointer; transition: all 0.2s;
            font-family: 'Plus Jakarta Sans', sans-serif;
            display: flex; align-items: center; justify-content: center; gap: 8px;
            margin-top: 6px;
        }
        .btn-register:hover { background: linear-gradient(135deg,#1d4ed8,#1e3a8a); box-shadow: 0 6px 20px rgba(37,99,235,0.35); transform: translateY(-1px); }
        .btn-register:active { transform: translateY(0); }

        .login-link { text-align: center; font-size: 13px; color: #6b7280; margin-top: 16px; }
        .login-link a { color: #2563eb; font-weight: 700; text-decoration: none; }
        .login-link a:hover { text-decoration: underline; }

        /* GURU FIELDS */
        #guruFields { display: block; }
        #adminFields { display: none; }

        @media (max-width: 760px) {
            .register-wrap { flex-direction: column; max-width: 460px; }
            .reg-left { width: 100%; padding: 28px 28px; min-height: auto; }
            .left-features { display: none; }
            .reg-right { padding: 28px 24px; }
            .form-row { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<div class="register-wrap">

    <!-- LEFT -->
    <div class="reg-left">
        <div class="brand">
            <div class="brand-icon"><i class="fa fa-qrcode"></i></div>
            <div>
                <div class="brand-name">ABSENSI QR</div>
                <div class="brand-sub">MAN 2 Banyumas</div>
            </div>
        </div>
        <div class="left-hero">
            <h2>Buat Akun Baru</h2>
            <p>Daftarkan diri sebagai Guru atau Admin untuk mengakses sistem absensi digital berbasis QR Code.</p>
        </div>
        <div class="left-features">
            <div class="feature-item"><i class="fa fa-shield-halved"></i> Data tersimpan aman di Supabase</div>
            <div class="feature-item"><i class="fa fa-bolt"></i> Proses pendaftaran cepat</div>
            <div class="feature-item"><i class="fa fa-mobile-screen"></i> Akses dari mana saja</div>
            <div class="feature-item"><i class="fa fa-qrcode"></i> Scan QR siswa real-time</div>
        </div>
    </div>

    <!-- RIGHT -->
    <div class="reg-right">
        <h3>Daftar Akun</h3>
        <p class="sub">Isi data lengkap untuk membuat akun baru</p>

        <?php if ($error): ?>
        <div class="alert alert-error"><i class="fa fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
        <div class="alert alert-success"><i class="fa fa-circle-check"></i> <?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <!-- ROLE TABS -->
        <div class="role-tabs">
            <button class="role-tab active" id="tabGuru" onclick="setRole('guru')">
                <i class="fa fa-chalkboard-user"></i> Guru
            </button>
            <button class="role-tab" id="tabAdmin" onclick="setRole('admin')">
                <i class="fa fa-user-shield"></i> Admin
            </button>
        </div>

        <form method="POST" action="?url=auth/doRegister" id="regForm">
            <input type="hidden" name="role" id="roleInput" value="guru">

            <!-- NAMA + EMAIL -->
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Nama Lengkap *</label>
                    <div class="input-wrap">
                        <i class="fa fa-user"></i>
                        <input type="text" name="nama" class="form-input" placeholder="Nama lengkap" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Email *</label>
                    <div class="input-wrap">
                        <i class="fa fa-envelope"></i>
                        <input type="email" name="email" class="form-input" placeholder="email@example.com" required>
                    </div>
                </div>
            </div>

            <!-- GURU FIELDS -->
            <div id="guruFields">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">NIP</label>
                        <div class="input-wrap">
                            <i class="fa fa-id-card"></i>
                            <input type="text" name="nip" class="form-input" placeholder="Nomor Induk Pegawai">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">No. HP</label>
                        <div class="input-wrap">
                            <i class="fa fa-phone"></i>
                            <input type="text" name="no_hp" class="form-input" placeholder="08xxxxxxxxxx">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Wali Kelas</label>
                    <div class="input-wrap">
                        <i class="fa fa-school"></i>
                        <select name="kelas" class="form-input">
                            <option value="">— Pilih Kelas (opsional) —</option>
                            <?php
                            $kelasList = ['X RPL 1','X RPL 2','XI RPL 1','XI RPL 2','XII RPL 1','XII RPL 2','X IPA 1','XI IPA 1','XII IPA 1'];
                            foreach ($kelasList as $k): ?>
                            <option value="<?= $k ?>"><?= $k ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <!-- PASSWORD -->
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Password *</label>
                    <div class="input-wrap">
                        <i class="fa fa-lock"></i>
                        <input type="password" name="password" id="pwInput" class="form-input"
                               placeholder="Min. 6 karakter" oninput="checkStrength(this.value)" required>
                        <button type="button" class="pw-toggle" onclick="togglePw('pwInput', this)">
                            <i class="fa fa-eye"></i>
                        </button>
                    </div>
                    <div class="strength-bar"><div class="strength-fill" id="strengthFill"></div></div>
                    <div class="strength-text" id="strengthText" style="color:#9ca3af;">Masukkan password</div>
                </div>
                <div class="form-group">
                    <label class="form-label">Konfirmasi Password *</label>
                    <div class="input-wrap">
                        <i class="fa fa-lock"></i>
                        <input type="password" name="konfirm" id="konfirmInput" class="form-input"
                               placeholder="Ulangi password" required>
                        <button type="button" class="pw-toggle" onclick="togglePw('konfirmInput', this)">
                            <i class="fa fa-eye"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- TERMS -->
            <div class="form-group" style="display:flex;align-items:center;gap:8px;">
                <input type="checkbox" id="terms" required style="accent-color:#2563eb;width:15px;height:15px;">
                <label for="terms" style="font-size:12.5px;color:#6b7280;cursor:pointer;">
                    Saya menyetujui <a href="#" style="color:#2563eb;font-weight:600;">Syarat & Ketentuan</a> penggunaan sistem
                </label>
            </div>

            <button type="submit" class="btn-register">
                <i class="fa fa-user-plus"></i> Buat Akun Sekarang
            </button>
        </form>

        <p class="login-link">Sudah punya akun? <a href="?url=login">Masuk di sini</a></p>
    </div>
</div>

<script>
function setRole(role) {
    document.getElementById('roleInput').value = role;
    document.getElementById('tabGuru').classList.toggle('active', role === 'guru');
    document.getElementById('tabAdmin').classList.toggle('active', role === 'admin');
    document.getElementById('guruFields').style.display = role === 'guru' ? 'block' : 'none';
}

function togglePw(inputId, btn) {
    const input = document.getElementById(inputId);
    const isText = input.type === 'text';
    input.type = isText ? 'password' : 'text';
    btn.querySelector('i').className = isText ? 'fa fa-eye' : 'fa fa-eye-slash';
}

function checkStrength(val) {
    const fill = document.getElementById('strengthFill');
    const text = document.getElementById('strengthText');
    let score = 0;
    if (val.length >= 6)  score++;
    if (val.length >= 10) score++;
    if (/[A-Z]/.test(val)) score++;
    if (/[0-9]/.test(val)) score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;
    const levels = [
        { w: '0%',   color: '#e5e7eb', label: 'Masukkan password', tc: '#9ca3af' },
        { w: '25%',  color: '#ef4444', label: 'Lemah',   tc: '#ef4444' },
        { w: '50%',  color: '#f97316', label: 'Cukup',   tc: '#f97316' },
        { w: '75%',  color: '#eab308', label: 'Bagus',   tc: '#eab308' },
        { w: '90%',  color: '#22c55e', label: 'Kuat',    tc: '#22c55e' },
        { w: '100%', color: '#16a34a', label: 'Sangat Kuat', tc: '#16a34a' },
    ];
    const l = levels[Math.min(score, 5)];
    fill.style.width = l.w;
    fill.style.background = l.color;
    text.textContent = l.label;
    text.style.color = l.tc;
}

// Validasi konfirm password realtime
document.getElementById('konfirmInput').addEventListener('input', function() {
    const pw  = document.getElementById('pwInput').value;
    const ok  = this.value === pw && pw.length > 0;
    this.style.borderColor = this.value.length === 0 ? '' : ok ? '#22c55e' : '#ef4444';
});
</script>
</body>
</html>