<?php
if (session_status() === PHP_SESSION_NONE) session_start();
date_default_timezone_set('Asia/Jakarta');

// Data dari controller
$kelasList   = $kelasList   ?? [];
$guruList    = $guruList    ?? [];
$totalKelas  = $totalKelas  ?? count($kelasList);
$totalSiswa  = $totalSiswa  ?? 0;
$kelasAktif  = $kelasAktif  ?? 0;
$kelasKosong = $kelasKosong ?? 0;

$warnaList = ['#4f46e5','#7c3aed','#0ea5e9','#10b981','#f59e0b','#ec4899','#ef4444','#14b8a6','#f97316','#06b6d4'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Kelas – Admin Absensi QR</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
    :root {
        --accent:#4f46e5;--accent2:#7c3aed;--bg:#f5f3ff;--text:#1e1b4b;
        --text2:#6b7280;--border:#e5e7eb;--sidebar-w:255px;
    }
    *,*::before,*::after{box-sizing:border-box;}
    body{font-family:'Poppins',sans-serif;background:var(--bg);color:var(--text);font-size:14px;margin:0;}
    .admin-layout{display:flex;min-height:100vh;}
    .admin-main{margin-left:var(--sidebar-w);flex:1;display:flex;flex-direction:column;background:var(--bg);}
    .admin-topbar{background:#fff;border-bottom:1px solid var(--border);padding:13px 28px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:50;box-shadow:0 1px 3px rgba(0,0,0,0.05);}
    .topbar-left{display:flex;align-items:center;gap:14px;}
    .topbar-toggle{background:none;border:none;font-size:18px;color:var(--text2);cursor:pointer;padding:4px;border-radius:6px;}
    .topbar-title h2{font-size:16px;font-weight:700;color:var(--text);margin:0;}
    .topbar-title p{font-size:12px;color:var(--text2);margin:0;}
    .topbar-right{display:flex;align-items:center;gap:10px;}
    .topbar-icon-btn{width:36px;height:36px;border-radius:9px;border:1px solid var(--border);background:#fff;display:flex;align-items:center;justify-content:center;cursor:pointer;color:var(--text2);font-size:15px;transition:all .15s;position:relative;}
    .notif-badge{position:absolute;top:-4px;right:-4px;width:16px;height:16px;background:#ef4444;border-radius:50%;font-size:9px;font-weight:700;color:#fff;display:flex;align-items:center;justify-content:center;border:2px solid #fff;}
    .topbar-avatar{width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,var(--accent),var(--accent2));display:flex;align-items:center;justify-content:center;font-weight:700;font-size:13px;color:#fff;cursor:pointer;}
    .admin-content{padding:24px 28px;flex:1;}
    .stat-cards{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:24px;}
    .stat-card{background:#fff;border-radius:14px;padding:18px 20px;box-shadow:0 1px 4px rgba(0,0,0,0.05);display:flex;align-items:center;gap:14px;transition:transform .2s,box-shadow .2s;}
    .stat-card:hover{transform:translateY(-2px);box-shadow:0 6px 20px rgba(79,70,229,0.1);}
    .stat-icon{width:46px;height:46px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0;}
    .stat-val{font-size:24px;font-weight:800;color:var(--text);line-height:1.1;}
    .stat-label{font-size:12px;color:var(--text2);margin-top:2px;}
    .table-card{background:#fff;border-radius:16px;box-shadow:0 1px 4px rgba(0,0,0,0.05);overflow:hidden;}
    .table-card-header{padding:16px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;}
    .table-card-header h3{font-size:15px;font-weight:700;color:var(--text);margin:0;}
    .filter-bar{display:flex;align-items:center;gap:10px;padding:13px 20px;border-bottom:1px solid var(--border);background:#fafafa;flex-wrap:wrap;}
    .search-wrap{position:relative;flex:1;min-width:220px;}
    .search-wrap i{position:absolute;left:11px;top:50%;transform:translateY(-50%);color:var(--text2);font-size:13px;}
    .search-input{width:100%;padding:8px 12px 8px 33px;border:1px solid var(--border);border-radius:9px;font-size:13px;font-family:inherit;color:var(--text);outline:none;background:#fff;}
    .search-input:focus{border-color:var(--accent);box-shadow:0 0 0 3px rgba(79,70,229,0.1);}
    .filter-select{padding:8px 12px;border:1px solid var(--border);border-radius:9px;font-size:13px;font-family:inherit;color:var(--text);outline:none;background:#fff;cursor:pointer;}
    .kelas-card{background:#fff;border-radius:14px;padding:18px 20px;box-shadow:0 1px 4px rgba(0,0,0,0.05);display:flex;align-items:center;gap:14px;transition:transform .2s,box-shadow .2s;border:2px solid transparent;}
    .kelas-card:hover{transform:translateY(-2px);box-shadow:0 6px 20px rgba(79,70,229,0.12);border-color:rgba(79,70,229,0.15);}
    .kelas-icon{width:46px;height:46px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:800;color:white;flex-shrink:0;}
    .kelas-nama{font-size:15px;font-weight:700;color:var(--text);}
    .kelas-wali{font-size:11.5px;color:var(--text2);margin-top:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:160px;}
    .kelas-siswa{font-size:11px;font-weight:600;background:#ede9fe;color:#7c3aed;padding:2px 8px;border-radius:20px;margin-top:4px;display:inline-block;}
    .kelas-actions{margin-left:auto;display:flex;gap:6px;flex-shrink:0;}
    .grid-kelas{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:14px;}
    .view-toggle{display:flex;gap:4px;}
    .view-btn{width:34px;height:34px;border-radius:8px;border:1px solid var(--border);background:white;display:flex;align-items:center;justify-content:center;cursor:pointer;color:var(--text2);font-size:15px;transition:all 0.15s;}
    .view-btn.active{background:var(--accent);color:white;border-color:var(--accent);}
    .admin-table{width:100%;border-collapse:collapse;}
    .admin-table thead th{padding:11px 16px;font-size:11px;font-weight:700;color:var(--text2);text-transform:uppercase;letter-spacing:.7px;background:#fafafa;border-bottom:1px solid var(--border);}
    .admin-table tbody td{padding:12px 16px;font-size:13px;color:#374151;border-bottom:1px solid #f3f4f6;vertical-align:middle;}
    .admin-table tbody tr:last-child td{border-bottom:none;}
    .admin-table tbody tr:hover td{background:#faf5ff;}
    .ava{width:28px;height:28px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-weight:700;font-size:10px;color:#fff;flex-shrink:0;}
    .badge{display:inline-flex;align-items:center;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;}
    .badge-success{background:#dcfce7;color:#16a34a;}
    .badge-danger{background:#fee2e2;color:#dc2626;}
    .btn-primary{background:var(--accent);color:#fff;border:none;padding:9px 18px;border-radius:9px;font-size:13px;font-weight:600;cursor:pointer;display:inline-flex;align-items:center;gap:7px;transition:all .15s;font-family:inherit;}
    .btn-primary:hover{background:#4338ca;}
    .btn-secondary{background:#f3f4f6;color:var(--text2);border:none;padding:8px 14px;border-radius:9px;font-size:13px;font-weight:600;cursor:pointer;display:inline-flex;align-items:center;gap:6px;transition:all .15s;font-family:inherit;}
    .btn-secondary:hover{background:#e5e7eb;}
    .btn-edit{background:#ede9fe;color:#7c3aed;border:none;padding:6px 12px;border-radius:7px;font-size:12px;font-weight:600;cursor:pointer;display:inline-flex;align-items:center;gap:5px;transition:all .15s;font-family:inherit;}
    .btn-edit:hover{background:#c4b5fd;}
    .btn-danger{background:#fee2e2;color:#dc2626;border:none;padding:6px 12px;border-radius:7px;font-size:12px;font-weight:600;cursor:pointer;display:inline-flex;align-items:center;gap:5px;transition:all .15s;font-family:inherit;}
    .btn-danger:hover{background:#fca5a5;}
    .btn-sm{padding:5px 10px;font-size:11.5px;}
    .form-group{margin-bottom:16px;}
    .form-label{display:block;font-size:12px;font-weight:600;color:var(--text2);margin-bottom:6px;}
    .form-control{width:100%;padding:9px 13px;border:1px solid var(--border);border-radius:9px;font-size:13px;font-family:inherit;color:var(--text);outline:none;background:#fff;transition:border-color .15s;}
    .form-control:focus{border-color:var(--accent);box-shadow:0 0 0 3px rgba(79,70,229,.1);}
    .form-grid-2{display:grid;grid-template-columns:1fr 1fr;gap:14px;}
    .modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:2000;align-items:center;justify-content:center;padding:20px;}
    .modal-overlay.show{display:flex;}
    .modal-box{background:#fff;border-radius:18px;width:100%;max-width:500px;max-height:90vh;overflow-y:auto;box-shadow:0 25px 60px rgba(0,0,0,.2);animation:modalIn .22s ease;}
    @keyframes modalIn{from{transform:scale(.94) translateY(12px);opacity:0;}to{transform:scale(1) translateY(0);opacity:1;}}
    .modal-header{padding:20px 24px 16px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;}
    .modal-header h4{font-size:16px;font-weight:700;margin:0;}
    .modal-close{width:30px;height:30px;border-radius:8px;border:none;background:var(--bg);cursor:pointer;font-size:14px;color:var(--text2);display:flex;align-items:center;justify-content:center;}
    .modal-close:hover{background:#fee2e2;color:#dc2626;}
    .modal-body{padding:20px 24px;}
    .modal-footer{padding:14px 24px 20px;display:flex;justify-content:flex-end;gap:10px;border-top:1px solid var(--border);}
    .confirm-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:1100;align-items:center;justify-content:center;}
    .confirm-overlay.show{display:flex;}
    .confirm-box{background:white;border-radius:16px;padding:28px;width:360px;text-align:center;box-shadow:0 25px 60px rgba(0,0,0,0.2);animation:modalIn 0.22s ease;}
    .confirm-icon-box{width:60px;height:60px;border-radius:50%;background:#fee2e2;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;font-size:26px;color:#dc2626;}
    .empty-state{text-align:center;padding:48px 20px;color:var(--text2);}
    .empty-state i{font-size:40px;display:block;margin-bottom:12px;opacity:.35;}
    .toast-wrap{position:fixed;bottom:24px;right:24px;z-index:9999;display:flex;flex-direction:column;gap:8px;}
    .toast{background:#1e1b4b;color:#fff;padding:13px 18px;border-radius:12px;font-size:13px;font-weight:500;display:flex;align-items:center;gap:10px;box-shadow:0 8px 24px rgba(0,0,0,.2);max-width:320px;}
    .toast.success{border-left:4px solid #22c55e;}
    .toast.error{border-left:4px solid #ef4444;}
    @keyframes toastIn{from{transform:translateX(120%);opacity:0;}to{transform:translateX(0);opacity:1;}}
    @media(max-width:1024px){.admin-main{margin-left:0;}.stat-cards{grid-template-columns:repeat(2,1fr);}}
    @media(max-width:640px){.admin-content{padding:16px;}.form-grid-2{grid-template-columns:1fr;}}
    </style>
</head>
<body>
<div class="admin-layout">
    <?php include dirname(__DIR__) . '/layouts/sidebar_admin.php'; ?>

    <div class="admin-main">
        <!-- TOPBAR -->
        <div class="admin-topbar">
            <div class="topbar-left">
                <button class="topbar-toggle" onclick="toggleSidebar()"><i class="bi bi-list"></i></button>
                <div class="topbar-title">
                    <h2>Data Kelas</h2>
                    <p>Kelola data kelas dan wali kelas</p>
                </div>
            </div>
            <div class="topbar-right">
                <div class="topbar-icon-btn">
                    <i class="bi bi-bell"></i>
                    <span class="notif-badge">3</span>
                </div>
                <div class="topbar-avatar">
                    <?php
                        $n = $_SESSION['user']['nama'] ?? 'AD';
                        $parts = explode(' ', $n);
                        echo strtoupper(implode('', array_map(fn($w)=>$w[0], array_slice($parts,0,2))));
                    ?>
                </div>
            </div>
        </div>

        <!-- CONTENT -->
        <div class="admin-content">

            <!-- STAT CARDS -->
            <div class="stat-cards">
                <div class="stat-card">
                    <div class="stat-icon" style="background:#ede9fe;"><i class="bi bi-building" style="color:#7c3aed;"></i></div>
                    <div><div class="stat-val"><?= $totalKelas ?></div><div class="stat-label">Total Kelas</div></div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background:#ede9fe;"><i class="bi bi-check-circle-fill" style="color:#4f46e5;"></i></div>
                    <div><div class="stat-val"><?= $kelasAktif ?></div><div class="stat-label">Kelas Aktif</div></div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background:#dbeafe;"><i class="bi bi-people-fill" style="color:#2563eb;"></i></div>
                    <div><div class="stat-val"><?= $totalSiswa ?></div><div class="stat-label">Total Siswa</div></div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background:#fee2e2;"><i class="bi bi-exclamation-circle-fill" style="color:#dc2626;"></i></div>
                    <div><div class="stat-val"><?= $kelasKosong ?></div><div class="stat-label">Kelas Kosong</div></div>
                </div>
            </div>

            <!-- TABLE CARD -->
            <div class="table-card">
                <div class="table-card-header">
                    <h3><i class="bi bi-building me-2" style="color:var(--accent);"></i>Daftar Kelas</h3>
                    <div style="display:flex;gap:8px;align-items:center;">
                        <div class="view-toggle">
                            <button class="view-btn active" id="btnGrid" onclick="setView('grid')" title="Grid View"><i class="bi bi-grid-3x3-gap-fill"></i></button>
                            <button class="view-btn" id="btnList" onclick="setView('list')" title="List View"><i class="bi bi-list-ul"></i></button>
                        </div>
                        <button class="btn-primary" onclick="openModal('modalTambah')">
                            <i class="bi bi-plus-lg"></i> Tambah Kelas
                        </button>
                    </div>
                </div>

                <!-- FILTER BAR -->
                <div class="filter-bar">
                    <div class="search-wrap">
                        <i class="bi bi-search"></i>
                        <input type="text" class="search-input" id="searchInput" placeholder="Cari nama kelas atau wali kelas..." oninput="filterKelas()">
                    </div>
                    <select class="filter-select" id="filterTingkat" onchange="filterKelas()">
                        <option value="">Semua Tingkat</option>
                        <option value="X">Kelas X</option>
                        <option value="XI">Kelas XI</option>
                        <option value="XII">Kelas XII</option>
                    </select>
                    <select class="filter-select" id="filterStatus" onchange="filterKelas()">
                        <option value="">Semua Status</option>
                        <option value="Aktif">Aktif</option>
                        <option value="Kosong">Kosong</option>
                    </select>
                    <span id="totalShown" style="font-size:12px;color:var(--text2);white-space:nowrap;"><?= $totalKelas ?> kelas</span>
                </div>

                <!-- GRID VIEW -->
                <div id="viewGrid" style="padding:20px;">
                    <div class="grid-kelas" id="gridContainer">
                        <?php foreach ($kelasList as $i => $k):
                            $warna   = $warnaList[$i % count($warnaList)];
                            $tingkat = explode(' ', $k['nama_kelas'])[0];
                            $kId     = $k['id'] ?? $i;
                            $namaK   = htmlspecialchars($k['nama_kelas']);
                            $waliK   = htmlspecialchars($k['wali_kelas'] ?? '-');
                            $tahunK  = htmlspecialchars($k['tahun_ajaran'] ?? date('Y').'/'.(date('Y')+1));
                            $statusK = $k['status'];
                            $kapK    = $k['kapasitas'] ?? 35;
                        ?>
                        <div class="kelas-card"
                             data-nama="<?= strtolower($k['nama_kelas']) ?>"
                             data-wali="<?= strtolower($k['wali_kelas'] ?? '') ?>"
                             data-tingkat="<?= $tingkat ?>"
                             data-status="<?= $statusK ?>">
                            <div class="kelas-icon" style="background:<?= $warna ?>;"><?= $tingkat ?></div>
                            <div style="flex:1;min-width:0;">
                                <div class="kelas-nama"><?= $namaK ?></div>
                                <div class="kelas-wali"><i class="bi bi-person-fill" style="font-size:10px;margin-right:3px;"></i><?= $waliK ?></div>
                                <span class="kelas-siswa"><i class="bi bi-people-fill" style="font-size:9px;margin-right:3px;"></i><?= $k['jumlah_siswa'] ?> Siswa</span>
                                <?php if ($statusK === 'Kosong'): ?><span class="badge badge-danger" style="margin-left:4px;font-size:9px;">Kosong</span><?php endif; ?>
                            </div>
                            <div class="kelas-actions">
                                <button class="btn-edit btn-sm" onclick="editKelas('<?= $kId ?>', '<?= $namaK ?>', '<?= $waliK ?>', '<?= $tahunK ?>', '<?= $kapK ?>')" title="Edit"><i class="bi bi-pencil-fill"></i></button>
                                <button class="btn-danger btn-sm" onclick="konfirmasiHapus('<?= $kId ?>', '<?= $namaK ?>')" title="Hapus"><i class="bi bi-trash-fill"></i></button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div id="emptyGrid" style="display:none;" class="empty-state">
                        <i class="bi bi-building-x"></i><p>Tidak ada kelas yang cocok dengan pencarian</p>
                    </div>
                </div>

                <!-- LIST VIEW -->
                <div id="viewList" style="display:none;">
                    <table class="admin-table" id="tableKelas">
                        <thead>
                            <tr>
                                <th style="width:50px;">No</th>
                                <th>Nama Kelas</th>
                                <th>Wali Kelas</th>
                                <th>Jumlah Siswa</th>
                                <th>Tahun Ajaran</th>
                                <th>Status</th>
                                <th style="width:130px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($kelasList as $i => $k):
                            $warna   = $warnaList[$i % count($warnaList)];
                            $tingkat = explode(' ', $k['nama_kelas'])[0];
                            $kId     = $k['id'] ?? $i;
                            $namaK   = htmlspecialchars($k['nama_kelas']);
                            $waliK   = htmlspecialchars($k['wali_kelas'] ?? '-');
                            $tahunK  = htmlspecialchars($k['tahun_ajaran'] ?? date('Y').'/'.(date('Y')+1));
                            $kapK    = $k['kapasitas'] ?? 35;
                        ?>
                            <tr class="kelas-row"
                                data-nama="<?= strtolower($k['nama_kelas']) ?>"
                                data-wali="<?= strtolower($k['wali_kelas'] ?? '') ?>"
                                data-tingkat="<?= $tingkat ?>"
                                data-status="<?= $k['status'] ?>">
                                <td><?= $i+1 ?></td>
                                <td>
                                    <div style="display:flex;align-items:center;gap:10px;">
                                        <div style="width:34px;height:34px;border-radius:9px;background:<?= $warna ?>;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:800;color:white;flex-shrink:0;"><?= $tingkat ?></div>
                                        <span style="font-weight:600;"><?= $namaK ?></span>
                                    </div>
                                </td>
                                <td>
                                    <?php if (($k['wali_kelas']??'-') !== '-'): ?>
                                    <div style="display:flex;align-items:center;gap:8px;">
                                        <div class="ava" style="background:<?= $warnaList[($i+3)%count($warnaList)] ?>;"><?= strtoupper(substr($k['wali_kelas']??'',0,2)) ?></div>
                                        <span><?= $waliK ?></span>
                                    </div>
                                    <?php else: ?><span style="color:var(--text2);">— Belum ada —</span><?php endif; ?>
                                </td>
                                <td>
                                    <div style="display:flex;align-items:center;gap:8px;">
                                        <div style="flex:1;max-width:80px;height:5px;background:#e5e7eb;border-radius:99px;overflow:hidden;">
                                            <div style="height:100%;background:<?= $warna ?>;border-radius:99px;width:<?= min(100, ($k['jumlah_siswa']??0)/35*100) ?>%;"></div>
                                        </div>
                                        <span style="font-weight:600;"><?= $k['jumlah_siswa']??0 ?></span>
                                    </div>
                                </td>
                                <td><?= $tahunK ?></td>
                                <td><?= $k['status']==='Aktif' ? '<span class="badge badge-success">Aktif</span>' : '<span class="badge badge-danger">Kosong</span>' ?></td>
                                <td>
                                    <div style="display:flex;gap:6px;">
                                        <button class="btn-edit btn-sm" onclick="editKelas('<?= $kId ?>', '<?= $namaK ?>', '<?= $waliK ?>', '<?= $tahunK ?>', '<?= $kapK ?>')"><i class="bi bi-pencil-fill"></i> Edit</button>
                                        <button class="btn-danger btn-sm" onclick="konfirmasiHapus('<?= $kId ?>', '<?= $namaK ?>')"><i class="bi bi-trash-fill"></i></button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <div id="emptyList" style="display:none;" class="empty-state">
                        <i class="bi bi-building-x"></i><p>Tidak ada kelas yang cocok</p>
                    </div>
                    <div style="padding:12px 20px;border-top:1px solid var(--border);font-size:12px;color:var(--text2);">
                        Menampilkan <?= $totalKelas ?> kelas
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL TAMBAH KELAS -->
<div class="modal-overlay" id="modalTambah">
    <div class="modal-box">
        <div class="modal-header">
            <h4><i class="bi bi-plus-circle-fill" style="color:var(--accent);margin-right:8px;"></i>Tambah Kelas Baru</h4>
            <button class="modal-close" onclick="closeModal('modalTambah')"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label class="form-label">Nama Kelas *</label>
                <input type="text" class="form-control" id="tambahNamaKelas" placeholder="Contoh: X RPL 1">
            </div>
            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">Tingkat *</label>
                    <select class="form-control" id="tambahTingkat">
                        <option value="">Pilih Tingkat</option>
                        <option value="X">X</option>
                        <option value="XI">XI</option>
                        <option value="XII">XII</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Tahun Ajaran</label>
                    <input type="text" class="form-control" id="tambahTahun" value="<?= date('Y').'/'.(date('Y')+1) ?>">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Wali Kelas</label>
                <select class="form-control" id="tambahWaliKelas">
                    <option value="">— Pilih Wali Kelas —</option>
                    <?php foreach ($guruList as $g): ?>
                    <option value="<?= htmlspecialchars($g) ?>"><?= htmlspecialchars($g) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Kapasitas Maksimal</label>
                <input type="number" class="form-control" id="tambahKapasitas" placeholder="35" min="1" max="50" value="35">
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn-secondary" onclick="closeModal('modalTambah')">Batal</button>
            <button class="btn-primary" id="btnSimpanKelas" onclick="simpanKelas()">
                <i class="bi bi-check-lg"></i> Simpan Kelas
            </button>
        </div>
    </div>
</div>

<!-- MODAL EDIT KELAS -->
<div class="modal-overlay" id="modalEdit">
    <div class="modal-box">
        <div class="modal-header">
            <h4><i class="bi bi-pencil-fill" style="color:#7c3aed;margin-right:8px;"></i>Edit Kelas</h4>
            <button class="modal-close" onclick="closeModal('modalEdit')"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="editId">
            <div class="form-group">
                <label class="form-label">Nama Kelas *</label>
                <input type="text" class="form-control" id="editNamaKelas">
            </div>
            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">Tingkat</label>
                    <select class="form-control" id="editTingkat">
                        <option value="X">X</option>
                        <option value="XI">XI</option>
                        <option value="XII">XII</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Tahun Ajaran</label>
                    <input type="text" class="form-control" id="editTahun">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Wali Kelas</label>
                <select class="form-control" id="editWaliKelas">
                    <option value="">— Pilih Wali Kelas —</option>
                    <?php foreach ($guruList as $g): ?>
                    <option value="<?= htmlspecialchars($g) ?>"><?= htmlspecialchars($g) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Kapasitas Maksimal</label>
                <input type="number" class="form-control" id="editKapasitas" min="1" max="50">
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn-secondary" onclick="closeModal('modalEdit')">Batal</button>
            <button class="btn-primary" id="btnUpdateKelas" onclick="updateKelas()">
                <i class="bi bi-check-lg"></i> Simpan Perubahan
            </button>
        </div>
    </div>
</div>

<!-- KONFIRMASI HAPUS -->
<div class="confirm-overlay" id="confirmHapus">
    <div class="confirm-box">
        <div class="confirm-icon-box"><i class="bi bi-trash-fill"></i></div>
        <h5 style="font-size:16px;font-weight:700;color:var(--text);margin-bottom:8px;">Hapus Kelas?</h5>
        <p style="font-size:13px;color:var(--text2);margin-bottom:20px;">
            Kelas <strong id="confirmNamaKelas" style="color:var(--text);"></strong> akan dihapus permanen.
        </p>
        <input type="hidden" id="confirmId">
        <div style="display:flex;gap:10px;justify-content:center;">
            <button class="btn-secondary" onclick="closeConfirm()" style="flex:1;">Batal</button>
            <button class="btn-danger" id="btnKonfirmHapusKelas" onclick="hapusKelas()" style="flex:1;justify-content:center;">
                <i class="bi bi-trash-fill"></i> Ya, Hapus
            </button>
        </div>
    </div>
</div>

<div class="toast-wrap" id="toastWrap"></div>

<script>
// ── SIDEBAR ───────────────────────────────────────────────────
function toggleSidebar() {
    const sb = document.getElementById('adminSidebar');
    const ov = document.getElementById('sidebarOverlay');
    if (!sb) return;
    sb.classList.toggle('open');
    if(ov) ov.style.display = sb.classList.contains('open') ? 'block' : 'none';
}

// ── TOAST ─────────────────────────────────────────────────────
function showToast(msg, type='success') {
    const wrap = document.getElementById('toastWrap');
    const t = document.createElement('div');
    t.className = 'toast ' + type;
    t.style.animation = 'toastIn .3s ease';
    t.innerHTML = `<i class="bi bi-${type==='success'?'check-circle-fill':'exclamation-circle-fill'}"></i> ${msg}`;
    wrap.appendChild(t);
    setTimeout(()=>{ t.style.opacity='0'; t.style.transition='opacity .3s'; }, 3000);
    setTimeout(()=>t.remove(), 3350);
}

// ── MODAL ─────────────────────────────────────────────────────
function openModal(id)  { document.getElementById(id).classList.add('show'); }
function closeModal(id) { document.getElementById(id).classList.remove('show'); }
document.querySelectorAll('.modal-overlay').forEach(o => {
    o.addEventListener('click', e => { if(e.target===o) closeModal(o.id); });
});
document.addEventListener('keydown', e => {
    if (e.key==='Escape') { closeConfirm(); document.querySelectorAll('.modal-overlay.show').forEach(m=>m.classList.remove('show')); }
});

// ── VIEW TOGGLE ───────────────────────────────────────────────
function setView(mode) {
    document.getElementById('viewGrid').style.display = mode==='grid' ? 'block' : 'none';
    document.getElementById('viewList').style.display = mode==='list' ? 'block' : 'none';
    document.getElementById('btnGrid').classList.toggle('active', mode==='grid');
    document.getElementById('btnList').classList.toggle('active', mode==='list');
    localStorage.setItem('kelasView', mode);
}
setView(localStorage.getItem('kelasView') || 'grid');

// ── FILTER ────────────────────────────────────────────────────
function filterKelas() {
    const q       = document.getElementById('searchInput').value.toLowerCase();
    const tingkat = document.getElementById('filterTingkat').value;
    const status  = document.getElementById('filterStatus').value;

    const cards = document.querySelectorAll('#gridContainer .kelas-card');
    let vis = 0;
    cards.forEach(card => {
        const match =
            (!q || card.dataset.nama.includes(q) || card.dataset.wali.includes(q)) &&
            (!tingkat || card.dataset.tingkat === tingkat) &&
            (!status  || card.dataset.status  === status);
        card.style.display = match ? '' : 'none';
        if (match) vis++;
    });
    document.getElementById('emptyGrid').style.display = vis ? 'none' : 'block';

    const rows = document.querySelectorAll('#tableKelas .kelas-row');
    let visL = 0;
    rows.forEach(row => {
        const match =
            (!q || row.dataset.nama.includes(q) || row.dataset.wali.includes(q)) &&
            (!tingkat || row.dataset.tingkat === tingkat) &&
            (!status  || row.dataset.status  === status);
        row.style.display = match ? '' : 'none';
        if (match) visL++;
    });
    document.getElementById('emptyList').style.display = visL ? 'none' : 'block';
    document.getElementById('totalShown').textContent = vis + ' kelas';
}

// ── TAMBAH ────────────────────────────────────────────────────
async function simpanKelas() {
    const nama = document.getElementById('tambahNamaKelas').value.trim();
    if (!nama) { showToast('Nama kelas wajib diisi!', 'error'); return; }

    const btn = document.getElementById('btnSimpanKelas');
    btn.disabled=true; btn.innerHTML='<i class="bi bi-hourglass-split"></i> Menyimpan...';

    try {
        const res  = await fetch('?url=admin/kelas/store', {
            method:'POST', headers:{'Content-Type':'application/json'},
            body: JSON.stringify({
                nama_kelas   : nama,
                tahun_ajaran : document.getElementById('tambahTahun').value,
                wali_kelas   : document.getElementById('tambahWaliKelas').value,
                kapasitas    : document.getElementById('tambahKapasitas').value || 35,
            })
        });
        const json = await res.json();
        if (json.success) {
            closeModal('modalTambah');
            showToast(json.message, 'success');
            setTimeout(()=>location.reload(), 1000);
        } else { showToast(json.message, 'error'); }
    } catch(e) { showToast('Gagal terhubung ke server', 'error'); }

    btn.disabled=false; btn.innerHTML='<i class="bi bi-check-lg"></i> Simpan Kelas';
}

// ── EDIT ──────────────────────────────────────────────────────
function editKelas(id, nama, wali, tahun, kapasitas) {
    document.getElementById('editId').value         = id;
    document.getElementById('editNamaKelas').value  = nama;
    document.getElementById('editTahun').value      = tahun;
    document.getElementById('editKapasitas').value  = kapasitas;
    document.getElementById('editTingkat').value    = nama.split(' ')[0] || 'X';
    const waliOpt = document.getElementById('editWaliKelas');
    for (let opt of waliOpt.options) { if (opt.value===wali) { opt.selected=true; break; } }
    openModal('modalEdit');
}

async function updateKelas() {
    const nama = document.getElementById('editNamaKelas').value.trim();
    if (!nama) { showToast('Nama kelas wajib diisi!', 'error'); return; }

    const btn = document.getElementById('btnUpdateKelas');
    btn.disabled=true; btn.innerHTML='<i class="bi bi-hourglass-split"></i> Menyimpan...';

    try {
        const res  = await fetch('?url=admin/kelas/update', {
            method:'POST', headers:{'Content-Type':'application/json'},
            body: JSON.stringify({
                id           : document.getElementById('editId').value,
                nama_kelas   : nama,
                tahun_ajaran : document.getElementById('editTahun').value,
                wali_kelas   : document.getElementById('editWaliKelas').value,
                kapasitas    : document.getElementById('editKapasitas').value,
            })
        });
        const json = await res.json();
        if (json.success) {
            closeModal('modalEdit');
            showToast(json.message, 'success');
            setTimeout(()=>location.reload(), 1000);
        } else { showToast(json.message, 'error'); }
    } catch(e) { showToast('Gagal terhubung ke server', 'error'); }

    btn.disabled=false; btn.innerHTML='<i class="bi bi-check-lg"></i> Simpan Perubahan';
}

// ── HAPUS ─────────────────────────────────────────────────────
function konfirmasiHapus(id, nama) {
    document.getElementById('confirmId').value = id;
    document.getElementById('confirmNamaKelas').textContent = nama;
    document.getElementById('confirmHapus').classList.add('show');
}
function closeConfirm() { document.getElementById('confirmHapus').classList.remove('show'); }

async function hapusKelas() {
    const id   = document.getElementById('confirmId').value;
    const nama = document.getElementById('confirmNamaKelas').textContent;
    const btn  = document.getElementById('btnKonfirmHapusKelas');
    btn.disabled=true; btn.innerHTML='<i class="bi bi-hourglass-split"></i> Menghapus...';

    try {
        const res  = await fetch('?url=admin/kelas/destroy', {
            method:'POST', headers:{'Content-Type':'application/json'},
            body: JSON.stringify({ id })
        });
        const json = await res.json();
        if (json.success) {
            closeConfirm();
            showToast(json.message, 'success');
            setTimeout(()=>location.reload(), 1000);
        } else { showToast(json.message, 'error'); }
    } catch(e) { showToast('Gagal terhubung ke server', 'error'); }

    btn.disabled=false; btn.innerHTML='<i class="bi bi-trash-fill"></i> Ya, Hapus';
}
</script>
</body>
</html>