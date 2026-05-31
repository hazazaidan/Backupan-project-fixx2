<?php
if (session_status() === PHP_SESSION_NONE) session_start();
date_default_timezone_set('Asia/Jakarta');

$nama  = $_SESSION['user']['nama']  ?? 'Guru';
$kelas = $_SESSION['user']['kelas'] ?? 'XI RPL 1';
$inisial = strtoupper(implode('', array_map(fn($w) => $w[0], array_slice(explode(' ', $nama), 0, 2))));

$bulan = [
    'January'=>'Januari','February'=>'Februari','March'=>'Maret','April'=>'April',
    'May'=>'Mei','June'=>'Juni','July'=>'Juli','August'=>'Agustus',
    'September'=>'September','October'=>'Oktober','November'=>'November','December'=>'Desember'
];
$hari = [
    'Sunday'=>'Minggu','Monday'=>'Senin','Tuesday'=>'Selasa',
    'Wednesday'=>'Rabu','Thursday'=>'Kamis','Friday'=>'Jumat','Saturday'=>'Sabtu'
];
$tanggalHariIni = $hari[date('l')] . ', ' . date('d') . ' ' . $bulan[date('F')] . ' ' . date('Y');

$totalSiswa       = $totalSiswa       ?? 0;
$hadirHariIni     = $hadirHariIni     ?? 0;
$terlambatHariIni = $terlambatHariIni ?? 0;
$izinHariIni      = $izinHariIni      ?? 0;
$alphaHariIni     = $alphaHariIni     ?? 0;
$belumAbsen       = $belumAbsen       ?? 0;
$siswaTerlambat   = $siswaTerlambat   ?? [];
$rekapKelas       = $rekapKelas       ?? [];
$chartLabels      = $chartLabels      ?? [];
$chartHadir       = $chartHadir       ?? [];
$chartTerlambat   = $chartTerlambat   ?? [];
$chartAlpha       = $chartAlpha       ?? [];

// Hitung persentase kehadiran hari ini
$persenHadir = $totalSiswa > 0 ? round($hadirHariIni / $totalSiswa * 100, 1) : 0;

