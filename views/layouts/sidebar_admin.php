<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$currentUrl = trim($_GET['url'] ?? 'admin/dashboard', '/');

function isActiveAdmin(string $route): string {
    global $currentUrl;
    return ($currentUrl === $route || str_starts_with($currentUrl, $route . '/'))
        ? 'active'
        : '';
}

$adminNama = $_SESSION['admin']['nama'] ?? 'Administrator';
$adminRole = $_SESSION['admin']['role'] ?? 'Admin';
$adminSekolah = htmlspecialchars($_SESSION['admin']['nama_sekolah'] ?? 'Man 2 Banyumas');
$inisial = strtoupper(implode('', array_map(fn($w) => $w[0], array_slice(explode(' ', $adminNama), 0, 2))));
?>
<aside class="admin-sidebar" id="adminSidebar">
    <div class="sidebar-header">
        <div class="sidebar-logo">
            <div class="logo-icon">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                    <rect x="2" y="2" width="6" height="6" rx="1.5" fill="white"/>
                    <rect x="12" y="2" width="6" height="6" rx="1.5" fill="white" opacity=".7"/>
                    <rect x="2" y="12" width="6" height="6" rx="1.5" fill="white" opacity=".7"/>
                    <rect x="12" y="12" width="2.5" height="2.5" rx=".5" fill="white"/>
                    <rect x="15.5" y="12" width="2.5" height="2.5" rx=".5" fill="white"/>
                    <rect x="12" y="15.5" width="2.5" height="2.5" rx=".5" fill="white"/>
                </svg>
            </div>
            <div class="logo-text">
                <span class="logo-title">ABSENSI QR</span>
                <span class="logo-sub"><?= $adminSekolah ?></span>
            </div>
        </div>
        <button class="sidebar-close" onclick="toggleSidebar()">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>

    <div class="sidebar-user">
        <div class="su-avatar"><?= $inisial ?></div>
        <div class="su-info">
            <span class="su-name"><?= htmlspecialchars($adminNama) ?></span>
            <span class="su-role"><?= $adminRole ?></span>
        </div>
        <div class="su-status"></div>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-group-label">Utama</div>
        <a href="?url=admin/dashboard" class="sn-item <?= isActiveAdmin('admin/dashboard') ?>">
            <i class="bi bi-grid-1x2-fill"></i>
            <span>Dashboard</span>
        </a>
        <a href="?url=admin/siswa" class="sn-item <?= isActiveAdmin('admin/siswa') ?>">
            <i class="bi bi-people-fill"></i>
            <span>Data Siswa</span>
        </a>
        <a href="?url=admin/guru" class="sn-item <?= isActiveAdmin('admin/guru') ?>">
            <i class="bi bi-person-badge-fill"></i>
            <span>Data Guru</span>
        </a>
        <a href="?url=admin/kelas" class="sn-item <?= isActiveAdmin('admin/kelas') ?>">
            <i class="bi bi-building"></i>
            <span>Data Kelas</span>
        </a>

        <div class="nav-group-label" style="margin-top:8px;">Laporan</div>
        <a href="?url=admin/laporan" class="sn-item <?= isActiveAdmin('admin/laporan') ?>">
            <i class="bi bi-file-earmark-bar-graph-fill"></i>
            <span>Laporan Kehadiran</span>
        </a>
        <a href="?url=admin/keluhan" class="sn-item <?= isActiveAdmin('admin/keluhan') ?>">
            <i class="bi bi-chat-left-text-fill"></i>
            <span>Keluhan & Laporan</span>
        </a>

        <div class="nav-group-label" style="margin-top:8px;">Sistem</div>
        <a href="?url=admin/pengaturan" class="sn-item <?= isActiveAdmin('admin/pengaturan') ?>">
            <i class="bi bi-gear-fill"></i>
            <span>Pengaturan</span>
        </a>
        <a href="?url=auth/logout" class="sn-item sn-logout">
            <i class="bi bi-box-arrow-left"></i>
            <span>Logout</span>
        </a>
    </nav>
</aside>

