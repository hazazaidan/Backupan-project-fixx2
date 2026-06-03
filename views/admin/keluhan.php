<?php
// views/admin/keluhan.php
// Variabel: $pageTitle, $reports, $parentReports, $filterStatus, $filterJenis, $stats
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Keluhan & Laporan') ?> — Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body>

<div class="admin-layout">
    <?php require_once BASE_PATH . '/views/layouts/sidebar_admin.php'; ?>

    <div class="admin-main">

        <!-- TOPBAR -->
        <div class="admin-topbar">
            <div class="topbar-left">
                <button class="topbar-toggle" onclick="toggleSidebar()">
                    <i class="bi bi-list"></i>
                </button>
                <div class="topbar-title">
                    <h2>Keluhan &amp; Laporan</h2>
                    <p>Manajemen laporan siswa &amp; orang tua</p>
                </div>
            </div>
            <div class="topbar-right">
                <div class="topbar-icon-btn">
                    <i class="bi bi-bell"></i>
                    <?php if (($stats['pending'] ?? 0) > 0): ?>
                        <span class="notif-badge"><?= $stats['pending'] ?></span>
                    <?php endif; ?>
                </div>
                <div class="topbar-avatar">
                    <?= strtoupper(substr($_SESSION['user']['nama'] ?? 'A', 0, 1)) ?>
                </div>
            </div>
        </div>

        <!-- CONTENT -->
        <div class="admin-content">

            <!-- STAT CARDS -->
            <div class="stat-cards">
                <div class="stat-card">
                    <div class="stat-icon" style="background:#ede9fe;">
                        <i class="bi bi-chat-left-text-fill" style="color:#7c3aed;font-size:22px;"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-val"><?= $stats['total'] ?? 0 ?></div>
                        <div class="stat-label">Total Laporan</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background:#fef3c7;">
                        <i class="bi bi-hourglass-split" style="color:#d97706;font-size:22px;"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-val"><?= $stats['pending'] ?? 0 ?></div>
                        <div class="stat-label">Menunggu</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background:#dcfce7;">
                        <i class="bi bi-check-circle-fill" style="color:#16a34a;font-size:22px;"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-val"><?= $stats['accepted'] ?? 0 ?></div>
                        <div class="stat-label">Diterima</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background:#fee2e2;">
                        <i class="bi bi-x-circle-fill" style="color:#dc2626;font-size:22px;"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-val"><?= $stats['rejected'] ?? 0 ?></div>
                        <div class="stat-label">Ditolak</div>
                    </div>
                </div>
            </div>

            <!-- TABLE CARD -->
            <div class="table-card">

                <!-- FILTER BAR -->
                <div class="filter-bar">
                    <div class="search-wrap">
                        <i class="bi bi-search"></i>
                        <input type="text" class="search-input" id="searchInput" placeholder="Cari judul, nama, kelas...">
                    </div>
                    <select class="filter-select" id="filterJenis" onchange="applyFilter()">
                        <option value="" <?= !$filterJenis ? 'selected' : '' ?>>Semua Jenis</option>
                        <option value="siswa" <?= $filterJenis === 'siswa' ? 'selected' : '' ?>>Laporan Siswa</option>
                        <option value="ortu"  <?= $filterJenis === 'ortu'  ? 'selected' : '' ?>>Laporan Orang Tua</option>
                    </select>
                    <select class="filter-select" id="filterStatus" onchange="applyFilter()">
                        <option value="" <?= !$filterStatus ? 'selected' : '' ?>>Semua Status</option>
                        <option value="pending"  <?= $filterStatus === 'pending'  ? 'selected' : '' ?>>Pending</option>
                        <option value="accepted" <?= $filterStatus === 'accepted' ? 'selected' : '' ?>>Diterima</option>
                        <option value="rejected" <?= $filterStatus === 'rejected' ? 'selected' : '' ?>>Ditolak</option>
                    </select>
                </div>

                <!-- TABS -->
                <div class="tab-bar">
                    <button class="tab-btn active" id="tabSiswa" onclick="switchTab('siswa')">
                        <i class="bi bi-person-fill"></i> Laporan Siswa
                        <span class="tab-count"><?= count($reports ?? []) ?></span>
                    </button>
                    <button class="tab-btn" id="tabOrtu" onclick="switchTab('ortu')">
                        <i class="bi bi-people-fill"></i> Laporan Orang Tua
                        <span class="tab-count"><?= count($parentReports ?? []) ?></span>
                    </button>
                </div>

                <!-- TABLE SISWA -->
                <div id="tableSiswa">
                    <?php if (empty($reports)): ?>
                        <div class="empty-state">
                            <i class="bi bi-inbox"></i>
                            <p>Belum ada laporan siswa</p>
                        </div>
                    <?php else: ?>
                        <div class="table-card-body">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Siswa</th>
                                        <th>Judul</th>
                                        <th>Status</th>
                                        <th>Tanggal</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($reports as $i => $r): ?>
                                        <?php
                                            $nama  = $r['students']['nama']  ?? $r['student_nis'] ?? '-';
                                            $kelas = $r['students']['kelas'] ?? '-';
                                            $st    = $r['status'] ?? 'pending';
                                            $badgeClass = match($st) { 'accepted' => 'badge-success', 'rejected' => 'badge-danger', default => 'badge-warning' };
                                            $badgeLabel = match($st) { 'accepted' => 'Diterima', 'rejected' => 'Ditolak', default => 'Pending' };
                                            $inisial = strtoupper(implode('', array_map(fn($w) => $w[0], array_slice(explode(' ', $nama), 0, 2))));
                                            $colors  = ['#4f46e5','#7c3aed','#0ea5e9','#16a34a','#d97706'];
                                            $color   = $colors[$i % count($colors)];
                                        ?>
                                        <tr class="report-row" data-search="<?= strtolower(htmlspecialchars($nama . ' ' . $kelas . ' ' . ($r['title'] ?? ''))) ?>">
                                            <td><?= $i + 1 ?></td>
                                            <td>
                                                <div style="display:flex;align-items:center;gap:10px;">
                                                    <div class="ava" style="background:<?= $color ?>;"><?= $inisial ?></div>
                                                    <div>
                                                        <div style="font-weight:600;font-size:13px;"><?= htmlspecialchars($nama) ?></div>
                                                        <div style="font-size:11px;color:var(--text2);"><?= htmlspecialchars($kelas) ?></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div style="font-weight:600;font-size:13px;"><?= htmlspecialchars($r['title'] ?? '-') ?></div>
                                                <div style="font-size:11px;color:var(--text2);max-width:220px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= htmlspecialchars($r['message'] ?? '') ?></div>
                                            </td>

                                            <!-- KOLOM STATUS: badge dropdown -->
                                            <td>
                                                <div class="status-wrap">
                                                    <span class="badge <?= $badgeClass ?> badge-clickable"
                                                          data-id="<?= $r['id'] ?>"
                                                          data-jenis="siswa"
                                                          onclick="toggleStatusDropdown(event, this)">
                                                        <?= $badgeLabel ?> <span style="font-size:10px;opacity:.7;">▾</span>
                                                    </span>
                                                </div>
                                            </td>

                                            <td style="font-size:12px;color:var(--text2);"><?= date('d M Y', strtotime($r['created_at'])) ?></td>
                                            <td>
                                                <div style="display:flex;gap:6px;flex-wrap:wrap;">
                                                    <a href="?url=admin/keluhan/chat&id=<?= urlencode($r['id']) ?>&jenis=siswa" class="btn-edit btn-sm">
                                                        <i class="bi bi-chat-dots"></i> Chat
                                                    </a>
                                                    <button class="btn-secondary btn-sm"
                                                        onclick='openDetailModal(<?= json_encode(['id'=>$r['id'],'title'=>$r['title']??'','message'=>$r['message']??'','status'=>$st,'created_at'=>$r['created_at'],'nama'=>$nama,'kelas'=>$kelas,'description'=>$r['description']??'']) ?>, "siswa")'>
                                                        <i class="bi bi-eye"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- TABLE ORANG TUA -->
                <div id="tableOrtu" style="display:none;">
                    <?php if (empty($parentReports)): ?>
                        <div class="empty-state">
                            <i class="bi bi-inbox"></i>
                            <p>Belum ada laporan orang tua</p>
                        </div>
                    <?php else: ?>
                        <div class="table-card-body">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Siswa</th>
                                        <th>Judul</th>
                                        <th>Status</th>
                                        <th>Tanggal</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($parentReports as $i => $r): ?>
                                        <?php
                                            $nama  = $r['students']['nama']  ?? $r['student_nis'] ?? '-';
                                            $kelas = $r['students']['kelas'] ?? '-';
                                            $st    = $r['status'] ?? 'pending';
                                            $badgeClass = match($st) { 'accepted' => 'badge-success', 'rejected' => 'badge-danger', default => 'badge-warning' };
                                            $badgeLabel = match($st) { 'accepted' => 'Diterima', 'rejected' => 'Ditolak', default => 'Pending' };
                                            $inisial = strtoupper(substr($nama, 0, 2));
                                            $colors  = ['#4f46e5','#7c3aed','#0ea5e9','#16a34a','#d97706'];
                                            $color   = $colors[$i % count($colors)];
                                        ?>
                                        <tr class="report-row" data-search="<?= strtolower(htmlspecialchars($nama . ' ' . $kelas . ' ' . ($r['title'] ?? ''))) ?>">
                                            <td><?= $i + 1 ?></td>
                                            <td>
                                                <div style="display:flex;align-items:center;gap:10px;">
                                                    <div class="ava" style="background:<?= $color ?>;"><?= $inisial ?></div>
                                                    <div>
                                                        <div style="font-weight:600;font-size:13px;"><?= htmlspecialchars($nama) ?></div>
                                                        <div style="font-size:11px;color:var(--text2);"><?= htmlspecialchars($kelas) ?></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div style="font-weight:600;font-size:13px;"><?= htmlspecialchars($r['title'] ?? '-') ?></div>
                                                <div style="font-size:11px;color:var(--text2);max-width:220px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= htmlspecialchars($r['message'] ?? '') ?></div>
                                            </td>

                                            <!-- KOLOM STATUS: badge dropdown -->
                                            <td>
                                                <div class="status-wrap">
                                                    <span class="badge <?= $badgeClass ?> badge-clickable"
                                                          data-id="<?= $r['id'] ?>"
                                                          data-jenis="ortu"
                                                          onclick="toggleStatusDropdown(event, this)">
                                                        <?= $badgeLabel ?> <span style="font-size:10px;opacity:.7;">▾</span>
                                                    </span>
                                                </div>
                                            </td>

                                            <td style="font-size:12px;color:var(--text2);"><?= date('d M Y', strtotime($r['created_at'])) ?></td>
                                            <td>
                                                <div style="display:flex;gap:6px;flex-wrap:wrap;">
                                                    <a href="?url=admin/keluhan/chat&id=<?= urlencode($r['id']) ?>&jenis=ortu" class="btn-edit btn-sm">
                                                        <i class="bi bi-chat-dots"></i> Chat
                                                    </a>
                                                    <button class="btn-secondary btn-sm"
                                                        onclick='openDetailModal(<?= json_encode(['id'=>$r['id'],'title'=>$r['title']??'','message'=>$r['message']??'','status'=>$st,'created_at'=>$r['created_at'],'nama'=>$nama,'kelas'=>$kelas,'description'=>'']) ?>, "ortu")'>
                                                        <i class="bi bi-eye"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>

            </div><!-- /.table-card -->
        </div><!-- /.admin-content -->
    </div><!-- /.admin-main -->
