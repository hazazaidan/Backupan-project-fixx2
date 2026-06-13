<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$userNama  = $user['nama']  ?? 'Budi Santoso';
$userRole  = $user['role']  ?? 'Guru';
$userKelas = $user['kelas'] ?? 'XI RPL 1';
$userInisial = strtoupper(implode('', array_map(fn($w) => $w[0], array_slice(explode(' ', $userNama), 0, 2))));

// ── Normalisasi kelasList ──
$kelasMapped = array_map(function($k) {
    return [
        'id'           => $k['id']           ?? $k['kelas_id']   ?? '',
        'nama'         => $k['nama']         ?? $k['nama_kelas'] ?? $k['kelas'] ?? '',
        'jumlah_siswa' => $k['jumlah_siswa'] ?? $k['total_siswa'] ?? $k['siswa'] ?? 0,
        'wali_kelas'   => $k['wali_kelas']   ?? $k['wali']       ?? $k['guru']  ?? '-',
        'jadwal_id'    => $k['jadwal_id']    ?? $k['id']         ?? '',
        'jurusan'      => $k['jurusan']      ?? 'UMUM',
    ];
}, $kelasList ?? []);

// ── Normalisasi mapelList ──
$mapelMapped = array_map(function($m) {
    return [
        'id'          => $m['id']             ?? '',
        'nama'        => $m['mata_pelajaran'] ?? $m['nama_mapel'] ?? $m['nama'] ?? '',
        'jam_mulai'   => $m['jam_mulai']      ?? $m['mulai']     ?? '',
        'jam_selesai' => $m['jam_selesai']    ?? $m['selesai']   ?? '',
        'hari'        => $m['hari']           ?? '',
        'ruangan'     => $m['ruangan']        ?? $m['room']      ?? '-',
        'status'      => $m['status']         ?? 'tersedia',
        'jadwal_id'   => $m['jadwal_id']      ?? $m['id']        ?? '',
    ];
}, $mapelList ?? []);

$totalSiswa  = array_sum(array_column($kelasMapped, 'jumlah_siswa'));
$jadwalAktif = count(array_filter($mapelMapped, fn($m) => ($m['status'] ?? '') === 'berjalan'));
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($title ?? 'Pilih Kelas') ?></title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<style>
*,*::before,*::after { box-sizing: border-box; margin: 0; padding: 0; }
:root {
    --sidebar-bg: #0f1729;
    --sidebar-hover: #1a2540;
    --sidebar-active: #2563eb;
    --accent: #2563eb;
    --primary:#2563eb; --primary-dark:#1d4ed8; --primary-soft:#eff6ff; --primary-mid:#bfdbfe;
    --purple:#7c3aed; --purple-soft:#f5f3ff;
    --bg:#f0f4f8; --white:#fff; --text:#0f172a; --muted:#64748b; --border:#e2e8f0;
    --green:#16a34a; --green-soft:#dcfce7;
    --orange:#d97706; --orange-soft:#fef3c7;
    --red:#ef4444; --red-soft:#fee2e2;
    --radius:16px; --radius-sm:10px;
    --shadow-sm:0 1px 3px rgba(0,0,0,.06);
    --shadow:0 4px 16px rgba(0,0,0,.08);
}

body { font-family: 'Plus Jakarta Sans', sans-serif; background: var(--bg); color: var(--text); min-height: 100vh; display: flex; font-size: 14px; }

.sidebar {
    background: var(--sidebar-bg);
    width: 260px;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    flex-shrink: 0;
    position: fixed;
    top: 0; left: 0; bottom: 0;
    z-index: 100;
}
.sidebar-brand {
    padding: 20px 20px 16px;
    border-bottom: 1px solid rgba(255,255,255,0.06);
    display: flex;
    align-items: center;
    gap: 12px;
}
.brand-icon {
    background: var(--accent);
    width: 42px;
    height: 42px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.user-card {
    margin: 16px 14px;
    background: rgba(255,255,255,0.06);
    border-radius: 12px;
    padding: 12px 14px;
    display: flex;
    align-items: center;
    gap: 12px;
}
.user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: var(--accent);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 14px;
    color: white;
    flex-shrink: 0;
}
.nav-section-label {
    padding: 12px 20px 6px;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 1.2px;
    color: rgba(255,255,255,0.35);
    text-transform: uppercase;
}
.nav-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 11px 16px;
    margin: 2px 10px;
    border-radius: 10px;
    color: rgba(255,255,255,0.6);
    font-size: 14px;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.2s;
}
.nav-item:hover { background: var(--sidebar-hover); color: white; }
.nav-item.active { background: var(--accent); color: white; }
.nav-icon { width: 18px; text-align: center; }
.sidebar-bottom {
    border-top: 1px solid rgba(255,255,255,0.07);
    padding-bottom: 8px;
    margin-top: auto;
}

