<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
date_default_timezone_set('Asia/Jakarta');

$nama  = $_SESSION['user']['nama'] ?? 'Siti Aisyah';
$kelas = $_SESSION['user']['kelas'] ?? 'XI RPL 1';

$hari = [
    'Sunday'=>'Minggu','Monday'=>'Senin','Tuesday'=>'Selasa',
    'Wednesday'=>'Rabu','Thursday'=>'Kamis','Friday'=>'Jumat','Saturday'=>'Sabtu'
];
$bulan = [
    'January'=>'Januari','February'=>'Februari','March'=>'Maret','April'=>'April',
    'May'=>'Mei','June'=>'Juni','July'=>'Juli','August'=>'Agustus',
    'September'=>'September','October'=>'Oktober','November'=>'November','December'=>'Desember'
];

$tanggal_lengkap = $hari[date('l')] . ', ' . date('d') . ' ' . $bulan[date('F')] . ' ' . date('Y');
$minggu_ke       = ceil(date('d') / 7);
$sub_tanggal     = 'Minggu ke-' . $minggu_ke . ', ' . date('d') . ' ' . $bulan[date('F')] . ' ' . date('Y');

$jam = date('H');
if ($jam >= 5 && $jam < 12)      $greeting = "Selamat Pagi ☀️";
elseif ($jam >= 12 && $jam < 15) $greeting = "Selamat Siang 🌤️";
elseif ($jam >= 15 && $jam < 18) $greeting = "Selamat Sore 🌇";
else                              $greeting = "Selamat Malam 🌙";

$inisial = strtoupper(implode('', array_map(fn($w) => $w[0], array_slice(explode(' ', $nama), 0, 2))));

function inisialSiswa($nama_siswa) {
    $w = explode(' ', trim($nama_siswa));
    return strtoupper(($w[0][0] ?? '') . ($w[1][0] ?? ''));
}


$totalSiswa    = $totalSiswa    ?? 0;
$hadirHariIni  = $hadirHariIni  ?? 0;
$izinSakit     = $izinSakit     ?? 0;
$belumAbsen    = $belumAbsen    ?? 0;
$kelasList     = $kelasList     ?? [];
$chart7Hari    = $chart7Hari    ?? [];
$aktivitas     = $aktivitas     ?? [];
$jadwalHariIni = $jadwalHariIni ?? [];

// rata-rata kehadiran
$rataKehadiran = $totalSiswa > 0 ? round($hadirHariIni / $totalSiswa * 100, 1) : 0;

// chart data
$chartLabels = [];
$chartData   = [];
foreach ($chart7Hari as $c) {
    $chartLabels[] = date('D', strtotime($c['tanggal']));
    $chartData[]   = $c['hadir'] ?? 0;
}
if (empty($chartLabels)) {
    $chartLabels = ['Sen','Sel','Rab','Kam','Jum'];
    $chartData   = [0,0,0,0,0];
}