<style>
:root {
    /* ── WARNA DISAMAKAN DENGAN SIDEBAR GURU ── */
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

/* SIDEBAR */
.admin-sidebar {
    width: var(--sidebar-w);
    background: var(--sb);
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    flex-shrink: 0;
    position: fixed;
    top: 0; left: 0; bottom: 0;
    z-index: 100;
    transition: transform 0.3s ease;
}
.admin-sidebar.collapsed { transform: translateX(-100%); }
.sidebar-header {
    padding: 18px 20px 14px;
    border-bottom: 1px solid rgba(255,255,255,0.07);
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.sidebar-logo { display: flex; align-items: center; gap: 10px; }
.logo-icon {
    width: 38px; height: 38px;
    background: var(--accent);
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.logo-title { display: block; font-size: 13px; font-weight: 700; color: white; line-height: 1.2; }
.logo-sub { display: block; font-size: 10px; color: rgba(255,255,255,0.45); }
.sidebar-close { display: none; background: none; border: none; color: rgba(255,255,255,0.5); cursor: pointer; font-size: 16px; }

.sidebar-user {
    margin: 14px 12px;
    background: rgba(255,255,255,0.06);
    border-radius: 12px;
    padding: 11px 13px;
    display: flex;
    align-items: center;
    gap: 10px;
}
.su-avatar {
    width: 38px; height: 38px;
    border-radius: 50%;
    background: var(--accent);
    display: flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: 13px; color: white; flex-shrink: 0;
}
.su-name { display: block; font-size: 13px; font-weight: 600; color: white; line-height: 1.3; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 120px; }
.su-role { display: block; font-size: 10px; color: rgba(255,255,255,0.45); }
.su-status { width: 8px; height: 8px; border-radius: 50%; background: #22c55e; margin-left: auto; flex-shrink: 0; box-shadow: 0 0 0 2px rgba(34,197,94,0.3); }

.sidebar-nav { padding: 8px 10px; flex: 1; overflow-y: auto; }
.nav-group-label { font-size: 9.5px; font-weight: 700; letter-spacing: 1.2px; color: rgba(255,255,255,0.3); text-transform: uppercase; padding: 10px 10px 5px; }
.sn-item {
    display: flex;
    align-items: center;
    gap: 11px;
    padding: 10px 13px;
    border-radius: 10px;
    color: var(--sb-text);
    font-size: 13.5px;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.18s;
    margin-bottom: 2px;
}
.sn-item i { font-size: 15px; width: 18px; text-align: center; flex-shrink: 0; }
.sn-item:hover { background: var(--sb-hover); color: white; }
.sn-item.active { background: var(--accent); color: white; box-shadow: 0 4px 14px rgba(37,99,235,0.35); }
.sn-logout { color: rgba(239,68,68,0.7); margin-top: 4px; }
.sn-logout:hover { background: rgba(239,68,68,0.12); color: #ef4444; }

/* MAIN */
.admin-main {
    margin-left: var(--sidebar-w);
    flex: 1;
    display: flex;
    flex-direction: column;
    min-height: 100vh;
    background: var(--bg);
}
.admin-topbar {
    background: white;
    border-bottom: 1px solid var(--border);
    padding: 13px 28px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    position: sticky;
    top: 0;
    z-index: 50;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}
.topbar-left { display: flex; align-items: center; gap: 14px; }
.topbar-toggle { background: none; border: none; font-size: 18px; color: var(--text2); cursor: pointer; padding: 4px; border-radius: 6px; }
.topbar-toggle:hover { background: var(--bg); }
.topbar-title h2 { font-size: 16px; font-weight: 700; color: var(--text); }
.topbar-title p  { font-size: 12px; color: var(--text2); }
.topbar-right { display: flex; align-items: center; gap: 12px; }
.topbar-icon-btn {
    width: 36px; height: 36px;
    border-radius: 9px;
    border: 1px solid var(--border);
    background: white;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer;
    color: var(--text2);
    font-size: 15px;
    transition: all 0.15s;
    position: relative;
}
.topbar-icon-btn:hover { background: var(--bg); color: var(--accent); border-color: var(--accent); }
.notif-badge {
    position: absolute; top: -4px; right: -4px;
    width: 16px; height: 16px;
    background: #ef4444;
    border-radius: 50%;
    font-size: 9px; font-weight: 700; color: white;
    display: flex; align-items: center; justify-content: center;
    border: 2px solid white;
}
.topbar-avatar {
    width: 36px; height: 36px;
    border-radius: 50%;
    background: var(--accent);
    display: flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: 13px; color: white;
    cursor: pointer;
}

/* CONTENT */
.admin-content { padding: 24px 28px; flex: 1; }

/* CARDS */
.stat-cards { display: grid; grid-template-columns: repeat(4, 1fr); gap: 18px; margin-bottom: 24px; }
.stat-card {
    background: white;
    border-radius: 14px;
    padding: 20px 22px;
    box-shadow: 0 1px 4px rgba(0,0,0,0.05);
    display: flex;
    align-items: center;
    gap: 16px;
    transition: transform 0.2s, box-shadow 0.2s;
}
.stat-card:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(37,99,235,0.1); }
.stat-icon {
    width: 50px; height: 50px;
    border-radius: 13px;
    display: flex; align-items: center; justify-content: center;
    font-size: 22px;
    flex-shrink: 0;
}
.stat-info .stat-val { font-size: 28px; font-weight: 800; color: var(--text); line-height: 1.1; }
.stat-info .stat-label { font-size: 12px; color: var(--text2); margin-top: 2px; }
.stat-info .stat-change { font-size: 11px; font-weight: 600; margin-top: 4px; }
.change-up { color: #22c55e; }
.change-down { color: #ef4444; }

/* TABLE CARD */
.table-card {
    background: white;
    border-radius: 14px;
    box-shadow: 0 1px 4px rgba(0,0,0,0.05);
    overflow: hidden;
}
.table-card-header {
    padding: 18px 22px;
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.table-card-header h3 { font-size: 15px; font-weight: 700; color: var(--text); }
.table-card-body { padding: 0; }
table.admin-table { width: 100%; border-collapse: collapse; }
table.admin-table thead th {
    padding: 11px 16px;
    font-size: 11px;
    font-weight: 700;
    color: var(--text2);
    text-transform: uppercase;
    letter-spacing: 0.6px;
    background: #f8fafc;
    border-bottom: 1px solid var(--border);
    text-align: left;
}
table.admin-table tbody td {
    padding: 12px 16px;
    font-size: 13.5px;
    color: #374151;
    border-bottom: 1px solid #f9fafb;
}
table.admin-table tbody tr:last-child td { border-bottom: none; }
table.admin-table tbody tr:hover td { background: #f8fafc; }

/* BADGE */
.badge { display: inline-flex; align-items: center; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; }
.badge-success { background: #dcfce7; color: #16a34a; }
.badge-warning { background: #fef3c7; color: #d97706; }
.badge-danger  { background: #fee2e2; color: #dc2626; }
.badge-info    { background: #dbeafe; color: #2563eb; }
.badge-purple  { background: #ede9fe; color: #7c3aed; }

/* BUTTONS */
.btn-primary {
    background: var(--accent);
    color: white;
    border: none;
    padding: 9px 18px;
    border-radius: 9px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 7px;
    transition: all 0.15s;
    font-family: 'Poppins', sans-serif;
}
.btn-primary:hover { background: #1d4ed8; box-shadow: 0 4px 12px rgba(37,99,235,0.35); }
.btn-secondary {
    background: white;
    color: var(--text);
    border: 1px solid var(--border);
    padding: 9px 18px;
    border-radius: 9px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 7px;
    transition: all 0.15s;
    font-family: 'Poppins', sans-serif;
}
.btn-secondary:hover { background: var(--bg); border-color: var(--accent); color: var(--accent); }
.btn-danger {
    background: #fee2e2; color: #dc2626; border: none;
    padding: 7px 13px; border-radius: 8px; font-size: 12px; font-weight: 600;
    cursor: pointer; transition: all 0.15s; font-family: 'Poppins', sans-serif;
}
.btn-danger:hover { background: #fca5a5; }
.btn-edit {
    background: #eff6ff; color: #2563eb; border: none;
    padding: 7px 13px; border-radius: 8px; font-size: 12px; font-weight: 600;
    cursor: pointer; transition: all 0.15s; font-family: 'Poppins', sans-serif;
}
.btn-edit:hover { background: #bfdbfe; }
.btn-sm { padding: 5px 11px; font-size: 12px; border-radius: 7px; }

/* SEARCH/FILTER BAR */
.filter-bar {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 14px 20px;
    border-bottom: 1px solid var(--border);
    background: white;
    flex-wrap: wrap;
}
.search-wrap { position: relative; flex: 1; min-width: 200px; }
.search-wrap i { position: absolute; left: 11px; top: 50%; transform: translateY(-50%); color: var(--text2); font-size: 14px; }
.search-input {
    width: 100%;
    padding: 8px 12px 8px 34px;
    border: 1px solid var(--border);
    border-radius: 9px;
    font-size: 13px;
    font-family: 'Poppins', sans-serif;
    color: var(--text);
    outline: none;
    transition: border-color 0.15s;
}
.search-input:focus { border-color: var(--accent); }
.filter-select {
    padding: 8px 12px;
    border: 1px solid var(--border);
    border-radius: 9px;
    font-size: 13px;
    font-family: 'Poppins', sans-serif;
    color: var(--text);
    outline: none;
    background: white;
    cursor: pointer;
}
.filter-select:focus { border-color: var(--accent); }

/* MODAL */
.modal-overlay {
    display: none;
    position: fixed; inset: 0;
    background: rgba(0,0,0,0.5);
    z-index: 1000;
    align-items: center;
    justify-content: center;
    padding: 20px;
}
.modal-overlay.show { display: flex; }
.modal-box {
    background: white;
    border-radius: 18px;
    width: 100%;
    max-width: 560px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 25px 60px rgba(0,0,0,0.2);
    animation: modalIn 0.22s ease;
}
.modal-box.modal-lg { max-width: 720px; }
@keyframes modalIn { from { transform: scale(0.94) translateY(10px); opacity: 0; } to { transform: scale(1) translateY(0); opacity: 1; } }
.modal-header {
    padding: 20px 24px 16px;
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.modal-header h4 { font-size: 16px; font-weight: 700; color: var(--text); }
.modal-close {
    width: 30px; height: 30px;
    border-radius: 8px;
    border: none;
    background: var(--bg);
    cursor: pointer;
    font-size: 14px;
    color: var(--text2);
    display: flex; align-items: center; justify-content: center;
    transition: all 0.15s;
}
.modal-close:hover { background: #fee2e2; color: #dc2626; }
.modal-body { padding: 20px 24px; }
.modal-footer {
    padding: 14px 24px 20px;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    border-top: 1px solid var(--border);
}

/* FORM */
.form-group { margin-bottom: 16px; }
.form-label { display: block; font-size: 12px; font-weight: 600; color: var(--text2); margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.5px; }
.form-control {
    width: 100%;
    padding: 9px 13px;
    border: 1px solid var(--border);
    border-radius: 9px;
    font-size: 13px;
    font-family: 'Poppins', sans-serif;
    color: var(--text);
    outline: none;
    transition: border-color 0.15s, box-shadow 0.15s;
    background: white;
}
.form-control:focus { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(37,99,235,0.1); }
.form-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }

/* AVATAR */
.ava {
    width: 34px; height: 34px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: 12px; color: white; flex-shrink: 0;
}

/* PAGINATION */
.pagination { display: flex; align-items: center; gap: 4px; padding: 14px 20px; justify-content: flex-end; border-top: 1px solid var(--border); }
.page-btn {
    width: 32px; height: 32px;
    border-radius: 8px;
    border: 1px solid var(--border);
    background: white;
    font-size: 13px;
    cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    color: var(--text);
    transition: all 0.15s;
    font-family: 'Poppins', sans-serif;
    font-weight: 500;
}
.page-btn:hover { border-color: var(--accent); color: var(--accent); }
.page-btn.active { background: var(--accent); color: white; border-color: var(--accent); }

/* EMPTY STATE */
.empty-state { text-align: center; padding: 50px 20px; color: var(--text2); }
.empty-state i { font-size: 40px; margin-bottom: 12px; display: block; opacity: 0.4; }
.empty-state p { font-size: 14px; }

/* TOAST */
.toast-wrap { position: fixed; bottom: 24px; right: 24px; z-index: 9999; display: flex; flex-direction: column; gap: 8px; }
.toast {
    background: #0f1729;
    color: white;
    padding: 13px 18px;
    border-radius: 12px;
    font-size: 13px;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 10px;
    box-shadow: 0 8px 24px rgba(0,0,0,0.2);
    animation: toastIn 0.3s ease;
    max-width: 320px;
}
.toast.success { border-left: 4px solid #22c55e; }
.toast.error   { border-left: 4px solid #ef4444; }
@keyframes toastIn { from { transform: translateX(120%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }

@media (max-width: 1024px) {
    .admin-sidebar { transform: translateX(-100%); }
    .admin-sidebar.open { transform: translateX(0); }
    .admin-main { margin-left: 0; }
    .stat-cards { grid-template-columns: repeat(2, 1fr); }
    .sidebar-close { display: flex; }
    .sidebar-overlay { display: block !important; }
}
@media (max-width: 640px) {
    .stat-cards { grid-template-columns: 1fr; }
    .admin-content { padding: 16px; }
    .admin-topbar { padding: 12px 16px; }
}
</style>

<div class="toast-wrap" id="toastWrap"></div>
<div class="sidebar-overlay" onclick="toggleSidebar()" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.4);z-index:99;"></div>

<script>
function toggleSidebar() {
    const sb = document.getElementById('adminSidebar');
    const ov = document.querySelector('.sidebar-overlay');
    sb.classList.toggle('open');
    ov.style.display = sb.classList.contains('open') ? 'block' : 'none';
}
function showToast(msg, type = 'success') {
    const wrap = document.getElementById('toastWrap');
    const t = document.createElement('div');
    t.className = 'toast ' + type;
    t.innerHTML = `<i class="bi bi-${type === 'success' ? 'check-circle-fill' : 'exclamation-circle-fill'}"></i> ${msg}`;
    wrap.appendChild(t);
    setTimeout(() => t.remove(), 3500);
}
function openModal(id) { document.getElementById(id).classList.add('show'); }
function closeModal(id) { document.getElementById(id).classList.remove('show'); }
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') document.querySelectorAll('.modal-overlay.show').forEach(m => m.classList.remove('show'));
});
</script>