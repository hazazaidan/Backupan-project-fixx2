<?php
// ======================================================
// SCAN QR PAGE — FULL UPDATED VERSION
// UI Modern + Kamera QR + Responsive + Sidebar Seragam
// ======================================================

if (session_status() === PHP_SESSION_NONE) session_start();

function inisial($nama) {
    $parts = explode(' ', trim($nama));
    $init  = '';
    foreach (array_slice($parts, 0, 2) as $p) {
        $init .= strtoupper($p[0] ?? '');
    }
    return $init ?: 'U';
}

$warnaList = ['av-blue','av-green','av-orange','av-purple','av-pink'];

$userNama  = $user['nama']  ?? 'User';
$userRole  = $user['role']  ?? 'Guru';
$userKelas = $user['kelas'] ?? 'XI RPL 1';
$userInit  = inisial($userNama);

$totalScan = count($scanHariIni ?? []);
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title><?= $title ?? 'Scan QR – Absensi' ?></title>

<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

<!-- jsQR -->
<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.js"></script>

<style>

*,
*::before,
*::after{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

:root{

    --sidebar-bg:#0f172a;
    --sidebar-hover:#1e293b;
    --sidebar-active:#2563eb;

    --primary:#2563eb;
    --primary-dark:#1d4ed8;

    --bg:#f1f5f9;
    --white:#ffffff;

    --text:#0f172a;
    --muted:#64748b;

    --border:#e2e8f0;

    --green:#16a34a;
    --green-soft:#dcfce7;

    --red:#ef4444;
    --red-soft:#fee2e2;

    --orange:#d97706;
    --orange-soft:#fef3c7;

    --sidebar-w:260px;
}

/* =========================================================
BODY
========================================================= */

body{
    font-family:'Plus Jakarta Sans',sans-serif;
    background:var(--bg);
    color:var(--text);
    min-height:100vh;
    display:flex;
}

/* =========================================================
SIDEBAR
========================================================= */

.sidebar{
    width:var(--sidebar-w);
    background:var(--sidebar-bg);
    position:fixed;
    top:0;
    left:0;
    bottom:0;
    z-index:100;
    display:flex;
    flex-direction:column;
    transition:.3s;
}

.sidebar-brand{
    display:flex;
    align-items:center;
    gap:12px;
    padding:22px 20px 18px;
    border-bottom:1px solid rgba(255,255,255,.06);
}

.brand-icon{
    width:44px;
    height:44px;
    border-radius:12px;
    background:var(--primary);
    display:flex;
    align-items:center;
    justify-content:center;
}

.brand-icon i{
    color:white;
    font-size:18px;
}

.brand-text strong{
    display:block;
    color:white;
    font-size:15px;
    font-weight:800;
}

.brand-text span{
    color:rgba(255,255,255,.45);
    font-size:11px;
}

.user-card{
    margin:16px 14px;
    background:rgba(255,255,255,.06);
    border-radius:14px;
    padding:12px 14px;
    display:flex;
    gap:12px;
    align-items:center;
}

.user-avatar{
    width:42px;
    height:42px;
    border-radius:50%;
    background:var(--primary);
    color:white;
    font-size:14px;
    font-weight:700;
    display:flex;
    align-items:center;
    justify-content:center;
}

.user-card strong{
    display:block;
    color:white;
    font-size:13px;
}

.user-card span{
    color:rgba(255,255,255,.45);
    font-size:11px;
}

.sidebar-nav{
    flex:1;
    overflow-y:auto;
    padding-top:8px;
}

.sidebar-nav::-webkit-scrollbar{
    width:0;
}

.nav-section-label{
    padding:12px 20px 8px;
    font-size:10px;
    letter-spacing:1.3px;
    text-transform:uppercase;
    color:rgba(255,255,255,.35);
    font-weight:700;
}

.nav-item{
    display:flex;
    align-items:center;
    gap:12px;
    padding:12px 16px;
    margin:2px 10px;
    border-radius:12px;
    color:rgba(255,255,255,.65);
    text-decoration:none;
    font-size:14px;
    font-weight:600;
    transition:.2s;
}

.nav-item:hover{
    background:var(--sidebar-hover);
    color:white;
}

.nav-item.active{
    background:var(--sidebar-active);
    color:white;
    box-shadow:0 8px 18px rgba(37,99,235,.35);
}

.nav-item i{
    width:18px;
    text-align:center;
}

.nav-badge{
    margin-left:auto;
    min-width:20px;
    padding:2px 8px;
    border-radius:999px;
    background:#ef4444;
    color:white;
    font-size:10px;
    font-weight:700;
    text-align:center;
}

.sidebar-bottom{
    border-top:1px solid rgba(255,255,255,.06);
    padding-bottom:14px;
}

/* =========================================================
MAIN
========================================================= */

.main{
    margin-left:var(--sidebar-w);
    flex:1;
    display:flex;
    flex-direction:column;
}

.topbar{
    height:68px;
    background:white;
    border-bottom:1px solid var(--border);
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:0 26px;
    position:sticky;
    top:0;
    z-index:50;
}

.topbar-left h1{
    font-size:19px;
    font-weight:800;
}

.topbar-left p{
    font-size:12px;
    color:var(--muted);
}

.topbar-right{
    display:flex;
    align-items:center;
    gap:10px;
}

.icon-btn{
    width:40px;
    height:40px;
    border-radius:12px;
    border:1px solid var(--border);
    background:white;
    color:var(--muted);
    cursor:pointer;
    display:flex;
    align-items:center;
    justify-content:center;
    transition:.2s;
    position:relative;
}

.icon-btn:hover{
    background:#eff6ff;
    border-color:var(--primary);
    color:var(--primary);
}

.icon-btn .dot{
    width:8px;
    height:8px;
    border-radius:50%;
    background:#f97316;
    position:absolute;
    top:8px;
    right:8px;
    border:2px solid white;
}

.topbar-avatar{
    width:40px;
    height:40px;
    border-radius:12px;
    background:var(--primary);
    color:white;
    font-size:13px;
    font-weight:800;
    display:flex;
    align-items:center;
    justify-content:center;
}

.content{
    padding:28px;
}

/* =========================================================
BUTTONS
========================================================= */

.scan-actions{
    display:flex;
    gap:14px;
    margin-bottom:24px;
}

.btn{
    flex:1;
    height:52px;
    border-radius:14px;
    border:none;
    cursor:pointer;
    font-family:inherit;
    font-weight:700;
    font-size:14px;
    transition:.2s;
}

.btn-outline{
    border:2px solid var(--border);
    background:white;
}

.btn-outline:hover{
    border-color:var(--primary);
    color:var(--primary);
    background:#eff6ff;
}

.btn-primary{
    background:linear-gradient(135deg,var(--primary-dark),var(--primary));
    color:white;
    box-shadow:0 8px 20px rgba(37,99,235,.3);
}

.btn-primary:hover{
    transform:translateY(-1px);
}

.btn-primary.active{
    background:linear-gradient(135deg,#dc2626,#ef4444);
}

/* =========================================================
GRID
========================================================= */

.scan-grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:20px;
}

/* =========================================================
CAMERA
========================================================= */

.camera-card{
    background:#020617;
    border-radius:22px;
    overflow:hidden;
    position:relative;
    aspect-ratio:4/3;
    display:flex;
    align-items:center;
    justify-content:center;
    box-shadow:0 10px 30px rgba(0,0,0,.25);
}

#videoEl{
    width:100%;
    height:100%;
    object-fit:cover;
    display:none;
}

