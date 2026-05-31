<?php
if (session_status() === PHP_SESSION_NONE) session_start();
date_default_timezone_set('Asia/Jakarta');

$nama  = $_SESSION['user']['nama']  ?? 'Guru';
$kelas = $_SESSION['user']['kelas'] ?? 'XI RPL 1';
$inisial = strtoupper(implode('', array_map(fn($w) => $w[0], array_slice(explode(' ', $nama), 0, 2))));

$bulan     = $bulan     ?? date('Y-m');
$kelasList = $kelasList ?? [];
$summary   = $summary   ?? ['hadir' => 0, 'terlambat' => 0, 'izin' => 0, 'alpha' => 0, 'total' => 0];

$bulanIndo = [
    '01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April',
    '05'=>'Mei','06'=>'Juni','07'=>'Juli','08'=>'Agustus',
    '09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'
];
$bulanLabel = $bulanIndo[substr($bulan, 5, 2)] . ' ' . substr($bulan, 0, 4);

$warnaList = ['#6366f1','#0ea5e9','#f59e0b','#10b981','#ec4899','#8b5cf6','#ef4444','#14b8a6'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Kelas – Absensi QR</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        * { font-family: 'Plus Jakarta Sans', sans-serif; }
        :root { --sidebar-bg:#0f1729; --sidebar-hover:#1a2540; --accent:#2563eb; --content-bg:#f1f5f9; }
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
        .card { background: white; border-radius: 16px; padding: 20px 22px; box-shadow: 0 1px 4px rgba(0,0,0,0.06); }
        .stat-icon-wrap { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 18px; margin-bottom: 12px; }
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
        .progress-bar  { height: 8px; background: #e2e8f0; border-radius: 99px; overflow: hidden; }
        .progress-fill { height: 100%; border-radius: 99px; transition: width 0.6s ease; }
        .content-scroll { flex: 1; overflow-y: auto; background: var(--content-bg); }
        .notif-dot { position: absolute; top: -2px; right: -2px; width: 8px; height: 8px; background: #f97316; border-radius: 50%; border: 2px solid white; }
        .empty-state { text-align: center; padding: 40px; color: #94a3b8; font-size: 13px; }
        .detail-table { display: none; }
        .detail-table.show { display: table-row-group; }
        .kelas-row { cursor: pointer; }
        .kelas-row:hover td { background: #f0f7ff !important; }
        .chevron { transition: transform 0.2s; }
        .chevron.open { transform: rotate(90deg); }
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
            <a href="?url=guru/dashboard" class="nav-item"><i class="fa fa-home nav-icon"></i> Dashboard</a>
            <a href="?url=guru/scan"      class="nav-item"><i class="fa fa-qrcode nav-icon"></i> Scan QR</a>
            <a href="?url=guru/riwayat"   class="nav-item"><i class="fa fa-clock-rotate-left nav-icon"></i> Riwayat Absensi</a>
            <a href="?url=guru/rekap"     class="nav-item active"><i class="fa fa-layer-group nav-icon"></i> Rekap Kelas</a>
            <a href="?url=guru/monitoring" class="nav-item"><i class="fa fa-chart-line nav-icon"></i> Monitoring</a>
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
                <h1 class="text-xl font-bold text-gray-800">Rekap Kelas</h1>
                <p class="text-sm text-gray-400">Data kehadiran bulanan per kelas</p>
            </div>
            <div class="flex items-center gap-4">
                <!-- Filter Bulan -->
                <form method="GET" action="" class="flex items-center gap-2">
                    <input type="hidden" name="url" value="guru/rekap">
                    <input type="month" name="bulan" value="<?= htmlspecialchars($bulan) ?>"
                           class="border border-gray-200 rounded-lg px-3 py-2 text-sm font-medium text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-blue-700 transition">
                        <i class="fa fa-filter mr-1"></i> Filter
                    </button>
                </form>
                <!-- Export -->
                <button onclick="exportRekap('pdf')"
                        class="flex items-center gap-2 bg-red-50 text-red-600 px-4 py-2 rounded-lg text-sm font-semibold hover:bg-red-100 transition">
                    <i class="fa fa-file-pdf"></i> PDF
                </button>
                <button onclick="exportRekap('xlsx')"
                        class="flex items-center gap-2 bg-green-50 text-green-600 px-4 py-2 rounded-lg text-sm font-semibold hover:bg-green-100 transition">
                    <i class="fa fa-file-excel"></i> Excel
                </button>
                <div class="flex items-center gap-3 pl-4" style="border-left:1px solid #e2e8f0;">
                    <div style="width:36px;height:36px;border-radius:50%;background:#2563eb;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:13px;color:white;"><?= $inisial ?></div>
                    <div>
                        <p class="text-sm font-semibold text-gray-800"><?= htmlspecialchars($nama) ?></p>
                        <p class="text-xs text-gray-400">Guru</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- BODY -->
        <div class="p-6 flex flex-col gap-5">

            <!-- Summary Cards -->
            <div class="grid gap-4" style="grid-template-columns: repeat(4, 1fr);">
                <div class="card">
                    <div class="stat-icon-wrap" style="background:#f0fdf4;"><i class="fa fa-circle-check" style="color:#16a34a;"></i></div>
                    <p class="text-sm text-gray-500">Total Hadir</p>
                    <h2 class="text-3xl font-extrabold text-gray-800"><?= $summary['hadir'] ?? 0 ?></h2>
                    <p class="text-xs text-gray-400 mt-1"><?= $bulanLabel ?></p>
                </div>
                <div class="card">
                    <div class="stat-icon-wrap" style="background:#fef3c7;"><i class="fa fa-clock" style="color:#d97706;"></i></div>
                    <p class="text-sm text-gray-500">Terlambat</p>
                    <h2 class="text-3xl font-extrabold text-gray-800"><?= $summary['terlambat'] ?? 0 ?></h2>
                    <p class="text-xs text-gray-400 mt-1"><?= $bulanLabel ?></p>
                </div>
                <div class="card">
                    <div class="stat-icon-wrap" style="background:#fff7ed;"><i class="fa fa-file-medical" style="color:#ea580c;"></i></div>
                    <p class="text-sm text-gray-500">Izin / Sakit</p>
                    <h2 class="text-3xl font-extrabold text-gray-800"><?= $summary['izin'] ?? 0 ?></h2>
                    <p class="text-xs text-gray-400 mt-1"><?= $bulanLabel ?></p>
                </div>
                <div class="card">
                    <div class="stat-icon-wrap" style="background:#fef2f2;"><i class="fa fa-circle-xmark" style="color:#dc2626;"></i></div>
                    <p class="text-sm text-gray-500">Alpha</p>
                    <h2 class="text-3xl font-extrabold text-gray-800"><?= $summary['alpha'] ?? 0 ?></h2>
                    <p class="text-xs text-gray-400 mt-1"><?= $bulanLabel ?></p>
                </div>
            </div>

            <!-- Tabel Rekap per Kelas -->
            <div class="card">
                <div class="flex items-center justify-between mb-5">
                    <h3 class="font-bold text-gray-800">
                        <i class="fa fa-layer-group text-blue-500 mr-2"></i>
                        Rekap Per Kelas – <?= $bulanLabel ?>
                    </h3>
                    <span class="text-sm text-gray-400"><?= count($kelasList) ?> kelas</span>
                </div>

                <?php if (empty($kelasList)): ?>
                    <div class="empty-state">
                        <i class="fa fa-inbox fa-2x mb-2 block"></i>
                        Belum ada data rekap untuk bulan <?= $bulanLabel ?>
                    </div>
                <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th style="width:30px;"></th>
                            <th>Kelas</th>
                            <th>Siswa</th>
                            <th>Hadir</th>
                            <th>Terlambat</th>
                            <th>Izin/Sakit</th>
                            <th>Alpha</th>
                            <th>Kehadiran</th>
                            <th>%</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($kelasList as $i => $k):
                        $warna   = $warnaList[$i % count($warnaList)];
                        $persen  = $k['persen'] ?? 0;
                        $persenColor = $persen >= 90 ? '#16a34a' : ($persen >= 75 ? '#d97706' : '#dc2626');
                        $kelasId = 'kelas-' . $i;
                    ?>
                        <!-- Row Kelas (klik untuk expand) -->
                        <tr class="kelas-row" onclick="toggleDetail('<?= $kelasId ?>', this)">
                            <td>
                                <i class="fa fa-chevron-right nav-icon chevron" id="chev-<?= $kelasId ?>" style="color:#94a3b8;font-size:11px;"></i>
                            </td>
                            <td>
                                <div class="flex items-center gap-2">
                                    <div style="width:10px;height:10px;border-radius:50%;background:<?= $warna ?>;flex-shrink:0;"></div>
                                    <span class="font-semibold text-gray-800"><?= htmlspecialchars($k['nama_kelas']) ?></span>
                                </div>
                            </td>
                            <td class="text-gray-500"><?= $k['total_siswa'] ?? 0 ?></td>
                            <td><span class="badge badge-hadir"><?= $k['hadir'] ?? 0 ?></span></td>
                            <td><span class="badge badge-terlambat"><?= $k['terlambat'] ?? 0 ?></span></td>
                            <td><span class="badge badge-izin"><?= $k['izin'] ?? 0 ?></span></td>
                            <td><span class="badge badge-alpha"><?= $k['alpha'] ?? 0 ?></span></td>
                            <td style="width:140px;">
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width:<?= $persen ?>%;background:<?= $warna ?>;"></div>
                                </div>
                            </td>
                            <td style="font-weight:700;color:<?= $persenColor ?>;"><?= $persen ?>%</td>
                        </tr>

                        <!-- Detail Siswa (tersembunyi, expand saat klik) -->
                        <?php if (!empty($k['siswa'])): ?>
                        <tr>
                            <td colspan="9" style="padding:0;background:#f8fafc;">
                                <div id="<?= $kelasId ?>" style="display:none;padding:12px 24px 16px;">
                                    <table style="width:100%;">
                                        <thead>
                                            <tr style="background:#f1f5f9;">
                                                <th style="padding:6px 10px;font-size:11px;color:#64748b;font-weight:600;">Nama Siswa</th>
                                                <th style="padding:6px 10px;font-size:11px;color:#64748b;font-weight:600;">NIS</th>
                                                <th style="padding:6px 10px;font-size:11px;color:#64748b;font-weight:600;">Hadir</th>
                                                <th style="padding:6px 10px;font-size:11px;color:#64748b;font-weight:600;">Terlambat</th>
                                                <th style="padding:6px 10px;font-size:11px;color:#64748b;font-weight:600;">Izin</th>
                                                <th style="padding:6px 10px;font-size:11px;color:#64748b;font-weight:600;">Alpha</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php foreach ($k['siswa'] as $s): ?>
                                            <tr style="border-bottom:1px solid #e2e8f0;">
                                                <td style="padding:7px 10px;font-size:13px;font-weight:500;color:#334155;"><?= htmlspecialchars($s['nama']) ?></td>
                                                <td style="padding:7px 10px;font-size:12px;color:#94a3b8;"><?= $s['nis'] ?></td>
                                                <td style="padding:7px 10px;"><span class="badge badge-hadir"><?= $s['hadir'] ?></span></td>
                                                <td style="padding:7px 10px;"><span class="badge badge-terlambat"><?= $s['terlambat'] ?></span></td>
                                                <td style="padding:7px 10px;"><span class="badge badge-izin"><?= $s['izin'] ?></span></td>
                                                <td style="padding:7px 10px;"><span class="badge badge-alpha"><?= $s['alpha'] ?></span></td>
                                            </tr>
                                        <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </td>
                        </tr>
                        <?php endif; ?>

                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>

        </div>
    </div>
</div>

<script>
function toggleDetail(id, row) {
    const el   = document.getElementById(id);
    const chev = document.getElementById('chev-' + id);
    const open = el.style.display === 'block';
    el.style.display   = open ? 'none' : 'block';
    chev.classList.toggle('open', !open);
}
function exportRekap(format) {
    alert('Export ' + format.toUpperCase() + ' segera hadir!');
}
</script>
</body>
</html>