$warnaList = ['#6366f1','#0ea5e9','#f59e0b','#10b981','#ec4899','#8b5cf6','#ef4444','#14b8a6'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Monitoring') ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        * { font-family: 'Plus Jakarta Sans', sans-serif; }
        :root {
            --sidebar-bg: #0f1729;
            --sidebar-hover: #1a2540;
            --accent: #2563eb;
            --content-bg: #f1f5f9;
        }
        body { background: var(--content-bg); }
        .sidebar { background: var(--sidebar-bg); width: 260px; min-height: 100vh; display: flex; flex-direction: column; flex-shrink: 0; }
        .sidebar-brand { padding: 20px 20px 16px; border-bottom: 1px solid rgba(255,255,255,0.06); }
        .brand-icon { background: var(--accent); width: 42px; height: 42px; border-radius: 10px; display: flex; align-items: center; justify-content: center; }
        .user-card { margin: 16px 14px; background: rgba(255,255,255,0.06); border-radius: 12px; padding: 12px 14px; display: flex; align-items: center; gap: 12px; }
        .user-avatar { width: 40px; height: 40px; border-radius: 50%; background: var(--accent); display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 14px; color: white; flex-shrink: 0; }
        .nav-section-label { padding: 12px 20px 6px; font-size: 10px; font-weight: 700; letter-spacing: 1.2px; color: rgba(255,255,255,0.35); text-transform: uppercase; }
        .nav-item { display: flex; align-items: center; gap: 12px; padding: 11px 16px; margin: 2px 10px; border-radius: 10px; color: rgba(255,255,255,0.6); font-size: 14px; font-weight: 500; text-decoration: none; transition: all 0.2s; }
        .nav-item:hover { background: var(--sidebar-hover); color: white; }
        .nav-item.active { background: var(--accent); color: white; }
        .nav-icon { width: 18px; text-align: center; }
        .topbar { background: white; border-bottom: 1px solid #e2e8f0; padding: 14px 28px; display: flex; align-items: center; justify-content: space-between; }
        .content-scroll { flex: 1; overflow-y: auto; background: var(--content-bg); }

        /* Stat Cards */
        .stat-card { background: white; border-radius: 16px; padding: 20px 22px; box-shadow: 0 1px 4px rgba(0,0,0,0.06); position: relative; overflow: hidden; }
        .stat-card::after { content: ''; position: absolute; bottom: -20px; right: -20px; width: 80px; height: 80px; border-radius: 50%; opacity: 0.06; }
        .stat-card.blue::after   { background: #2563eb; }
        .stat-card.green::after  { background: #16a34a; }
        .stat-card.yellow::after { background: #d97706; }
        .stat-card.orange::after { background: #ea580c; }
        .stat-card.red::after    { background: #dc2626; }
        .stat-icon-wrap { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 18px; margin-bottom: 12px; }
        .stat-persen { display: inline-flex; align-items: center; gap: 4px; font-size: 11px; font-weight: 600; padding: 2px 8px; border-radius: 20px; margin-top: 6px; }
        .stat-persen.up   { background: #dcfce7; color: #16a34a; }
        .stat-persen.down { background: #fee2e2; color: #dc2626; }
        .stat-persen.warn { background: #fef3c7; color: #d97706; }

        /* Chart section */
        .chart-section { background: white; border-radius: 16px; padding: 22px 24px; box-shadow: 0 1px 4px rgba(0,0,0,0.06); }

        /* Table section */
        .table-section { background: white; border-radius: 16px; padding: 22px 24px; box-shadow: 0 1px 4px rgba(0,0,0,0.06); }
        table { width: 100%; border-collapse: collapse; }
        thead th { text-align: left; font-size: 12px; font-weight: 600; color: #94a3b8; padding: 8px 12px; border-bottom: 1px solid #f1f5f9; text-transform: uppercase; letter-spacing: 0.5px; }
        tbody td { padding: 10px 12px; font-size: 14px; color: #334155; }
        tbody tr:not(:last-child) td { border-bottom: 1px solid #f8fafc; }
        tbody tr:hover td { background: #f8fafc; }
        .badge { padding: 3px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .badge-hadir     { background:#dcfce7; color:#16a34a; }
        .badge-terlambat { background:#fef3c7; color:#d97706; }
        .badge-izin      { background:#fef9c3; color:#ca8a04; }
        .badge-alpha     { background:#fee2e2; color:#dc2626; }

        /* Progress */
        .progress-bar  { height: 8px; background: #e2e8f0; border-radius: 99px; overflow: hidden; }
        .progress-fill { height: 100%; border-radius: 99px; transition: width 0.8s ease; }

        /* Avatar */
        .ava-inisial { width: 34px; height: 34px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 12px; color: white; flex-shrink: 0; }

        /* Empty state */
        .empty-state { text-align: center; padding: 32px; color: #94a3b8; font-size: 13px; }

        /* Donut wrapper */
        .donut-wrap { position: relative; width: 120px; height: 120px; flex-shrink: 0; }
        .donut-label { position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; }
    </style>
</head>
<body>
<div class="flex" style="min-height:100vh;">

    <!-- SIDEBAR -->
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
            <a href="?url=guru/scan"       class="nav-item"><i class="fa fa-qrcode nav-icon"></i> Scan QR</a>
            <a href="?url=guru/riwayat"    class="nav-item"><i class="fa fa-clock-rotate-left nav-icon"></i> Riwayat Absensi</a>
            <a href="?url=guru/rekap"      class="nav-item"><i class="fa fa-layer-group nav-icon"></i> Rekap Kelas</a>
            <a href="?url=guru/monitoring" class="nav-item active"><i class="fa fa-chart-line nav-icon"></i> Monitoring</a>
        </nav>
        <div style="border-top:1px solid rgba(255,255,255,0.07); padding-bottom:8px; margin-top:16px;">
            <p class="nav-section-label">Sistem</p>
            <a href="?url=guru/pengaturan" class="nav-item"><i class="fa fa-gear nav-icon"></i> Pengaturan</a>
            <a href="?url=auth/logout"     class="nav-item"><i class="fa fa-right-from-bracket nav-icon"></i> Logout</a>
        </div>
    </aside>

    <!-- MAIN -->
    <div class="content-scroll flex flex-col">

        <!-- TOPBAR -->
        <div class="topbar">
            <div>
                <h1 class="text-xl font-bold text-gray-800">Monitoring Kehadiran</h1>
                <p class="text-sm text-gray-400"><?= $tanggalHariIni ?></p>
            </div>
            <div class="flex items-center gap-3 pl-4" style="border-left:1px solid #e2e8f0;">
                <div style="width:36px;height:36px;border-radius:50%;background:#2563eb;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:13px;color:white;"><?= $inisial ?></div>
                <div>
                    <p class="text-sm font-semibold text-gray-800"><?= htmlspecialchars($nama) ?></p>
                    <p class="text-xs text-gray-400">Guru</p>
                </div>
            </div>
        </div>

        <!-- BODY -->
        <div class="p-6 flex flex-col gap-5">

            <!-- ROW 1: Stat Cards -->
            <div class="grid gap-4" style="grid-template-columns: repeat(5, 1fr);">

                <!-- Total Siswa -->
                <div class="stat-card blue">
                    <div class="stat-icon-wrap" style="background:#eff6ff;">
                        <i class="fa fa-users" style="color:#2563eb;"></i>
                    </div>
                    <p class="text-sm text-gray-500">Total Siswa</p>
                    <h2 class="text-3xl font-extrabold text-gray-800"><?= $totalSiswa ?></h2>
                    <p class="text-xs text-gray-400 mt-1">Terdaftar</p>
                </div>

                <!-- Hadir + persentase -->
                <div class="stat-card green">
                    <div class="stat-icon-wrap" style="background:#f0fdf4;">
                        <i class="fa fa-circle-check" style="color:#16a34a;"></i>
                    </div>
                    <p class="text-sm text-gray-500">Hadir</p>
                    <h2 class="text-3xl font-extrabold text-gray-800"><?= $hadirHariIni ?></h2>
                    <?php
                        $pc = $persenHadir;
                        $cls = $pc >= 90 ? 'up' : ($pc >= 75 ? 'warn' : 'down');
                        $icon = $pc >= 75 ? 'arrow-trend-up' : 'arrow-trend-down';
                    ?>
                    <span class="stat-persen <?= $cls ?>">
                        <i class="fa fa-<?= $icon ?>"></i> <?= $persenHadir ?>%
                    </span>
                </div>

                <!-- Terlambat -->
                <div class="stat-card yellow">
                    <div class="stat-icon-wrap" style="background:#fef3c7;">
                        <i class="fa fa-clock" style="color:#d97706;"></i>
                    </div>
                    <p class="text-sm text-gray-500">Terlambat</p>
                    <h2 class="text-3xl font-extrabold text-gray-800"><?= $terlambatHariIni ?></h2>
                    <p class="text-xs text-gray-400 mt-1">Hari ini</p>
                </div>

                <!-- Izin/Sakit -->
                <div class="stat-card orange">
                    <div class="stat-icon-wrap" style="background:#fff7ed;">
                        <i class="fa fa-file-medical" style="color:#ea580c;"></i>
                    </div>
                    <p class="text-sm text-gray-500">Izin/Sakit</p>
                    <h2 class="text-3xl font-extrabold text-gray-800"><?= $izinHariIni ?></h2>
                    <p class="text-xs text-gray-400 mt-1">Hari ini</p>
                </div>

                <!-- Belum Absen -->
                <div class="stat-card red">
                    <div class="stat-icon-wrap" style="background:#fef2f2;">
                        <i class="fa fa-circle-xmark" style="color:#dc2626;"></i>
                    </div>
                    <p class="text-sm text-gray-500">Belum Absen</p>
                    <h2 class="text-3xl font-extrabold text-gray-800"><?= $belumAbsen ?></h2>
                    <p class="text-xs text-gray-400 mt-1">Hari ini</p>
                </div>

            </div>

            <!-- ROW 2: Line Chart + Donut -->
            <div class="grid gap-5" style="grid-template-columns: 1fr 320px;">

                <!-- Line Chart Tren -->
                <div class="chart-section">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-bold text-gray-800">
                            <i class="fa fa-chart-line text-blue-500 mr-2"></i>
                            Tren Kehadiran 30 Hari Terakhir
                        </h3>
                        <div class="flex items-center gap-4 text-xs text-gray-500">
                            <span><span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:#2563eb;margin-right:4px;"></span>Hadir</span>
                            <span><span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:#d97706;margin-right:4px;"></span>Terlambat</span>
                            <span><span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:#dc2626;margin-right:4px;"></span>Alpha</span>
                        </div>
                    </div>
                    <?php if (empty($chartLabels)): ?>
                        <div class="empty-state">
                            <i class="fa fa-chart-line fa-2x mb-2 block" style="color:#cbd5e1;"></i>
                            Belum ada data kehadiran
                        </div>
                    <?php else: ?>
                        <canvas id="chartTren" height="100"></canvas>
                    <?php endif; ?>
                </div>

                <!-- Donut Chart ringkasan hari ini -->
                <div class="chart-section flex flex-col">
                    <h3 class="font-bold text-gray-800 mb-4">
                        <i class="fa fa-chart-pie text-blue-500 mr-2"></i>
                        Ringkasan Hari Ini
                    </h3>
                    <div class="flex flex-col items-center justify-center flex-1 gap-4">
                        <div class="donut-wrap">
                            <canvas id="chartDonut"></canvas>
                            <div class="donut-label">
                                <span class="text-2xl font-extrabold text-gray-800"><?= $persenHadir ?>%</span>
                                <span class="text-xs text-gray-400">Hadir</span>
                            </div>
                        </div>
                        <div class="flex flex-col gap-2 w-full">
                            <div class="flex justify-between text-sm">
                                <span class="flex items-center gap-2"><span style="width:10px;height:10px;border-radius:50%;background:#2563eb;display:inline-block;"></span> Hadir</span>
                                <span class="font-semibold text-gray-700"><?= $hadirHariIni ?></span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="flex items-center gap-2"><span style="width:10px;height:10px;border-radius:50%;background:#d97706;display:inline-block;"></span> Terlambat</span>
                                <span class="font-semibold text-gray-700"><?= $terlambatHariIni ?></span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="flex items-center gap-2"><span style="width:10px;height:10px;border-radius:50%;background:#ea580c;display:inline-block;"></span> Izin/Sakit</span>
                                <span class="font-semibold text-gray-700"><?= $izinHariIni ?></span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="flex items-center gap-2"><span style="width:10px;height:10px;border-radius:50%;background:#dc2626;display:inline-block;"></span> Belum Absen</span>
                                <span class="font-semibold text-gray-700"><?= $belumAbsen ?></span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- ROW 3: Terlambat + Rekap per Kelas -->
            <div class="grid gap-5" style="grid-template-columns: 1fr 1fr;">

                <!-- Siswa Terlambat -->
                <div class="table-section">
                    <h3 class="font-bold text-gray-800 mb-4">
                        <i class="fa fa-clock text-yellow-500 mr-2"></i>
                        Siswa Terlambat Hari Ini
                        <span style="background:#fef3c7;color:#d97706;font-size:12px;padding:2px 8px;border-radius:20px;font-weight:600;margin-left:8px;"><?= count($siswaTerlambat) ?></span>
                    </h3>
                    <?php if (empty($siswaTerlambat)): ?>
                        <div class="empty-state">
                            <i class="fa fa-circle-check fa-2x mb-2 block" style="color:#16a34a;"></i>
                            Tidak ada siswa terlambat hari ini 🎉
                        </div>
                    <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Siswa</th>
                                <th>Kelas</th>
                                <th>Waktu Masuk</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($siswaTerlambat as $i => $s): ?>
                            <tr>
                                <td>
                                    <div class="flex items-center gap-2">
                                        <div class="ava-inisial" style="background:<?= $warnaList[$i % count($warnaList)] ?>;">
                                            <?= strtoupper(implode('', array_map(fn($w) => $w[0], array_slice(explode(' ', $s['nama']), 0, 2)))) ?>
                                        </div>
                                        <div>
                                            <p class="font-semibold text-gray-700"><?= htmlspecialchars($s['nama']) ?></p>
                                            <p class="text-xs text-gray-400"><?= $s['nis'] ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-gray-500"><?= htmlspecialchars($s['nama_kelas']) ?></td>
                                <td><span class="badge badge-terlambat"><?= $s['waktu'] ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php endif; ?>
                </div>

                <!-- Rekap per Kelas -->
                <div class="table-section">
                    <h3 class="font-bold text-gray-800 mb-4">
                        <i class="fa fa-school text-blue-500 mr-2"></i>
                        Kehadiran per Kelas Hari Ini
                    </h3>
                    <?php if (empty($rekapKelas)): ?>
                        <div class="empty-state">
                            <i class="fa fa-inbox fa-2x mb-2 block"></i>Belum ada data
                        </div>
                    <?php else: ?>
                    <div class="flex flex-col gap-4">
                    <?php $i = 0; foreach ($rekapKelas as $namaKls => $r):
                        $persen = $r['total'] > 0 ? round($r['hadir'] / $r['total'] * 100) : 0;
                        $warna  = $warnaList[$i % count($warnaList)];
                        $persenColor = $persen >= 90 ? '#16a34a' : ($persen >= 75 ? '#d97706' : '#dc2626');
                        $i++;
                    ?>
                        <div>
                            <div class="flex justify-between items-center text-sm mb-1">
                                <span class="font-semibold text-gray-700"><?= htmlspecialchars($namaKls) ?></span>
                                <div class="flex items-center gap-2">
                                    <span class="text-gray-400 text-xs"><?= $r['hadir'] ?>/<?= $r['total'] ?> Siswa</span>
                                    <span style="font-size:11px;font-weight:700;color:<?= $persenColor ?>;"><?= $persen ?>%</span>
                                </div>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width:<?= $persen ?>%;background:<?= $warna ?>;"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
// ── Line Chart Tren ──────────────────────────────
<?php if (!empty($chartLabels)): ?>
new Chart(document.getElementById('chartTren').getContext('2d'), {
    type: 'line',
    data: {
        labels: <?= json_encode($chartLabels) ?>,
        datasets: [
            {
                label: 'Hadir',
                data: <?= json_encode($chartHadir) ?>,
                borderColor: '#2563eb',
                backgroundColor: 'rgba(37,99,235,0.08)',
                borderWidth: 2.5,
                pointBackgroundColor: '#2563eb',
                pointRadius: 3,
                tension: 0.4,
                fill: true,
            },
            {
                label: 'Terlambat',
                data: <?= json_encode($chartTerlambat) ?>,
                borderColor: '#d97706',
                backgroundColor: 'rgba(217,119,6,0.06)',
                borderWidth: 2,
                pointBackgroundColor: '#d97706',
                pointRadius: 3,
                tension: 0.4,
                fill: false,
            },
            {
                label: 'Alpha',
                data: <?= json_encode($chartAlpha) ?>,
                borderColor: '#dc2626',
                backgroundColor: 'rgba(220,38,38,0.06)',
                borderWidth: 2,
                pointBackgroundColor: '#dc2626',
                pointRadius: 3,
                tension: 0.4,
                fill: false,
            }
        ]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: { min: 0, ticks: { color: '#94a3b8', font: { size: 11 } }, grid: { color: '#f1f5f9' } },
            x: { ticks: { color: '#94a3b8', font: { size: 10 }, maxRotation: 45 }, grid: { display: false } }
        }
    }
});
<?php endif; ?>

// ── Donut Chart ──────────────────────────────────
new Chart(document.getElementById('chartDonut').getContext('2d'), {
    type: 'doughnut',
    data: {
        labels: ['Hadir', 'Terlambat', 'Izin/Sakit', 'Belum Absen'],
        datasets: [{
            data: [
                <?= $hadirHariIni ?>,
                <?= $terlambatHariIni ?>,
                <?= $izinHariIni ?>,
                <?= max(0, $belumAbsen) ?>
            ],
            backgroundColor: ['#2563eb', '#d97706', '#ea580c', '#dc2626'],
            borderWidth: 0,
            hoverOffset: 6,
        }]
    },
    options: {
        cutout: '72%',
        responsive: true,
        plugins: { legend: { display: false }, tooltip: { callbacks: {
            label: ctx => ' ' + ctx.label + ': ' + ctx.parsed
        }}}
    }
});
</script>
</body>
</html>