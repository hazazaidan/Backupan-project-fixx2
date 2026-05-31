<?php
if (session_status() === PHP_SESSION_NONE) session_start();

function inisialAbsensi($nama) {
    $parts = explode(' ', trim($nama));
    $init  = '';
    foreach (array_slice($parts, 0, 2) as $p) {
        $init .= strtoupper($p[0] ?? '');
    }
    return $init ?: '?';
}

$userNama      = $user['nama']  ?? 'User';
$userRole      = $user['role']  ?? 'Guru';
$userKelas     = $user['kelas'] ?? '';
$userInit      = inisialAbsensi($userNama);
$kelasParam    = htmlspecialchars($kelas     ?? '');
$mapelParam    = htmlspecialchars($mapel     ?? '');
$jadwalIdParam = $jadwal_id ?? '';

$warnaList = ['#2563eb','#7c3aed','#db2777','#ea580c','#16a34a','#0891b2','#d97706','#dc2626'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($title ?? 'Absensi') ?></title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.js"></script>
<style>
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box;}
:root{
    --sidebar-bg:#0f172a; --sidebar-hover:#1e293b; --sidebar-active:#2563eb;
    --primary:#2563eb; --primary-dark:#1d4ed8;
    --bg:#f1f5f9; --white:#ffffff; --text:#0f172a; --muted:#64748b; --border:#e2e8f0;
    --green:#16a34a; --green-soft:#dcfce7;
    --red:#ef4444; --red-soft:#fee2e2;
    --orange:#d97706; --orange-soft:#fef3c7;
    --sidebar-w:260px;
}
body{font-family:'Plus Jakarta Sans',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;display:flex;}

/* SIDEBAR */
.sidebar{width:var(--sidebar-w);background:var(--sidebar-bg);position:fixed;top:0;left:0;bottom:0;z-index:100;display:flex;flex-direction:column;transition:.3s;}
.sidebar-brand{display:flex;align-items:center;gap:12px;padding:22px 20px 18px;border-bottom:1px solid rgba(255,255,255,.06);}
.brand-icon{width:44px;height:44px;border-radius:12px;background:var(--primary);display:flex;align-items:center;justify-content:center;}
.brand-icon i{color:white;font-size:18px;}
.brand-text strong{display:block;color:white;font-size:15px;font-weight:800;}
.brand-text span{color:rgba(255,255,255,.45);font-size:11px;}
.user-card{margin:16px 14px;background:rgba(255,255,255,.06);border-radius:14px;padding:12px 14px;display:flex;gap:12px;align-items:center;}
.user-avatar{width:42px;height:42px;border-radius:50%;background:var(--primary);color:white;font-size:14px;font-weight:700;display:flex;align-items:center;justify-content:center;}
.user-card strong{display:block;color:white;font-size:13px;}
.user-card span{color:rgba(255,255,255,.45);font-size:11px;}
.sidebar-nav{flex:1;overflow-y:auto;padding-top:8px;}
.sidebar-nav::-webkit-scrollbar{width:0;}
.nav-section-label{padding:12px 20px 8px;font-size:10px;letter-spacing:1.3px;text-transform:uppercase;color:rgba(255,255,255,.35);font-weight:700;}
.nav-item{display:flex;align-items:center;gap:12px;padding:12px 16px;margin:2px 10px;border-radius:12px;color:rgba(255,255,255,.65);text-decoration:none;font-size:14px;font-weight:600;transition:.2s;}
.nav-item:hover{background:var(--sidebar-hover);color:white;}
.nav-item.active{background:var(--sidebar-active);color:white;box-shadow:0 8px 18px rgba(37,99,235,.35);}
.nav-item i{width:18px;text-align:center;}
.sidebar-bottom{border-top:1px solid rgba(255,255,255,.06);padding-bottom:14px;}