</div><!-- /.admin-layout -->

<!-- DROPDOWN PORTAL — di luar semua container, position:fixed bebas clip -->
<div id="statusDropdownPortal" class="status-keluhan-dropdown">
    <div class="skd-item" onclick="pilihStatusKeluhan('accepted')">
        <span class="skd-dot" style="background:#16a34a;"></span> Diterima
    </div>
    <div class="skd-item" onclick="pilihStatusKeluhan('pending')">
        <span class="skd-dot" style="background:#d97706;"></span> Pending
    </div>
    <div class="skd-item" onclick="pilihStatusKeluhan('rejected')">
        <span class="skd-dot" style="background:#dc2626;"></span> Ditolak
    </div>
</div>

<!-- MODAL DETAIL -->
<div class="modal-overlay" id="modalDetail">
    <div class="modal-box">
        <div class="modal-header">
            <h4 id="modalDetailTitle">Detail Laporan</h4>
            <button class="modal-close" onclick="closeModal('modalDetail')"><i class="bi bi-x"></i></button>
        </div>
        <div class="modal-body" id="modalDetailBody"></div>
        <div class="modal-footer">
            <button class="btn-secondary" onclick="closeModal('modalDetail')">Tutup</button>
        </div>
    </div>
</div>

<!-- MODAL TOLAK -->
<div class="modal-overlay" id="modalTolak">
    <div class="modal-box">
        <div class="modal-header">
            <h4>Tolak Laporan</h4>
            <button class="modal-close" onclick="closeModal('modalTolak')"><i class="bi bi-x"></i></button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="tolakId">
            <input type="hidden" id="tolakJenis">
            <div class="form-group">
                <label class="form-label">Alasan Penolakan (opsional)</label>
                <textarea class="form-control" id="tolakCatatan" rows="3" placeholder="Tuliskan alasan penolakan..."></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn-secondary" onclick="closeModal('modalTolak')">Batal</button>
            <button class="btn-danger" onclick="submitTolak()"><i class="bi bi-x-circle"></i> Tolak Laporan</button>
        </div>
    </div>
