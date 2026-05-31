<?php
if (session_status() === PHP_SESSION_NONE) session_start();
date_default_timezone_set('Asia/Jakarta');

$bulan = ['January'=>'Januari','February'=>'Februari','March'=>'Maret','April'=>'April',
    'May'=>'Mei','June'=>'Juni','July'=>'Juli','August'=>'Agustus',
    'September'=>'September','October'=>'Oktober','November'=>'November','December'=>'Desember'];
$tanggalHariIni = date('d') . ' ' . $bulan[date('F')] . ' ' . date('Y');

// Data dummy laporan
$laporanData = [
    ['tanggal'=>'2026-05-14','nama'=>'Ahmad Fauzi','kelas'=>'XI RPL 1','jam_datang'=>'07:05','jam_pulang'=>'15:30','status'=>'Hadir','ket'=>'Tepat Waktu'],
    ['tanggal'=>'2026-05-14','nama'=>'Bella Safitri','kelas'=>'XI RPL 1','jam_datang'=>'07:20','jam_pulang'=>'15:30','status'=>'Hadir','ket'=>'Tepat Waktu'],
    ['tanggal'=>'2026-05-14','nama'=>'Cahya Ramadan','kelas'=>'XI RPL 1','jam_datang'=>'08:15','jam_pulang'=>'15:30','status'=>'Terlambat','ket'=>'Terlambat 15 menit'],
    ['tanggal'=>'2026-05-14','nama'=>'Dewi Lestari','kelas'=>'X RPL 2','jam_datang'=>'07:00','jam_pulang'=>'15:30','status'=>'Hadir','ket'=>'Tepat Waktu'],
    ['tanggal'=>'2026-05-14','nama'=>'Eko Prasetyo','kelas'=>'X RPL 2','jam_datang'=>'-','jam_pulang'=>'-','status'=>'Alpha','ket'=>'Tidak Hadir'],
    ['tanggal'=>'2026-05-14','nama'=>'Fitri Handayani','kelas'=>'XII IPA 1','jam_datang'=>'07:10','jam_pulang'=>'15:30','status'=>'Hadir','ket'=>'Tepat Waktu'],
    ['tanggal'=>'2026-05-13','nama'=>'Gilang Ramadhan','kelas'=>'XII IPA 1','jam_datang'=>'-','jam_pulang'=>'-','status'=>'Izin','ket'=>'Surat Izin'],
    ['tanggal'=>'2026-05-13','nama'=>'Hana Pertiwi','kelas'=>'XI RPL 2','jam_datang'=>'07:25','jam_pulang'=>'15:30','status'=>'Hadir','ket'=>'Tepat Waktu'],
    ['tanggal'=>'2026-05-13','nama'=>'Irfan Maulana','kelas'=>'XI RPL 2','jam_datang'=>'-','jam_pulang'=>'-','status'=>'Sakit','ket'=>'Surat Sakit'],
    ['tanggal'=>'2026-05-13','nama'=>'Jasmine Putri','kelas'=>'X RPL 1','jam_datang'=>'07:05','jam_pulang'=>'15:30','status'=>'Hadir','ket'=>'Tepat Waktu'],
    ['tanggal'=>'2026-05-12','nama'=>'Kevin Ardian','kelas'=>'X RPL 1','jam_datang'=>'08:45','jam_pulang'=>'15:30','status'=>'Terlambat','ket'=>'Terlambat 45 menit'],
    ['tanggal'=>'2026-05-12','nama'=>'Layla Sari','kelas'=>'XII RPL 1','jam_datang'=>'07:00','jam_pulang'=>'15:30','status'=>'Hadir','ket'=>'Tepat Waktu'],
];

$kelasList = ['X RPL 1','X RPL 2','XI RPL 1','XI RPL 2','XII RPL 1','XII RPL 2','X IPA 1','XI IPA 1','XII IPA 1'];