/* MAIN */
.main{margin-left:var(--sidebar-w);flex:1;display:flex;flex-direction:column;}
.topbar{height:68px;background:white;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;padding:0 26px;position:sticky;top:0;z-index:50;}
.topbar-left h1{font-size:19px;font-weight:800;}
.topbar-left p{font-size:12px;color:var(--muted);}
.topbar-right{display:flex;align-items:center;gap:10px;}
.icon-btn{width:40px;height:40px;border-radius:12px;border:1px solid var(--border);background:white;color:var(--muted);cursor:pointer;display:flex;align-items:center;justify-content:center;transition:.2s;text-decoration:none;}
.icon-btn:hover{background:#eff6ff;border-color:var(--primary);color:var(--primary);}
.topbar-avatar{width:40px;height:40px;border-radius:12px;background:var(--primary);color:white;font-size:13px;font-weight:800;display:flex;align-items:center;justify-content:center;}
.content{padding:28px;}
.btn-hamburger{width:40px;height:40px;border-radius:12px;border:1px solid var(--border);background:white;display:none;align-items:center;justify-content:center;cursor:pointer;}
.sidebar-overlay{position:fixed;inset:0;background:rgba(0,0,0,.4);display:none;z-index:99;}
.sidebar-overlay.show{display:block;}

/* INFO BAR */
.info-bar{display:flex;align-items:center;gap:12px;background:white;border:1px solid var(--border);border-radius:16px;padding:16px 20px;margin-bottom:22px;flex-wrap:wrap;}
.info-chip{display:flex;align-items:center;gap:8px;font-size:13px;font-weight:600;}
.info-chip i{color:var(--primary);}
.info-divider{width:1px;height:20px;background:var(--border);}

/* SCAN SECTION */
.scan-section{background:white;border:1px solid var(--border);border-radius:20px;padding:22px;margin-bottom:22px;}
.scan-section-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;}
.scan-section-header h2{font-size:15px;font-weight:800;display:flex;align-items:center;gap:8px;}
.scan-toggle{display:flex;gap:10px;}
.btn-sm{padding:8px 16px;border-radius:10px;border:none;cursor:pointer;font-family:inherit;font-size:13px;font-weight:700;transition:.2s;}
.btn-sm-outline{background:white;border:1.5px solid var(--border);color:var(--muted);}
.btn-sm-outline:hover{border-color:var(--primary);color:var(--primary);}
.btn-sm-primary{background:linear-gradient(135deg,var(--primary-dark),var(--primary));color:white;box-shadow:0 4px 12px rgba(37,99,235,.3);}
.btn-sm-primary:hover{transform:translateY(-1px);}
.btn-sm-primary.stop{background:linear-gradient(135deg,#b91c1c,#ef4444);}

/* CAMERA */
.camera-wrap{
    position:relative;background:#020617;border-radius:16px;overflow:hidden;
    width:100%;max-width:480px;margin:0 auto;
    aspect-ratio:4/3;
    display:flex;align-items:center;justify-content:center;
}
.camera-wrap video{width:100%;height:100%;object-fit:cover;display:none;}
.camera-wrap canvas{display:none;}
.cam-placeholder{display:flex;flex-direction:column;align-items:center;gap:8px;color:#475569;}
.cam-placeholder i{font-size:32px;}
.cam-placeholder p{font-size:13px;text-align:center;padding:0 20px;}
.scan-frame-overlay{position:absolute;width:55%;aspect-ratio:1;pointer-events:none;}
.scan-frame-overlay::before,.scan-frame-overlay::after,
.scan-frame-overlay span::before,.scan-frame-overlay span::after{content:'';position:absolute;width:24px;height:24px;border-style:solid;border-color:#22c55e;}
.scan-frame-overlay::before{top:0;left:0;border-width:3px 0 0 3px;}
.scan-frame-overlay::after{top:0;right:0;border-width:3px 3px 0 0;}
.scan-frame-overlay span::before{left:0;bottom:0;border-width:0 0 3px 3px;}
.scan-frame-overlay span::after{right:0;bottom:0;border-width:0 3px 3px 0;}
.laser-line{width:55%;height:2px;background:linear-gradient(90deg,transparent,#ef4444,transparent);position:absolute;animation:laserAnim 2s infinite ease-in-out;}
@keyframes laserAnim{0%{top:22%;}100%{top:78%;}}
.scan-dot-status{position:absolute;top:12px;left:12px;background:rgba(0,0,0,.5);padding:5px 12px;border-radius:999px;display:none;align-items:center;gap:6px;backdrop-filter:blur(8px);}
.scan-dot-status.show{display:flex;}
.dot-pulse{width:7px;height:7px;border-radius:50%;background:#22c55e;animation:pulse .9s infinite;}
@keyframes pulse{50%{opacity:.3;transform:scale(.7);}}
.scan-dot-status span{color:white;font-size:11px;font-weight:700;}

/* LAST SCAN RESULT */
.last-scan{margin-top:12px;padding:12px 16px;border-radius:12px;background:#f8fafc;border:1px solid var(--border);display:flex;align-items:center;gap:12px;min-height:52px;}
.last-scan.success{background:var(--green-soft);border-color:#86efac;}
.last-scan.error{background:var(--red-soft);border-color:#fca5a5;}
.last-scan.warning{background:var(--orange-soft);border-color:#fcd34d;}
.last-scan i{font-size:18px;}
.last-scan.success i{color:var(--green);}
.last-scan.error i{color:var(--red);}
.last-scan.warning i{color:var(--orange);}
.last-scan p{font-size:13px;font-weight:600;margin:0;}
.last-scan span{font-size:11px;color:var(--muted);}

/* DAFTAR SISWA */
.daftar-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;flex-wrap:wrap;gap:10px;}
.daftar-header h2{font-size:15px;font-weight:800;display:flex;align-items:center;gap:8px;}
.stat-chips{display:flex;gap:8px;}
.stat-chip{padding:5px 12px;border-radius:999px;font-size:12px;font-weight:700;}
.chip-hadir{background:var(--green-soft);color:var(--green);}
.chip-izin{background:var(--orange-soft);color:var(--orange);}
.chip-alpha{background:var(--red-soft);color:var(--red);}

/* TABLE */
.siswa-table{width:100%;border-collapse:collapse;}
.siswa-table th{text-align:left;padding:10px 14px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:var(--muted);background:#f8fafc;border-bottom:1px solid var(--border);}
.siswa-table th:first-child{border-radius:10px 0 0 10px;}
.siswa-table th:last-child{border-radius:0 10px 10px 0;}
.siswa-table td{padding:12px 14px;border-bottom:1px solid #f1f5f9;vertical-align:middle;}
.siswa-row:hover td{background:#fafafa;}
.siswa-row.hadir-row td:first-child{border-left:3px solid var(--green);}
.siswa-row.izin-row td:first-child{border-left:3px solid var(--orange);}
.siswa-row.alpha-row td:first-child{border-left:3px solid var(--red);}
.siswa-avatar{width:36px;height:36px;border-radius:10px;color:white;font-size:12px;font-weight:800;display:flex;align-items:center;justify-content:center;}
.siswa-nama{font-size:13px;font-weight:700;}
.siswa-nis{font-size:11px;color:var(--muted);}

/* ── STATUS TOGGLE — FIX ───────────────────────────────────────── */
.status-toggle{
    display:flex;gap:4px;
    background:#f1f5f9;
    border-radius:10px;
    padding:3px;
}
.status-btn{
    padding:6px 14px;
    border:none;
    border-radius:8px;
    font-family:'Plus Jakarta Sans',sans-serif;
    font-size:12px;
    font-weight:700;
    cursor:pointer;
    transition:.15s;
    background:transparent;
    color:#64748b;   /* warna default jelas terlihat */
    outline:none;
    user-select:none;
}
.status-btn:hover{ background:rgba(255,255,255,.7); }
/* state aktif masing-masing — !important agar tidak tertimpa reset inline */
.status-btn.active-hadir{
    background:#ffffff !important;
    color:var(--green) !important;
    box-shadow:0 1px 4px rgba(0,0,0,.12);
}
.status-btn.active-izin{
    background:#ffffff !important;
    color:var(--orange) !important;
    box-shadow:0 1px 4px rgba(0,0,0,.12);
}
.status-btn.active-alpha{
    background:#ffffff !important;
    color:var(--red) !important;
    box-shadow:0 1px 4px rgba(0,0,0,.12);
}

/* SUBMIT BAR */
.submit-bar{position:sticky;bottom:0;background:white;border-top:1px solid var(--border);padding:16px 28px;display:flex;align-items:center;justify-content:space-between;z-index:40;gap:14px;}
.submit-summary{font-size:13px;color:var(--muted);}
.submit-summary strong{color:var(--text);}
.btn-submit{padding:13px 32px;background:linear-gradient(135deg,var(--primary-dark),var(--primary));color:white;border:none;border-radius:14px;font-family:inherit;font-size:15px;font-weight:700;cursor:pointer;transition:.2s;box-shadow:0 6px 18px rgba(37,99,235,.3);display:flex;align-items:center;gap:10px;}
.btn-submit:hover{transform:translateY(-2px);box-shadow:0 10px 24px rgba(37,99,235,.4);}
.btn-submit:disabled{opacity:.5;cursor:not-allowed;transform:none;}

/* TOAST */
.toast{position:fixed;right:26px;bottom:90px;padding:14px 18px;border-radius:14px;display:flex;align-items:center;gap:10px;font-size:13px;font-weight:700;box-shadow:0 12px 25px rgba(0,0,0,.2);transform:translateY(80px);opacity:0;transition:.35s;z-index:999;color:white;max-width:320px;}
.toast.show{transform:translateY(0);opacity:1;}
.toast.ok{background:var(--green);}
.toast.err{background:var(--red);}
.toast.warn{background:var(--orange);}

/* MODAL KONFIRMASI */
.modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.4);display:none;align-items:center;justify-content:center;z-index:200;backdrop-filter:blur(4px);}
.modal-overlay.show{display:flex;}
.modal-box{background:white;border-radius:22px;padding:32px;max-width:420px;width:90%;text-align:center;animation:popIn .3s ease;}
@keyframes popIn{from{transform:scale(.9);opacity:0;}to{transform:scale(1);opacity:1;}}
.modal-icon{width:64px;height:64px;border-radius:50%;background:#eff6ff;color:var(--primary);font-size:26px;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;}
.modal-box h3{font-size:18px;font-weight:800;margin-bottom:8px;}
.modal-box p{font-size:13px;color:var(--muted);margin-bottom:24px;line-height:1.6;}
.modal-actions{display:flex;gap:10px;}
.btn-cancel{flex:1;padding:12px;border-radius:12px;border:1.5px solid var(--border);background:white;font-family:inherit;font-size:14px;font-weight:700;cursor:pointer;}
.btn-confirm{flex:1;padding:12px;border-radius:12px;border:none;background:linear-gradient(135deg,var(--primary-dark),var(--primary));color:white;font-family:inherit;font-size:14px;font-weight:700;cursor:pointer;}

/* SUCCESS MODAL */
.success-overlay{position:fixed;inset:0;background:rgba(0,0,0,.5);display:none;align-items:center;justify-content:center;z-index:300;backdrop-filter:blur(4px);}
.success-overlay.show{display:flex;}
.success-box{background:white;border-radius:22px;padding:36px;max-width:400px;width:90%;text-align:center;animation:popIn .3s ease;}
.success-icon{width:72px;height:72px;border-radius:50%;background:var(--green-soft);color:var(--green);font-size:30px;display:flex;align-items:center;justify-content:center;margin:0 auto 18px;}
.success-box h3{font-size:20px;font-weight:800;margin-bottom:8px;}
.success-box p{font-size:13px;color:var(--muted);margin-bottom:6px;}
.success-stats{display:flex;justify-content:center;gap:16px;margin:20px 0;flex-wrap:wrap;}
.sstat{text-align:center;}
.sstat-num{font-size:24px;font-weight:800;}
.sstat-label{font-size:11px;color:var(--muted);}
.btn-selesai{width:100%;padding:14px;border-radius:14px;border:none;background:linear-gradient(135deg,var(--primary-dark),var(--primary));color:white;font-family:inherit;font-size:15px;font-weight:700;cursor:pointer;margin-top:8px;}

@media(max-width:768px){
    .sidebar{transform:translateX(-100%);}
    .sidebar.open{transform:translateX(0);}
    .main{margin-left:0;}
    .content{padding:14px 14px 80px;}
    .topbar{padding:0 14px;}
    .btn-hamburger{display:flex;}
    .stat-chips{display:none;}
    .info-divider{display:none;}
    .submit-bar{padding:12px 14px;}
    .status-btn{padding:5px 8px;font-size:11px;}
    .camera-wrap{max-width:100%;}
}
</style>
</head>
<body>

<div class="sidebar-overlay" id="sidebarOverlay" onclick="tutupSidebar()"></div>

<!-- SIDEBAR -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <div class="brand-icon"><i class="fa fa-qrcode"></i></div>
        <div class="brand-text">
            <strong>ABSENSI QR</strong>
            <span><?= htmlspecialchars($user['nama_sekolah'] ?? 'MAN 2 Banyumas') ?></span>
        </div>
    </div>
    <div class="user-card">
        <div class="user-avatar"><?= $userInit ?></div>
        <div>
            <strong><?= htmlspecialchars($userNama) ?></strong>
            <span><?= ucfirst($userRole) ?> — <?= htmlspecialchars($userKelas) ?></span>
        </div>
    </div>
    <div class="sidebar-nav">
        <p class="nav-section-label">Menu Utama</p>
        <a href="?url=guru/dashboard" class="nav-item"><i class="fa fa-house"></i> Dashboard</a>
        <a href="?url=guru/kelas" class="nav-item active"><i class="fa fa-door-open"></i> Kelas</a>
        <a href="?url=guru/riwayat" class="nav-item"><i class="fa fa-clock-rotate-left"></i> Riwayat</a>
        <a href="?url=guru/rekap" class="nav-item"><i class="fa fa-layer-group"></i> Rekap</a>
        <a href="?url=guru/monitoring" class="nav-item"><i class="fa fa-chart-line"></i> Monitoring</a>
    </div>
    <div class="sidebar-bottom">
        <p class="nav-section-label">Sistem</p>
        <a href="?url=guru/pengaturan" class="nav-item"><i class="fa fa-gear"></i> Pengaturan</a>
        <a href="?url=auth/logout" class="nav-item"><i class="fa fa-right-from-bracket"></i> Logout</a>
    </div>
</aside>

<!-- MAIN -->
<div class="main">
<header class="topbar">
    <div style="display:flex;align-items:center;gap:12px;">
        <button class="btn-hamburger" onclick="bukaSidebar()"><i class="fa fa-bars"></i></button>
        <div class="topbar-left">
            <h1>Absensi <?= $kelasParam ?></h1>
            <p><?= $mapelParam ?> · <?= date('d M Y') ?></p>
        </div>
    </div>
    <div class="topbar-right">
        <a href="?url=guru/kelas" class="icon-btn" title="Kembali">
            <i class="fa fa-arrow-left"></i>
        </a>
        <div class="topbar-avatar"><?= $userInit ?></div>
    </div>
</header>

<div class="content">

    <!-- INFO BAR -->
    <div class="info-bar">
        <div class="info-chip"><i class="fa fa-chalkboard"></i> <?= $kelasParam ?></div>
        <div class="info-divider"></div>
        <div class="info-chip"><i class="fa fa-book-open"></i> <?= $mapelParam ?></div>
        <div class="info-divider"></div>
        <div class="info-chip"><i class="fa fa-calendar"></i> <?= date('d M Y') ?></div>
        <div class="info-divider"></div>
        <div class="info-chip"><i class="fa fa-users"></i> <?= count($siswaList ?? []) ?> Siswa</div>
    </div>

    <!-- SCAN QR SECTION -->
    <div class="scan-section">
        <div class="scan-section-header">
            <h2><i class="fa fa-qrcode" style="color:#2563eb"></i> Scan QR Code</h2>
            <div class="scan-toggle">
                <button class="btn-sm btn-sm-outline" onclick="simulasiScan()">
                    <i class="fa fa-rotate"></i> Simulasi
                </button>
                <button class="btn-sm btn-sm-primary" id="btnKam" onclick="toggleKamera()">
                    <i class="fa fa-camera"></i> Aktifkan Kamera
                </button>
            </div>
        </div>

        <div class="camera-wrap" id="cameraWrap">
            <video id="videoEl" autoplay playsinline></video>
            <canvas id="canvasEl"></canvas>
            <div class="cam-placeholder" id="camPlaceholder">
                <i class="fa fa-camera-slash"></i>
                <p>Kamera belum aktif — tekan "Aktifkan Kamera"</p>
            </div>
            <div class="scan-frame-overlay"><span></span></div>
            <div class="laser-line"></div>
            <div class="scan-dot-status" id="scanStatus">
                <div class="dot-pulse"></div>
                <span>Scanning...</span>
            </div>
        </div>

        <div class="last-scan" id="lastScan">
            <i class="fa fa-circle-info"></i>
            <div>
                <p>Menunggu scan QR</p>
                <span>Arahkan QR Code siswa ke kamera</span>
            </div>
        </div>
    </div>

    <!-- DAFTAR SISWA -->
    <div style="background:white;border:1px solid var(--border);border-radius:20px;padding:22px;">
        <div class="daftar-header">
            <h2><i class="fa fa-list-check" style="color:#2563eb"></i> Daftar Siswa</h2>
            <div class="stat-chips">
                <div class="stat-chip chip-hadir"><span id="countHadir">0</span> Hadir</div>
                <div class="stat-chip chip-izin"><span id="countIzin">0</span> Izin</div>
                <div class="stat-chip chip-alpha"><span id="countAlpha"><?= count($siswaList ?? []) ?></span> Alpha</div>
            </div>
        </div>

        <?php if (empty($siswaList)): ?>
            <div style="text-align:center;padding:40px;color:var(--muted);">
                <i class="fa fa-users" style="font-size:36px;display:block;margin-bottom:12px;opacity:.3"></i>
                <p>Tidak ada siswa di kelas ini.</p>
            </div>
        <?php else: ?>
        <div style="overflow-x:auto;">
        <table class="siswa-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Siswa</th>
                    <th>NIS</th>
                    <th>Status Kehadiran</th>
                </tr>
            </thead>
            <tbody id="siswaTableBody">
            <?php foreach ($siswaList as $i => $siswa): ?>
                <?php
                $init  = inisialAbsensi($siswa['nama'] ?? '?');
                $warna = $warnaList[$i % count($warnaList)];
                $sid   = $siswa['id'] ?? '';
                ?>
                <tr class="siswa-row alpha-row" id="row-<?= htmlspecialchars($sid) ?>"
                    data-siswa-id="<?= htmlspecialchars($sid) ?>" data-status="alpha">
                    <td style="color:var(--muted);font-size:12px;"><?= $i + 1 ?></td>
                    <td>
                        <div style="display:flex;align-items:center;gap:10px;">
                            <div class="siswa-avatar" style="background:<?= $warna ?>"><?= $init ?></div>
                            <div class="siswa-nama"><?= htmlspecialchars($siswa['nama'] ?? '-') ?></div>
                        </div>
                    </td>
                    <td><span class="siswa-nis"><?= htmlspecialchars($siswa['nis'] ?? '-') ?></span></td>
                    <td>
                        <!-- ── FIX: gunakan data-id attribute, handler di JS (hindari konflik escape) ── -->
                        <div class="status-toggle" data-siswa="<?= htmlspecialchars($sid) ?>">
                            <button class="status-btn" data-target="hadir">Hadir</button>
                            <button class="status-btn" data-target="izin">Izin</button>
                            <button class="status-btn active-alpha" data-target="alpha">Alpha</button>
                        </div>
                        <input type="hidden" id="input-<?= htmlspecialchars($sid) ?>" value="alpha">
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
        <?php endif; ?>
    </div>

</div>

<!-- SUBMIT BAR -->
<div class="submit-bar">
    <div class="submit-summary">
        Data absensi <strong>belum tersimpan</strong> · Tekan Submit untuk menyimpan
    </div>
    <button class="btn-submit" id="btnSubmit" onclick="konfirmasiSubmit()">
        <i class="fa fa-paper-plane"></i> Submit Absensi
    </button>
</div>
</div>

<!-- MODAL KONFIRMASI -->
<div class="modal-overlay" id="modalKonfirmasi">
    <div class="modal-box">
        <div class="modal-icon"><i class="fa fa-paper-plane"></i></div>
        <h3>Submit Absensi?</h3>
        <p id="modalDesc">Data absensi akan disimpan ke database dan tidak bisa diubah lagi.</p>
        <div class="modal-actions">
            <button class="btn-cancel" onclick="tutupModal()">Batal</button>
            <button class="btn-confirm" onclick="doSubmit()">Ya, Submit</button>
        </div>
    </div>
</div>

<!-- MODAL SUKSES -->
<div class="success-overlay" id="modalSukses">
    <div class="success-box">
        <div class="success-icon"><i class="fa fa-circle-check"></i></div>
        <h3>Absensi Tersimpan!</h3>
        <p><?= $kelasParam ?> · <?= $mapelParam ?></p>
        <p style="color:var(--muted);font-size:12px;"><?= date('d M Y') ?></p>
        <div class="success-stats">
            <div class="sstat"><div class="sstat-num" style="color:var(--green)" id="sucHadir">0</div><div class="sstat-label">Hadir</div></div>
            <div class="sstat"><div class="sstat-num" style="color:var(--orange)" id="sucIzin">0</div><div class="sstat-label">Izin</div></div>
            <div class="sstat"><div class="sstat-num" style="color:var(--red)" id="sucAlpha">0</div><div class="sstat-label">Alpha</div></div>
        </div>
        <button class="btn-selesai" onclick="window.location.href='?url=guru/kelas'">
            <i class="fa fa-check"></i> Selesai
        </button>
    </div>
</div>

<!-- TOAST -->
<div class="toast" id="toast">
    <i class="fa fa-circle-check" id="toastIco"></i>
    <span id="toastMsg">OK</span>
</div>

<script>
// ── DATA ──────────────────────────────────────────────────
const JADWAL_ID = <?= json_encode($jadwalIdParam) ?>;
const KELAS     = <?= json_encode($kelas  ?? '') ?>;
const MAPEL     = <?= json_encode($mapel  ?? '') ?>;

const statusMap = {};
<?php foreach ($siswaList ?? [] as $siswa): ?>
statusMap[<?= json_encode((string)($siswa['id'] ?? '')) ?>] = 'alpha';
<?php endforeach; ?>

const qrMap = {};
<?php foreach ($siswaList ?? [] as $siswa):
    $sid   = (string)($siswa['id'] ?? '');
    $qrVal = $siswa['qr_image'] ?? $siswa['qr_code'] ?? $siswa['nis'] ?? '';
    if (!empty($qrVal) && !empty($sid)):
?>
qrMap[<?= json_encode((string)$qrVal) ?>] = <?= json_encode($sid) ?>;
<?php endif; endforeach; ?>

// ── SIDEBAR ───────────────────────────────────────────────
function bukaSidebar()  { document.getElementById('sidebar').classList.add('open');    document.getElementById('sidebarOverlay').classList.add('show'); }
function tutupSidebar() { document.getElementById('sidebar').classList.remove('open'); document.getElementById('sidebarOverlay').classList.remove('show'); }

// ── STATUS TOGGLE — FIX ───────────────────────────────────
// Pasang event listener via JS, bukan onclick di HTML
// agar tidak ada masalah escape karakter pada siswa_id
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.status-toggle').forEach(function(toggle) {
        toggle.querySelectorAll('.status-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var siswaId = toggle.getAttribute('data-siswa');
                var status  = btn.getAttribute('data-target');
                setStatus(siswaId, status);
            });
        });
    });
    updateCounters();
});

function setStatus(siswaId, status) {
    statusMap[siswaId] = status;

    // Update hidden input
    var input = document.getElementById('input-' + siswaId);
    if (input) input.value = status;

    // Reset semua tombol di toggle ini
    var toggle = document.querySelector('.status-toggle[data-siswa="' + siswaId + '"]');
    if (!toggle) return;
    toggle.querySelectorAll('.status-btn').forEach(function(b) {
        b.className = 'status-btn';
    });

    // Aktifkan tombol yang sesuai
    var activeBtn = toggle.querySelector('[data-target="' + status + '"]');
    if (activeBtn) {
        activeBtn.className = 'status-btn active-' + status;
    }

    // Update warna border kiri row
    var row = document.getElementById('row-' + siswaId);
    if (row) {
        row.className      = 'siswa-row ' + status + '-row';
        row.dataset.status = status;
    }

    updateCounters();
}

function updateCounters() {
    var h = 0, iz = 0, al = 0;
    Object.values(statusMap).forEach(function(s) {
        if (s === 'hadir')      h++;
        else if (s === 'izin')  iz++;
        else                    al++;
    });
    document.getElementById('countHadir').textContent = h;
    document.getElementById('countIzin').textContent  = iz;
    document.getElementById('countAlpha').textContent = al;
}

// ── KAMERA ────────────────────────────────────────────────
var kameraAktif = false, stream = null, animId = null, sedangProses = false;

function toggleKamera() { kameraAktif ? matikanKamera() : aktifkanKamera(); }

async function aktifkanKamera() {
    try {
        stream = await navigator.mediaDevices.getUserMedia({
            video: { facingMode: 'environment', width: { ideal: 1280 }, height: { ideal: 960 } }
        });
        var vid = document.getElementById('videoEl');
        vid.srcObject     = stream;
        vid.style.display = 'block';
        document.getElementById('camPlaceholder').style.display = 'none';
        document.getElementById('scanStatus').classList.add('show');
        kameraAktif = true;
        var btn = document.getElementById('btnKam');
        btn.classList.add('stop');
        btn.innerHTML = '<i class="fa fa-video-slash"></i> Matikan';
        vid.addEventListener('loadedmetadata', mulaiScan);
        showToast('Kamera aktif', 'ok');
    } catch(e) {
        showToast('Gagal akses kamera: ' + e.message, 'err');
    }
}

function matikanKamera() {
    if (stream)  stream.getTracks().forEach(function(t){ t.stop(); });
    if (animId)  cancelAnimationFrame(animId);
    kameraAktif = false; stream = null; animId = null;
    document.getElementById('videoEl').style.display = 'none';
    document.getElementById('camPlaceholder').style.display = 'flex';
    document.getElementById('scanStatus').classList.remove('show');
    var btn = document.getElementById('btnKam');
    btn.classList.remove('stop');
    btn.innerHTML = '<i class="fa fa-camera"></i> Aktifkan Kamera';
}

function mulaiScan() {
    var vid = document.getElementById('videoEl');
    var cvs = document.getElementById('canvasEl');
    var ctx = cvs.getContext('2d');
    function tick() {
        if (!kameraAktif) return;
        if (vid.readyState === vid.HAVE_ENOUGH_DATA) {
            cvs.width  = vid.videoWidth;
            cvs.height = vid.videoHeight;
            ctx.drawImage(vid, 0, 0, cvs.width, cvs.height);
            var img  = ctx.getImageData(0, 0, cvs.width, cvs.height);
            var code = jsQR(img.data, img.width, img.height, { inversionAttempts: 'dontInvert' });
            if (code && !sedangProses) {
                sedangProses = true;
                prosesQR(code.data);
                setTimeout(function(){ sedangProses = false; }, 2500);
            }
        }
        animId = requestAnimationFrame(tick);
    }
    tick();
}

// ── QR PROCESSING ─────────────────────────────────────────
function prosesQR(qrData) {
    var siswaId = qrMap[qrData];
    if (!siswaId) {
        tampilLastScan(
            'Siswa tidak terdaftar di kelas ini',
            'QR: ' + qrData.substring(0, 24) + ' — bukan anggota ' + KELAS,
            'error'
        );
        showToast('Siswa bukan anggota ' + KELAS, 'err');
        return;
    }
    if (statusMap[siswaId] === 'hadir') {
        var nama = getNama(siswaId);
        tampilLastScan(nama + ' sudah tercatat Hadir', 'Scan duplikat diabaikan', 'warning');
        showToast(nama + ' sudah Hadir', 'warn');
        return;
    }
    setStatus(siswaId, 'hadir');
    var nama = getNama(siswaId);
    tampilLastScan(nama, 'Status berubah jadi Hadir ✓', 'success');
    showToast('✓ ' + nama + ' — Hadir', 'ok');
    var row = document.getElementById('row-' + siswaId);
    if (row) row.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function getNama(siswaId) {
    var el = document.querySelector('#row-' + siswaId + ' .siswa-nama');
    return el ? el.textContent.trim() : 'Siswa';
}

function simulasiScan() {
    var keys = Object.keys(qrMap);
    if (keys.length) {
        prosesQR(keys[Math.floor(Math.random() * keys.length)]);
    } else {
        var firstId = Object.keys(statusMap)[0];
        if (firstId) {
            setStatus(firstId, 'hadir');
            var nama = getNama(firstId);
            tampilLastScan(nama, 'Simulasi — Status berubah jadi Hadir', 'success');
            showToast('✓ ' + nama + ' — Hadir (simulasi)', 'ok');
        } else {
            showToast('Tidak ada siswa', 'err');
        }
    }
}

function tampilLastScan(title, sub, type) {
    var el = document.getElementById('lastScan');
    var icons = { success: 'circle-check', error: 'circle-xmark', warning: 'triangle-exclamation' };
    el.className = 'last-scan ' + (type || 'error');
    el.innerHTML =
        '<i class="fa fa-' + (icons[type] || 'circle-xmark') + '"></i>' +
        '<div><p>' + title + '</p><span>' + (sub || '') + '</span></div>';
}

// ── SUBMIT ────────────────────────────────────────────────
function konfirmasiSubmit() {
    var h  = parseInt(document.getElementById('countHadir').textContent);
    var iz = parseInt(document.getElementById('countIzin').textContent);
    var al = parseInt(document.getElementById('countAlpha').textContent);
    document.getElementById('modalDesc').textContent =
        'Total: ' + h + ' Hadir, ' + iz + ' Izin, ' + al + ' Alpha. Data akan disimpan ke database.';
    document.getElementById('modalKonfirmasi').classList.add('show');
}

function tutupModal() {
    document.getElementById('modalKonfirmasi').classList.remove('show');
}

function doSubmit() {
    tutupModal();
    var btn = document.getElementById('btnSubmit');
    btn.disabled  = true;
    btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Menyimpan...';

    var fd = new FormData();
    fd.append('jadwal_id', JADWAL_ID);
    fd.append('kelas',     KELAS);
    fd.append('mapel',     MAPEL);
    Object.entries(statusMap).forEach(function([id, status]) {
        fd.append('status[' + id + ']', status);
    });

    fetch('?url=guru/absensi/submit', { method: 'POST', body: fd })
        .then(function(r){ return r.json(); })
        .then(function(data) {
            if (data.success) {
                document.getElementById('sucHadir').textContent = document.getElementById('countHadir').textContent;
                document.getElementById('sucIzin').textContent  = document.getElementById('countIzin').textContent;
                document.getElementById('sucAlpha').textContent = document.getElementById('countAlpha').textContent;
                document.getElementById('modalSukses').classList.add('show');
                matikanKamera();
            } else {
                showToast(data.message || 'Gagal menyimpan', 'err');
                btn.disabled  = false;
                btn.innerHTML = '<i class="fa fa-paper-plane"></i> Submit Absensi';
            }
        })
        .catch(function() {
            showToast('Server error', 'err');
            btn.disabled  = false;
            btn.innerHTML = '<i class="fa fa-paper-plane"></i> Submit Absensi';
        });
}

// ── TOAST ─────────────────────────────────────────────────
function showToast(msg, type) {
    var t = document.getElementById('toast');
    t.className = 'toast show ' + (type === 'err' ? 'err' : type === 'warn' ? 'warn' : 'ok');
    document.getElementById('toastMsg').textContent = msg;
    setTimeout(function(){ t.classList.remove('show'); }, 3000);
}
</script>
</body>
</html>