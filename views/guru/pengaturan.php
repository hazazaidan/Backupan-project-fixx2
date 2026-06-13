<?php
if (session_status() === PHP_SESSION_NONE) session_start();
date_default_timezone_set('Asia/Jakarta');

$u       = $user ?? $_SESSION['user'] ?? [];
$nama    = $u['nama']  ?? 'Guru';
$kelas   = $u['kelas'] ?? 'XI RPL 1';
$inisial = strtoupper(implode('', array_map(fn($w) => $w[0], array_slice(explode(' ', $nama), 0, 2))));

$bulan = [
    'January'=>'Januari','February'=>'Februari','March'=>'Maret','April'=>'April',
    'May'=>'Mei','June'=>'Juni','July'=>'Juli','August'=>'Agustus',
    'September'=>'September','October'=>'Oktober','November'=>'November','December'=>'Desember'
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan – Absensi QR</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        * { font-family: 'Plus Jakarta Sans', sans-serif; }
        :root {
            --sidebar-bg: #0f1729;
            --sidebar-hover: #1a2540;
            --sidebar-active: #2563eb;
            --accent: #2563eb;
            --accent-light: #3b82f6;
            --content-bg: #f1f5f9;
        }
        body { background: var(--content-bg); }
        .sidebar { background: var(--sidebar-bg); width: 260px; min-height: 100vh; display: flex; flex-direction: column; flex-shrink: 0; }
        .sidebar-brand { padding: 20px 20px 16px; border-bottom: 1px solid rgba(255,255,255,0.06); }
        .brand-icon { background: var(--accent); width: 42px; height: 42px; border-radius: 10px; display: flex; align-items: center; justify-content: center; }
        .user-card { margin: 16px 14px; background: rgba(255,255,255,0.06); border-radius: 12px; padding: 12px 14px; display: flex; align-items: center; gap: 12px; }
        .user-avatar { width: 40px; height: 40px; border-radius: 50%; background: var(--accent); display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 14px; color: white; flex-shrink: 0; }
        .nav-section-label { padding: 12px 20px 6px; font-size: 10px; font-weight: 700; letter-spacing: 1.2px; color: rgba(255,255,255,0.35); text-transform: uppercase; }
        .nav-item { display: flex; align-items: center; gap: 12px; padding: 11px 16px; margin: 2px 10px; border-radius: 10px; color: rgba(255,255,255,0.6); font-size: 14px; font-weight: 500; text-decoration: none; transition: all 0.2s; position: relative; }
        .nav-item:hover { background: var(--sidebar-hover); color: white; }
        .nav-item.active { background: var(--accent); color: white; }
        .nav-icon { width: 18px; text-align: center; }
        .topbar { background: white; border-bottom: 1px solid #e2e8f0; padding: 14px 28px; display: flex; align-items: center; justify-content: space-between; }
        .content-scroll { flex: 1; overflow-y: auto; background: var(--content-bg); }
        .notif-dot { position: absolute; top: -2px; right: -2px; width: 8px; height: 8px; background: #f97316; border-radius: 50%; border: 2px solid white; }
        /* FORM STYLES */
        .card-section { background: white; border-radius: 16px; box-shadow: 0 1px 4px rgba(0,0,0,0.06); overflow: hidden; }
        .card-header { padding: 18px 24px; border-bottom: 1px solid #f1f5f9; display: flex; align-items: center; gap: 10px; }
        .card-icon { width: 32px; height: 32px; border-radius: 9px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .card-body { padding: 24px; display: flex; flex-direction: column; gap: 16px; }
        .form-group { display: flex; flex-direction: column; gap: 6px; }
        .form-label { font-size: 12px; font-weight: 600; color: #475569; }
        .form-input-wrap { position: relative; }
        .form-input-wrap .fi { position: absolute; left: 11px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 14px; pointer-events: none; }
        .form-input { width: 100%; border: 1.5px solid #e2e8f0; border-radius: 10px; padding: 10px 12px 10px 34px; font-size: 14px; color: #0f172a; outline: none; transition: border-color 0.2s; font-family: inherit; }
        .form-input:focus { border-color: #2563eb; }
        .form-input-pr { padding-right: 40px; }
        .toggle-pass { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #94a3b8; padding: 0; display: flex; align-items: center; }
        .btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 10px 20px; border-radius: 10px; font-size: 14px; font-weight: 600; cursor: pointer; border: none; transition: all 0.15s; font-family: inherit; width: 100%; margin-top: 4px; }
        .btn-blue   { background: #2563eb; color: white; }
        .btn-blue:hover { background: #1d4ed8; }
        .btn-orange { background: #ea580c; color: white; }
        .btn-orange:hover { background: #c2410c; }
        /* TOAST */
        .toast-container { position: fixed; bottom: 24px; right: 24px; z-index: 999; }
        .toast { display: flex; align-items: center; gap: 10px; padding: 12px 18px; border-radius: 12px; font-size: 13px; font-weight: 500; box-shadow: 0 8px 24px rgba(0,0,0,.12); animation: slideUp .3s ease; min-width: 260px; }
        .toast-success { background: #fff; color: #15803d; border-left: 4px solid #16a34a; }
        .toast-danger  { background: #fff; color: #b91c1c; border-left: 4px solid #dc2626; }
        @keyframes slideUp { from { opacity:0; transform:translateY(12px); } to { opacity:1; transform:translateY(0); } }
        /* STRENGTH */
        #strengthWrap { margin-top: 8px; display: none; }
        .strength-track { height: 4px; border-radius: 99px; background: #f1f5f9; overflow: hidden; }
        #strengthBar { height: 100%; width: 0; border-radius: 99px; transition: .3s; }
        #matchMsg { font-size: 10px; margin-top: 4px; display: none; }
    </style>
</head>

<body>
<div class="flex" style="min-height:100vh;">

    <!-- ═══ SIDEBAR (sama persis dengan dashboard) ═══ -->
    <aside class="sidebar">
        <div class="sidebar-brand flex items-center gap-3">
            <div class="brand-icon"><i class="fa fa-qrcode text-white text-lg"></i></div>
            <div>
                <p class="text-white font-bold text-base leading-tight">ABSENSI QR</p>
                <p class="text-xs" style="color:rgba(255,255,255,0.45);">Man 2 Banyumas</p>
            </div>
        </div>
        <div class="user-card">
            <div class="user-avatar"><?= $inisial ?></div>
            <div>
                <p class="text-white font-semibold text-sm leading-tight"><?= htmlspecialchars($nama) ?></p>
                <p class="text-xs" style="color:rgba(255,255,255,0.45);">Guru – <?= htmlspecialchars($kelas) ?></p>
            </div>
        </div>
        <p class="nav-section-label">Menu Utama</p>
        <nav>
            <a href="?url=guru/dashboard"  class="nav-item"><i class="fa fa-home nav-icon"></i> Dashboard</a>
            <a href="?url=guru/kelas"      class="nav-item"><i class="fa fa-door-open nav-icon"></i> Kelas</a>
            <a href="?url=guru/riwayat"    class="nav-item"><i class="fa fa-clock-rotate-left nav-icon"></i> Riwayat Absensi</a>
            <a href="?url=guru/rekap"      class="nav-item"><i class="fa fa-layer-group nav-icon"></i> Rekap Kelas</a>
            <a href="?url=guru/monitoring" class="nav-item"><i class="fa fa-chart-line nav-icon"></i> Monitoring</a>
        </nav>
        <div style="border-top:1px solid rgba(255,255,255,0.07); padding-bottom:8px; margin-top:16px;">
            <p class="nav-section-label">Sistem</p>
            <a href="?url=guru/pengaturan" class="nav-item active"><i class="fa fa-gear nav-icon"></i> Pengaturan</a>
            <a href="?url=auth/logout"     class="nav-item"><i class="fa fa-right-from-bracket nav-icon"></i> Logout</a>
        </div>
    </aside>

    <!-- ═══ MAIN CONTENT ═══ -->
    <div class="content-scroll flex flex-col">

        <!-- TOPBAR -->
        <div class="topbar">
            <div>
                <h1 class="text-xl font-bold text-gray-800">Pengaturan</h1>
                <p class="text-sm text-gray-400">Kelola profil dan keamanan akun Anda</p>
            </div>
            <div class="flex items-center gap-5">
                <div class="relative cursor-pointer">
                    <i class="fa fa-bell text-gray-400 text-lg"></i>
                </div>
                <i class="fa fa-comment-dots text-gray-400 text-lg cursor-pointer"></i>
                <div class="flex items-center gap-3 pl-4" style="border-left:1px solid #e2e8f0;">
                    <div style="width:36px;height:36px;border-radius:50%;background:#2563eb;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:13px;color:white;"><?= $inisial ?></div>
                    <div>
                        <p class="text-sm font-semibold text-gray-800"><?= htmlspecialchars($nama) ?></p>
                        <p class="text-xs text-gray-400">Guru</p>
                    </div>
                    <i class="fa fa-chevron-down text-gray-400 text-xs ml-1"></i>
                </div>
            </div>
        </div>

        <!-- PAGE BODY -->
        <div class="p-6 flex flex-col gap-5">

            <!-- TOAST -->
            <?php if (isset($_SESSION['success'])): ?>
            <div class="toast-container" id="toastBox">
                <div class="toast toast-success" id="toastMsg">
                    <i class="fa fa-circle-check" style="flex-shrink:0;"></i>
                    <span><?= htmlspecialchars($_SESSION['success']) ?></span>
                </div>
            </div>
            <?php unset($_SESSION['success']); ?>
            <?php elseif (isset($_SESSION['error'])): ?>
            <div class="toast-container" id="toastBox">
                <div class="toast toast-danger" id="toastMsg">
                    <i class="fa fa-circle-xmark" style="flex-shrink:0;"></i>
                    <span><?= htmlspecialchars($_SESSION['error']) ?></span>
                </div>
            </div>
            <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <!-- PAGE HEADER -->
            <div class="flex items-center gap-3">
                <div style="width:40px;height:40px;background:#eff6ff;border-radius:12px;display:flex;align-items:center;justify-content:center;">
                    <i class="fa fa-gear" style="color:#2563eb;font-size:18px;"></i>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-gray-800">Pengaturan Akun</h1>
                    <p class="text-xs text-gray-400 mt-0.5">Update informasi profil dan kata sandi Anda</p>
                </div>
            </div>

            <!-- PROFILE BANNER -->
            <div style="background:linear-gradient(135deg,#1e40af 0%,#2563eb 60%,#3b82f6 100%);border-radius:16px;padding:24px;position:relative;overflow:hidden;box-shadow:0 4px 20px rgba(37,99,235,0.35);">
                <div style="position:absolute;top:-30px;right:-30px;width:140px;height:140px;border-radius:50%;background:rgba(255,255,255,.07);"></div>
                <div style="position:absolute;bottom:-50px;right:80px;width:180px;height:180px;border-radius:50%;background:rgba(255,255,255,.05);"></div>
                <div class="flex items-center gap-4" style="position:relative;">
                    <div style="width:64px;height:64px;background:rgba(255,255,255,.2);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:22px;font-weight:700;color:#fff;border:3px solid rgba(255,255,255,.3);flex-shrink:0;">
                        <?= $inisial ?>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-white"><?= htmlspecialchars($u['nama'] ?? 'Nama Guru') ?></h2>
                        <p class="text-sm mt-1" style="color:rgba(255,255,255,.75);"><?= htmlspecialchars($u['email'] ?? '') ?></p>
                        <span class="inline-flex items-center gap-1 mt-2 text-white text-xs font-semibold" style="background:rgba(255,255,255,.15);border-radius:99px;padding:3px 10px;">
                            <i class="fa fa-shield-halved" style="font-size:11px;"></i> Akun Aktif
                        </span>
                    </div>
                </div>
            </div>

            <!-- TWO COLUMN -->
            <div class="grid gap-5" style="grid-template-columns:1fr 1fr;">

                <!-- EDIT PROFIL -->
                <div class="card-section">
                    <div class="card-header">
                        <div class="card-icon" style="background:#eff6ff;">
                            <i class="fa fa-user-pen" style="color:#2563eb;font-size:14px;"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-800" style="font-size:14px;">Edit Profil</h3>
                            <p class="text-xs text-gray-400 mt-0.5">Perbarui nama dan email akun</p>
                        </div>
                    </div>
                    <form action="<?= BASE_URL ?>?url=pengaturan/updateProfil" method="POST" class="card-body">
                        <div class="form-group">
                            <label class="form-label">Nama Lengkap</label>
                            <div class="form-input-wrap">
                                <i class="fa fa-user fi"></i>
                                <input type="text" name="nama" class="form-input"
                                       value="<?= htmlspecialchars($u['nama'] ?? '') ?>" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email</label>
                            <div class="form-input-wrap">
                                <i class="fa fa-envelope fi"></i>
                                <input type="email" name="email" class="form-input"
                                       value="<?= htmlspecialchars($u['email'] ?? '') ?>" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-blue">
                            <i class="fa fa-floppy-disk"></i> Simpan Perubahan
                        </button>
                    </form>
                </div>

                <!-- GANTI PASSWORD -->
                <div class="card-section">
                    <div class="card-header">
                        <div class="card-icon" style="background:#fff7ed;">
                            <i class="fa fa-lock" style="color:#ea580c;font-size:14px;"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-800" style="font-size:14px;">Ganti Password</h3>
                            <p class="text-xs text-gray-400 mt-0.5">Pastikan password minimal 6 karakter</p>
                        </div>
                    </div>
                    <form action="<?= BASE_URL ?>?url=pengaturan/gantiPassword" method="POST" class="card-body">

                        <div class="form-group">
                            <label class="form-label">Password Lama</label>
                            <div class="form-input-wrap">
                                <i class="fa fa-lock fi"></i>
                                <input type="password" name="password_lama" id="passLama"
                                       class="form-input form-input-pr" required>
                                <button type="button" class="toggle-pass" data-target="passLama">
                                    <i class="fa fa-eye" id="icon-passLama"></i>
                                </button>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Password Baru</label>
                            <div class="form-input-wrap">
                                <i class="fa fa-lock-open fi"></i>
                                <input type="password" name="password_baru" id="passBaru"
                                       class="form-input form-input-pr" required minlength="6">
                                <button type="button" class="toggle-pass" data-target="passBaru">
                                    <i class="fa fa-eye" id="icon-passBaru"></i>
                                </button>
                            </div>
                            <div id="strengthWrap">
                                <div class="strength-track"><div id="strengthBar"></div></div>
                                <p id="strengthLabel" style="font-size:10px;margin-top:4px;"></p>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Konfirmasi Password Baru</label>
                            <div class="form-input-wrap">
                                <i class="fa fa-shield-halved fi"></i>
                                <input type="password" name="konfirmasi_pass" id="passKonfirm"
                                       class="form-input form-input-pr" required minlength="6">
                                <button type="button" class="toggle-pass" data-target="passKonfirm">
                                    <i class="fa fa-eye" id="icon-passKonfirm"></i>
                                </button>
                            </div>
                            <p id="matchMsg"></p>
                        </div>

                        <button type="submit" class="btn btn-orange">
                            <i class="fa fa-shield-halved"></i> Ganti Password
                        </button>
                    </form>
                </div>

            </div><!-- end grid -->
        </div><!-- end page body -->
    </div><!-- end content-scroll -->
</div><!-- end flex wrapper -->

<script>
// Auto hide toast
const toast = document.getElementById('toastMsg');
if (toast) setTimeout(() => {
    toast.style.transition = 'opacity .4s';
    toast.style.opacity = '0';
    setTimeout(() => toast.parentElement?.remove(), 400);
}, 3500);

// Toggle password
document.querySelectorAll('.toggle-pass').forEach(btn => {
    btn.addEventListener('click', function () {
        const input = document.getElementById(this.dataset.target);
        const icon  = document.getElementById('icon-' + this.dataset.target);
        if (input.type === 'password') {
            input.type = 'text';
            icon.className = 'fa fa-eye-slash';
        } else {
            input.type = 'password';
            icon.className = 'fa fa-eye';
        }
    });
});

// Password strength
const passBaru      = document.getElementById('passBaru');
const strengthBar   = document.getElementById('strengthBar');
const strengthLabel = document.getElementById('strengthLabel');
const strengthWrap  = document.getElementById('strengthWrap');

passBaru.addEventListener('input', function () {
    const v = this.value;
    strengthWrap.style.display = v.length ? 'block' : 'none';
    let score = 0;
    if (v.length >= 6)  score++;
    if (v.length >= 10) score++;
    if (/[A-Z]/.test(v) && /[a-z]/.test(v)) score++;
    if (/[0-9]/.test(v)) score++;
    if (/[^A-Za-z0-9]/.test(v)) score++;
    const levels = [
        { w:'20%', bg:'#ef4444', label:'Sangat Lemah' },
        { w:'40%', bg:'#f97316', label:'Lemah' },
        { w:'60%', bg:'#eab308', label:'Cukup' },
        { w:'80%', bg:'#22c55e', label:'Kuat' },
        { w:'100%',bg:'#16a34a', label:'Sangat Kuat' },
    ];
    const lvl = levels[Math.min(score, 4)];
    strengthBar.style.width      = lvl.w;
    strengthBar.style.background = lvl.bg;
    strengthLabel.style.color    = lvl.bg;
    strengthLabel.textContent    = lvl.label;
    checkMatch();
});

// Password match
const passKonfirm = document.getElementById('passKonfirm');
const matchMsg    = document.getElementById('matchMsg');
function checkMatch() {
    if (!passKonfirm.value) { matchMsg.style.display = 'none'; return; }
    matchMsg.style.display = 'block';
    if (passBaru.value === passKonfirm.value) {
        matchMsg.textContent = '✓ Password cocok';
        matchMsg.style.color = '#16a34a';
        passKonfirm.style.borderColor = '#16a34a';
    } else {
        matchMsg.textContent = '✗ Password tidak cocok';
        matchMsg.style.color = '#dc2626';
        passKonfirm.style.borderColor = '#dc2626';
    }
}
passKonfirm.addEventListener('input', checkMatch);
</script>
</body>
</html>