</div>

<!-- TOAST -->
<div id="toastWrap" style="position:fixed;bottom:24px;right:24px;z-index:9999;display:flex;flex-direction:column;gap:8px;"></div>

<style>
/* ── TABS ─────────────────────────────────────────────── */
.tab-bar {
    display: flex;
    gap: 4px;
    padding: 12px 16px;
    border-bottom: 1px solid var(--border);
    background: #fafafa;
}
.tab-btn {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 8px 18px;
    border-radius: 9px;
    border: 1px solid transparent;
    background: none;
    font-size: 13px;
    font-weight: 600;
    color: var(--text2);
    cursor: pointer;
    font-family: 'Poppins', sans-serif;
    transition: all 0.15s;
}
.tab-btn:hover { background: var(--bg); color: var(--text); }
.tab-btn.active { background: var(--accent); color: white; border-color: var(--accent); box-shadow: 0 4px 12px rgba(79,70,229,0.3); }
.tab-count {
    background: rgba(255,255,255,0.25);
    border-radius: 20px;
    padding: 1px 7px;
    font-size: 11px;
    font-weight: 700;
}
.tab-btn:not(.active) .tab-count { background: var(--border); color: var(--text2); }

/* ── STATUS BADGE ─────────────────────────────────────── */
.status-wrap {
    display: inline-block;
}
.badge-clickable {
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    user-select: none;
    transition: opacity 0.15s;
}
.badge-clickable:hover { opacity: 0.82; }

