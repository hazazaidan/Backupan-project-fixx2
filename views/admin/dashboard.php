<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$currentUrl = trim($_GET['url'] ?? 'admin/dashboard', '/');

$totalSiswa    = $totalSiswa    ?? 0;
$totalGuru     = $totalGuru     ?? 0;
$totalKelas    = $totalKelas    ?? 0;
$kehadiranHari = $kehadiranHari ?? 0;
$persenHadir   = $persenHadir   ?? ($totalSiswa > 0 ? round(($kehadiranHari / $totalSiswa) * 100) : 0);

$izinHariIni  = $izinHariIni  ?? 0;
$sakitHariIni = $sakitHariIni ?? 0;
$alphaHariIni = $alphaHariIni ?? 0;

$chartLabels = $chartLabels ?? ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];
$chartHadir  = $chartHadir  ?? [0,0,0,0,0,0,0];
$chartAlpha  = $chartAlpha  ?? [0,0,0,0,0,0,0];
$chartIzin   = $chartIzin   ?? [0,0,0,0,0,0,0];
$chartSakit  = $chartSakit  ?? [0,0,0,0,0,0,0];

$aktivitas = $aktivitas ?? [];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin — Absensi QR</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    <style>
        :root {
            /* ── DISAMAKAN DENGAN SIDEBAR GURU ── */
            --sb: #0f1729;
            --sb2: #1a2540;
            --sb-active: #2563eb;
            --sb-hover: rgba(37,99,235,0.15);
            --sb-text: rgba(255,255,255,0.6);
            --sb-text-active: #fff;
            --accent: #2563eb;
            --accent2: #1d4ed8;
            --bg: #f1f5f9;
            --card: #ffffff;
            --text: #0f172a;
            --text2: #64748b;
            --border: #e2e8f0;
            --sidebar-w: 255px;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Poppins', sans-serif; background: var(--bg); color: var(--text); }

        .admin-layout { display: flex; min-height: 100vh; }
        .admin-main {
            margin-left: var(--sidebar-w);
            flex: 1; display: flex; flex-direction: column;
            min-height: 100vh; background: var(--bg);
        }

        /* TOPBAR */
        .admin-topbar {
            background: white; border-bottom: 1px solid var(--border);
            padding: 13px 28px; display: flex; align-items: center;
            justify-content: space-between; position: sticky; top: 0;
            z-index: 50; box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        .topbar-left { display: flex; align-items: center; gap: 14px; }
        .topbar-toggle { background: none; border: none; font-size: 18px; color: var(--text2); cursor: pointer; padding: 4px; border-radius: 6px; }
        .topbar-toggle:hover { background: var(--bg); }
        .topbar-title h2 { font-size: 16px; font-weight: 700; color: var(--text); }
        .topbar-title p  { font-size: 12px; color: var(--text2); }
        .topbar-right { display: flex; align-items: center; gap: 10px; }
        .topbar-icon-btn {
            width: 36px; height: 36px; border-radius: 9px;
            border: 1px solid var(--border); background: white;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; color: var(--text2); font-size: 15px;
            transition: all 0.15s; position: relative;
        }
        .topbar-icon-btn:hover { background: var(--bg); color: var(--accent); border-color: var(--accent); }
        .notif-badge {
            position: absolute; top: -4px; right: -4px;
            width: 16px; height: 16px; background: #ef4444;
            border-radius: 50%; font-size: 9px; font-weight: 700;
            color: white; display: flex; align-items: center; justify-content: center;
            border: 2px solid white;
        }
        .topbar-avatar {
            width: 36px; height: 36px; border-radius: 50%;
            background: var(--accent);
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 13px; color: white; cursor: pointer;
        }
        .btn-refresh {
            display: inline-flex; align-items: center; gap: 7px;
            background: var(--accent); color: white; border: none;
            padding: 8px 16px; border-radius: 9px; font-size: 12.5px;
            font-weight: 600; cursor: pointer; font-family: 'Poppins', sans-serif;
            transition: all 0.15s;
        }
        .btn-refresh:hover { background: #1d4ed8; box-shadow: 0 4px 12px rgba(37,99,235,0.35); }
        .btn-refresh.spinning i { animation: spin 0.7s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }

        .date-badge {
            display: inline-flex; align-items: center; gap: 7px;
            background: var(--bg); border: 1px solid var(--border);
            padding: 6px 14px; border-radius: 20px;
            font-size: 12px; font-weight: 600; color: var(--text2);
        }
        .date-badge i { color: var(--accent); font-size: 13px; }

        .admin-content { padding: 24px 28px; flex: 1; }

        /* STAT CARDS */
        .stat-cards { display: grid; grid-template-columns: repeat(4, 1fr); gap: 18px; margin-bottom: 24px; }
        .stat-card {
            background: white; border-radius: 16px; padding: 22px 22px 18px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.05);
            display: flex; flex-direction: column; gap: 12px;
            transition: transform 0.2s, box-shadow 0.2s;
            overflow: hidden; position: relative;
        }
        .stat-card::after {
            content: ''; position: absolute; bottom: -18px; right: -18px;
            width: 70px; height: 70px; border-radius: 50%; opacity: 0.07;
        }
        .stat-card:hover { transform: translateY(-3px); box-shadow: 0 8px 24px rgba(37,99,235,0.12); }
        .sc-top { display: flex; align-items: flex-start; justify-content: space-between; }
        .sc-icon {
            width: 48px; height: 48px; border-radius: 13px;
            display: flex; align-items: center; justify-content: center;
            font-size: 22px; flex-shrink: 0;
        }
        .sc-badge {
            display: inline-flex; align-items: center; gap: 4px;
            font-size: 11px; font-weight: 600; padding: 3px 9px; border-radius: 20px;
        }
        .sc-val { font-size: 32px; font-weight: 800; color: var(--text); line-height: 1; }
        .sc-label { font-size: 12px; color: var(--text2); margin-top: 3px; }
        .sc-bar { height: 4px; border-radius: 2px; background: var(--border); overflow: hidden; margin-top: 2px; }
        .sc-bar-fill { height: 100%; border-radius: 2px; transition: width 1s ease; }

        .sc-siswa  .sc-icon { background: #eff6ff; color: #2563eb; }
        .sc-siswa  .sc-bar-fill { background: linear-gradient(90deg,#3b82f6,#2563eb); }
        .sc-siswa::after { background: #2563eb; }
        .sc-guru   .sc-icon { background: #f0fdf4; color: #16a34a; }
        .sc-guru   .sc-bar-fill { background: linear-gradient(90deg,#22c55e,#16a34a); }
        .sc-guru::after { background: #16a34a; }
        .sc-kelas  .sc-icon { background: #fef3c7; color: #d97706; }
        .sc-kelas  .sc-bar-fill { background: linear-gradient(90deg,#f59e0b,#d97706); }
        .sc-kelas::after { background: #d97706; }
        .sc-hadir  .sc-icon { background: #dcfce7; color: #16a34a; }
        .sc-hadir  .sc-bar-fill { background: linear-gradient(90deg,#22c55e,#16a34a); }
        .sc-hadir::after { background: #16a34a; }

        /* GRID */
        .dash-grid { display: grid; grid-template-columns: 1fr 340px; gap: 20px; margin-bottom: 20px; }

        /* CHART CARD */
        .chart-card {
            background: white; border-radius: 16px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.05); overflow: hidden;
        }
        .chart-card-header {
            padding: 18px 22px 14px; border-bottom: 1px solid var(--border);
            display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;
        }
        .chart-card-header h3 { font-size: 14px; font-weight: 700; color: var(--text); }
        .chart-legend { display: flex; align-items: center; gap: 14px; flex-wrap: wrap; }
        .legend-dot { display: flex; align-items: center; gap: 5px; font-size: 11px; color: var(--text2); font-weight: 500; }
        .legend-dot span { width: 10px; height: 10px; border-radius: 3px; display: inline-block; }
        .chart-body { padding: 16px 20px 20px; }

        /* DONUT */
        .donut-card {
            background: white; border-radius: 16px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.05); overflow: hidden;
        }
        .donut-card-header { padding: 18px 22px 14px; border-bottom: 1px solid var(--border); }
        .donut-card-header h3 { font-size: 14px; font-weight: 700; color: var(--text); }
        .donut-card-body { padding: 20px; display: flex; flex-direction: column; align-items: center; gap: 16px; }
        .donut-wrap { position: relative; width: 160px; height: 160px; }
        .donut-center {
            position: absolute; inset: 0;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
        }
        .donut-pct { font-size: 26px; font-weight: 800; color: var(--text); line-height: 1; }
        .donut-sub { font-size: 10px; color: var(--text2); font-weight: 500; }
        .donut-legend { width: 100%; display: flex; flex-direction: column; gap: 8px; }
        .dl-item { display: flex; align-items: center; justify-content: space-between; }
        .dl-left { display: flex; align-items: center; gap: 8px; font-size: 12px; color: var(--text2); }
        .dl-dot { width: 10px; height: 10px; border-radius: 3px; flex-shrink: 0; }
        .dl-val { font-size: 12px; font-weight: 700; color: var(--text); }

        /* BOTTOM */
        .bottom-grid { display: grid; grid-template-columns: 1fr 340px; gap: 20px; }

        /* TABLE CARD */
        .table-card {
            background: white; border-radius: 16px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.05); overflow: hidden;
        }
        .table-card-header {
            padding: 16px 22px; border-bottom: 1px solid var(--border);
            display: flex; align-items: center; justify-content: space-between;
        }
        .table-card-header h3 { font-size: 14px; font-weight: 700; color: var(--text); }
        table.dash-table { width: 100%; border-collapse: collapse; }
        table.dash-table thead th {
            padding: 10px 16px; font-size: 10.5px; font-weight: 700;
            color: var(--text2); text-transform: uppercase; letter-spacing: 0.6px;
            background: #f8fafc; border-bottom: 1px solid var(--border); text-align: left;
        }
        table.dash-table tbody td {
            padding: 11px 16px; font-size: 13px; color: #374151;
            border-bottom: 1px solid #f9fafb;
        }
        table.dash-table tbody tr:last-child td { border-bottom: none; }
        table.dash-table tbody tr:hover td { background: #f8fafc; }
        .ava {
            width: 32px; height: 32px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 11px; color: white; flex-shrink: 0;
        }
        .badge { display: inline-flex; align-items: center; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; }
        .badge-success { background: #dcfce7; color: #16a34a; }
        .badge-warning { background: #fef3c7; color: #d97706; }
        .badge-danger  { background: #fee2e2; color: #dc2626; }
        .badge-info    { background: #dbeafe; color: #2563eb; }
        .badge-purple  { background: #ede9fe; color: #7c3aed; }

        /* QUICK ACCESS */
        .qa-card {
            background: white; border-radius: 16px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.05); overflow: hidden;
        }
        .qa-card-header { padding: 16px 22px; border-bottom: 1px solid var(--border); }
        .qa-card-header h3 { font-size: 14px; font-weight: 700; color: var(--text); }
        .qa-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; padding: 16px; }
        .qa-item {
            display: flex; flex-direction: column; align-items: center;
            gap: 8px; padding: 16px 10px; border-radius: 12px;
            background: var(--bg); border: 1px solid var(--border);
            cursor: pointer; text-decoration: none; transition: all 0.15s; text-align: center;
        }
        .qa-item:hover { border-color: var(--accent); background: #eff6ff; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(37,99,235,0.12); }
        .qa-icon { width: 42px; height: 42px; border-radius: 11px; display: flex; align-items: center; justify-content: center; font-size: 20px; }
        .qa-label { font-size: 11.5px; font-weight: 600; color: var(--text); }

        /* WELCOME BANNER */
        .welcome-banner {
            background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 60%, #3b82f6 100%);
            border-radius: 16px; padding: 22px 28px;
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 22px; overflow: hidden; position: relative;
            color: white; box-shadow: 0 8px 24px rgba(37,99,235,0.3);
        }
        .wb-bg  { position: absolute; right: -20px; top: -20px; width: 180px; height: 180px; border-radius: 50%; background: rgba(255,255,255,0.06); }
        .wb-bg2 { position: absolute; right: 60px; bottom: -30px; width: 120px; height: 120px; border-radius: 50%; background: rgba(255,255,255,0.04); }
        .wb-title { font-size: 18px; font-weight: 800; line-height: 1.3; }
        .wb-sub { font-size: 12.5px; opacity: 0.8; margin-top: 4px; }
        .wb-right { display: flex; align-items: center; gap: 10px; position: relative; }

        /* RESPONSIVE */
        @media (max-width: 1024px) {
            .admin-main { margin-left: 0; }
            .dash-grid { grid-template-columns: 1fr; }
            .bottom-grid { grid-template-columns: 1fr; }
            .stat-cards { grid-template-columns: repeat(2,1fr); }
        }
        @media (max-width: 640px) {
            .stat-cards { grid-template-columns: 1fr 1fr; }
            .admin-content { padding: 14px 16px; }
            .admin-topbar { padding: 11px 16px; }
            .welcome-banner { flex-direction: column; align-items: flex-start; gap: 14px; }
            .topbar-title p { display: none; }
        }
        @media (max-width: 400px) { .stat-cards { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
<div class="admin-layout">

    <?php include(dirname(__DIR__) . '/layouts/sidebar_admin.php'); ?>

    <main class="admin-main">

        <!-- TOPBAR -->
        <div class="admin-topbar">
            <div class="topbar-left">
                <button class="topbar-toggle" onclick="toggleSidebar()">
                    <i class="bi bi-list"></i>
                </button>
                <div class="topbar-title">
                    <h2>Dashboard</h2>
                    <p>Selamat datang kembali 👋</p>
                </div>
            </div>
            <div class="topbar-right">
                <span class="date-badge d-none d-md-inline-flex">
                    <i class="bi bi-calendar3"></i>
                    <span id="tanggalHariIni"></span>
                </span>
                <button class="btn-refresh" id="btnRefresh" onclick="refreshDashboard()">
                    <i class="bi bi-arrow-clockwise"></i>
                    <span class="d-none d-sm-inline">Refresh</span>
                </button>
                <div class="topbar-icon-btn">
                    <i class="bi bi-bell"></i>
                    <span class="notif-badge">3</span>
                </div>
                <div class="topbar-avatar" title="<?= htmlspecialchars($_SESSION['user']['nama'] ?? 'Admin') ?>">
                    <?php
                        $n = $_SESSION['user']['nama'] ?? 'A';
                        $parts = explode(' ', $n);
                        echo strtoupper(implode('', array_map(fn($w)=>$w[0], array_slice($parts,0,2))));
                    ?>
                </div>
            </div>
        </div>

        <!-- CONTENT -->
        <div class="admin-content">

            <!-- WELCOME BANNER -->
            <div class="welcome-banner">
                <div>
                    <div class="wb-title">Halo, <?= htmlspecialchars(explode(' ', $_SESSION['user']['nama'] ?? 'Admin')[0]) ?>! 👋</div>
                    <div class="wb-sub">Pantau kehadiran siswa hari ini dengan mudah &amp; cepat.</div>
                </div>
                <div class="wb-right">
                    <button class="btn-refresh" style="background:rgba(255,255,255,0.2);border:1px solid rgba(255,255,255,0.3);" onclick="window.location.href='?url=admin/laporan'">
                        <i class="bi bi-file-earmark-bar-graph-fill"></i>
                        Lihat Laporan
                    </button>
                </div>
                <div class="wb-bg"></div>
                <div class="wb-bg2"></div>
            </div>

            <!-- STAT CARDS -->
            <div class="stat-cards">
                <div class="stat-card sc-siswa">
                    <div class="sc-top">
                        <div class="sc-icon"><i class="bi bi-people-fill"></i></div>
                        <span class="sc-badge" style="background:#eff6ff;color:#2563eb;">
                            <i class="bi bi-arrow-up"></i> 2.4%
                        </span>
                    </div>
                    <div>
                        <div class="sc-val"><?= number_format($totalSiswa) ?></div>
                        <div class="sc-label">Total Siswa</div>
                    </div>
                    <div class="sc-bar"><div class="sc-bar-fill" style="width:82%"></div></div>
                </div>

                <div class="stat-card sc-guru">
                    <div class="sc-top">
                        <div class="sc-icon"><i class="bi bi-person-badge-fill"></i></div>
                        <span class="sc-badge" style="background:#f0fdf4;color:#16a34a;">
                            <i class="bi bi-dash"></i> Tetap
                        </span>
                    </div>
                    <div>
                        <div class="sc-val"><?= number_format($totalGuru) ?></div>
                        <div class="sc-label">Total Guru</div>
                    </div>
                    <div class="sc-bar"><div class="sc-bar-fill" style="width:65%"></div></div>
                </div>

                <div class="stat-card sc-kelas">
                    <div class="sc-top">
                        <div class="sc-icon"><i class="bi bi-building"></i></div>
                        <span class="sc-badge" style="background:#fef3c7;color:#d97706;">
                            <i class="bi bi-dash"></i> Aktif
                        </span>
                    </div>
                    <div>
                        <div class="sc-val"><?= number_format($totalKelas) ?></div>
                        <div class="sc-label">Total Kelas</div>
                    </div>
                    <div class="sc-bar"><div class="sc-bar-fill" style="width:55%"></div></div>
                </div>

                <div class="stat-card sc-hadir">
                    <div class="sc-top">
                        <div class="sc-icon"><i class="bi bi-check-circle-fill"></i></div>
                        <span class="sc-badge" style="background:#dcfce7;color:#16a34a;">
                            <?= $persenHadir ?>%
                        </span>
                    </div>
                    <div>
                        <div class="sc-val"><?= number_format($kehadiranHari) ?></div>
                        <div class="sc-label">Hadir Hari Ini</div>
                    </div>
                    <div class="sc-bar"><div class="sc-bar-fill" style="width:<?= $persenHadir ?>%"></div></div>
                </div>
            </div>

            <!-- CHART + DONUT -->
            <div class="dash-grid">
                <div class="chart-card">
                    <div class="chart-card-header">
                        <div>
                            <h3><i class="bi bi-bar-chart-line-fill me-2" style="color:var(--accent)"></i>Statistik Kehadiran 7 Hari</h3>
                        </div>
                        <div class="chart-legend">
                            <span class="legend-dot"><span style="background:#2563eb"></span>Hadir</span>
                            <span class="legend-dot"><span style="background:#f59e0b"></span>Izin</span>
                            <span class="legend-dot"><span style="background:#3b82f6"></span>Sakit</span>
                            <span class="legend-dot"><span style="background:#ef4444"></span>Alpha</span>
                        </div>
                    </div>
                    <div class="chart-body">
                        <canvas id="chartKehadiran" height="200"></canvas>
                    </div>
                </div>

                <div class="donut-card">
                    <div class="donut-card-header">
                        <h3><i class="bi bi-pie-chart-fill me-2" style="color:var(--accent)"></i>Kehadiran Hari Ini</h3>
                    </div>
                    <div class="donut-card-body">
                        <div class="donut-wrap">
                            <canvas id="chartDonut"></canvas>
                            <div class="donut-center">
                                <div class="donut-pct"><?= $persenHadir ?>%</div>
                                <div class="donut-sub">Hadir</div>
                            </div>
                        </div>
                        <div class="donut-legend">
                            <?php
                            $items = [
                                ['Hadir', $kehadiranHari, '#2563eb'],
                                ['Izin',  $izinHariIni,   '#f59e0b'],
                                ['Sakit', $sakitHariIni,  '#3b82f6'],
                                ['Alpha', $alphaHariIni,  '#ef4444'],
                            ];
                            foreach ($items as $it): ?>
                            <div class="dl-item">
                                <div class="dl-left">
                                    <span class="dl-dot" style="background:<?= $it[2] ?>"></span>
                                    <?= $it[0] ?>
                                </div>
                                <span class="dl-val"><?= $it[1] ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ACTIVITY + QUICK ACCESS -->
            <div class="bottom-grid">
                <div class="table-card">
                    <div class="table-card-header">
                        <h3><i class="bi bi-activity me-2" style="color:var(--accent)"></i>Aktivitas Absensi Terkini</h3>
                        <a href="?url=admin/laporan" style="font-size:12px;color:var(--accent);text-decoration:none;font-weight:600;">
                            Lihat Semua <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                    <table class="dash-table">
                        <thead>
                            <tr>
                                <th>Siswa</th>
                                <th>Kelas</th>
                                <th>Status</th>
                                <th>Waktu</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        $avatarColors = ['#2563eb','#1d4ed8','#3b82f6','#d97706','#16a34a','#dc2626'];
                        $badgeMap = [
                            'hadir' => 'badge-success',
                            'izin'  => 'badge-warning',
                            'sakit' => 'badge-info',
                            'alpha' => 'badge-danger',
                        ];
                        if (empty($aktivitas)): ?>
                            <tr><td colspan="4" style="text-align:center;padding:24px;color:#94a3b8;">Belum ada aktivitas hari ini</td></tr>
                        <?php else: ?>
                        <?php foreach ($aktivitas as $i => $row):
                            $parts   = explode(' ', $row['nama']);
                            $inisial = strtoupper(implode('', array_map(fn($w)=>$w[0], array_slice($parts,0,2))));
                            $color   = $avatarColors[$i % count($avatarColors)];
                            $badge   = $badgeMap[strtolower($row['status'])] ?? 'badge-info';
                        ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="ava" style="background:<?= $color ?>"><?= $inisial ?></div>
                                    <span><?= htmlspecialchars($row['nama']) ?></span>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($row['kelas']) ?></td>
                            <td><span class="badge <?= $badge ?>"><?= ucfirst($row['status']) ?></span></td>
                            <td><?= htmlspecialchars($row['waktu']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="qa-card">
                    <div class="qa-card-header">
                        <h3><i class="bi bi-lightning-charge-fill me-2" style="color:var(--accent)"></i>Akses Cepat</h3>
                    </div>
                    <div class="qa-grid">
                        <a href="?url=admin/siswa#tambah" class="qa-item">
                            <div class="qa-icon" style="background:#eff6ff;color:#2563eb;"><i class="bi bi-person-plus-fill"></i></div>
                            <span class="qa-label">Tambah Siswa</span>
                        </a>
                        <a href="?url=admin/guru#tambah" class="qa-item">
                            <div class="qa-icon" style="background:#f0fdf4;color:#16a34a;"><i class="bi bi-person-badge-fill"></i></div>
                            <span class="qa-label">Tambah Guru</span>
                        </a>
                        <!-- ✅ FIX: link diubah ke #tambah agar modal otomatis terbuka -->
                        <a href="?url=admin/kelas#tambah" class="qa-item">
                            <div class="qa-icon" style="background:#fef3c7;color:#d97706;"><i class="bi bi-building-add"></i></div>
                            <span class="qa-label">Tambah Kelas</span>
                        </a>
                        <a href="?url=admin/laporan" class="qa-item">
                            <div class="qa-icon" style="background:#dcfce7;color:#16a34a;"><i class="bi bi-file-earmark-bar-graph-fill"></i></div>
                            <span class="qa-label">Laporan</span>
                        </a>
                        <!-- ✅ HAPUS: Scan QR dihapus -->
                        <a href="?url=admin/pengaturan" class="qa-item">
                            <div class="qa-icon" style="background:#f1f5f9;color:#64748b;"><i class="bi bi-gear-fill"></i></div>
                            <span class="qa-label">Pengaturan</span>
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function() {
    const days   = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
    const months = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    const now = new Date();
    document.getElementById('tanggalHariIni').textContent =
        days[now.getDay()] + ', ' + now.getDate() + ' ' + months[now.getMonth()] + ' ' + now.getFullYear();
})();

function refreshDashboard() {
    const btn = document.getElementById('btnRefresh');
    btn.classList.add('spinning');
    setTimeout(() => { btn.classList.remove('spinning'); window.location.reload(); }, 900);
}

// BAR CHART
const ctxBar = document.getElementById('chartKehadiran').getContext('2d');
new Chart(ctxBar, {
    type: 'bar',
    data: {
        labels: <?= json_encode($chartLabels) ?>,
        datasets: [
            { label:'Hadir', data:<?= json_encode($chartHadir) ?>, backgroundColor:'#2563eb', borderRadius:7, borderSkipped:false },
            { label:'Izin',  data:<?= json_encode($chartIzin)  ?>, backgroundColor:'#f59e0b', borderRadius:7, borderSkipped:false },
            { label:'Sakit', data:<?= json_encode($chartSakit) ?>, backgroundColor:'#3b82f6', borderRadius:7, borderSkipped:false },
            { label:'Alpha', data:<?= json_encode($chartAlpha) ?>, backgroundColor:'#ef4444', borderRadius:7, borderSkipped:false },
        ]
    },
    options: {
        responsive: true, maintainAspectRatio: true,
        plugins: {
            legend: { display: false },
            tooltip: { backgroundColor:'#0f1729', padding:10, titleFont:{family:'Poppins',weight:'700',size:13}, bodyFont:{family:'Poppins',size:12}, cornerRadius:8 }
        },
        scales: {
            x: { stacked:false, grid:{display:false}, ticks:{font:{family:'Poppins',size:12},color:'#64748b'} },
            y: { beginAtZero:true, grid:{color:'#f1f5f9'}, ticks:{font:{family:'Poppins',size:11},color:'#94a3b8'} }
        }
    }
});

// DONUT CHART
const ctxDonut = document.getElementById('chartDonut').getContext('2d');
new Chart(ctxDonut, {
    type: 'doughnut',
    data: {
        labels: ['Hadir','Izin','Sakit','Alpha'],
        datasets: [{
            data: [<?= (int)$kehadiranHari ?>, <?= (int)$izinHariIni ?>, <?= (int)$sakitHariIni ?>, <?= (int)$alphaHariIni ?>],
            backgroundColor: ['#2563eb','#f59e0b','#3b82f6','#ef4444'],
            borderWidth: 3, borderColor: '#fff', hoverOffset: 6,
        }]
    },
    options: {
        cutout: '72%', responsive: true,
        plugins: {
            legend: { display: false },
            tooltip: { backgroundColor:'#0f1729', padding:10, titleFont:{family:'Poppins',weight:'700'}, bodyFont:{family:'Poppins'}, cornerRadius:8 }
        }
    }
});
</script>
</body>
</html>