#canvasEl{
    display:none;
}

.cam-placeholder{
    display:flex;
    flex-direction:column;
    align-items:center;
    gap:12px;
    color:#475569;
}

.cam-placeholder i{
    font-size:44px;
}

.scan-frame{
    position:absolute;
    width:56%;
    aspect-ratio:1;
    pointer-events:none;
}

.scan-frame::before,
.scan-frame::after,
.scan-frame span::before,
.scan-frame span::after{
    content:'';
    position:absolute;
    width:28px;
    height:28px;
    border-style:solid;
    border-color:#22c55e;
}

.scan-frame::before{
    top:0;
    left:0;
    border-width:3px 0 0 3px;
}

.scan-frame::after{
    top:0;
    right:0;
    border-width:3px 3px 0 0;
}

.scan-frame span::before{
    left:0;
    bottom:0;
    border-width:0 0 3px 3px;
}

.scan-frame span::after{
    right:0;
    bottom:0;
    border-width:0 3px 3px 0;
}

.laser{
    width:56%;
    height:2px;
    background:linear-gradient(90deg,transparent,#ef4444,transparent);
    position:absolute;
    animation:scanLaser 2s infinite ease-in-out;
}

@keyframes scanLaser{
    0%{ transform:translateY(-70px);}
    100%{ transform:translateY(70px);}
}

.cam-label{
    position:absolute;
    bottom:16px;
    background:rgba(0,0,0,.45);
    color:rgba(255,255,255,.7);
    padding:6px 16px;
    border-radius:999px;
    font-size:12px;
    backdrop-filter:blur(8px);
}

.scan-status{
    position:absolute;
    top:14px;
    left:14px;
    background:rgba(0,0,0,.45);
    padding:6px 12px;
    border-radius:999px;
    display:none;
    align-items:center;
    gap:8px;
    backdrop-filter:blur(8px);
}

.scan-status.show{
    display:flex;
}

.scan-dot{
    width:8px;
    height:8px;
    border-radius:50%;
    background:#22c55e;
    animation:pulse 1s infinite;
}

@keyframes pulse{
    50%{
        opacity:.4;
        transform:scale(.8);
    }
}

.scan-status span{
    color:white;
    font-size:11px;
    font-weight:700;
}

/* =========================================================
RIGHT
========================================================= */

.right-col{
    display:flex;
    flex-direction:column;
    gap:18px;
}

.card{
    background:white;
    border-radius:18px;
    border:1px solid var(--border);
    box-shadow:0 1px 5px rgba(0,0,0,.05);
}

.tips-card{
    padding:20px;
}

.tips-card h3{
    font-size:15px;
    font-weight:800;
    margin-bottom:16px;
}

.tip-item{
    display:flex;
    gap:10px;
    margin-bottom:12px;
    font-size:13px;
    color:var(--muted);
}

.tip-item i{
    color:var(--green);
    margin-top:2px;
}

.result-card{
    flex:1;
    min-height:180px;
    padding:22px;
    text-align:center;
    display:flex;
    flex-direction:column;
    justify-content:center;
    align-items:center;
    transition:.3s;
}

.result-card.has-result{
    background:var(--green-soft);
    border-color:#86efac;
}

/* FIX: alpha = merah */
.result-card.has-alpha{
    background:var(--red-soft);
    border-color:#fca5a5;
}

.result-card.has-error{
    background:var(--red-soft);
    border-color:#fca5a5;
}

.result-icon{
    width:60px;
    height:60px;
    border-radius:50%;
    background:#e2e8f0;
    display:flex;
    align-items:center;
    justify-content:center;
    margin-bottom:14px;
    font-size:24px;
}

.result-card.has-result .result-icon{
    background:var(--green);
    color:white;
}

.result-card.has-alpha .result-icon{
    background:var(--red);
    color:white;
}

.result-card.has-error .result-icon{
    background:var(--red);
    color:white;
}

.result-card h3{
    font-size:16px;
    font-weight:800;
}

.result-card p{
    font-size:12px;
    color:var(--muted);
}

.result-detail{
    margin-top:16px;
    width:100%;
    background:rgba(255,255,255,.75);
    border-radius:12px;
    padding:14px;
    display:none;
}

.result-card.has-result .result-detail,
.result-card.has-alpha .result-detail{
    display:block;
}

.result-row{
    display:flex;
    justify-content:space-between;
    font-size:12px;
    margin-bottom:8px;
}

.result-row:last-child{
    margin-bottom:0;
}

.result-row span:last-child{
    font-weight:700;
}

/* =========================================================
TODAY LIST
========================================================= */

.today-card{
    margin-top:22px;
}

.today-header{
    padding:18px 20px;
    border-bottom:1px solid var(--border);
    display:flex;
    justify-content:space-between;
    align-items:center;
}

.today-header h3{
    font-size:15px;
    font-weight:800;
}

.today-header span{
    background:#f1f5f9;
    padding:5px 12px;
    border-radius:999px;
    font-size:12px;
    font-weight:700;
    color:var(--muted);
}

.scan-list{
    padding:8px 0;
}

.scan-row{
    display:flex;
    align-items:center;
    gap:12px;
    padding:12px 20px;
}

.scan-row:hover{
    background:#f8fafc;
}

.scan-row-avatar{
    width:38px;
    height:38px;
    border-radius:12px;
    display:flex;
    align-items:center;
    justify-content:center;
    color:white;
    font-size:12px;
    font-weight:800;
}

.scan-row-info{
    flex:1;
}

.scan-row-info strong{
    display:block;
    font-size:13px;
}

.scan-row-info span{
    font-size:11px;
    color:var(--muted);
}

.scan-row-time{
    font-size:12px;
    color:var(--muted);
}

/* FIX: tambah badge alpha */
.badge-hadir,
.badge-terlambat,
.badge-alpha{
    padding:4px 10px;
    border-radius:999px;
    font-size:11px;
    font-weight:700;
}

.badge-hadir{
    background:var(--green-soft);
    color:var(--green);
}

.badge-terlambat{
    background:var(--orange-soft);
    color:var(--orange);
}

.badge-alpha{
    background:var(--red-soft);
    color:var(--red);
}

/* Avatar Colors */

.av-blue{ background:linear-gradient(135deg,#2563eb,#1d4ed8);}
.av-green{ background:linear-gradient(135deg,#16a34a,#15803d);}
.av-orange{ background:linear-gradient(135deg,#ea580c,#c2410c);}
.av-purple{ background:linear-gradient(135deg,#7c3aed,#6d28d9);}
.av-pink{ background:linear-gradient(135deg,#db2777,#be185d);}

/* =========================================================
TOAST
========================================================= */

.toast{
    position:fixed;
    right:26px;
    bottom:26px;
    background:var(--green);
    color:white;
    padding:14px 18px;
    border-radius:14px;
    display:flex;
    align-items:center;
    gap:10px;
    font-size:13px;
    font-weight:700;
    box-shadow:0 12px 25px rgba(0,0,0,.2);
    transform:translateY(80px);
    opacity:0;
    transition:.35s;
    z-index:999;
}

.toast.show{
    transform:translateY(0);
    opacity:1;
}

.toast.error{
    background:var(--red);
}

/* =========================================================
RESPONSIVE
========================================================= */

.btn-hamburger{
    width:40px;
    height:40px;
    border-radius:12px;
    border:1px solid var(--border);
    background:white;
    display:none;
    align-items:center;
    justify-content:center;
    cursor:pointer;
}

.sidebar-overlay{
    position:fixed;
    inset:0;
    background:rgba(0,0,0,.4);
    display:none;
    z-index:99;
}

.sidebar-overlay.show{
    display:block;
}

@media(max-width:900px){

    .scan-grid{
        grid-template-columns:1fr;
    }

}

@media(max-width:768px){

    .sidebar{
        transform:translateX(-100%);
    }

    .sidebar.open{
        transform:translateX(0);
    }

    .main{
        margin-left:0;
    }

    .content{
        padding:16px;
    }

    .topbar{
        padding:0 16px;
    }

    .btn-hamburger{
        display:flex;
    }

    .scan-actions{
        flex-direction:column;
    }

}

</style>
</head>

<body>

<div class="sidebar-overlay" id="sidebarOverlay" onclick="tutupSidebar()"></div>

<!-- SIDEBAR -->
<aside class="sidebar" id="sidebar">

    <div class="sidebar-brand">
        <div class="brand-icon">
            <i class="fa fa-qrcode"></i>
        </div>

        <div class="brand-text">
            <strong>ABSENSI QR</strong>
            <span><?= htmlspecialchars($sekolah['nama_sekolah'] ?? 'MAN 2 Banyumas') ?></span>
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

        <a href="?url=guru/dashboard" class="nav-item">
            <i class="fa fa-house"></i>
            Dashboard
        </a>

        <a href="?url=guru/scan" class="nav-item active">
            <i class="fa fa-qrcode"></i>
            Scan QR

            <span class="nav-badge" id="navScanCount">
                <?= $totalScan ?>
            </span>
        </a>

        <a href="?url=guru/riwayat" class="nav-item">
            <i class="fa fa-clock-rotate-left"></i>
            Riwayat
        </a>

        <a href="?url=guru/rekap" class="nav-item">
            <i class="fa fa-layer-group"></i>
            Rekap
        </a>

        <a href="?url=guru/monitoring" class="nav-item">
            <i class="fa fa-chart-line"></i>
            Monitoring
        </a>

    </div>

    <div class="sidebar-bottom">

        <p class="nav-section-label">Sistem</p>

        <a href="?url=guru/pengaturan" class="nav-item">
            <i class="fa fa-gear"></i>
            Pengaturan
        </a>

        <a href="?url=auth/logout" class="nav-item">
            <i class="fa fa-right-from-bracket"></i>
            Logout
        </a>

    </div>

</aside>

<!-- MAIN -->
<div class="main">

<header class="topbar">

    <div style="display:flex;align-items:center;gap:12px;">

        <button class="btn-hamburger" onclick="bukaSidebar()">
            <i class="fa fa-bars"></i>
        </button>

        <div class="topbar-left">
            <h1>Scan QR</h1>
            <p>Arahkan kamera ke QR Code siswa</p>
        </div>

    </div>

    <div class="topbar-right">

        <button class="icon-btn">
            <i class="fa fa-bell"></i>
            <span class="dot"></span>
        </button>

        <button class="icon-btn">
            <i class="fa fa-gear"></i>
        </button>

        <div class="topbar-avatar"><?= $userInit ?></div>

    </div>

</header>

<div class="content">

    <!-- ACTION -->
    <div class="scan-actions">

        <button class="btn btn-outline" onclick="simulasiScan()">
            <i class="fa fa-rotate"></i>
            Simulasi Scan
        </button>

        <button class="btn btn-primary" id="btnKamera" onclick="toggleKamera()">
            <i class="fa fa-camera"></i>
            Aktifkan Kamera
        </button>

    </div>

    <!-- GRID -->
    <div class="scan-grid">

        <!-- CAMERA -->
        <div class="camera-card">

            <video id="videoEl" autoplay playsinline></video>
            <canvas id="canvasEl"></canvas>

            <div class="cam-placeholder" id="camPlaceholder">
                <i class="fa fa-camera-slash"></i>
                <p>Kamera belum aktif</p>
            </div>

            <div class="scan-frame">
                <span></span>
            </div>

            <div class="laser"></div>

            <div class="scan-status" id="scanStatus">
                <div class="scan-dot"></div>
                <span>Scanning...</span>
            </div>

            <div class="cam-label">
                Arahkan kamera ke QR siswa
            </div>

        </div>

        <!-- RIGHT -->
        <div class="right-col">

            <!-- TIPS -->
            <div class="card tips-card">

                <h3>
                    <i class="fa fa-lightbulb" style="color:#f59e0b"></i>
                    Tips Scan QR
                </h3>

                <div class="tip-item">
                    <i class="fa fa-check"></i>
                    <span>Pencahayaan harus cukup terang</span>
                </div>

                <div class="tip-item">
                    <i class="fa fa-check"></i>
                    <span>QR Code harus jelas di kamera</span>
                </div>

                <div class="tip-item">
                    <i class="fa fa-check"></i>
                    <span>Jarak ideal 15–30 cm</span>
                </div>

                <div class="tip-item">
                    <i class="fa fa-check"></i>
                    <span>Pastikan siswa belum absen</span>
                </div>

            </div>

            <!-- RESULT -->
            <div class="card result-card" id="resultCard">

                <div class="result-icon" id="resultIcon">
                    <i class="fa fa-qrcode"></i>
                </div>

                <h3 id="resultTitle">
                    Menunggu Scan
                </h3>

                <p id="resultSub">
                    Hasil scan QR akan muncul di sini
                </p>

                <div class="result-detail" id="resultDetail">

                    <div class="result-row">
                        <span>Nama</span>
                        <span id="rNama">-</span>
                    </div>

                    <div class="result-row">
                        <span>Kelas</span>
                        <span id="rKelas">-</span>
                    </div>

                    <div class="result-row">
                        <span>Waktu</span>
                        <span id="rWaktu">-</span>
                    </div>

                    <div class="result-row">
                        <span>Status</span>
                        <span id="rStatus">-</span>
                    </div>

                </div>

            </div>

        </div>

    </div>

    <!-- TODAY -->
    <div class="card today-card">

        <div class="today-header">

            <h3>
                <i class="fa fa-list-check" style="color:#2563eb"></i>
                Scan Hari Ini
            </h3>

            <span id="totalScanLabel">
                <?= $totalScan ?> siswa
            </span>

        </div>

        <div class="scan-list" id="scanList">

            <?php if(empty($scanHariIni)): ?>

                <div class="scan-row" id="emptyState">
                    <div class="scan-row-info">
                        <strong>Belum ada scan hari ini</strong>
                        <span>Data absensi akan muncul di sini</span>
                    </div>
                </div>

            <?php else: ?>

                <?php foreach($scanHariIni as $i => $row): ?>

                    <?php
                    $warna  = $warnaList[$i % count($warnaList)];
                    $init   = inisial($row['nama'] ?? '?');
                    // FIX: cek semua status dari server (hadir / alpha / terlambat)
                    $status = strtolower($row['status'] ?? 'hadir');
                    ?>

                    <div class="scan-row">

                        <div class="scan-row-avatar <?= $warna ?>">
                            <?= $init ?>
                        </div>

                        <div class="scan-row-info">
                            <strong><?= htmlspecialchars($row['nama']) ?></strong>
                            <span><?= htmlspecialchars($row['nama_kelas'] ?? '-') ?></span>
                        </div>

                        <span class="scan-row-time">
                            <?= substr($row['waktu'] ?? '',11,5) ?>
                        </span>

                        <?php if($status === 'alpha'): ?>
                            <span class="badge-alpha">Alpha</span>
                        <?php elseif($status === 'terlambat'): ?>
                            <span class="badge-terlambat">Terlambat</span>
                        <?php else: ?>
                            <span class="badge-hadir">Hadir</span>
                        <?php endif; ?>

                    </div>

                <?php endforeach; ?>

            <?php endif; ?>

        </div>

    </div>

</div>
</div>

<!-- TOAST -->
<div class="toast" id="toast">
    <i class="fa fa-circle-check" id="toastIcon"></i>
    <span id="toastMsg">Berhasil!</span>
</div>

<script>

let kameraAktif = false;
let stream = null;
let animFrameId = null;
let scanCount = <?= $totalScan ?>;
let sedangProses = false;

const warnaList = ['av-blue','av-green','av-orange','av-purple','av-pink'];

/* =========================================================
SIDEBAR
========================================================= */

function bukaSidebar(){
    document.getElementById('sidebar').classList.add('open');
    document.getElementById('sidebarOverlay').classList.add('show');
}

function tutupSidebar(){
    document.getElementById('sidebar').classList.remove('open');
    document.getElementById('sidebarOverlay').classList.remove('show');
}

/* =========================================================
KAMERA
========================================================= */

function toggleKamera(){
    kameraAktif ? matikanKamera() : aktifkanKamera();
}

async function aktifkanKamera(){

    try{

        stream = await navigator.mediaDevices.getUserMedia({
            video:{
                facingMode:'environment'
            }
        });

        const video = document.getElementById('videoEl');

        video.srcObject = stream;
        video.style.display = 'block';

        document.getElementById('camPlaceholder').style.display = 'none';

        kameraAktif = true;

        const btn = document.getElementById('btnKamera');

        btn.classList.add('active');

        btn.innerHTML = `
            <i class="fa fa-video-slash"></i>
            Matikan Kamera
        `;

        document.getElementById('scanStatus').classList.add('show');

        video.addEventListener('loadedmetadata', mulaiScanLoop);

        showToast('Kamera aktif!', false);

    }catch(err){

        showToast('Gagal akses kamera', true);

    }

}

function matikanKamera(){

    if(stream){
        stream.getTracks().forEach(track => track.stop());
    }

    if(animFrameId){
        cancelAnimationFrame(animFrameId);
    }

    kameraAktif = false;

    document.getElementById('videoEl').style.display = 'none';
    document.getElementById('camPlaceholder').style.display = 'flex';

    document.getElementById('scanStatus').classList.remove('show');

    const btn = document.getElementById('btnKamera');

    btn.classList.remove('active');

    btn.innerHTML = `
        <i class="fa fa-camera"></i>
        Aktifkan Kamera
    `;

}

/* =========================================================
SCAN LOOP
========================================================= */

function mulaiScanLoop(){

    const video = document.getElementById('videoEl');
    const canvas = document.getElementById('canvasEl');

    const ctx = canvas.getContext('2d');

    function tick(){

        if(!kameraAktif) return;

        if(video.readyState === video.HAVE_ENOUGH_DATA){

            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;

            ctx.drawImage(video,0,0,canvas.width,canvas.height);

            const imageData = ctx.getImageData(0,0,canvas.width,canvas.height);

            const code = jsQR(
                imageData.data,
                imageData.width,
                imageData.height
            );

            if(code && !sedangProses){

                sedangProses = true;

                prosesQR(code.data);

                setTimeout(() => {
                    sedangProses = false;
                },3000);

            }

        }

        animFrameId = requestAnimationFrame(tick);

    }

    tick();

}

/* =========================================================
PROCESS QR — FIX: pakai data.status dari server
========================================================= */

function prosesQR(qrCode){

    fetch('?url=scan/api',{

        method:'POST',

        headers:{
            'Content-Type':'application/x-www-form-urlencoded'
        },

        body:'qr_code=' + encodeURIComponent(qrCode)

    })
    .then(r => r.json())
    .then(data => {

        if(data.success){

            // FIX: gunakan data.status langsung dari server
            const status = (data.status || '').toLowerCase();

            tampilkanHasil(
                data.siswa,
                data.waktu,
                status,
                true
            );

            tambahKeDaftar(
                data.siswa,
                data.waktu,
                status
            );

            showToast(data.message, status === 'alpha');

        }else{

            tampilkanError(data.message);

            showToast(data.message, true);

        }

    })
    .catch(() => {

        showToast('Server error', true);

    });

}

/* =========================================================
SIMULASI — FIX: gunakan status string
========================================================= */

function simulasiScan(){

    const dummy = [

        {
            nama:'Budi Santoso',
            nama_kelas:'XI RPL 1',
            warna:'av-blue'
        },

        {
            nama:'Siti Aisyah',
            nama_kelas:'XI RPL 2',
            warna:'av-green'
        },

        {
            nama:'Rizky Pratama',
            nama_kelas:'XI TKJ 1',
            warna:'av-purple'
        }

    ];

    const siswa = dummy[Math.floor(Math.random() * dummy.length)];

    const waktu = new Date().toTimeString().slice(0,5);

    // Simulasi: jam >= 07:30 = alpha
    const jam = new Date();
    const menitTotal = jam.getHours() * 60 + jam.getMinutes();
    const status = menitTotal > (7 * 60 + 30) ? 'alpha' : 'hadir';

    tampilkanHasil(siswa, waktu, status, true);
    tambahKeDaftar(siswa, waktu, status);

    showToast('Simulasi: ' + status, status === 'alpha');

}

/* =========================================================
HASIL — FIX: pakai status string 'hadir'/'alpha'/'terlambat'
========================================================= */

function tampilkanHasil(siswa, waktu, status, sukses){

    const card = document.getElementById('resultCard');

    if(status === 'alpha'){
        card.className = 'card result-card has-alpha';
        document.getElementById('resultIcon').innerHTML =
            '<i class="fa fa-circle-xmark"></i>';
        document.getElementById('resultTitle').textContent = 'Alpha!';
        document.getElementById('resultSub').textContent =
            'Melewati batas waktu absensi';
    } else if(status === 'terlambat'){
        card.className = 'card result-card has-alpha';
        document.getElementById('resultIcon').innerHTML =
            '<i class="fa fa-clock"></i>';
        document.getElementById('resultTitle').textContent = 'Terlambat';
        document.getElementById('resultSub').textContent =
            'Siswa datang terlambat';
    } else {
        card.className = 'card result-card has-result';
        document.getElementById('resultIcon').innerHTML =
            '<i class="fa fa-circle-check"></i>';
        document.getElementById('resultTitle').textContent = 'Absensi Berhasil!';
        document.getElementById('resultSub').textContent =
            'Data siswa ditemukan';
    }

    document.getElementById('rNama').textContent =
        siswa.nama ?? '-';

    document.getElementById('rKelas').textContent =
        siswa.nama_kelas ?? '-';

    document.getElementById('rWaktu').textContent =
        waktu;

    // FIX: tampilkan label status yang benar
    const labelMap = {
        'hadir'    : 'Hadir',
        'alpha'    : 'Alpha',
        'terlambat': 'Terlambat'
    };

    document.getElementById('rStatus').textContent =
        labelMap[status] ?? status;

}

function tampilkanError(msg){

    const card = document.getElementById('resultCard');

    card.className = 'card result-card has-error';

    document.getElementById('resultIcon').innerHTML =
        '<i class="fa fa-circle-xmark"></i>';

    document.getElementById('resultTitle').textContent =
        'Gagal';

    document.getElementById('resultSub').textContent =
        msg;

}

/* =========================================================
ADD LIST — FIX: pakai status string
========================================================= */

function tambahKeDaftar(siswa, waktu, status){

    const empty = document.getElementById('emptyState');

    if(empty){
        empty.remove();
    }

    const inisial = siswa.nama
        .split(' ')
        .map(v => v[0])
        .slice(0,2)
        .join('')
        .toUpperCase();

    const warna = siswa.warna ||
        warnaList[scanCount % warnaList.length];

    // FIX: badge berdasarkan status string
    let badgeClass, badgeLabel;

    if(status === 'alpha'){
        badgeClass = 'badge-alpha';
        badgeLabel = 'Alpha';
    } else if(status === 'terlambat'){
        badgeClass = 'badge-terlambat';
        badgeLabel = 'Terlambat';
    } else {
        badgeClass = 'badge-hadir';
        badgeLabel = 'Hadir';
    }

    const row = document.createElement('div');

    row.className = 'scan-row';

    row.innerHTML = `
        <div class="scan-row-avatar ${warna}">
            ${inisial}
        </div>

        <div class="scan-row-info">
            <strong>${siswa.nama}</strong>
            <span>${siswa.nama_kelas}</span>
        </div>

        <span class="scan-row-time">
            ${waktu}
        </span>

        <span class="${badgeClass}">
            ${badgeLabel}
        </span>
    `;

    document.getElementById('scanList')
        .prepend(row);

    scanCount++;

    document.getElementById('totalScanLabel')
        .textContent = scanCount + ' siswa';

    document.getElementById('navScanCount')
        .textContent = scanCount;

}

/* =========================================================
TOAST
========================================================= */

function showToast(msg, error){

    const toast = document.getElementById('toast');

    document.getElementById('toastMsg')
        .textContent = msg;

    toast.className = 'toast' + (
        error ? ' error' : ''
    );

    toast.classList.add('show');

    setTimeout(() => {
        toast.classList.remove('show');
    },3000);

}

</script>

</body>
</html>