/* ── DROPDOWN PORTAL (position:fixed — tidak bisa terpotong) ── */
.status-keluhan-dropdown {
    display: none;
    position: fixed;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    box-shadow: 0 8px 28px rgba(0,0,0,0.14);
    z-index: 99999;
    min-width: 150px;
    overflow: hidden;
    animation: dropFadeIn 0.12s ease;
}
@keyframes dropFadeIn {
    from { opacity: 0; transform: translateY(-4px); }
    to   { opacity: 1; transform: translateY(0); }
}
.status-keluhan-dropdown.open { display: block; }
.skd-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 15px;
    font-size: 12.5px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.1s;
    font-family: 'Poppins', sans-serif;
    color: var(--text);
}
.skd-item:hover { background: #f5f3ff; }
.skd-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    flex-shrink: 0;
}
</style>

<script>
/* ── TAB ────────────────────────────────────────────────── */
function switchTab(tab) {
    document.getElementById('tableSiswa').style.display = tab === 'siswa' ? 'block' : 'none';
    document.getElementById('tableOrtu').style.display  = tab === 'ortu'  ? 'block' : 'none';
    document.getElementById('tabSiswa').classList.toggle('active', tab === 'siswa');
    document.getElementById('tabOrtu').classList.toggle('active',  tab === 'ortu');
}