.main {
    margin-left: 260px;
    flex: 1;
    display: flex;
    flex-direction: column;
    min-width: 0;
}
.topbar {
    height: 64px;
    background: #fff;
    border-bottom: 1px solid var(--border);
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0 28px;
    position: sticky;
    top: 0;
    z-index: 50;
    box-shadow: var(--shadow-sm);
}
.topbar-left { display: flex; align-items: center; gap: 12px; }
.topbar h1 { font-size: 16px; font-weight: 800; letter-spacing: -.3px; }
.topbar p  { font-size: 11px; color: var(--muted); margin-top: 1px; }
.topbar-r  { display: flex; align-items: center; gap: 8px; }
.ibtn {
    width: 38px; height: 38px;
    border-radius: var(--radius-sm);
    border: 1px solid var(--border);
    background: #fff; color: var(--muted);
    cursor: pointer; display: flex;
    align-items: center; justify-content: center;
    transition: .2s;
}
.ibtn:hover { background: var(--primary-soft); border-color: var(--primary-mid); color: var(--primary); }
.notif-dot {
    position: absolute; top: 8px; right: 8px;
    width: 7px; height: 7px;
    background: #ef4444; border-radius: 50%;
    border: 2px solid #fff;
}
.tav {
    width: 38px; height: 38px;
    border-radius: var(--radius-sm);
    background: var(--primary); color: #fff;
    font-size: 12px; font-weight: 800;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer;
}
.date-badge {
    display: flex; align-items: center; gap: 6px;
    padding: 6px 12px;
    background: var(--primary-soft);
    border: 1px solid var(--primary-mid);
    border-radius: 999px;
    font-size: 11px; font-weight: 700; color: var(--primary);
}

.content { padding: 24px 28px; flex: 1; background: var(--bg); }