$warnaList = ['#6366f1','#0ea5e9','#f59e0b','#10b981','#ec4899','#8b5cf6','#ef4444','#14b8a6','#f97316','#06b6d4'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Guru - Absensi QR</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        .stat-card { background: white; border-radius: 16px; padding: 20px 22px; box-shadow: 0 1px 4px rgba(0,0,0,0.06); }
        .stat-icon-wrap { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 18px; margin-bottom: 12px; }
        .scan-card { background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 60%, #3b82f6 100%); border-radius: 16px; padding: 24px 28px; color: white; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 4px 20px rgba(37,99,235,0.35); }
        .scan-btn { background: white; color: #1d4ed8; border: none; padding: 10px 20px; border-radius: 10px; font-weight: 700; font-size: 14px; cursor: pointer; display: flex; align-items: center; gap: 8px; margin-top: 14px; transition: transform 0.15s; pointer-events: auto; }
        .scan-btn:hover { transform: scale(1.03); }
        .table-section { background: white; border-radius: 16px; padding: 22px 24px; box-shadow: 0 1px 4px rgba(0,0,0,0.06); }
        table { width: 100%; border-collapse: collapse; }
        thead th { text-align: left; font-size: 12px; font-weight: 600; color: #94a3b8; padding: 8px 12px; border-bottom: 1px solid #f1f5f9; text-transform: uppercase; letter-spacing: 0.5px; }
        tbody td { padding: 10px 12px; font-size: 14px; color: #334155; }
        tbody tr:not(:last-child) td { border-bottom: 1px solid #f8fafc; }
        tbody tr:hover td { background: #f8fafc; }
        .badge-hadir { background: #dcfce7; color: #16a34a; padding: 3px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .badge-izin  { background: #fef9c3; color: #ca8a04; padding: 3px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .badge-alpha { background: #fee2e2; color: #dc2626; padding: 3px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .progress-bar { height: 6px; background: #e2e8f0; border-radius: 99px; overflow: hidden; }
        .progress-fill { height: 100%; border-radius: 99px; transition: width 0.6s ease; }
        .fill-x   { background: #2563eb; }
        .fill-xi  { background: #10b981; }
        .fill-xii { background: #f97316; }
        .ava-inisial { width: 34px; height: 34px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 12px; color: white; flex-shrink: 0; }
        .notif-dot { position: absolute; top: -2px; right: -2px; width: 8px; height: 8px; background: #f97316; border-radius: 50%; border: 2px solid white; }
        .content-scroll { flex: 1; overflow-y: auto; background: var(--content-bg); }
        .kelas-scroll { max-height: 380px; overflow-y: auto; padding-right: 4px; }
        .kelas-scroll::-webkit-scrollbar { width: 4px; }
        .kelas-scroll::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 99px; }
        .kelas-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 99px; }
        .btn-export { display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; border-radius: 8px; font-size: 12px; font-weight: 600; cursor: pointer; border: none; transition: all 0.15s; }
        .btn-pdf  { background: #fee2e2; color: #dc2626; }
        .btn-pdf:hover  { background: #fca5a5; }
        .btn-xlsx { background: #dcfce7; color: #16a34a; }
        .btn-xlsx:hover { background: #86efac; }
        .modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.45); z-index: 999; align-items: center; justify-content: center; }
        .modal-overlay.show { display: flex; }
        .modal-box { background: white; border-radius: 20px; padding: 28px; width: 420px; box-shadow: 0 20px 60px rgba(0,0,0,0.2); animation: modalIn 0.2s ease; }
        @keyframes modalIn { from { transform: scale(0.95); opacity: 0; } to { transform: scale(1); opacity: 1; } }
        .drop-zone { border: 2px dashed #cbd5e1; border-radius: 12px; padding: 28px; text-align: center; background: #f8fafc; cursor: pointer; transition: all 0.2s; margin-bottom: 16px; }
        .drop-zone.dragover { border-color: #2563eb; background: #eff6ff; }
        .drop-zone-icon { font-size: 36px; color: #94a3b8; margin-bottom: 8px; }
        .drop-zone p { font-size: 13px; color: #64748b; }
        .drop-zone span { font-size: 11px; color: #94a3b8; }
        .file-preview { display: none; align-items: center; gap: 10px; background: #f1f5f9; border-radius: 10px; padding: 10px 14px; margin-bottom: 16px; font-size: 13px; color: #334155; }
        .file-preview.show { display: flex; }
        .export-type-btns { display: flex; gap: 10px; margin-bottom: 20px; }
        .export-type-btn { flex: 1; padding: 10px; border-radius: 10px; border: 2px solid #e2e8f0; font-size: 13px; font-weight: 600; cursor: pointer; background: white; color: #64748b; transition: all 0.15s; text-align: center; }
        .export-type-btn.selected-pdf  { border-color: #dc2626; background: #fee2e2; color: #dc2626; }
        .export-type-btn.selected-xlsx { border-color: #16a34a; background: #dcfce7; color: #16a34a; }
        .btn-do-export { width: 100%; padding: 12px; border-radius: 10px; border: none; font-size: 14px; font-weight: 700; cursor: pointer; color: white; background: #2563eb; transition: background 0.15s; }
        .btn-do-export:hover { background: #1d4ed8; }
        .btn-cancel { width: 100%; padding: 10px; border-radius: 10px; border: 1px solid #e2e8f0; font-size: 13px; font-weight: 600; cursor: pointer; color: #64748b; background: white; margin-top: 8px; transition: background 0.15s; }
        .btn-cancel:hover { background: #f1f5f9; }
        .jadwal-empty { text-align:center; padding: 20px; color: #94a3b8; font-size: 13px; }
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
            <a href="?url=guru/dashboard"  class="nav-item active"><i class="fa fa-home nav-icon"></i> Dashboard</a>
            
            <a href="?url=guru/kelas"      class="nav-item"><i class="fa fa-door-open nav-icon"></i> Kelas</a>
            <a href="?url=guru/riwayat"    class="nav-item"><i class="fa fa-clock-rotate-left nav-icon"></i> Riwayat Absensi</a>
            <a href="?url=guru/rekap"      class="nav-item"><i class="fa fa-layer-group nav-icon"></i> Rekap Kelas</a>
            <a href="?url=guru/monitoring" class="nav-item"><i class="fa fa-chart-line nav-icon"></i> Monitoring</a>
        </nav>
        <div style="border-top:1px solid rgba(255,255,255,0.07); padding-bottom:8px; margin-top:16px;">
            <p class="nav-section-label">Sistem</p>
            <a href="?url=guru/pengaturan" class="nav-item"><i class="fa fa-gear nav-icon"></i> Pengaturan</a>
            <a href="?url=auth/logout"     class="nav-item"><i class="fa fa-right-from-bracket nav-icon"></i> Logout</a>
        </div>
    </aside>

    <!-- MAIN CONTENT -->
    <div class="content-scroll flex flex-col">

        <!-- TOP BAR -->
        <div class="topbar">
            <div>
                <h1 class="text-xl font-bold text-gray-800">Ringkasan Dashboard</h1>
                <p class="text-sm text-gray-400"><?= $sub_tanggal ?></p>
            </div>
            <div class="flex items-center gap-5">
                <div class="relative cursor-pointer">
                    <i class="fa fa-bell text-gray-400 text-lg"></i>
                    <span class="notif-dot"></span>
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

            <!-- ROW 1: CTA Kelas + 3 Stat Cards -->
            <div class="grid gap-5" style="grid-template-columns: 280px 1fr 1fr 1fr;">

                
                <div class="scan-card">
                    <div>
                        <p class="text-xs font-semibold opacity-70 uppercase tracking-widest mb-1">ABSENSI</p>
                        <h2 class="text-2xl font-bold leading-tight">Mulai Absensi</h2>
                        <p class="text-sm opacity-80 mt-1">Pilih kelas dan mata pelajaran<br>untuk memulai absensi siswa.</p>
                        
                        <button class="scan-btn" onclick="window.location.href='?url=guru/kelas'">
                            <i class="fa fa-door-open"></i> Mulai Absensi
                        </button>
                    </div>
                    <i class="fa fa-chalkboard-user text-6xl opacity-20"></i>
                </div>

                <!-- Total Siswa Hadir -->
                <div class="stat-card flex flex-col justify-between">
                    <div>
                        <div class="stat-icon-wrap" style="background:#eff6ff;">
                            <i class="fa fa-users" style="color:#2563eb;"></i>
                        </div>
                        <p class="text-sm text-gray-500 font-medium">Total Siswa Hadir</p>
                        <h2 class="text-4xl font-extrabold text-gray-800 mt-1"><?= $hadirHariIni ?></h2>
                    </div>
                    <p class="text-xs text-gray-400 mt-3">Hari Ini · <?= date('d') . ' ' . $bulan[date('F')] . ' ' . date('Y') ?></p>
                </div>

                <!-- Rata-rata Kehadiran -->
                <div class="stat-card flex flex-col justify-between">
                    <div>
                        <div class="stat-icon-wrap" style="background:#f0fdf4;">
                            <i class="fa fa-circle-check" style="color:#16a34a;"></i>
                        </div>
                        <p class="text-sm text-gray-500 font-medium">Rata-rata Kehadiran</p>
                        <h2 class="text-4xl font-extrabold text-gray-800 mt-1"><?= $rataKehadiran ?>%</h2>
                    </div>
                    <p class="text-xs text-gray-400 mt-3">Minggu ke-<?= $minggu_ke ?> · <?= $bulan[date('F')] . ' ' . date('Y') ?></p>
                </div>

                <!-- Siswa Izin/Sakit -->
                <div class="stat-card flex flex-col justify-between">
                    <div>
                        <div class="stat-icon-wrap" style="background:#fff7ed;">
                            <i class="fa fa-clipboard-list" style="color:#ea580c;"></i>
                        </div>
                        <p class="text-sm text-gray-500 font-medium">Siswa Izin/Sakit</p>
                        <h2 class="text-4xl font-extrabold text-gray-800 mt-1"><?= $izinSakit ?></h2>
                    </div>
                    <p class="text-xs text-gray-400 mt-3">Hari Ini · <?= date('d') . ' ' . $bulan[date('F')] . ' ' . date('Y') ?></p>
                </div>

            </div>

            <!-- ROW 2: Chart + Daftar Hadir + Kehadiran per Kelas -->
            <div class="grid gap-5" style="grid-template-columns: 1fr 1.4fr 0.9fr;">

                <!-- Grafik Kehadiran -->
                <div class="table-section">
                    <h3 class="font-bold text-gray-800 mb-4">Grafik Kehadiran Mingguan</h3>
                    <canvas id="chartKehadiran" height="180"></canvas>
                </div>

                <!-- Daftar Hadir Terkini -->
                <div class="table-section">
                    <h3 class="font-bold text-gray-800 mb-4">Daftar Hadir Terkini</h3>
                    <?php if (empty($aktivitas)): ?>
                        <div class="jadwal-empty"><i class="fa fa-inbox fa-2x mb-2 block"></i>Belum ada absensi hari ini</div>
                    <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Siswa</th>
                                <th>Waktu Masuk</th>
                                <th>Status</th>
                                <th>Kelas</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($aktivitas as $i => $a):
                            $namaS  = $a['students']['nama']  ?? $a['nama']  ?? '-';
                            $nisS   = $a['students']['nis']   ?? $a['nis']   ?? '-';
                            $kelasS = $a['students']['kelas'] ?? $a['kelas'] ?? '-';
                            $waktu  = isset($a['waktu_masuk']) ? date('h:i A', strtotime($a['waktu_masuk'])) : '-';
                            $status = $a['status'] ?? 'Hadir';
                            $bc     = match($status) { 'Hadir' => 'badge-hadir', 'Izin' => 'badge-izin', default => 'badge-alpha' };
                            $warna  = $warnaList[$i % count($warnaList)];
                        ?>
                            <tr>
                                <td>
                                    <div class="flex items-center gap-2">
                                        <div class="ava-inisial" style="background:<?= $warna ?>;"><?= inisialSiswa($namaS) ?></div>
                                        <div>
                                            <p class="font-semibold text-gray-700"><?= htmlspecialchars($namaS) ?></p>
                                            <p class="text-xs text-gray-400"><?= $nisS ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-gray-500"><?= $waktu ?></td>
                                <td><span class="<?= $bc ?>"><?= $status ?></span></td>
                                <td class="text-gray-500"><?= htmlspecialchars($kelasS) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php endif; ?>
                </div>

                <!-- Kehadiran per Kelas -->
                <div class="table-section">
                    <h3 class="font-bold text-gray-800 mb-4">Kehadiran per Kelas</h3>
                    <?php if (empty($kelasList)): ?>
                        <div class="jadwal-empty"><i class="fa fa-inbox fa-2x mb-2 block"></i>Belum ada data</div>
                    <?php else: ?>
                    <div class="kelas-scroll flex flex-col gap-4">
                    <?php foreach ($kelasList as $k):
                        $tingkat    = strtoupper(explode(' ', $k['nama_kelas'])[0] ?? 'X');
                        $fillClass  = match($tingkat) { 'XI' => 'fill-xi', 'XII' => 'fill-xii', default => 'fill-x' };
                        $persenColor = ($k['persen'] >= 95) ? '#16a34a' : (($k['persen'] >= 88) ? '#ca8a04' : '#dc2626');
                    ?>
                        <div>
                            <div class="flex justify-between items-center text-sm mb-1">
                                <span class="font-semibold text-gray-700"><?= htmlspecialchars($k['nama_kelas']) ?></span>
                                <span class="text-gray-400 text-xs"><?= $k['hadir'] ?>/<?= $k['total_siswa'] ?> Siswa</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill <?= $fillClass ?>" style="width:<?= $k['persen'] ?>%"></div>
                            </div>
                            <p class="text-xs text-right mt-0.5" style="color:<?= $persenColor ?>;"><?= $k['persen'] ?>%</p>
                        </div>
                    <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>

            </div>

            <!-- ROW 3: Jadwal Hari Ini -->
            <div class="table-section">
                <h3 class="font-bold text-gray-800 mb-4">
                    <i class="fa fa-calendar-day text-blue-500 mr-2"></i>
                    Jadwal Hari Ini — <?= $hari[date('l')] ?>, <?= date('d') . ' ' . $bulan[date('F')] . ' ' . date('Y') ?>
                </h3>
                <?php if (empty($jadwalHariIni)): ?>
                    <div class="jadwal-empty">
                        <i class="fa fa-calendar-xmark fa-2x mb-2 block"></i>
                        Tidak ada jadwal mengajar hari ini
                    </div>
                <?php else: ?>
                <div class="grid gap-3" style="grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));">
                <?php
                $jamSekarang = date('H:i');
                foreach ($jadwalHariIni as $j):
                    $jamMulai   = substr($j['jam_mulai'] ?? '00:00', 0, 5);
                    $jamSelesai = substr($j['jam_selesai'] ?? '00:00', 0, 5);
                    $mapel      = $j['mata_pelajaran'] ?? '-';
                    $kelasJ     = $j['kelas'] ?? '-';
                    if ($jamSekarang < $jamMulai) {
                        $badgeText = 'Mendatang'; $badgeBg = '#eff6ff'; $badgeColor = '#2563eb';
                    } elseif ($jamSekarang >= $jamMulai && $jamSekarang <= $jamSelesai) {
                        $badgeText = 'Berlangsung'; $badgeBg = '#dcfce7'; $badgeColor = '#16a34a';
                    } else {
                        $badgeText = 'Selesai'; $badgeBg = '#f1f5f9'; $badgeColor = '#94a3b8';
                    }
                ?>
                    <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:14px 16px;">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-semibold" style="color:#94a3b8;">
                                <i class="fa fa-clock mr-1"></i><?= $jamMulai ?> – <?= $jamSelesai ?>
                            </span>
                            <span style="background:<?= $badgeBg ?>;color:<?= $badgeColor ?>;font-size:10px;padding:2px 8px;border-radius:20px;font-weight:600;">
                                <?= $badgeText ?>
                            </span>
                        </div>
                        <p class="font-bold text-gray-800 text-sm"><?= htmlspecialchars($mapel) ?></p>
                        <p class="text-xs text-gray-400 mt-1"><i class="fa fa-door-open mr-1"></i><?= htmlspecialchars($kelasJ) ?></p>
                    </div>
                <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

        </div>
    </div>
</div>

<!-- EXPORT MODAL -->
<div class="modal-overlay" id="exportModal">
    <div class="modal-box">
        <div class="flex items-center justify-between mb-5">
            <div>
                <h2 class="text-lg font-bold text-gray-800" id="modalTitle">Export Data</h2>
                <p class="text-xs text-gray-400 mt-0.5" id="modalSub">Pilih format & upload file jika diperlukan</p>
            </div>
            <button onclick="closeExport()" style="background:#f1f5f9;border:none;border-radius:8px;width:32px;height:32px;cursor:pointer;font-size:16px;color:#64748b;">✕</button>
        </div>
        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Format Export</p>
        <div class="export-type-btns">
            <button class="export-type-btn" id="btnPDF" onclick="selectFormat('pdf')"><i class="fa fa-file-pdf" style="color:#dc2626;margin-right:6px;"></i> PDF</button>
            <button class="export-type-btn" id="btnXLSX" onclick="selectFormat('xlsx')"><i class="fa fa-file-excel" style="color:#16a34a;margin-right:6px;"></i> Excel (.xlsx)</button>
        </div>
        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Upload File Pendukung <span style="font-weight:400;text-transform:none;color:#94a3b8;">(opsional)</span></p>
        <div class="drop-zone" id="dropZone" ondragover="handleDragOver(event)" ondragleave="handleDragLeave(event)" ondrop="handleDrop(event)" onclick="document.getElementById('fileInput').click()">
            <div class="drop-zone-icon"><i class="fa fa-cloud-arrow-up"></i></div>
            <p>Drag & drop file ke sini, atau <strong style="color:#2563eb;">klik untuk pilih</strong></p>
            <span>Mendukung: .xlsx, .csv, .pdf (maks. 10MB)</span>
        </div>
        <input type="file" id="fileInput" accept=".xlsx,.csv,.pdf" style="display:none;" onchange="handleFileInput(event)">
        <div class="file-preview" id="filePreview">
            <i class="fa fa-file-lines" style="color:#2563eb;font-size:20px;"></i>
            <div style="flex:1;"><p class="font-semibold" id="fileName">file.xlsx</p><p class="text-xs text-gray-400" id="fileSize">0 KB</p></div>
            <button onclick="removeFile()" style="border:none;background:none;color:#ef4444;cursor:pointer;font-size:14px;"><i class="fa fa-trash"></i></button>
        </div>
        <button class="btn-do-export" onclick="doExport()"><i class="fa fa-download" style="margin-right:6px;"></i> Export Sekarang</button>
        <button class="btn-cancel" onclick="closeExport()">Batal</button>
    </div>
</div>

<script>
const ctx = document.getElementById('chartKehadiran').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?= json_encode($chartLabels) ?>,
        datasets: [{
            label: 'Kehadiran',
            data: <?= json_encode($chartData) ?>,
            borderColor: '#2563eb',
            backgroundColor: 'rgba(37,99,235,0.08)',
            borderWidth: 2.5,
            pointBackgroundColor: '#2563eb',
            pointRadius: 4,
            tension: 0.4,
            fill: true,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: { min: 0, ticks: { color: '#94a3b8', font: { size: 11 } }, grid: { color: '#f1f5f9' } },
            x: { ticks: { color: '#94a3b8', font: { size: 11 } }, grid: { display: false } }
        }
    }
});

let currentFormat = null, currentSource = null, uploadedFile = null;
function openExport(format, source) {
    currentFormat = format; currentSource = source; uploadedFile = null;
    const labels = { rekap: 'Rekap Kelas', monitoring: 'Monitoring' };
    document.getElementById('modalTitle').textContent = 'Export ' + (labels[source] ?? source);
    selectFormat(format);
    document.getElementById('filePreview').classList.remove('show');
    document.getElementById('dropZone').style.display = 'block';
    document.getElementById('fileInput').value = '';
    document.getElementById('exportModal').classList.add('show');
}
function closeExport() { document.getElementById('exportModal').classList.remove('show'); }
function selectFormat(fmt) {
    currentFormat = fmt;
    document.getElementById('btnPDF').className  = 'export-type-btn' + (fmt === 'pdf'  ? ' selected-pdf'  : '');
    document.getElementById('btnXLSX').className = 'export-type-btn' + (fmt === 'xlsx' ? ' selected-xlsx' : '');
}
function handleDragOver(e) { e.preventDefault(); document.getElementById('dropZone').classList.add('dragover'); }
function handleDragLeave(e) { document.getElementById('dropZone').classList.remove('dragover'); }
function handleDrop(e) { e.preventDefault(); document.getElementById('dropZone').classList.remove('dragover'); const file = e.dataTransfer.files[0]; if (file) showFilePreview(file); }
function handleFileInput(e) { const file = e.target.files[0]; if (file) showFilePreview(file); }
function showFilePreview(file) { uploadedFile = file; document.getElementById('fileName').textContent = file.name; document.getElementById('fileSize').textContent = (file.size/1024).toFixed(1)+' KB'; document.getElementById('filePreview').classList.add('show'); document.getElementById('dropZone').style.display = 'none'; }
function removeFile() { uploadedFile = null; document.getElementById('filePreview').classList.remove('show'); document.getElementById('dropZone').style.display = 'block'; document.getElementById('fileInput').value = ''; }
function doExport() { if (!currentFormat) { alert('Pilih format export terlebih dahulu!'); return; } alert('✅ Export berhasil!'); closeExport(); }
document.getElementById('exportModal').addEventListener('click', function(e) { if (e.target === this) closeExport(); });
</script>

</body>
</html>