/* ── SEARCH ─────────────────────────────────────────────── */
document.getElementById('searchInput').addEventListener('input', function() {
    const q = this.value.toLowerCase();
    document.querySelectorAll('.report-row').forEach(row => {
        row.style.display = row.dataset.search.includes(q) ? '' : 'none';
    });
});

/* ── FILTER ─────────────────────────────────────────────── */
function applyFilter() {
    const jenis  = document.getElementById('filterJenis').value;
    const status = document.getElementById('filterStatus').value;
    let url = '?url=admin/keluhan';
    if (jenis)  url += '&jenis='  + jenis;
    if (status) url += '&status=' + status;
    window.location = url;
}

/* ── MODAL ──────────────────────────────────────────────── */
function openModal(id)  { document.getElementById(id).classList.add('show'); }
function closeModal(id) { document.getElementById(id).classList.remove('show'); }

/* ── TOAST ──────────────────────────────────────────────── */
function showToast(msg, type = 'success') {
    const wrap = document.getElementById('toastWrap');
    const t = document.createElement('div');
    t.style.cssText = `background:#1e1b4b;color:#fff;padding:13px 18px;border-radius:12px;font-size:13px;font-weight:500;display:flex;align-items:center;gap:10px;box-shadow:0 8px 24px rgba(0,0,0,.2);max-width:320px;border-left:4px solid ${type === 'success' ? '#22c55e' : '#ef4444'};font-family:'Poppins',sans-serif;`;
    t.innerHTML = `<i class="bi bi-${type === 'success' ? 'check-circle-fill' : 'exclamation-circle-fill'}"></i> ${msg}`;
    wrap.appendChild(t);
    setTimeout(() => { t.style.opacity = '0'; t.style.transition = 'opacity .3s'; }, 3000);
    setTimeout(() => t.remove(), 3350);
}

/* ── STATUS DROPDOWN (PORTAL — position:fixed) ──────────── */
let _activeBadge = null;
const portal = document.getElementById('statusDropdownPortal');

function toggleStatusDropdown(e, badge) {
    e.stopPropagation();

    // toggle: kalau badge yang sama diklik lagi, tutup
    if (_activeBadge === badge && portal.classList.contains('open')) {
        closeStatusDropdown();
        return;
    }

    _activeBadge = badge;

    const rect  = badge.getBoundingClientRect();
    const ddH   = 130; // estimasi tinggi dropdown (3 item)
    const ddW   = 155;

    // posisi horizontal: clamped agar tidak keluar layar kanan
    let left = rect.left;
    if (left + ddW > window.innerWidth - 8) left = window.innerWidth - ddW - 8;

    // posisi vertikal: flip ke atas jika ruang bawah kurang
    let top;
    if (window.innerHeight - rect.bottom < ddH + 8) {
        top = rect.top - ddH - 4; // muncul ke atas
    } else {
        top = rect.bottom + 4;    // muncul ke bawah
    }

    portal.style.top  = top  + 'px';
    portal.style.left = left + 'px';
    portal.classList.add('open');
}

function closeStatusDropdown() {
    portal.classList.remove('open');
    _activeBadge = null;
}

// tutup saat klik di luar
document.addEventListener('click', function(e) {
    if (!portal.contains(e.target)) closeStatusDropdown();
});

// tutup saat scroll (posisi berubah)
window.addEventListener('scroll', closeStatusDropdown, true);

/* ── PILIH STATUS ───────────────────────────────────────── */
const keluhanBadgeConfig = {
    'accepted': { cls: 'badge-success', label: 'Diterima' },
    'pending':  { cls: 'badge-warning', label: 'Pending'  },
    'rejected': { cls: 'badge-danger',  label: 'Ditolak'  },
};

