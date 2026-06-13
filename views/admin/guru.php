<?php
if (session_status() === PHP_SESSION_NONE) session_start();

// Data dari controller
$guruList  = $guruList  ?? [];
$kelasList = $kelasList ?? [];
$totalGuru = $totalGuru ?? count($guruList);
$aktif     = $aktif     ?? $totalGuru;
$waliKelas = $waliKelas ?? 0;
$nonWali   = $nonWali   ?? 0;

$avatarColors = ['#2563eb','#1d4ed8','#3b82f6','#0ea5e9','#10b981','#f59e0b','#ef4444','#ec4899'];
function guruColor(string $id, array $colors): string {
    $h = 0; foreach (str_split((string)$id) as $c) $h = ($h*31+ord($c))%count($colors); return $colors[$h];
}
function guruInitials(string $nama): string {
    $w = explode(' ', trim($nama));
    return strtoupper(implode('', array_map(fn($x)=>$x[0]??'', array_slice($w,0,2))));
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Guru – Absensi QR Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
    /* ── SEMUA VAR DISAMAKAN DENGAN sidebar_admin.php & dashboard.php ── */
    :root {
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
    *,*::before,*::after { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'Poppins', sans-serif; background: var(--bg); color: var(--text); font-size: 14px; }
    .admin-layout { display: flex; min-height: 100vh; }
    .admin-main { margin-left: var(--sidebar-w); flex: 1; display: flex; flex-direction: column; background: var(--bg); }

    /* TOPBAR */
    .admin-topbar { background: #fff; border-bottom: 1px solid var(--border); padding: 13px 28px; display: flex; align-items: center; justify-content: space-between; position: sticky; top: 0; z-index: 50; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
    .topbar-left { display: flex; align-items: center; gap: 14px; }
    .topbar-toggle { background: none; border: none; font-size: 18px; color: var(--text2); cursor: pointer; padding: 4px; border-radius: 6px; }
    .topbar-toggle:hover { background: var(--bg); }
    .topbar-title h2 { font-size: 16px; font-weight: 700; color: var(--text); margin: 0; }
    .topbar-title p { font-size: 12px; color: var(--text2); margin: 0; }
    .topbar-right { display: flex; align-items: center; gap: 10px; }
    .topbar-icon-btn { width: 36px; height: 36px; border-radius: 9px; border: 1px solid var(--border); background: #fff; display: flex; align-items: center; justify-content: center; cursor: pointer; color: var(--text2); font-size: 15px; transition: all .15s; position: relative; }
    .topbar-icon-btn:hover { background: var(--bg); color: var(--accent); border-color: var(--accent); }
    .notif-badge { position: absolute; top: -4px; right: -4px; width: 16px; height: 16px; background: #ef4444; border-radius: 50%; font-size: 9px; font-weight: 700; color: #fff; display: flex; align-items: center; justify-content: center; border: 2px solid #fff; }
    .topbar-avatar { width: 36px; height: 36px; border-radius: 50%; background: var(--accent); display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 13px; color: #fff; cursor: pointer; }

    /* CONTENT */
    .admin-content { padding: 24px 28px; flex: 1; }
    .page-header { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 22px; flex-wrap: wrap; gap: 12px; }
    .page-header-left h1 { font-size: 22px; font-weight: 800; color: var(--text); margin: 0 0 3px; }
    .page-header-left p { font-size: 13px; color: var(--text2); margin: 0; }
    .breadcrumb-pill { display: inline-flex; align-items: center; gap: 6px; background: rgba(37,99,235,0.08); border: 1px solid rgba(37,99,235,0.2); color: var(--accent); border-radius: 20px; padding: 4px 12px; font-size: 11.5px; font-weight: 600; margin-bottom: 8px; }

    /* STAT CARDS */
    .stat-cards { display: grid; grid-template-columns: repeat(4,1fr); gap: 16px; margin-bottom: 24px; }
    .stat-card { background: #fff; border-radius: 14px; padding: 18px 20px; box-shadow: 0 1px 4px rgba(0,0,0,0.05); display: flex; align-items: center; gap: 15px; transition: transform .2s, box-shadow .2s; }
    .stat-card:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(37,99,235,0.1); }
    .stat-icon { width: 48px; height: 48px; border-radius: 13px; display: flex; align-items: center; justify-content: center; font-size: 21px; flex-shrink: 0; }
    .stat-val { font-size: 26px; font-weight: 800; color: var(--text); line-height: 1.1; }
    .stat-label { font-size: 12px; color: var(--text2); margin-top: 2px; }

    /* TABLE CARD */
    .table-card { background: #fff; border-radius: 16px; box-shadow: 0 1px 4px rgba(0,0,0,0.05); overflow: hidden; }
    .table-card-header { padding: 16px 20px; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; }
    .table-card-header h3 { font-size: 15px; font-weight: 700; color: var(--text); margin: 0; }
    .results-count { font-size: 12px; color: var(--text2); }

    /* FILTER BAR */
    .filter-bar { display: flex; align-items: center; gap: 10px; padding: 13px 20px; border-bottom: 1px solid var(--border); background: #fafafa; flex-wrap: wrap; }
    .search-wrap { position: relative; flex: 1; min-width: 220px; }
    .search-wrap i { position: absolute; left: 11px; top: 50%; transform: translateY(-50%); color: var(--text2); font-size: 13px; }
    .search-input { width: 100%; padding: 8px 12px 8px 33px; border: 1px solid var(--border); border-radius: 9px; font-size: 13px; font-family: inherit; color: var(--text); outline: none; background: #fff; transition: border-color .15s, box-shadow .15s; }
    .search-input:focus { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(37,99,235,0.1); }
    .filter-select { padding: 8px 12px; border: 1px solid var(--border); border-radius: 9px; font-size: 13px; font-family: inherit; color: var(--text); outline: none; background: #fff; cursor: pointer; }
    .filter-select:focus { border-color: var(--accent); }
    .filter-count { font-size: 12px; color: var(--text2); background: var(--bg); padding: 5px 12px; border-radius: 20px; white-space: nowrap; font-weight: 500; }

    /* TABLE */
    .table-responsive { overflow-x: auto; }
    .admin-table { width: 100%; border-collapse: collapse; }
    .admin-table thead th { padding: 11px 16px; font-size: 11px; font-weight: 700; color: var(--text2); text-transform: uppercase; letter-spacing: .7px; background: #f8fafc; border-bottom: 1px solid var(--border); white-space: nowrap; text-align: left; }
    .admin-table tbody td { padding: 12px 16px; font-size: 13.5px; color: #374151; border-bottom: 1px solid #f3f4f6; vertical-align: middle; }
    .admin-table tbody tr:last-child td { border-bottom: none; }
    .admin-table tbody tr:hover td { background: #f8fafc; }

    /* AVATAR & GURU INFO */
    .ava { width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 12px; color: #fff; flex-shrink: 0; }
    .guru-info { display: flex; align-items: center; gap: 10px; }
    .guru-name { font-weight: 600; font-size: 13.5px; color: var(--text); }
    .guru-sub { font-size: 11.5px; color: var(--text2); }

    /* BADGES */
    .badge-pill { display: inline-flex; align-items: center; gap: 5px; padding: 4px 11px; border-radius: 20px; font-size: 11.5px; font-weight: 600; }
    .badge-wali { background: #dbeafe; color: #2563eb; }
    .badge-nonwali { background: #f3f4f6; color: #9ca3af; }

    /* BUTTONS */
    .btn-primary-custom { background: var(--accent); color: #fff; border: none; padding: 9px 18px; border-radius: 9px; font-size: 13px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 7px; transition: all .15s; font-family: inherit; }
    .btn-primary-custom:hover { background: #1d4ed8; box-shadow: 0 4px 12px rgba(37,99,235,.35); }
    .btn-secondary { background: #f3f4f6; color: #374151; border: 1px solid var(--border); padding: 9px 18px; border-radius: 9px; font-size: 13px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 7px; transition: all .15s; font-family: inherit; }
    .btn-secondary:hover { background: #e5e7eb; }
    .btn-danger-solid { background: #dc2626; color: white; border: none; padding: 9px 18px; border-radius: 9px; font-size: 13px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 7px; transition: all .15s; font-family: inherit; }
    .btn-danger-solid:hover { background: #b91c1c; }

    /* ACTION BUTTONS */
    .action-wrap { display: flex; align-items: center; justify-content: flex-end; gap: 5px; }
    .act-btn { width: 30px; height: 30px; border-radius: 8px; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 13px; transition: all .15s; }
    .act-edit { background: #eff6ff; color: #2563eb; }
    .act-edit:hover { background: #bfdbfe; }
    .act-del { background: #fee2e2; color: #dc2626; }
    .act-del:hover { background: #fca5a5; }

    /* PAGINATION */
    .pagination-wrap { display: flex; align-items: center; justify-content: space-between; padding: 14px 20px; border-top: 1px solid var(--border); flex-wrap: wrap; gap: 10px; }
    .pg-info { font-size: 12.5px; color: var(--text2); }
    .pagination { display: flex; align-items: center; gap: 4px; }
    .page-btn { min-width: 32px; height: 32px; padding: 0 8px; border: 1px solid var(--border); background: #fff; border-radius: 7px; font-size: 12px; font-weight: 600; cursor: pointer; font-family: inherit; transition: all .15s; color: var(--text); }
    .page-btn:hover:not(:disabled) { background: var(--bg); border-color: var(--accent); color: var(--accent); }
    .page-btn.active { background: var(--accent); color: #fff; border-color: var(--accent); }
    .page-btn:disabled { opacity: .4; cursor: default; }

    /* EMPTY STATE */
    .empty-state { text-align: center; padding: 60px 20px; color: var(--text2); }
    .empty-state i { font-size: 42px; display: block; margin-bottom: 14px; opacity: .35; }
    .empty-state p { font-size: 14px; margin: 0; }

    /* MODAL */
    .modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.45); z-index: 2000; align-items: center; justify-content: center; padding: 20px; }
    .modal-overlay.show { display: flex; }
    .modal-box { background: #fff; border-radius: 18px; width: 100%; max-width: 520px; max-height: 90vh; overflow-y: auto; box-shadow: 0 25px 60px rgba(0,0,0,.2); animation: modalIn .22s ease; position: relative; }
    @keyframes modalIn { from { transform: scale(.94) translateY(12px); opacity: 0; } to { transform: scale(1) translateY(0); opacity: 1; } }
    .modal-header { padding: 20px 24px 16px; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; }
    .modal-header h4 { font-size: 16px; font-weight: 700; margin: 0; }
    .modal-header p { font-size: 12px; color: var(--text2); margin: 0; }
    .modal-close { width: 30px; height: 30px; border-radius: 8px; border: none; background: var(--bg); cursor: pointer; font-size: 14px; color: var(--text2); display: flex; align-items: center; justify-content: center; transition: all .15s; }
    .modal-close:hover { background: #fee2e2; color: #dc2626; }
    .modal-body { padding: 20px 24px; }
    .modal-footer { padding: 14px 24px 20px; display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid var(--border); }
    .modal-ico { width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 16px; }
    .modal-ico-add { background: #eff6ff; color: #2563eb; }
    .modal-ico-edit { background: #fef3c7; color: #d97706; }
    .modal-ico-del { background: #fee2e2; color: #dc2626; }

    /* FORM */
    .form-group { margin-bottom: 16px; }
    .form-label-custom { display: block; font-size: 11.5px; font-weight: 700; color: var(--text2); margin-bottom: 6px; text-transform: uppercase; letter-spacing: .6px; }
    .form-control-custom { width: 100%; padding: 9px 13px; border: 1px solid var(--border); border-radius: 9px; font-size: 13px; font-family: inherit; color: var(--text); outline: none; transition: border-color .15s, box-shadow .15s; background: #fff; }
    .form-control-custom:focus { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(37,99,235,.1); }
    .optional-tag { display: inline-block; font-size: 10px; font-weight: 600; background: #f3f4f6; color: #9ca3af; border-radius: 4px; padding: 1px 6px; margin-left: 6px; vertical-align: middle; }
    .req { color: #ef4444; }
    .pw-wrap { position: relative; }
    .pw-wrap .form-control-custom { padding-right: 40px; }
    .pw-toggle { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: var(--text2); font-size: 15px; padding: 0; }
    .pw-toggle:hover { color: var(--accent); }

    /* DELETE MODAL */
    .del-icon-wrap { width: 56px; height: 56px; border-radius: 50%; background: #fee2e2; display: flex; align-items: center; justify-content: center; font-size: 26px; color: #dc2626; margin: 0 auto 16px; }
    .del-title { font-size: 18px; font-weight: 700; text-align: center; margin-bottom: 8px; }
    .del-sub { font-size: 13.5px; color: var(--text2); text-align: center; line-height: 1.6; }

    /* TOAST */
    .toast-wrap { position: fixed; bottom: 24px; right: 24px; z-index: 9999; display: flex; flex-direction: column; gap: 8px; }
    .toast { background: #0f1729; color: #fff; padding: 13px 18px; border-radius: 12px; font-size: 13px; font-weight: 500; display: flex; align-items: center; gap: 10px; box-shadow: 0 8px 24px rgba(0,0,0,.2); animation: toastIn .3s ease; max-width: 320px; }
    .toast.success { border-left: 4px solid #22c55e; }
    .toast.error { border-left: 4px solid #ef4444; }
    @keyframes toastIn { from { transform: translateX(120%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }

    /* RESPONSIVE */
    @media(max-width:1024px) { .admin-main { margin-left: 0; } .stat-cards { grid-template-columns: repeat(2,1fr); } }
    @media(max-width:640px) { .stat-cards { grid-template-columns: 1fr 1fr; } .admin-content { padding: 16px; } .admin-topbar { padding: 12px 16px; } }
    </style>
</head>
<body>
<div class="admin-layout">
    <?php include dirname(__DIR__) . '/layouts/sidebar_admin.php'; ?>

    <main class="admin-main">
        <header class="admin-topbar">
            <div class="topbar-left">
                <button class="topbar-toggle" onclick="toggleSidebar()"><i class="bi bi-list"></i></button>
                <div class="topbar-title">
                    <h2>Data Guru</h2>
                    <p>Manajemen akun guru &amp; wali kelas</p>
                </div>
            </div>
            <div class="topbar-right">
                <div class="topbar-icon-btn">
                    <i class="bi bi-bell"></i>
                </div>
                <div class="topbar-avatar">
                    <?php
                        $n = $_SESSION['user']['nama'] ?? 'AD';
                        $parts = explode(' ', $n);
                        echo strtoupper(implode('', array_map(fn($w)=>$w[0], array_slice($parts,0,2))));
                    ?>
                </div>
            </div>
        </header>

        <div class="admin-content">

            <div class="page-header">
                <div class="page-header-left">
                    <div class="breadcrumb-pill">
                        <i class="bi bi-house-fill" style="font-size:10px;"></i>
                        Admin › Data Guru
                    </div>
                    <h1>Data Guru</h1>
                    <p>Kelola akun login guru dan penugasan wali kelas</p>
                </div>
                <button class="btn-primary-custom" onclick="openModal('modalTambah')">
                    <i class="bi bi-plus-lg"></i> Tambah Guru
                </button>
            </div>

            <!-- STAT CARDS -->
            <div class="stat-cards">
                <div class="stat-card">
                    <div class="stat-icon" style="background:#eff6ff;"><i class="bi bi-person-badge-fill" style="color:#2563eb;"></i></div>
                    <div><div class="stat-val" id="statTotal"><?= $totalGuru ?></div><div class="stat-label">Total Guru</div></div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background:#dcfce7;"><i class="bi bi-patch-check-fill" style="color:#16a34a;"></i></div>
                    <div><div class="stat-val"><?= $totalGuru ?></div><div class="stat-label">Guru Aktif</div></div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background:#dbeafe;"><i class="bi bi-house-door-fill" style="color:#2563eb;"></i></div>
                    <div><div class="stat-val"><?= $waliKelas ?></div><div class="stat-label">Wali Kelas</div></div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background:#fef3c7;"><i class="bi bi-person-dash-fill" style="color:#d97706;"></i></div>
                    <div><div class="stat-val"><?= $nonWali ?></div><div class="stat-label">Tanpa Kelas</div></div>
                </div>
            </div>

            <!-- TABLE CARD -->
            <div class="table-card">
                <div class="table-card-header">
                    <h3><i class="bi bi-person-badge-fill" style="color:var(--accent);margin-right:8px;"></i>Daftar Guru</h3>
                    <span class="results-count" id="resultsCount"><?= $totalGuru ?> guru ditemukan</span>
                </div>
                <div class="filter-bar">
                    <input type="text"     style="display:none" aria-hidden="true">
                    <input type="password" style="display:none" aria-hidden="true">
                    <div class="search-wrap" id="searchWrap">
                        <i class="bi bi-search"></i>
                    </div>
                    <select class="filter-select" id="filterKelas" onchange="filterData()">
                        <option value="">Semua Kelas</option>
                        <?php foreach ($kelasList as $k): ?>
                        <option value="<?= htmlspecialchars($k) ?>"><?= htmlspecialchars($k) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button class="btn-secondary" style="padding:8px 14px;font-size:12px;" onclick="resetFilter()">
                        <i class="bi bi-x-circle"></i> Reset
                    </button>
                    <span class="filter-count" id="filterCount"></span>
                </div>

                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th style="width:42px">#</th>
                                <th>Guru</th>
                                <th>NIP</th>
                                <th>Email</th>
                                <th>No. HP</th>
                                <th>Kelas</th>
                                <th style="text-align:right;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="guruTbody"></tbody>
                    </table>
                    <div class="empty-state" id="emptyState" style="display:none;">
                        <i class="bi bi-person-x"></i>
                        <p>Tidak ada guru yang sesuai pencarian.</p>
                    </div>
                </div>

                <div class="pagination-wrap">
                    <span class="pg-info" id="pgInfo">Menampilkan <?= $totalGuru ?> guru</span>
                    <div class="pagination" id="pagination"></div>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- ===================== MODAL TAMBAH ===================== -->
<div class="modal-overlay" id="modalTambah">
    <div class="modal-box">
        <div class="modal-header">
            <div style="display:flex;align-items:center;">
                <div class="modal-ico modal-ico-add" style="margin-right:12px;"><i class="bi bi-person-plus-fill"></i></div>
                <div><h4>Tambah Guru</h4><p>Buat akun login untuk guru baru</p></div>
            </div>
            <button class="modal-close" onclick="closeModal('modalTambah')"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label class="form-label-custom">Nama Lengkap <span class="req">*</span></label>
                <input type="text" class="form-control-custom" id="addNama" placeholder="cth. Budi Santoso" autocomplete="off">
            </div>
            <div class="form-group">
                <label class="form-label-custom">NIP</label>
                <input type="text" class="form-control-custom" id="addNip" placeholder="Nomor Induk Pegawai" autocomplete="off">
            </div>
            <div class="form-group">
                <label class="form-label-custom">Email</label>
                <input type="text" class="form-control-custom" id="addEmail" placeholder="email@sekolah.sch.id" autocomplete="new-password">
            </div>
            <div class="form-group">
                <label class="form-label-custom">No. HP</label>
                <input type="text" class="form-control-custom" id="addHp" placeholder="08xx-xxxx-xxxx" autocomplete="off">
            </div>
            <div class="form-group">
                <label class="form-label-custom">Kelas <span class="optional-tag">Opsional</span></label>
                <select class="form-control-custom" id="addKelas">
                    <option value="">– Tidak sebagai wali kelas –</option>
                    <?php foreach ($kelasList as $k): ?>
                    <option value="<?= htmlspecialchars($k) ?>"><?= htmlspecialchars($k) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label-custom">Password <span class="req">*</span></label>
                <div class="pw-wrap">
                    <input type="password" class="form-control-custom" id="addPassword" placeholder="Minimal 8 karakter" autocomplete="new-password">
                    <button type="button" class="pw-toggle" onclick="togglePw('addPassword',this)"><i class="bi bi-eye"></i></button>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn-secondary" onclick="closeModal('modalTambah')">Batal</button>
            <button class="btn-primary-custom" id="btnSimpanGuru" onclick="submitTambah()">
                <i class="bi bi-plus-lg"></i> Simpan Guru
            </button>
        </div>
    </div>
</div>

<!-- ===================== MODAL EDIT ===================== -->
<div class="modal-overlay" id="modalEdit">
    <div class="modal-box">
        <div class="modal-header">
            <div style="display:flex;align-items:center;">
                <div class="modal-ico modal-ico-edit" style="margin-right:12px;"><i class="bi bi-pencil-fill"></i></div>
                <div><h4>Edit Guru</h4><p id="editSubtitle">Perbarui data guru</p></div>
            </div>
            <button class="modal-close" onclick="closeModal('modalEdit')"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="editId">
            <div class="form-group">
                <label class="form-label-custom">Nama Lengkap <span class="req">*</span></label>
                <input type="text" class="form-control-custom" id="editNama" autocomplete="off">
            </div>
            <div class="form-group">
                <label class="form-label-custom">NIP</label>
                <input type="text" class="form-control-custom" id="editNip" autocomplete="off">
            </div>
            <div class="form-group">
                <label class="form-label-custom">Email</label>
                <input type="text" class="form-control-custom" id="editEmail" autocomplete="new-password">
            </div>
            <div class="form-group">
                <label class="form-label-custom">No. HP</label>
                <input type="text" class="form-control-custom" id="editHp" autocomplete="off">
            </div>
            <div class="form-group">
                <label class="form-label-custom">Kelas <span class="optional-tag">Opsional</span></label>
                <select class="form-control-custom" id="editKelas">
                    <option value="">– Tidak sebagai wali kelas –</option>
                    <?php foreach ($kelasList as $k): ?>
                    <option value="<?= htmlspecialchars($k) ?>"><?= htmlspecialchars($k) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label-custom">Password Baru <span class="optional-tag">Opsional</span></label>
                <div class="pw-wrap">
                    <input type="password" class="form-control-custom" id="editPassword" placeholder="Kosongkan jika tidak diubah" autocomplete="new-password">
                    <button type="button" class="pw-toggle" onclick="togglePw('editPassword',this)"><i class="bi bi-eye"></i></button>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn-secondary" onclick="closeModal('modalEdit')">Batal</button>
            <button class="btn-primary-custom" id="btnUpdateGuru" onclick="submitEdit()">
                <i class="bi bi-check-lg"></i> Simpan Perubahan
            </button>
        </div>
    </div>
</div>

<!-- ===================== MODAL HAPUS ===================== -->
<div class="modal-overlay" id="modalHapus">
    <div class="modal-box" style="max-width:420px;">
        <div class="modal-header">
            <div style="display:flex;align-items:center;">
                <div class="modal-ico modal-ico-del" style="margin-right:12px;"><i class="bi bi-trash3-fill"></i></div>
                <h4>Hapus Guru</h4>
            </div>
            <button class="modal-close" onclick="closeModal('modalHapus')"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="modal-body" style="text-align:center;padding:28px 24px;">
            <div class="del-icon-wrap"><i class="bi bi-exclamation-triangle-fill"></i></div>
            <div class="del-title">Yakin ingin menghapus?</div>
            <div class="del-sub">Akun guru <strong id="hapusNamaGuru"></strong> akan dihapus secara permanen.</div>
        </div>
        <div class="modal-footer" style="justify-content:center;gap:12px;">
            <button class="btn-secondary" onclick="closeModal('modalHapus')"><i class="bi bi-x-lg"></i> Batal</button>
            <button class="btn-danger-solid" id="btnKonfirmHapusGuru" onclick="konfirmHapusNow()">
                <i class="bi bi-trash3"></i> Ya, Hapus
            </button>
        </div>
    </div>
</div>

<div class="toast-wrap" id="toastWrap"></div>

<script>
let dataGuru = <?= json_encode(array_values(array_filter(array_map(fn($g) => [
    'id'    => $g['id']         ?? '',
    'nama'  => $g['nama']       ?? '-',
    'nip'   => (string)($g['nip']   ?? ''),
    'email' => $g['email']      ?? '-',
    'no_hp' => $g['no_hp']      ?? '-',
    'kelas' => $g['wali_kelas'] ?? '',
], $guruList), fn($g) => !empty($g['id'])))) ?>;

let currentPage  = 1;
const perPage    = 10;
let filteredData = [...dataGuru];

const avatarColors = ['#2563eb','#1d4ed8','#3b82f6','#0ea5e9','#10b981','#f59e0b','#ef4444','#ec4899'];

function getColor(id) {
    let h = 0;
    for (let c of String(id)) h = (h * 31 + c.charCodeAt(0)) % avatarColors.length;
    return avatarColors[h];
}
function getInisial(nama) {
    return nama.split(' ').slice(0,2).map(w => w[0] || '').join('').toUpperCase();
}

function renderTable() {
    const start = (currentPage - 1) * perPage;
    const paged = filteredData.slice(start, start + perPage);
    const tbody = document.getElementById('guruTbody');
    const empty = document.getElementById('emptyState');

    if (paged.length === 0) {
        tbody.innerHTML = '';
        empty.style.display = 'block';
        document.getElementById('pgInfo').textContent = '';
        document.getElementById('pagination').innerHTML = '';
        document.getElementById('resultsCount').textContent = '0 guru ditemukan';
        document.getElementById('filterCount').textContent = '0 hasil';
        return;
    }

    empty.style.display = 'none';
    tbody.innerHTML = paged.map((g, i) => `
        <tr>
            <td style="color:var(--text2);font-size:12px;">${start + i + 1}</td>
            <td>
                <div class="guru-info">
                    <div class="ava" style="background:${getColor(g.id)}">${getInisial(g.nama)}</div>
                    <div>
                        <div class="guru-name">${escHtml(g.nama)}</div>
                        <div class="guru-sub">${escHtml(g.nip) || '-'}</div>
                    </div>
                </div>
            </td>
            <td style="font-size:12.5px;font-family:monospace;color:var(--text2);">${escHtml(g.nip) || '-'}</td>
            <td style="font-size:13px;">${escHtml(g.email)}</td>
            <td style="font-size:13px;">${escHtml(g.no_hp)}</td>
            <td>
                ${g.kelas
                    ? `<span class="badge-pill badge-wali"><i class="bi bi-house-door" style="font-size:11px;"></i> ${escHtml(g.kelas)}</span>`
                    : `<span class="badge-pill badge-nonwali"><i class="bi bi-dash-circle" style="font-size:11px;"></i> –</span>`
                }
            </td>
            <td>
                <div class="action-wrap">
                    <button class="act-btn act-edit" onclick="openEdit('${g.id}')" title="Edit">
                        <i class="bi bi-pencil-fill"></i>
                    </button>
                    <button class="act-btn act-del" data-id="${g.id}" onclick="openHapusBtn(this)" title="Hapus">
                        <i class="bi bi-trash3"></i>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');

    renderPagination();
    const total = filteredData.length;
    document.getElementById('pgInfo').textContent = `Menampilkan ${start + 1}–${Math.min(start + perPage, total)} dari ${total} guru`;
    document.getElementById('resultsCount').textContent = `${total} guru ditemukan`;
    document.getElementById('filterCount').textContent  = `${total} hasil`;
}

function renderPagination() {
    const total = Math.ceil(filteredData.length / perPage);
    const pg    = document.getElementById('pagination');
    if (total <= 1) { pg.innerHTML = ''; return; }
    let html = `<button class="page-btn" onclick="gotoPage(${currentPage - 1})" ${currentPage === 1 ? 'disabled' : ''}>‹</button>`;
    for (let i = 1; i <= total; i++) {
        if (total > 7 && i > 2 && i < total - 1 && Math.abs(i - currentPage) > 1) {
            if (i === 3 || i === total - 2) html += `<span style="padding:0 4px;color:var(--text2)">…</span>`;
            continue;
        }
        html += `<button class="page-btn ${i === currentPage ? 'active' : ''}" onclick="gotoPage(${i})">${i}</button>`;
    }
    html += `<button class="page-btn" onclick="gotoPage(${currentPage + 1})" ${currentPage === total ? 'disabled' : ''}>›</button>`;
    pg.innerHTML = html;
}

function gotoPage(p) {
    const total = Math.ceil(filteredData.length / perPage);
    if (p < 1 || p > total) return;
    currentPage = p; renderTable();
}

function filterData() {
    const searchEl = document.getElementById('searchInput');
    const q  = searchEl ? searchEl.value.toLowerCase().trim() : '';
    const kl = document.getElementById('filterKelas').value;
    filteredData = dataGuru.filter(g =>
        (!q  || g.nama.toLowerCase().includes(q) || (g.nip || '').toLowerCase().includes(q)) &&
        (!kl || g.kelas === kl)
    );
    currentPage = 1; renderTable();
}

function resetFilter() {
    const searchEl = document.getElementById('searchInput');
    if (searchEl) searchEl.value = '';
    document.getElementById('filterKelas').value = '';
    filterData();
}

function escHtml(str) {
    if (!str) return '';
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function showToast(msg, type = 'success') {
    const wrap = document.getElementById('toastWrap');
    const t = document.createElement('div');
    t.className = 'toast ' + type;
    t.innerHTML = `<i class="bi bi-${type === 'success' ? 'check-circle-fill' : 'exclamation-circle-fill'}"></i> ${msg}`;
    wrap.appendChild(t);
    setTimeout(() => { t.style.opacity = '0'; t.style.transition = 'opacity .3s'; }, 3000);
    setTimeout(() => t.remove(), 3350);
}

function openModal(id) {
    document.getElementById(id).classList.add('show');
    if (id === 'modalTambah') {
        ['addNama','addNip','addEmail','addHp','addPassword'].forEach(fid => {
            const el = document.getElementById(fid);
            if (el) el.value = '';
        });
        document.getElementById('addKelas').value = '';
    }
}
function closeModal(id) {
    document.getElementById(id).classList.remove('show');
}
document.querySelectorAll('.modal-overlay').forEach(o => {
    o.addEventListener('click', e => { if (e.target === o) closeModal(o.id); });
});
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') document.querySelectorAll('.modal-overlay.show').forEach(m => m.classList.remove('show'));
});

function togglePw(id, btn) {
    const inp = document.getElementById(id);
    inp.type = inp.type === 'text' ? 'password' : 'text';
    btn.querySelector('i').className = inp.type === 'text' ? 'bi bi-eye-slash' : 'bi bi-eye';
}

async function submitTambah() {
    const nama     = document.getElementById('addNama').value.trim();
    const password = document.getElementById('addPassword').value;
    if (!nama) { showToast('Nama wajib diisi!', 'error'); return; }
    if (!password || password.length < 8) { showToast('Password minimal 8 karakter!', 'error'); return; }

    const btn = document.getElementById('btnSimpanGuru');
    btn.disabled = true; btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Menyimpan...';

    try {
        const res  = await fetch('?url=admin/guru/store', {
            method: 'POST', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                nama,
                nip      : document.getElementById('addNip').value.trim(),
                email    : document.getElementById('addEmail').value.trim(),
                no_hp    : document.getElementById('addHp').value.trim(),
                kelas    : document.getElementById('addKelas').value,
                password,
            })
        });
        const json = await res.json();
        if (json.success) {
            closeModal('modalTambah');
            showToast(json.message, 'success');
            setTimeout(() => location.reload(), 1000);
        } else { showToast(json.message, 'error'); }
    } catch(e) { showToast('Gagal terhubung ke server', 'error'); }

    btn.disabled = false; btn.innerHTML = '<i class="bi bi-plus-lg"></i> Simpan Guru';
}

function openEdit(id) {
    const g = dataGuru.find(x => String(x.id) === String(id));
    if (!g) { showToast('Data tidak ditemukan', 'error'); return; }
    document.getElementById('editId').value       = g.id;
    document.getElementById('editNama').value     = g.nama;
    document.getElementById('editNip').value      = g.nip   || '';
    document.getElementById('editEmail').value    = g.email || '';
    document.getElementById('editHp').value       = g.no_hp || '';
    document.getElementById('editKelas').value    = g.kelas || '';
    document.getElementById('editPassword').value = '';
    document.getElementById('editSubtitle').textContent = `Mengedit: ${g.nama}`;
    openModal('modalEdit');
}

async function submitEdit() {
    const id   = document.getElementById('editId').value;
    const nama = document.getElementById('editNama').value.trim();
    const pw   = document.getElementById('editPassword').value;
    if (!nama) { showToast('Nama wajib diisi!', 'error'); return; }
    if (pw && pw.length < 8) { showToast('Password baru minimal 8 karakter!', 'error'); return; }

    const btn = document.getElementById('btnUpdateGuru');
    btn.disabled = true; btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Menyimpan...';

    try {
        const payload = {
            id, nama,
            nip      : document.getElementById('editNip').value.trim(),
            email    : document.getElementById('editEmail').value.trim(),
            no_hp    : document.getElementById('editHp').value.trim(),
            kelas    : document.getElementById('editKelas').value,
        };
        if (pw) payload.password = pw;

        const res  = await fetch('?url=admin/guru/update', {
            method: 'POST', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const json = await res.json();
        if (json.success) {
            closeModal('modalEdit');
            showToast(json.message, 'success');
            setTimeout(() => location.reload(), 1000);
        } else { showToast(json.message, 'error'); }
    } catch(e) { showToast('Gagal terhubung ke server', 'error'); }

    btn.disabled = false; btn.innerHTML = '<i class="bi bi-check-lg"></i> Simpan Perubahan';
}

function openHapusBtn(btn) {
    const actualBtn = btn.closest('[data-id]');
    const idGuru    = actualBtn ? actualBtn.getAttribute('data-id') : null;
    const g         = dataGuru.find(x => String(x.id) === String(idGuru));

    if (!g || !idGuru) { showToast('Data tidak ditemukan', 'error'); return; }

    document.getElementById('hapusNamaGuru').textContent             = g.nama;
    document.getElementById('btnKonfirmHapusGuru').dataset.hapusId   = String(idGuru);
    openModal('modalHapus');
}

async function konfirmHapusNow() {
    const btn        = document.getElementById('btnKonfirmHapusGuru');
    const hapusIdnya = btn.dataset.hapusId;

    if (!hapusIdnya) { showToast('ID tidak valid', 'error'); return; }

    btn.disabled  = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Menghapus...';

    try {
        const res  = await fetch('?url=admin/guru/destroy', {
            method: 'POST', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: hapusIdnya })
        });
        const json = await res.json();

        if (json.success) {
            dataGuru     = dataGuru.filter(x => String(x.id) !== hapusIdnya);
            filteredData = filteredData.filter(x => String(x.id) !== hapusIdnya);
            closeModal('modalHapus');
            renderTable();
            showToast(json.message, 'success');
        } else {
            showToast(json.message, 'error');
        }
    } catch(e) {
        showToast('Gagal terhubung ke server', 'error');
    }

    btn.dataset.hapusId = '';
    btn.disabled        = false;
    btn.innerHTML       = '<i class="bi bi-trash3"></i> Ya, Hapus';
}

// INIT
filteredData = [...dataGuru];
renderTable();

window.addEventListener('load', function() {
    setTimeout(function() {
        const wrap = document.getElementById('searchWrap');
        wrap.querySelectorAll('input').forEach(el => el.remove());
        const inp = document.createElement('input');
        inp.type        = 'text';
        inp.className   = 'search-input';
        inp.id          = 'searchInput';
        inp.placeholder = 'Cari nama atau NIP…';
        inp.setAttribute('autocomplete', 'new-password');
        inp.value       = '';
        inp.addEventListener('input', filterData);
        wrap.appendChild(inp);
    }, 300);
});
</script>
</body>
</html>