$totalHadir    = count(array_filter($laporanData, fn($r) => $r['status'] === 'Hadir'));
$totalTerlambat= count(array_filter($laporanData, fn($r) => $r['status'] === 'Terlambat'));
$totalIzinSakit= count(array_filter($laporanData, fn($r) => in_array($r['status'], ['Izin','Sakit'])));
$totalAlpha    = count(array_filter($laporanData, fn($r) => $r['status'] === 'Alpha'));
$total         = count($laporanData);

$warnaAva = ['#4f46e5','#7c3aed','#0ea5e9','#10b981','#f59e0b','#ec4899','#ef4444','#14b8a6','#f97316','#06b6d4'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Kehadiran – Admin Absensi QR</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <?php require_once __DIR__ . '/../layouts/sidebar_admin.php'; ?>
    <style>
        .filter-card {
            background: white;
            border-radius: 14px;
            padding: 20px 24px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }
        .filter-card h4 {
            font-size: 14px; font-weight: 700;
            color: var(--text); margin-bottom: 16px;
            display: flex; align-items: center; gap: 8px;
        }
        .filter-row { display: grid; grid-template-columns: 1fr 1fr 1fr auto auto; gap: 12px; align-items: end; }
        .filter-field label { display: block; font-size: 11px; font-weight: 600; color: var(--text2); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px; }
        .filter-field input,
        .filter-field select {
            width: 100%; padding: 9px 13px;
            border: 1px solid var(--border);
            border-radius: 9px; font-size: 13px;
            font-family: 'Poppins', sans-serif;
            color: var(--text); outline: none;
            background: white;
            transition: border-color 0.15s, box-shadow 0.15s;
        }
        .filter-field input:focus,
        .filter-field select:focus { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(79,70,229,0.1); }

        .summary-chips { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 20px; }
        .chip {
            display: flex; align-items: center; gap: 8px;
            background: white; border-radius: 12px;
            padding: 10px 16px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.06);
            font-size: 13px; font-weight: 600;
            flex: 1; min-width: 130px;
            border-left: 4px solid transparent;
        }
        .chip-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
        .chip-val { font-size: 20px; font-weight: 800; }
        .chip-lbl { font-size: 11px; color: var(--text2); font-weight: 500; }

        .status-hadir    { background:#dcfce7; color:#16a34a; }
        .status-terlambat{ background:#fef3c7; color:#d97706; }
        .status-izin     { background:#dbeafe; color:#2563eb; }
        .status-sakit    { background:#fce7f3; color:#db2777; }
        .status-alpha    { background:#fee2e2; color:#dc2626; }

        .btn-export-xl {
            background: linear-gradient(135deg, #16a34a, #15803d);
            color: white; border: none;
            padding: 9px 18px; border-radius: 9px;
            font-size: 13px; font-weight: 600;
            cursor: pointer; display: inline-flex;
            align-items: center; gap: 7px;
            transition: all 0.15s;
            font-family: 'Poppins', sans-serif;
            box-shadow: 0 2px 8px rgba(22,163,74,0.3);
        }
        .btn-export-xl:hover { background: linear-gradient(135deg, #15803d, #166534); box-shadow: 0 4px 14px rgba(22,163,74,0.45); }

        .btn-export-pdf {
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            color: white; border: none;
            padding: 9px 18px; border-radius: 9px;
            font-size: 13px; font-weight: 600;
            cursor: pointer; display: inline-flex;
            align-items: center; gap: 7px;
            transition: all 0.15s;
            font-family: 'Poppins', sans-serif;
            box-shadow: 0 2px 8px rgba(220,38,38,0.3);
        }
        .btn-export-pdf:hover { box-shadow: 0 4px 14px rgba(220,38,38,0.45); }

        .table-responsive { overflow-x: auto; }

        .ket-badge {
            display: inline-flex; align-items: center; gap: 5px;
            font-size: 11.5px; font-weight: 500;
            color: var(--text2);
        }

        /* Empty state */
        .empty-laporan {
            text-align: center; padding: 70px 20px;
        }
        .empty-laporan .empty-icon {
            width: 80px; height: 80px;
            background: #ede9fe;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 16px;
            font-size: 36px; color: var(--accent);
        }
        .empty-laporan h5 { font-size: 16px; font-weight: 700; color: var(--text); margin-bottom: 6px; }
        .empty-laporan p  { font-size: 13px; color: var(--text2); }

        /* Chart section */
        .chart-row { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; margin-bottom: 20px; }
        .chart-card { background: white; border-radius: 14px; padding: 20px 22px; box-shadow: 0 1px 4px rgba(0,0,0,0.05); }
        .chart-card h4 { font-size: 14px; font-weight: 700; color: var(--text); margin-bottom: 14px; }

        @media (max-width: 900px) {
            .filter-row { grid-template-columns: 1fr 1fr; }
            .chart-row  { grid-template-columns: 1fr; }
        }
        @media (max-width: 600px) {
            .filter-row { grid-template-columns: 1fr; }
        }

        /* Print */
        @media print {
            .admin-sidebar, .admin-topbar, .filter-card,
            .summary-chips, .chart-row, .table-actions { display: none !important; }
            .admin-main { margin-left: 0 !important; }
            .table-card { box-shadow: none !important; }
        }
    </style>
</head>
<body>
<div class="admin-layout">
    <div class="admin-main">

        <!-- TOPBAR -->
        <div class="admin-topbar">
            <div class="topbar-left">
                <button class="topbar-toggle" onclick="toggleSidebar()"><i class="bi bi-list"></i></button>
                <div class="topbar-title">
                    <h2>Laporan Kehadiran</h2>
                    <p>Rekap & ekspor data kehadiran siswa</p>
                </div>
            </div>
            <div class="topbar-right">
                <div class="topbar-icon-btn" onclick="window.print()" title="Print">
                    <i class="bi bi-printer"></i>
                </div>
                <div class="topbar-icon-btn">
                    <i class="bi bi-bell"></i>
                    <span class="notif-badge">3</span>
                </div>
                <div class="topbar-avatar">AD</div>
            </div>
        </div>

        <div class="admin-content">

            <!-- FILTER CARD -->
            <div class="filter-card">
                <h4><i class="bi bi-funnel-fill" style="color:var(--accent);"></i> Filter Data Kehadiran</h4>
                <div class="filter-row">
                    <div class="filter-field">
                        <label>Tanggal Mulai</label>
                        <input type="date" id="tglMulai" value="2026-05-12">
                    </div>
                    <div class="filter-field">
                        <label>Tanggal Akhir</label>
                        <input type="date" id="tglAkhir" value="2026-05-14">
                    </div>
                    <div class="filter-field">
                        <label>Kelas</label>
                        <select id="filterKelas">
                            <option value="">Semua Kelas</option>
                            <?php foreach ($kelasList as $k): ?>
                            <option value="<?= $k ?>"><?= $k ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="filter-field">
                        <label>Status</label>
                        <select id="filterStatus">
                            <option value="">Semua Status</option>
                            <option value="Hadir">Hadir</option>
                            <option value="Terlambat">Terlambat</option>
                            <option value="Izin">Izin</option>
                            <option value="Sakit">Sakit</option>
                            <option value="Alpha">Alpha</option>
                        </select>
                    </div>
                    <div class="filter-field" style="display:flex;gap:8px;">
                        <div>
                            <label style="visibility:hidden;">Cari</label>
                            <button class="btn-primary" onclick="cariData()" style="white-space:nowrap;">
                                <i class="bi bi-search"></i> Cari
                            </button>
                        </div>
                        <div>
                            <label style="visibility:hidden;">Reset</label>
                            <button class="btn-secondary" onclick="resetFilter()" style="white-space:nowrap;">
                                <i class="bi bi-arrow-counterclockwise"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SUMMARY CHIPS -->
            <div class="summary-chips" id="summaryChips">
                <div class="chip" style="border-color:#16a34a;">
                    <div class="chip-dot" style="background:#16a34a;"></div>
                    <div>
                        <div class="chip-val" style="color:#16a34a;" id="chipHadir"><?= $totalHadir ?></div>
                        <div class="chip-lbl">Hadir</div>
                    </div>
                </div>
                <div class="chip" style="border-color:#d97706;">
                    <div class="chip-dot" style="background:#d97706;"></div>
                    <div>
                        <div class="chip-val" style="color:#d97706;" id="chipTerlambat"><?= $totalTerlambat ?></div>
                        <div class="chip-lbl">Terlambat</div>
                    </div>
                </div>
                <div class="chip" style="border-color:#2563eb;">
                    <div class="chip-dot" style="background:#2563eb;"></div>
                    <div>
                        <div class="chip-val" style="color:#2563eb;" id="chipIzinSakit"><?= $totalIzinSakit ?></div>
                        <div class="chip-lbl">Izin / Sakit</div>
                    </div>
                </div>
                <div class="chip" style="border-color:#dc2626;">
                    <div class="chip-dot" style="background:#dc2626;"></div>
                    <div>
                        <div class="chip-val" style="color:#dc2626;" id="chipAlpha"><?= $totalAlpha ?></div>
                        <div class="chip-lbl">Alpha</div>
                    </div>
                </div>
                <div class="chip" style="border-color:var(--accent);">
                    <div class="chip-dot" style="background:var(--accent);"></div>
                    <div>
                        <div class="chip-val" style="color:var(--accent);" id="chipTotal"><?= $total ?></div>
                        <div class="chip-lbl">Total Rekaman</div>
                    </div>
                </div>
            </div>

            <!-- CHART ROW -->
            <div class="chart-row" id="chartSection">
                <div class="chart-card">
                    <h4><i class="bi bi-pie-chart-fill" style="color:var(--accent);margin-right:6px;"></i>Proporsi Kehadiran</h4>
                    <canvas id="chartPie" height="180"></canvas>
                </div>
                <div class="chart-card">
                    <h4><i class="bi bi-bar-chart-fill" style="color:var(--accent);margin-right:6px;"></i>Kehadiran per Hari</h4>
                    <canvas id="chartBar" height="180"></canvas>
                </div>
            </div>

            <!-- TABLE CARD -->
            <div class="table-card">
                <div class="table-card-header">
                    <div>
                        <h3><i class="bi bi-table" style="color:var(--accent);margin-right:6px;"></i>Data Kehadiran</h3>
                        <p style="font-size:12px;color:var(--text2);margin-top:2px;" id="tableInfo">
                            Menampilkan <strong id="rowCount"><?= $total ?></strong> rekaman
                        </p>
                    </div>
                    <div class="table-actions" style="display:flex;gap:8px;align-items:center;">
                        <!-- Search inline -->
                        <div style="position:relative;">
                            <i class="bi bi-search" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:var(--text2);font-size:13px;"></i>
                            <input type="text" id="searchNama" placeholder="Cari nama siswa..."
                                style="padding:8px 12px 8px 32px;border:1px solid var(--border);border-radius:9px;font-size:13px;font-family:'Poppins',sans-serif;outline:none;width:200px;"
                                oninput="filterTable()">
                        </div>
                        <button class="btn-export-xl" onclick="exportExcel()">
                            <i class="bi bi-file-earmark-excel-fill"></i> Excel
                        </button>
                        <button class="btn-export-pdf" onclick="exportPDF()">
                            <i class="bi bi-file-earmark-pdf-fill"></i> PDF
                        </button>
                    </div>
                </div>

                <!-- TABLE -->
                <div class="table-responsive" id="tableSection">
                    <table class="admin-table" id="tblLaporan">
                        <thead>
                            <tr>
                                <th style="width:40px;">No</th>
                                <th style="cursor:pointer;" onclick="sortTable(1)">Tanggal <i class="bi bi-arrow-down-up" style="font-size:10px;opacity:.5;"></i></th>
                                <th style="cursor:pointer;" onclick="sortTable(2)">Nama Siswa <i class="bi bi-arrow-down-up" style="font-size:10px;opacity:.5;"></i></th>
                                <th>Kelas</th>
                                <th>Jam Datang</th>
                                <th>Jam Pulang</th>
                                <th>Status</th>
                                <th>Keterangan Waktu</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyLaporan">
                        <?php foreach ($laporanData as $i => $row):
                            $tgl = date('d M Y', strtotime($row['tanggal']));
                            $ava = $warnaAva[$i % count($warnaAva)];
                            $inisial = strtoupper(implode('', array_map(fn($w) => $w[0], array_slice(explode(' ', $row['nama']), 0, 2))));
                            $statusClass = match($row['status']) {
                                'Hadir'     => 'status-hadir',
                                'Terlambat' => 'status-terlambat',
                                'Izin'      => 'status-izin',
                                'Sakit'     => 'status-sakit',
                                default     => 'status-alpha',
                            };
                            $statusIcon = match($row['status']) {
                                'Hadir'     => 'bi-check-circle-fill',
                                'Terlambat' => 'bi-clock-fill',
                                'Izin'      => 'bi-file-text-fill',
                                'Sakit'     => 'bi-heart-pulse-fill',
                                default     => 'bi-x-circle-fill',
                            };
                            $ketIcon = match(true) {
                                str_contains($row['ket'], 'Terlambat') => 'bi-clock-history',
                                str_contains($row['ket'], 'Surat')     => 'bi-file-earmark-text',
                                str_contains($row['ket'], 'Tidak')     => 'bi-x-circle',
                                default                                 => 'bi-check2-circle',
                            };
                        ?>
                            <tr data-nama="<?= strtolower($row['nama']) ?>"
                                data-kelas="<?= $row['kelas'] ?>"
                                data-status="<?= $row['status'] ?>"
                                data-tanggal="<?= $row['tanggal'] ?>">
                                <td style="color:var(--text2);font-size:12px;"><?= $i + 1 ?></td>
                                <td>
                                    <div style="font-size:13px;font-weight:600;color:var(--text);"><?= $tgl ?></div>
                                    <div style="font-size:11px;color:var(--text2);"><?= date('l', strtotime($row['tanggal'])) ?></div>
                                </td>
                                <td>
                                    <div style="display:flex;align-items:center;gap:9px;">
                                        <div style="width:32px;height:32px;border-radius:50%;background:<?= $ava ?>;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:white;flex-shrink:0;"><?= $inisial ?></div>
                                        <span style="font-weight:600;font-size:13px;"><?= htmlspecialchars($row['nama']) ?></span>
                                    </div>
                                </td>
                                <td>
                                    <span style="background:#ede9fe;color:#7c3aed;padding:3px 9px;border-radius:20px;font-size:11.5px;font-weight:600;"><?= $row['kelas'] ?></span>
                                </td>
                                <td>
                                    <?php if ($row['jam_datang'] !== '-'): ?>
                                    <span style="font-weight:600;font-size:13px;"><?= $row['jam_datang'] ?></span>
                                    <?php else: ?>
                                    <span style="color:var(--text2);font-size:13px;">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($row['jam_pulang'] !== '-'): ?>
                                    <span style="font-weight:600;font-size:13px;"><?= $row['jam_pulang'] ?></span>
                                    <?php else: ?>
                                    <span style="color:var(--text2);font-size:13px;">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge <?= $statusClass ?>" style="gap:5px;">
                                        <i class="bi <?= $statusIcon ?>"></i> <?= $row['status'] ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="ket-badge">
                                        <i class="bi <?= $ketIcon ?>" style="font-size:13px;"></i>
                                        <?= htmlspecialchars($row['ket']) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>

                    <!-- EMPTY STATE -->
                    <div id="emptyState" style="display:none;" class="empty-laporan">
                        <div class="empty-icon"><i class="bi bi-clipboard-x"></i></div>
                        <h5>Tidak Ada Data</h5>
                        <p>Data kehadiran tidak ditemukan untuk filter yang dipilih.<br>Coba ubah rentang tanggal atau filter kelas.</p>
                        <button class="btn-secondary" onclick="resetFilter()" style="margin-top:14px;">
                            <i class="bi bi-arrow-counterclockwise"></i> Reset Filter
                        </button>
                    </div>
                </div>

                <!-- PAGINATION -->
                <div class="pagination" id="paginationWrap">
                    <span style="font-size:12px;color:var(--text2);margin-right:8px;" id="pageInfo">Halaman 1 dari 1</span>
                    <button class="page-btn"><i class="bi bi-chevron-left" style="font-size:11px;"></i></button>
                    <button class="page-btn active">1</button>
                    <button class="page-btn">2</button>
                    <button class="page-btn">3</button>
                    <button class="page-btn"><i class="bi bi-chevron-right" style="font-size:11px;"></i></button>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- MODAL EXPORT -->
<div class="modal-overlay" id="modalExport" onclick="if(event.target===this)closeModal('modalExport')">
    <div class="modal-box" style="max-width:420px;">
        <div class="modal-header">
            <h4 id="exportModalTitle"><i class="bi bi-download" style="color:var(--accent);margin-right:8px;"></i>Export Data</h4>
            <button class="modal-close" onclick="closeModal('modalExport')"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="modal-body">
            <div style="background:#f5f3ff;border-radius:12px;padding:14px 16px;margin-bottom:16px;display:flex;align-items:center;gap:12px;">
                <i class="bi bi-info-circle-fill" style="color:var(--accent);font-size:20px;flex-shrink:0;"></i>
                <div style="font-size:13px;color:var(--text);">
                    File akan diexport berdasarkan filter yang sedang aktif.<br>
                    <strong id="exportInfo">12 rekaman</strong> akan diekspor.
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Nama File</label>
                <input type="text" class="form-control" id="exportFileName" value="laporan_kehadiran_<?= date('Ymd') ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Sertakan Kolom</label>
                <div style="display:flex;flex-direction:column;gap:8px;">
                    <?php foreach (['Tanggal','Nama Siswa','Kelas','Jam Datang','Jam Pulang','Status','Keterangan'] as $col): ?>
                    <label style="display:flex;align-items:center;gap:8px;font-size:13px;cursor:pointer;">
                        <input type="checkbox" checked style="accent-color:var(--accent);"> <?= $col ?>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn-secondary" onclick="closeModal('modalExport')">Batal</button>
            <button class="btn-primary" id="btnDoExport" onclick="doExport()">
                <i class="bi bi-download"></i> Download
            </button>
        </div>
    </div>
</div>

<script>
// ── Data PHP → JS ────────────────────────────────────────
const allData = <?= json_encode($laporanData) ?>;
let currentExportType = 'excel';

// ── Charts ───────────────────────────────────────────────
const ctxPie = document.getElementById('chartPie').getContext('2d');
new Chart(ctxPie, {
    type: 'doughnut',
    data: {
        labels: ['Hadir', 'Terlambat', 'Izin/Sakit', 'Alpha'],
        datasets: [{
            data: [<?= $totalHadir ?>, <?= $totalTerlambat ?>, <?= $totalIzinSakit ?>, <?= $totalAlpha ?>],
            backgroundColor: ['#16a34a','#d97706','#2563eb','#dc2626'],
            borderWidth: 0,
            hoverOffset: 6,
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'bottom', labels: { font: { size: 11, family: 'Poppins' }, padding: 14 } }
        },
        cutout: '65%',
    }
});

const ctxBar = document.getElementById('chartBar').getContext('2d');
new Chart(ctxBar, {
    type: 'bar',
    data: {
        labels: ['12 Mei','13 Mei','14 Mei'],
        datasets: [
            { label: 'Hadir',     data: [1,3,4], backgroundColor: '#4f46e5', borderRadius: 5 },
            { label: 'Terlambat', data: [1,0,1], backgroundColor: '#d97706', borderRadius: 5 },
            { label: 'Alpha',     data: [0,0,1], backgroundColor: '#dc2626', borderRadius: 5 },
        ]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'bottom', labels: { font: { size: 11, family: 'Poppins' }, padding: 12 } } },
        scales: {
            x: { stacked: false, grid: { display: false }, ticks: { font: { size: 11, family: 'Poppins' } } },
            y: { min: 0, ticks: { font: { size: 11, family: 'Poppins' }, stepSize: 1 }, grid: { color: '#f1f5f9' } }
        }
    }
});

// ── Filter & Search ──────────────────────────────────────
function filterTable() {
    const q      = document.getElementById('searchNama').value.toLowerCase();
    const kelas  = document.getElementById('filterKelas').value;
    const status = document.getElementById('filterStatus').value;
    const rows   = document.querySelectorAll('#tbodyLaporan tr');
    let count = 0;

    rows.forEach(row => {
        const match =
            (!q      || row.dataset.nama.includes(q)) &&
            (!kelas  || row.dataset.kelas === kelas)  &&
            (!status || row.dataset.status === status);
        row.style.display = match ? '' : 'none';
        if (match) count++;
    });

    document.getElementById('rowCount').textContent = count;
    document.getElementById('emptyState').style.display = count === 0 ? 'block' : 'none';
    document.querySelector('#tblLaporan').style.display  = count === 0 ? 'none' : '';
    document.getElementById('paginationWrap').style.display = count === 0 ? 'none' : '';
}

function cariData() {
    filterTable();
    showToast('Filter diterapkan — ' + document.getElementById('rowCount').textContent + ' rekaman ditemukan.');
}

function resetFilter() {
    document.getElementById('tglMulai').value    = '2026-05-12';
    document.getElementById('tglAkhir').value    = '2026-05-14';
    document.getElementById('filterKelas').value  = '';
    document.getElementById('filterStatus').value = '';
    document.getElementById('searchNama').value   = '';
    filterTable();
    showToast('Filter direset.');
}

// ── Sort Table ───────────────────────────────────────────
let sortDir = {};
function sortTable(col) {
    const tbody = document.getElementById('tbodyLaporan');
    const rows  = Array.from(tbody.querySelectorAll('tr'));
    sortDir[col] = !sortDir[col];
    rows.sort((a, b) => {
        const av = a.cells[col].innerText.trim();
        const bv = b.cells[col].innerText.trim();
        return sortDir[col] ? av.localeCompare(bv) : bv.localeCompare(av);
    });
    rows.forEach(r => tbody.appendChild(r));
}

// ── Export ───────────────────────────────────────────────
function exportExcel() {
    currentExportType = 'excel';
    document.getElementById('exportModalTitle').innerHTML =
        '<i class="bi bi-file-earmark-excel-fill" style="color:#16a34a;margin-right:8px;"></i>Export Excel';
    document.getElementById('btnDoExport').innerHTML =
        '<i class="bi bi-file-earmark-excel-fill"></i> Download Excel';
    document.getElementById('exportInfo').textContent =
        document.getElementById('rowCount').textContent + ' rekaman';
    openModal('modalExport');
}
function exportPDF() {
    currentExportType = 'pdf';
    document.getElementById('exportModalTitle').innerHTML =
        '<i class="bi bi-file-earmark-pdf-fill" style="color:#dc2626;margin-right:8px;"></i>Export PDF';
    document.getElementById('btnDoExport').innerHTML =
        '<i class="bi bi-file-earmark-pdf-fill"></i> Download PDF';
    document.getElementById('exportInfo').textContent =
        document.getElementById('rowCount').textContent + ' rekaman';
    openModal('modalExport');
}
function doExport() {
    const fname = document.getElementById('exportFileName').value || 'laporan_kehadiran';
    closeModal('modalExport');
    showToast('File "' + fname + '.' + (currentExportType === 'excel' ? 'xlsx' : 'pdf') + '" sedang diunduh...');
    // TODO: hit controller endpoint untuk generate file sungguhan
}
</script>
</body>
</html>