async function pilihStatusKeluhan(statusBaru) {
    const badge = _activeBadge;
    if (!badge) return;

    const id    = badge.dataset.id;
    const jenis = badge.dataset.jenis;

    closeStatusDropdown();

    if (!id) { showToast('ID tidak ditemukan', 'error'); return; }

    badge.style.opacity       = '0.5';
    badge.style.pointerEvents = 'none';

    try {
        const res  = await fetch('?url=admin/keluhan/updateStatus', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify({ id, status: statusBaru, jenis }),
        });
        const json = await res.json();

        if (json.success) {
            const cfg = keluhanBadgeConfig[statusBaru];
            badge.className = `badge ${cfg.cls} badge-clickable`;
            badge.innerHTML = `${cfg.label} <span style="font-size:10px;opacity:.7;">▾</span>`;
            badge.dataset.id    = id;
            badge.dataset.jenis = jenis;
            showToast(json.message ?? 'Status berhasil diubah', 'success');
        } else {
            showToast(json.message || 'Gagal mengubah status', 'error');
        }
    } catch (e) {
        showToast('Gagal terhubung ke server', 'error');
    }

    badge.style.opacity       = '1';
    badge.style.pointerEvents = 'auto';
}

/* ── TOLAK MODAL ────────────────────────────────────────── */
function openTolakModal(id, jenis) {
    document.getElementById('tolakId').value      = id;
    document.getElementById('tolakJenis').value   = jenis;
    document.getElementById('tolakCatatan').value = '';
    openModal('modalTolak');
}
function submitTolak() {
    const id      = document.getElementById('tolakId').value;
    const jenis   = document.getElementById('tolakJenis').value;
    const catatan = document.getElementById('tolakCatatan').value;
    closeModal('modalTolak');

    fetch('?url=admin/keluhan/updateStatus', {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify({ id, status: 'rejected', jenis, catatan }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast(data.message ?? 'Laporan ditolak', 'success');
            const badge = document.querySelector(`.badge-clickable[data-id="${id}"]`);
            if (badge) {
                badge.className = 'badge badge-danger badge-clickable';
                badge.innerHTML = 'Ditolak <span style="font-size:10px;opacity:.7;">▾</span>';
            }
        } else {
            showToast(data.message || 'Gagal', 'error');
        }
    })
    .catch(() => showToast('Terjadi kesalahan', 'error'));
}

/* ── DETAIL MODAL ───────────────────────────────────────── */
function openDetailModal(data, jenis) {
    document.getElementById('modalDetailTitle').textContent = data.title || 'Detail Laporan';
    const stMap = {
        accepted: ['badge-success', 'Diterima'],
        rejected: ['badge-danger',  'Ditolak'],
        pending:  ['badge-warning', 'Pending'],
    };
    const [stClass, stLabel] = stMap[data.status] || stMap.pending;
    const tgl = data.created_at
        ? new Date(data.created_at).toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' })
        : '-';

    let html = `
        <div style="display:flex;gap:12px;margin-bottom:16px;align-items:center;">
            <div class="ava" style="background:#4f46e5;width:44px;height:44px;font-size:16px;">${esc(data.nama || '-').substring(0, 2).toUpperCase()}</div>
            <div>
                <div style="font-weight:700;font-size:14px;">${esc(data.nama || '-')}</div>
                <div style="font-size:12px;color:var(--text2);">${esc(data.kelas || '-')}</div>
            </div>
        </div>
        <div style="margin-bottom:12px;">
            <span class="badge ${stClass}">${stLabel}</span>
            <span style="font-size:11px;color:var(--text2);margin-left:8px;">${tgl}</span>
        </div>
        <div style="background:var(--bg);border-radius:10px;padding:14px;font-size:13px;line-height:1.7;color:var(--text);">${esc(data.message || '-')}</div>
    `;
    if (data.description) {
        html += `<div style="margin-top:12px;padding:10px 14px;background:#ede9fe;border-radius:9px;font-size:12px;color:#7c3aed;">
            <strong>Catatan Admin:</strong> ${esc(data.description)}
        </div>`;
    }
    document.getElementById('modalDetailBody').innerHTML = html;
    openModal('modalDetail');
}

function esc(str) {
    const d = document.createElement('div');
    d.textContent = String(str);
    return d.innerHTML;
}

/* ── AUTO SWITCH TAB ────────────────────────────────────── */
const fj = '<?= $filterJenis ?>';
if (fj === 'ortu') switchTab('ortu');
</script>

</body>
</html>