.stats { display: grid; grid-template-columns: repeat(4,1fr); gap: 14px; margin-bottom: 22px; }
.stat-card {
    background: #fff; border: 1px solid var(--border);
    border-radius: var(--radius); padding: 18px;
    display: flex; align-items: center; gap: 14px;
    transition: .25s;
}
.stat-card:hover { box-shadow: var(--shadow); transform: translateY(-2px); }
.stat-ico { width: 46px; height: 46px; border-radius: 13px; display: flex; align-items: center; justify-content: center; font-size: 19px; flex-shrink: 0; }
.stat-ico.blue   { background: #eff6ff; color: var(--primary); }
.stat-ico.purple { background: #f5f3ff; color: var(--purple); }
.stat-ico.green  { background: var(--green-soft); color: var(--green); }
.stat-ico.orange { background: var(--orange-soft); color: var(--orange); }
.stat-label { font-size: 10px; color: var(--muted); font-weight: 700; text-transform: uppercase; letter-spacing: .8px; margin-bottom: 3px; }
.stat-val   { font-size: 26px; font-weight: 800; line-height: 1; letter-spacing: -1px; margin-bottom: 2px; }
.stat-sub   { font-size: 10px; color: var(--muted); font-weight: 500; }
.stat-trend { display: inline-flex; align-items: center; gap: 3px; font-size: 10px; font-weight: 700; padding: 2px 7px; border-radius: 999px; margin-top: 4px; }
.stat-trend.up   { background: var(--green-soft); color: var(--green); }
.stat-trend.info { background: var(--primary-soft); color: var(--primary); }

.stepper {
    background: #fff; border: 1px solid var(--border);
    border-radius: var(--radius); padding: 18px 24px; margin-bottom: 22px;
    display: flex; align-items: center; gap: 0;
    box-shadow: var(--shadow-sm);
}
.step { display: flex; align-items: center; gap: 10px; flex: 1; }
.step-num {
    width: 36px; height: 36px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 13px; font-weight: 800; flex-shrink: 0;
    transition: .35s; border: 2px solid transparent;
}
.step-num.done   { background: var(--green); color: #fff; border-color: var(--green); }
.step-num.active { background: var(--primary); color: #fff; border-color: var(--primary); box-shadow: 0 0 0 6px rgba(37,99,235,.12); }
.step-num.idle   { background: #f8fafc; color: #94a3b8; border-color: var(--border); }
.step-info strong { font-size: 12px; font-weight: 700; display: block; color: var(--text); }
.step-info span   { font-size: 10px; color: var(--muted); font-weight: 500; }
.step-line { flex: 1; height: 2px; background: var(--border); border-radius: 2px; margin: 0 12px; transition: .35s; }
.step-line.done   { background: var(--green); }
.step-line.active { background: linear-gradient(90deg,var(--primary) 60%,var(--border)); }

.sec-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; }
.sec-title  { font-size: 14px; font-weight: 800; display: flex; align-items: center; gap: 8px; letter-spacing: -.2px; }
.sec-title i { color: var(--primary); font-size: 13px; }
.sec-count  { font-size: 11px; font-weight: 600; color: var(--muted); background: var(--bg); padding: 3px 10px; border-radius: 999px; border: 1px solid var(--border); }

.toolbar { display: flex; gap: 8px; margin-bottom: 16px; flex-wrap: wrap; align-items: center; }
.search-wrap { position: relative; flex: 1; min-width: 200px; max-width: 340px; }
.search-wrap i { position: absolute; left: 13px; top: 50%; transform: translateY(-50%); color: var(--muted); font-size: 12px; pointer-events: none; }
.search-wrap input {
    width: 100%; padding: 9px 12px 9px 37px;
    border: 1.5px solid var(--border); border-radius: 999px;
    font-family: inherit; font-size: 13px; outline: none;
    transition: .22s; background: #fff; color: var(--text);
}
.search-wrap input:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(37,99,235,.09); }
.pills { display: flex; gap: 6px; flex-wrap: wrap; }
.pill {
    padding: 7px 16px; border: 1.5px solid var(--border);
    border-radius: 999px; background: #fff;
    font-family: inherit; font-size: 12px; font-weight: 600;
    color: var(--muted); cursor: pointer; transition: .2s;
}
.pill:hover, .pill.active { border-color: var(--primary); color: var(--primary); background: var(--primary-soft); }

.kelas-grid { display: grid; grid-template-columns: repeat(auto-fill,minmax(200px,1fr)); gap: 14px; margin-bottom: 28px; }
.kelas-card {
    background: #fff; border: 2px solid var(--border);
    border-radius: var(--radius); padding: 20px 18px 16px;
    cursor: pointer; transition: all .25s cubic-bezier(.34,1.56,.64,1);
    position: relative; overflow: hidden;
}
.kelas-card::after { content:''; position: absolute; bottom: 0; left: 0; right: 0; height: 3px; background: linear-gradient(90deg,var(--c1,#2563eb),var(--c2,#7c3aed)); opacity: 0; transition: .25s; }
.kelas-card:hover  { border-color: #93c5fd; box-shadow: 0 8px 28px rgba(37,99,235,.12); transform: translateY(-3px); }
.kelas-card:hover::after, .kelas-card.selected::after { opacity: 1; }
.kelas-card.selected { border-color: var(--primary); box-shadow: 0 8px 28px rgba(37,99,235,.18); background: var(--primary-soft); }
.kelas-badge { position: absolute; top: 12px; right: 12px; background: var(--green); color: #fff; font-size: 9px; font-weight: 700; padding: 3px 9px; border-radius: 999px; display: none; align-items: center; gap: 4px; }
.kelas-card.selected .kelas-badge { display: flex; }
.kelas-ico  { width: 52px; height: 52px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 15px; font-weight: 800; color: #fff; margin-bottom: 14px; letter-spacing: -.5px; }
.kelas-name { font-size: 16px; font-weight: 800; margin-bottom: 8px; letter-spacing: -.3px; }
.kelas-meta { font-size: 11px; color: var(--muted); display: flex; align-items: center; gap: 5px; margin-bottom: 4px; font-weight: 500; }
.kelas-meta i { font-size: 10px; width: 12px; text-align: center; flex-shrink: 0; }
.kelas-jurusan { display: inline-flex; align-items: center; margin-top: 8px; font-size: 9px; font-weight: 700; padding: 3px 9px; border-radius: 999px; letter-spacing: .5px; }
.jurusan-RPL  { background: #ede9fe; color: #7c3aed; }
.jurusan-TKJ  { background: #e0f2fe; color: #0369a1; }
.jurusan-UMUM { background: #f1f5f9; color: #475569; }

.mapel-grid { display: grid; grid-template-columns: repeat(auto-fill,minmax(250px,1fr)); gap: 14px; margin-bottom: 28px; }
.mapel-card {
    background: #fff; border: 2px solid var(--border);
    border-radius: var(--radius); padding: 16px;
    cursor: pointer; transition: all .22s;
    display: flex; gap: 13px; align-items: flex-start;
}
.mapel-card:hover  { border-color: #93c5fd; box-shadow: 0 6px 22px rgba(37,99,235,.1); transform: translateY(-2px); }
.mapel-card.selected { border-color: var(--primary); background: var(--primary-soft); box-shadow: 0 6px 22px rgba(37,99,235,.15); }
.mapel-card.disabled { cursor: not-allowed; opacity: 0.5; }
.mapel-card.disabled:hover { transform: none !important; box-shadow: none !important; border-color: var(--border) !important; }
.mapel-ico  { width: 46px; height: 46px; border-radius: 13px; display: flex; align-items: center; justify-content: center; font-size: 19px; flex-shrink: 0; }
.mapel-info { flex: 1; min-width: 0; }
.mapel-name { font-size: 13px; font-weight: 800; margin-bottom: 6px; letter-spacing: -.2px; }
.mapel-detail { font-size: 11px; color: var(--muted); display: flex; align-items: center; gap: 4px; margin-bottom: 3px; font-weight: 500; }
.mapel-detail i { font-size: 10px; width: 11px; text-align: center; flex-shrink: 0; }
.mapel-status { display: inline-flex; align-items: center; gap: 5px; margin-top: 8px; padding: 4px 10px; border-radius: 999px; font-size: 10px; font-weight: 700; }
.mapel-status.available     { background: var(--green-soft); color: var(--green); }
.mapel-status.berjalan      { background: var(--orange-soft); color: var(--orange); }
.mapel-status.tidak-tersedia { background: var(--red-soft); color: var(--red); }
.radio-wrap   { display: flex; align-items: flex-start; padding-top: 1px; }
.radio-circle { width: 21px; height: 21px; border-radius: 50%; border: 2px solid var(--border); flex-shrink: 0; transition: .22s; display: flex; align-items: center; justify-content: center; background: #fff; }
.mapel-card.selected .radio-circle { border-color: var(--primary); background: var(--primary); }
.radio-dot { width: 7px; height: 7px; border-radius: 50%; background: #fff; display: none; }
.mapel-card.selected .radio-dot { display: block; }

.empty-state { text-align: center; padding: 48px 20px; color: var(--muted); }
.empty-state i      { font-size: 42px; display: block; margin-bottom: 14px; opacity: .2; }
.empty-state strong { display: block; font-size: 14px; font-weight: 700; margin-bottom: 6px; color: var(--text); }
.empty-state p      { font-size: 12px; opacity: .7; }

.submit-bar {
    position: sticky; bottom: 0;
    background: rgba(255,255,255,.92);
    backdrop-filter: blur(16px);
    border-top: 1px solid var(--border);
    padding: 14px 28px;
    display: flex; align-items: center; justify-content: space-between; gap: 16px;
    z-index: 40;
    box-shadow: 0 -4px 20px rgba(0,0,0,.06);
}
.sub-info  { display: flex; align-items: center; gap: 10px; }
.sub-icon  { width: 36px; height: 36px; border-radius: var(--radius-sm); background: var(--primary-soft); display: flex; align-items: center; justify-content: center; font-size: 14px; color: var(--primary); }
.sub-sum   { font-size: 12px; color: var(--muted); font-weight: 500; line-height: 1.5; }
.sub-sum strong { color: var(--text); font-weight: 700; }
.btn-mulai {
    padding: 12px 28px;
    background: linear-gradient(135deg,var(--primary-dark),var(--primary));
    color: #fff; border: none; border-radius: 12px;
    font-family: inherit; font-size: 14px; font-weight: 700;
    cursor: pointer; transition: .22s;
    box-shadow: 0 6px 18px rgba(37,99,235,.3);
    display: inline-flex; align-items: center; gap: 8px;
    text-decoration: none; white-space: nowrap;
}
.btn-mulai:hover  { transform: translateY(-1px); box-shadow: 0 10px 26px rgba(37,99,235,.4); }
.btn-mulai.disabled { opacity: .35; cursor: not-allowed; transform: none !important; pointer-events: none; box-shadow: none; }

.loading-spinner { display: inline-block; width: 14px; height: 14px; border: 2px solid rgba(37,99,235,.3); border-top-color: var(--primary); border-radius: 50%; animation: spin .6s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

.hidden { display: none !important; }

@media(max-width:1180px){ .stats { grid-template-columns: repeat(2,1fr); } }
@media(max-width:768px){
    .main { margin-left: 0; }
    .content { padding: 14px 16px; }
    .topbar { padding: 0 16px; }
    .stats { grid-template-columns: repeat(2,1fr); gap: 10px; }
    .kelas-grid { grid-template-columns: repeat(2,1fr); }
    .mapel-grid { grid-template-columns: 1fr; }
    .submit-bar { padding: 10px 16px; gap: 10px; flex-direction: column; }
    .sub-sum { font-size: 11px; }
    .btn-mulai { width: 100%; justify-content: center; }
}
</style>
</head>
<body>

<aside class="sidebar">
    <div class="sidebar-brand">
        <div class="brand-icon"><i class="fa fa-qrcode text-white text-lg"></i></div>
        <div>
            <p style="color:white;font-weight:700;font-size:14px;line-height:1.2;">ABSENSI QR</p>
            <p style="color:rgba(255,255,255,0.45);font-size:10px;"><?= htmlspecialchars($user['nama_sekolah'] ?? 'Man 2 Banyumas') ?></p>
        </div>
    </div>
    <div class="user-card">
        <div class="user-avatar"><?= htmlspecialchars($userInisial) ?></div>
        <div>
            <p style="color:white;font-weight:600;font-size:13px;line-height:1.2;"><?= htmlspecialchars($userNama) ?></p>
            <p style="color:rgba(255,255,255,0.45);font-size:11px;">Guru – <?= htmlspecialchars($userKelas) ?></p>
        </div>
    </div>
    <p class="nav-section-label">Menu Utama</p>
    <nav role="navigation" aria-label="Main navigation">
        <a href="?url=guru/dashboard"  class="nav-item"><i class="fa fa-home nav-icon"></i> Dashboard</a>
        <a href="?url=guru/kelas"      class="nav-item active" aria-current="page"><i class="fa fa-door-open nav-icon"></i> Kelas</a>
        <a href="?url=guru/riwayat"    class="nav-item"><i class="fa fa-clock-rotate-left nav-icon"></i> Riwayat Absensi</a>
        <a href="?url=guru/rekap"      class="nav-item"><i class="fa fa-layer-group nav-icon"></i> Rekap Kelas</a>
        <a href="?url=guru/monitoring" class="nav-item"><i class="fa fa-chart-line nav-icon"></i> Monitoring</a>
    </nav>
    <div class="sidebar-bottom">
        <p class="nav-section-label">Sistem</p>
        <a href="?url=guru/pengaturan" class="nav-item"><i class="fa fa-gear nav-icon"></i> Pengaturan</a>
        <a href="?url=auth/logout"     class="nav-item"><i class="fa fa-right-from-bracket nav-icon"></i> Logout</a>
    </div>
</aside>

<div class="main">
<header class="topbar">
    <div class="topbar-left">
        <div>
            <h1>Pilih Kelas & Mata Pelajaran</h1>
            <p>Mulai sesi absensi untuk kelas hari ini</p>
        </div>
    </div>
    <div class="topbar-r">
        <div class="date-badge"><i class="fa fa-calendar-day"></i><span id="tglHariIni"></span></div>
        <button class="ibtn" aria-label="Notifikasi"><i class="fa fa-bell"></i><span class="notif-dot"></span></button>
        <div class="tav" title="<?= htmlspecialchars($userNama) ?>" role="img" aria-label="User avatar"><?= htmlspecialchars($userInisial) ?></div>
    </div>
</header>

<div class="content">

    <div class="stats">
        <div class="stat-card">
            <div class="stat-ico blue"><i class="fa fa-school"></i></div>
            <div>
                <div class="stat-label">Total Kelas</div>
                <div class="stat-val"><?= count($kelasMapped) ?></div>
                <div class="stat-trend info"><i class="fa fa-check"></i> Aktif semua</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-ico purple"><i class="fa fa-book-open"></i></div>
            <div>
                <div class="stat-label">Mapel Tersedia</div>
                <div class="stat-val"><?= count($mapelMapped) ?></div>
                <div class="stat-trend info"><i class="fa fa-calendar"></i> Hari ini</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-ico green"><i class="fa fa-circle-play"></i></div>
            <div>
                <div class="stat-label">Jadwal Aktif</div>
                <div class="stat-val"><?= $jadwalAktif ?></div>
                <div class="stat-trend up"><i class="fa fa-circle"></i> Sedang berjalan</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-ico orange"><i class="fa fa-users"></i></div>
            <div>
                <div class="stat-label">Total Siswa</div>
                <div class="stat-val"><?= $totalSiswa ?></div>
                <div class="stat-trend info"><i class="fa fa-user-group"></i> Seluruh kelas</div>
            </div>
        </div>
    </div>

    <div class="stepper" role="progressbar" aria-valuenow="1" aria-valuemin="1" aria-valuemax="3" aria-label="Selection progress">
        <div class="step">
            <div class="step-num active" id="step1num">1</div>
            <div class="step-info">
                <strong>Pilih Kelas</strong>
                <span id="step1sub">Belum dipilih</span>
            </div>
        </div>
        <div class="step-line" id="line1"></div>
        <div class="step">
            <div class="step-num idle" id="step2num">2</div>
            <div class="step-info">
                <strong>Pilih Mata Pelajaran</strong>
                <span id="step2sub">Pilih kelas dulu</span>
            </div>
        </div>
        <div class="step-line" id="line2"></div>
        <div class="step">
            <div class="step-num idle" id="step3num"><i class="fa fa-flag" style="font-size:11px"></i></div>
            <div class="step-info">
                <strong>Mulai Absensi</strong>
                <span>Halaman absensi siswa</span>
            </div>
        </div>
    </div>

    <div class="sec-header">
        <div class="sec-title"><i class="fa fa-chalkboard-user"></i> Kelas yang Anda Ajar</div>
        <span class="sec-count" id="kelasCount"><?= count($kelasMapped) ?> kelas</span>
    </div>
    <div class="toolbar">
        <div class="search-wrap">
            <i class="fa fa-magnifying-glass"></i>
            <input type="text" placeholder="Cari kelas atau wali kelas..." id="searchKelas" aria-label="Search classes" oninput="filterKelas()">
        </div>
        <div class="pills" role="group" aria-label="Class filters">
            <button class="pill active" onclick="setFilter('all',this)">Semua</button>
            <button class="pill" onclick="setFilter('X',this)">Kelas X</button>
            <button class="pill" onclick="setFilter('XI',this)">Kelas XI</button>
            <button class="pill" onclick="setFilter('XII',this)">Kelas XII</button>
            <button class="pill" onclick="setFilter('RPL',this)">RPL</button>
            <button class="pill" onclick="setFilter('TKJ',this)">TKJ</button>
        </div>
    </div>
    <div class="kelas-grid" id="kelasGrid" role="region" aria-label="Available classes"></div>
    <div id="emptyKelas" class="empty-state hidden">
        <i class="fa fa-magnifying-glass-minus"></i>
        <strong>Tidak ditemukan</strong>
        <p>Tidak ada kelas yang cocok dengan pencarian Anda.</p>
    </div>

    <div id="mapelSection" class="hidden">
        <div class="sec-header">
            <div class="sec-title"><i class="fa fa-book-bookmark"></i> Mata Pelajaran</div>
            <span class="sec-count" id="mapelCount">–</span>
        </div>
        <div class="mapel-grid" id="mapelGrid" role="region" aria-label="Available subjects"></div>
    </div>

</div>

<div class="submit-bar">
    <div class="sub-info">
        <div class="sub-icon" id="subIcon"><i class="fa fa-arrow-right"></i></div>
        <div class="sub-sum" id="subSum">Pilih kelas dan mata pelajaran untuk melanjutkan</div>
    </div>
    <a href="#" class="btn-mulai disabled" id="btnMulai" aria-disabled="true">
        <i class="fa fa-play"></i> Mulai Absensi
    </a>
</div>

</div>

<script>
const kelasList = <?= json_encode(array_values($kelasMapped)) ?>;
const mapelList = <?= json_encode(array_values($mapelMapped)) ?>;

const WARNA = [
    ['#2563eb','#1d4ed8'],['#7c3aed','#6d28d9'],['#0891b2','#0e7490'],
    ['#db2777','#be185d'],['#16a34a','#15803d'],['#ea580c','#c2410c'],
    ['#d97706','#b45309'],['#dc2626','#b91c1c']
];
const ICON = [
    'fa-code','fa-database','fa-book','fa-calculator','fa-star-of-david',
    'fa-briefcase','fa-language','fa-cubes','fa-pen-ruler','fa-network-wired',
    'fa-flask','fa-atom','fa-palette','fa-globe','fa-shield-halved'
];

let selectedKelas = null, selectedNamaKelas = null, selectedJadwalKelas = null;
let selectedMapel = null, selectedNamaMapel = null;
let activeFilter = 'all', currentMapels = [];
let mapelLoading = false;

const NAMA_HARI = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
const HARI_INI  = NAMA_HARI[new Date().getDay()];

(function() {
    const hari  = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
    const bulan = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agt','Sep','Okt','Nov','Des'];
    const d = new Date();
    document.getElementById('tglHariIni').textContent =
        hari[d.getDay()] + ', ' + d.getDate() + ' ' + bulan[d.getMonth()] + ' ' + d.getFullYear();
})();

function escHtml(str) {
    const map = {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'};
    return String(str||'').replace(/[&<>"']/g, m => map[m]);
}

function renderKelas(list) {
    const grid  = document.getElementById('kelasGrid');
    const empty = document.getElementById('emptyKelas');
    document.getElementById('kelasCount').textContent = list.length + ' kelas';
    if (!list.length) { 
        grid.innerHTML = ''; 
        empty.classList.remove('hidden'); 
        return; 
    }
    empty.classList.add('hidden');
    grid.innerHTML = list.map((k, i) => {
        const nama    = escHtml(k.nama || '??');
        const siswa   = k.jumlah_siswa ?? 0;
        const wali    = escHtml(k.wali_kelas || '-');
        const jur     = k.jurusan || 'UMUM';
        const inisial = k.nama ? k.nama.replace(/\s+/g,' ').split(' ').slice(0,2).map(s=>s[0]||'').join('').toUpperCase() : '??';
        const [c1,c2] = WARNA[i % WARNA.length];
        const sel     = selectedKelas === String(k.id) ? ' selected' : '';
        const kIdx    = kelasList.findIndex(x => String(x.id) === String(k.id));
        return `
            <div class="kelas-card${sel}" id="kcard-${escHtml(String(k.id))}" data-idx="${kIdx}" style="--c1:${c1};--c2:${c2}" role="button" tabindex="0" aria-label="Kelas ${nama}">
                <span class="kelas-badge"><i class="fa fa-check" style="font-size:8px"></i> Aktif</span>
                <div class="kelas-ico" style="background:linear-gradient(135deg,${c1},${c2})">${escHtml(inisial)}</div>
                <div class="kelas-name">${nama}</div>
                <div class="kelas-meta"><i class="fa fa-users"></i> ${siswa} Siswa</div>
                <div class="kelas-meta" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;display:block;padding-left:17px;margin-top:-4px;" title="${wali}">${wali}</div>
            </div>`;
    }).join('');
}

function renderMapel(list) {
    currentMapels = list;
    const grid = document.getElementById('mapelGrid');
    const jmlHariIni = list.filter(m => (m.hari||'').toLowerCase() === HARI_INI.toLowerCase()).length;
    document.getElementById('mapelCount').textContent = jmlHariIni + ' tersedia hari ini (' + HARI_INI + ')';
    if (!list.length) {
        grid.innerHTML = '<div class="empty-state"><i class="fa fa-book-open"></i><strong>Belum ada mapel</strong><p>Tidak ada mata pelajaran untuk kelas ini.</p></div>';
        return;
    }
    grid.innerHTML = list.map((m, i) => {
        const nama    = escHtml(m.nama || '-');
        const jam     = (m.jam_mulai && m.jam_selesai) ? `${escHtml(m.jam_mulai)} – ${escHtml(m.jam_selesai)}` : '-';
        const ruangan = escHtml(m.ruangan || '-');
        const hari    = escHtml(m.hari || '');
        const [c1]    = WARNA[i % WARNA.length];
        const icon    = ICON[i % ICON.length];
        const isHariIni  = hari.toLowerCase() === HARI_INI.toLowerCase();
        const statusVal  = (m.status || '').toLowerCase();
        const isBerjalan = isHariIni && (statusVal === 'berjalan' || statusVal === 'sedang berjalan');
        const disabledCls = !isHariIni ? ' disabled' : '';
        const sel         = isHariIni && selectedMapel === String(m.id) ? ' selected' : '';
        let statusHtml;
        if (!isHariIni) {
            statusHtml = `<span class="mapel-status tidak-tersedia"><i class="fa fa-lock"></i> Tidak Tersedia</span>`;
        } else if (isBerjalan) {
            statusHtml = `<span class="mapel-status berjalan"><i class="fa fa-circle-dot"></i> Sedang Berjalan</span>`;
        } else {
            statusHtml = `<span class="mapel-status available"><i class="fa fa-circle-check"></i> Tersedia</span>`;
        }
        return `
            <div class="mapel-card${sel}${disabledCls}" id="mcard-${escHtml(String(m.id))}" data-id="${escHtml(String(m.id))}" data-hari-ini="${isHariIni ? '1' : '0'}" role="button" tabindex="${isHariIni ? '0' : '-1'}" aria-label="Mapel ${nama}" ${!isHariIni ? 'aria-disabled="true"' : ''}>
                <div class="radio-wrap"><div class="radio-circle"><div class="radio-dot"></div></div></div>
                <div class="mapel-ico" style="background:${c1}18"><i class="fa ${icon}" style="color:${c1}"></i></div>
                <div class="mapel-info">
                    <div class="mapel-name">${nama}</div>
                    ${hari ? `<div class="mapel-detail"><i class="fa fa-calendar-week"></i> ${hari}</div>` : ''}
                    <div class="mapel-detail"><i class="fa fa-clock"></i> ${jam}</div>
                    <div class="mapel-detail"><i class="fa fa-building-columns"></i> ${ruangan}</div>
                    ${statusHtml}
                </div>
            </div>`;
    }).join('');
    mapelLoading = false;
}

function pilihKelas(id, nama, jadwalId) {
    if (!id) console.warn('[pilihKelas] id kosong, fallback ke nama:', nama);

    selectedKelas      = id || nama;
    selectedNamaKelas  = nama;
    selectedJadwalKelas = String(jadwalId || id);
    selectedMapel      = null;
    selectedNamaMapel  = null;

    renderKelas(getFilteredKelas());
    document.getElementById('mapelSection').classList.remove('hidden');

    document.getElementById('step1sub').textContent = nama;
    document.getElementById('step1num').className   = 'step-num done';
    document.getElementById('step1num').innerHTML   = '<i class="fa fa-check" style="font-size:11px"></i>';
    document.getElementById('line1').className      = 'step-line done';

    document.getElementById('step2num').className   = 'step-num active';
    document.getElementById('step2sub').textContent = 'Pilih sekarang';

    document.getElementById('step3num').className = 'step-num idle';
    document.getElementById('step3num').innerHTML = '<i class="fa fa-flag" style="font-size:11px"></i>';
    document.getElementById('line2').className    = 'step-line';

    updateSubmit();
    mapelLoading = true;

    fetch(`?url=guru/kelas&action=mapel&kelas=${encodeURIComponent(nama)}`)
        .then(r => r.json())
        .then(data => {
            if (!Array.isArray(data)) throw new Error('Invalid response');
            const normalized = data.map(m => ({
                id: m.id ?? '', nama: m.mata_pelajaran ?? m.nama_mapel ?? m.nama ?? '',
                jam_mulai: m.jam_mulai ?? '', jam_selesai: m.jam_selesai ?? '',
                hari: m.hari ?? '', ruangan: m.ruangan ?? '-',
                status: m.status ?? 'tersedia', jadwal_id: m.jadwal_id ?? m.id ?? '',
            }));
            renderMapel(normalized.length ? normalized : mapelList);
        })
        .catch(err => {
            console.error('Fetch error:', err);
            currentMapels = [];
            document.getElementById('mapelGrid').innerHTML =
                '<div class="empty-state"><i class="fa fa-triangle-exclamation"></i><strong>Gagal memuat mapel</strong><p>Tidak dapat mengambil data. Coba pilih kelas kembali.</p></div>';
            mapelLoading = false;
        });

    setTimeout(() => document.getElementById('mapelSection').scrollIntoView({behavior:'smooth',block:'start'}), 100);
}

function pilihMapel(id, nama) {
    selectedMapel = id || nama;
    selectedNamaMapel = nama;
    renderMapel(currentMapels);
    document.getElementById('step2sub').textContent = nama;
    document.getElementById('step2num').className   = 'step-num done';
    document.getElementById('step2num').innerHTML   = '<i class="fa fa-check" style="font-size:11px"></i>';
    document.getElementById('line2').className      = 'step-line done';
    document.getElementById('step3num').className   = 'step-num active';
    document.getElementById('step3num').innerHTML   = '<i class="fa fa-flag" style="font-size:11px"></i>';
    updateSubmit();
}

function updateSubmit() {
    const btn  = document.getElementById('btnMulai');
    const sum  = document.getElementById('subSum');
    const icon = document.getElementById('subIcon');
    if (selectedKelas && selectedMapel) {
        const url = `?url=guru/absensi&kelas_id=${encodeURIComponent(selectedKelas)}&jadwal_id=${encodeURIComponent(selectedMapel)}&kelas=${encodeURIComponent(selectedNamaKelas)}&mapel=${encodeURIComponent(selectedNamaMapel)}`;
        btn.classList.remove('disabled'); 
        btn.href = url;
        btn.setAttribute('aria-disabled', 'false');
        sum.innerHTML = `<strong>${escHtml(selectedNamaKelas)}</strong> &middot; <strong>${escHtml(selectedNamaMapel)}</strong>`;
        icon.innerHTML = '<i class="fa fa-play"></i>';
        icon.style.background = '#dcfce7'; 
        icon.style.color = '#16a34a';
    } else {
        btn.classList.add('disabled'); 
        btn.href = '#';
        btn.setAttribute('aria-disabled', 'true');
        icon.innerHTML = '<i class="fa fa-arrow-right"></i>';
        icon.style.background = '#eff6ff'; 
        icon.style.color = '#2563eb';
        sum.innerHTML = selectedKelas
            ? 'Pilih <strong>mata pelajaran</strong> untuk melanjutkan'
            : 'Pilih <strong>kelas</strong> dan <strong>mata pelajaran</strong> untuk melanjutkan';
    }
}

function getFilteredKelas() {
    const q = (document.getElementById('searchKelas').value || '').toLowerCase().trim();
    return kelasList.filter(k => {
        const nama = (k.nama || '').toUpperCase();
        const matchF = activeFilter === 'all' || nama.startsWith(activeFilter.toUpperCase());
        const matchS = !q || (k.nama||'').toLowerCase().includes(q) || (k.wali_kelas||'').toLowerCase().includes(q);
        return matchF && matchS;
    });
}

function filterKelas() { renderKelas(getFilteredKelas()); }

function setFilter(val, el) {
    activeFilter = val;
    document.querySelectorAll('.pill').forEach(b => b.classList.remove('active'));
    el.classList.add('active');
    filterKelas();
}

const kelasGrid = document.getElementById('kelasGrid');
kelasGrid.addEventListener('click', function(e) {
    const card = e.target.closest('[data-idx]');
    if (!card) return;
    const k = kelasList[parseInt(card.dataset.idx)];
    if (!k) return;
    pilihKelas(String(k.id), k.nama || '', String(k.jadwal_id || k.id));
});

kelasGrid.addEventListener('keydown', function(e) {
    if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        const card = e.target.closest('[data-idx]');
        if (!card) return;
        const k = kelasList[parseInt(card.dataset.idx)];
        if (!k) return;
        pilihKelas(String(k.id), k.nama || '', String(k.jadwal_id || k.id));
    }
});

const mapelGrid = document.getElementById('mapelGrid');
mapelGrid.addEventListener('click', function(e) {
    const card = e.target.closest('[data-id]');
    if (!card || card.dataset.hariIni === '0') return;
    const targetId = card.dataset.id;
    const m = currentMapels.find(x => String(x.id) === targetId);
    if (!m) return;
    pilihMapel(String(m.id), m.nama || '');
});

mapelGrid.addEventListener('keydown', function(e) {
    if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        const card = e.target.closest('[data-id]');
        if (!card || card.dataset.hariIni === '0') return;
        const targetId = card.dataset.id;
        const m = currentMapels.find(x => String(x.id) === targetId);
        if (!m) return;
        pilihMapel(String(m.id), m.nama || '');
    }
});

renderKelas(kelasList);
if (mapelList.length) renderMapel(mapelList);
updateSubmit();
</script>
</body>
</html>