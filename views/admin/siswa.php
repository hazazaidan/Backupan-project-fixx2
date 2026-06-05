<?php
if (session_status() === PHP_SESSION_NONE) session_start();

// Data dari controller
$siswa       = $siswa       ?? [];
$daftarKelas = $daftarKelas ?? [];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Siswa – Absensi QR</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body>

<div class="admin-layout">
    <?php include dirname(__DIR__) . '/layouts/sidebar_admin.php'; ?>

    <main class="admin-main">

        <!-- TOPBAR -->
        <div class="admin-topbar">
            <div class="topbar-left">
                <button class="topbar-toggle" onclick="toggleSidebar()"><i class="bi bi-list"></i></button>
                <div class="topbar-title">
                    <h2>Data Siswa</h2>
                    <p>Kelola data siswa terdaftar</p>
                </div>
            </div>
            <div class="topbar-right">
                <div class="topbar-icon-btn" title="Notifikasi">
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

            <!-- PAGE HEADER -->
            <div class="page-header">
                <div class="ph-left">
                    <div class="ph-icon"><i class="bi bi-people-fill"></i></div>
                    <div>
                        <div class="ph-title">Data Siswa</div>
                        <div class="ph-sub" id="subtitleCount">Memuat data...</div>
                    </div>
                </div>
                <div class="ph-actions">
                    <button class="btn-primary" onclick="openModal('modalTambah')">
                        <i class="bi bi-plus-lg"></i> Tambah Siswa
                    </button>
                </div>
            </div>

            <!-- TABLE CARD -->
            <div class="table-card">

                <!-- FILTER BAR -->
                <div class="filter-bar">
                    <div class="search-wrap">
                        <i class="bi bi-search"></i>
                        <input type="text" class="search-input" id="searchInput"
                               name="search_siswa"
                               placeholder="Cari nama atau NIS..."
                               oninput="filterData()"
                               autocomplete="new-password">
                    </div>
                    <select class="filter-select" id="filterKelas" onchange="filterData()">
                        <option value="">Semua Kelas</option>
                        <?php foreach ($daftarKelas as $k): ?>
                        <option value="<?= htmlspecialchars($k) ?>"><?= htmlspecialchars($k) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button class="btn-secondary btn-sm" onclick="resetFilter()">
                        <i class="bi bi-x-circle"></i> Reset
                    </button>
                    <span class="filter-count" id="filterCount"></span>
                </div>

                <!-- TABLE -->
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th style="width:42px">No</th>
                                <th>Siswa</th>
                                <th>NIS</th>
                                <th>Kelas</th>
                                <th style="width:120px;text-align:center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tbodySiswa"></tbody>
                    </table>
                </div>

                <!-- PAGINATION -->
                <div class="pagination-bar">
                    <div class="pg-info" id="pgInfo"></div>
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
            <div style="display:flex;align-items:center;gap:10px">
                <div class="modal-ico modal-ico-add"><i class="bi bi-person-plus-fill"></i></div>
                <h4>Tambah Siswa Baru</h4>
            </div>
            <button class="modal-close" onclick="closeModal('modalTambah')"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label class="form-label">Nama Lengkap <span class="req">*</span></label>
                <input type="text" class="form-control" id="add_nama" placeholder="Nama lengkap siswa" autocomplete="new-password">
            </div>
            <div class="form-group">
                <label class="form-label">NIS <span class="req">*</span></label>
                <input type="text" class="form-control" id="add_nis" placeholder="Nomor Induk Siswa" autocomplete="new-password">
            </div>
            <div class="form-group">
                <label class="form-label">Kelas <span class="req">*</span></label>
                <select class="form-control" id="add_kelas">
                    <option value="">Pilih Kelas</option>
                    <?php foreach ($daftarKelas as $k): ?>
                    <option value="<?= htmlspecialchars($k) ?>"><?= htmlspecialchars($k) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Password <span class="req">*</span></label>
                <input type="password" class="form-control" id="add_password" placeholder="Password login siswa" autocomplete="new-password">
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn-secondary" onclick="closeModal('modalTambah')">Batal</button>
            <button class="btn-primary" id="btnSimpanTambah" onclick="simpanTambah()">
                <i class="bi bi-check-lg"></i> Simpan
            </button>
        </div>
    </div>
</div>

<!-- ===================== MODAL EDIT ===================== -->
<div class="modal-overlay" id="modalEdit">
    <div class="modal-box">
        <div class="modal-header">
            <div style="display:flex;align-items:center;gap:10px">
                <div class="modal-ico modal-ico-edit"><i class="bi bi-pencil-fill"></i></div>
                <h4>Edit Data Siswa</h4>
            </div>
            <button class="modal-close" onclick="closeModal('modalEdit')"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="edit_id">
            <div class="form-group">
                <label class="form-label">Nama Lengkap <span class="req">*</span></label>
                <input type="text" class="form-control" id="edit_nama" autocomplete="new-password">
            </div>
            <div class="form-group">
                <label class="form-label">NIS <span class="req">*</span></label>
                <input type="text" class="form-control" id="edit_nis" autocomplete="new-password">
            </div>
            <div class="form-group">
                <label class="form-label">Kelas <span class="req">*</span></label>
                <select class="form-control" id="edit_kelas">
                    <option value="">Pilih Kelas</option>
                    <?php foreach ($daftarKelas as $k): ?>
                    <option value="<?= htmlspecialchars($k) ?>"><?= htmlspecialchars($k) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn-secondary" onclick="closeModal('modalEdit')">Batal</button>
            <button class="btn-primary" id="btnSimpanEdit" onclick="simpanEdit()">
                <i class="bi bi-check-lg"></i> Update
            </button>
        </div>
    </div>
</div>

<!-- ===================== MODAL DETAIL ===================== -->
<div class="modal-overlay" id="modalDetail">
    <div class="modal-box">
        <div class="modal-header">
            <div style="display:flex;align-items:center;gap:10px">
                <div class="modal-ico modal-ico-detail"><i class="bi bi-person-lines-fill"></i></div>
                <h4>Detail Siswa</h4>
            </div>
            <button class="modal-close" onclick="closeModal('modalDetail')"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="modal-body" id="detailContent"></div>
        <div class="modal-footer">
            <button class="btn-secondary" onclick="closeModal('modalDetail')">Tutup</button>
        </div>
    </div>
</div>

<!-- ===================== MODAL HAPUS ===================== -->
<div class="modal-overlay" id="modalHapus">
    <div class="modal-box" style="max-width:420px">
        <div class="modal-header">
            <div style="display:flex;align-items:center;gap:10px">
                <div class="modal-ico modal-ico-del"><i class="bi bi-trash3-fill"></i></div>
                <h4>Hapus Siswa</h4>
            </div>
            <button class="modal-close" onclick="closeModal('modalHapus')"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="modal-body" style="text-align:center;padding:28px 24px">
            <div class="del-icon-wrap"><i class="bi bi-exclamation-triangle-fill"></i></div>
            <div class="del-title">Yakin ingin menghapus?</div>
            <div class="del-sub">Siswa <strong id="hapusNama"></strong> akan dihapus secara permanen.</div>
        </div>
        <div class="modal-footer">
            <button class="btn-secondary" onclick="closeModal('modalHapus')">Batal</button>
            <button class="btn-danger-solid" id="btnKonfirmHapus" onclick="konfirmHapusNow()">
                <i class="bi bi-trash3"></i> Ya, Hapus
            </button>
        </div>
    </div>
</div>

<!-- TOAST -->
<div id="toastWrap" style="position:fixed;bottom:24px;right:24px;z-index:9999;display:flex;flex-direction:column;gap:8px;"></div>

<!-- ===================== STYLE ===================== -->
<style>
.page-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:20px; flex-wrap:wrap; gap:12px; }
.ph-left  { display:flex; align-items:center; gap:14px; }
.ph-icon  { width:48px; height:48px; background:linear-gradient(135deg,#4f46e5,#7c3aed); border-radius:14px; display:flex; align-items:center; justify-content:center; font-size:22px; color:white; }
.ph-title { font-size:18px; font-weight:700; color:var(--text); }
.ph-sub   { font-size:12px; color:var(--text2); margin-top:2px; }
.ph-actions { display:flex; gap:10px; flex-wrap:wrap; }
.filter-count { font-size:12px; color:var(--text2); background:var(--bg); padding:5px 12px; border-radius:20px; white-space:nowrap; font-weight:500; }
.pagination-bar { display:flex; align-items:center; justify-content:space-between; padding:12px 20px; border-top:1px solid var(--border); flex-wrap:wrap; gap:10px; }
.pg-info { font-size:12px; color:var(--text2); }
.modal-ico { width:36px; height:36px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:16px; }
.modal-ico-add    { background:#ede9fe; color:#4f46e5; }
.modal-ico-edit   { background:#fef3c7; color:#d97706; }
.modal-ico-detail { background:#dbeafe; color:#2563eb; }
.modal-ico-del    { background:#fee2e2; color:#dc2626; }
.req { color:#ef4444; }
.del-icon-wrap { width:64px; height:64px; background:#fee2e2; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:28px; color:#dc2626; margin:0 auto 14px; }
.del-title { font-size:16px; font-weight:700; color:var(--text); margin-bottom:8px; }
.del-sub   { font-size:13px; color:var(--text2); line-height:1.6; }
.btn-danger-solid { background:#dc2626; color:white; border:none; padding:9px 18px; border-radius:9px; font-size:13px; font-weight:600; cursor:pointer; display:inline-flex; align-items:center; gap:7px; transition:all 0.15s; font-family:'Poppins',sans-serif; }
.btn-danger-solid:hover { background:#b91c1c; }
.detail-avatar { width:70px; height:70px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:26px; font-weight:800; color:white; margin:0 auto 12px; }
.detail-name { text-align:center; font-size:16px; font-weight:700; color:var(--text); }
.detail-sub  { text-align:center; font-size:12px; color:var(--text2); margin-bottom:18px; }
.detail-grid { display:grid; grid-template-columns:1fr 1fr; gap:10px; }
.detail-item { background:var(--bg); border-radius:10px; padding:10px 13px; }
.detail-item .di-label { font-size:10px; font-weight:700; color:var(--text2); text-transform:uppercase; letter-spacing:0.6px; }
.detail-item .di-val   { font-size:13px; font-weight:600; color:var(--text); margin-top:2px; }
.detail-full { grid-column:1/-1; }
.tbl-ava { width:34px; height:34px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; font-size:12px; font-weight:700; color:white; flex-shrink:0; }
.tbl-name { font-weight:600; font-size:13.5px; color:var(--text); }
.tbl-nis  { font-size:11px; color:var(--text2); }
.action-wrap { display:flex; align-items:center; justify-content:center; gap:5px; }
.act-btn { width:30px; height:30px; border-radius:8px; border:none; cursor:pointer; display:flex; align-items:center; justify-content:center; font-size:13px; transition:all 0.15s; }
.act-detail { background:#dbeafe; color:#2563eb; }
.act-detail:hover { background:#bfdbfe; }
.act-edit   { background:#ede9fe; color:#7c3aed; }
.act-edit:hover { background:#c4b5fd; }
.act-del    { background:#fee2e2; color:#dc2626; }
.act-del:hover { background:#fca5a5; }
.empty-state { text-align:center; padding:48px 20px; color:var(--text2); }
.empty-state i { font-size:40px; display:block; margin-bottom:12px; opacity:.35; }
@media (max-width:768px) { .ph-actions { width:100%; } }
</style>

<!-- ===================== SCRIPT ===================== -->
<script>
let dataSiswa = <?= json_encode(array_values(array_filter(array_map(fn($s) => [
    'id'   => $s['id']    ?? '',
    'nama' => $s['nama']  ?? '-',
    'nis'  => (string)($s['nis'] ?? ''),
    'kelas'=> $s['kelas'] ?? '',
], $siswa), fn($s) => !empty($s['id'])))) ?>;

let currentPage  = 1;
const perPage    = 8;
let filteredData = [...dataSiswa];


let _hapusId = '';

const avatarColors = ['#4f46e5','#7c3aed','#10b981','#f59e0b','#ef4444','#0d9488','#2563eb','#db2777'];
function getColor(nama)   { let h=0; for(let c of nama) h=(h*31+c.charCodeAt(0))%avatarColors.length; return avatarColors[h]; }
function getInisial(nama) { return nama.split(' ').slice(0,2).map(w=>w[0]).join('').toUpperCase(); }

function showToast(msg, type='success') {
    const wrap = document.getElementById('toastWrap');
    const t = document.createElement('div');
    t.style.cssText = `background:#1e1b4b;color:#fff;padding:13px 18px;border-radius:12px;font-size:13px;font-weight:500;display:flex;align-items:center;gap:10px;box-shadow:0 8px 24px rgba(0,0,0,.2);max-width:320px;border-left:4px solid ${type==='success'?'#22c55e':'#ef4444'};font-family:'Poppins',sans-serif;`;
    t.innerHTML = `<i class="bi bi-${type==='success'?'check-circle-fill':'exclamation-circle-fill'}"></i> ${msg}`;
    wrap.appendChild(t);
    setTimeout(()=>{ t.style.opacity='0'; t.style.transition='opacity .3s'; }, 3000);
    setTimeout(()=>t.remove(), 3350);
}

function openModal(id)  { document.getElementById(id).classList.add('show'); }
function closeModal(id) { document.getElementById(id).classList.remove('show'); }
document.querySelectorAll('.modal-overlay').forEach(o => {
    o.addEventListener('click', e => { if(e.target===o) closeModal(o.id); });
});

function renderTable() {
    const start = (currentPage-1)*perPage;
    const paged = filteredData.slice(start, start+perPage);
    const tbody = document.getElementById('tbodySiswa');

    if (paged.length === 0) {
        tbody.innerHTML = `<tr><td colspan="5"><div class="empty-state"><i class="bi bi-people"></i><p>Tidak ada data siswa ditemukan</p></div></td></tr>`;
        document.getElementById('pgInfo').textContent = '';
        document.getElementById('pagination').innerHTML = '';
        document.getElementById('subtitleCount').textContent = `${dataSiswa.length} siswa terdaftar · 0 ditampilkan`;
        document.getElementById('filterCount').textContent = '0 hasil';
        return;
    }

    tbody.innerHTML = paged.map((s,i) => `
        <tr>
            <td style="color:var(--text2);font-size:13px">${start+i+1}</td>
            <td>
                <div style="display:flex;align-items:center;gap:10px">
                    <div class="tbl-ava" style="background:${getColor(s.nama)}">${getInisial(s.nama)}</div>
                    <div>
                        <div class="tbl-name">${s.nama}</div>
                        <div class="tbl-nis">${s.nis || '-'}</div>
                    </div>
                </div>
            </td>
            <td style="font-size:13px;font-family:monospace;color:var(--text2)">${s.nis || '-'}</td>
            <td><span class="badge badge-purple">${s.kelas || '-'}</span></td>
            <td>
                <div class="action-wrap">
                    <button class="act-btn act-detail" onclick="openDetail('${s.id}')" title="Detail"><i class="bi bi-eye"></i></button>
                    <button class="act-btn act-edit"   onclick="openEdit('${s.id}')"   title="Edit"><i class="bi bi-pencil"></i></button>
                    <button class="act-btn act-del" data-id="${s.id}" onclick="openHapusBtn(this)" title="Hapus"><i class="bi bi-trash3"></i></button>
                </div>
            </td>
        </tr>
    `).join('');

    renderPagination();
    document.getElementById('pgInfo').textContent = `Menampilkan ${start+1}–${Math.min(start+perPage,filteredData.length)} dari ${filteredData.length} siswa`;
    document.getElementById('subtitleCount').textContent = `${dataSiswa.length} siswa terdaftar · ${filteredData.length} ditampilkan`;
    document.getElementById('filterCount').textContent   = `${filteredData.length} hasil`;
}

function renderPagination() {
    const total = Math.ceil(filteredData.length/perPage);
    const pg    = document.getElementById('pagination');
    if (total<=1) { pg.innerHTML=''; return; }
    let html = `<button class="page-btn" onclick="gotoPage(${currentPage-1})" ${currentPage===1?'disabled':''}>‹</button>`;
    for (let i=1; i<=total; i++) {
        if (total>7 && i>2 && i<total-1 && Math.abs(i-currentPage)>1) {
            if (i===3||i===total-2) html+=`<span style="padding:0 4px;color:var(--text2)">…</span>`;
            continue;
        }
        html+=`<button class="page-btn ${i===currentPage?'active':''}" onclick="gotoPage(${i})">${i}</button>`;
    }
    html+=`<button class="page-btn" onclick="gotoPage(${currentPage+1})" ${currentPage===total?'disabled':''}>›</button>`;
    pg.innerHTML = html;
}

function gotoPage(p) {
    const total = Math.ceil(filteredData.length/perPage);
    if (p<1||p>total) return;
    currentPage = p; renderTable();
}

function filterData() {
    const q  = document.getElementById('searchInput').value.toLowerCase().trim();
    const kl = document.getElementById('filterKelas').value;
    filteredData = dataSiswa.filter(s =>
        (!q  || s.nama.toLowerCase().includes(q) || (s.nis||'').includes(q)) &&
        (!kl || s.kelas === kl)
    );
    currentPage = 1; renderTable();
}

function resetFilter() {
    document.getElementById('searchInput').value = '';
    document.getElementById('filterKelas').value = '';
    filterData();
}

async function simpanTambah() {
    const nama     = document.getElementById('add_nama').value.trim();
    const nis      = document.getElementById('add_nis').value.trim();
    const kelas    = document.getElementById('add_kelas').value;
    const password = document.getElementById('add_password').value;

    if (!nama||!nis||!kelas||!password) { showToast('Isi semua field wajib','error'); return; }

    const btn = document.getElementById('btnSimpanTambah');
    btn.disabled=true; btn.innerHTML='<i class="bi bi-hourglass-split"></i> Menyimpan...';

    try {
        const res  = await fetch('?url=admin/siswa/store', {
            method:'POST', headers:{'Content-Type':'application/json'},
            body: JSON.stringify({ nama, nisn:nis, kelas, password })
        });
        const json = await res.json();
        if (json.success) {
            closeModal('modalTambah');
            showToast(json.message,'success');
            setTimeout(()=>location.reload(), 1000);
        } else { showToast(json.message,'error'); }
    } catch(e) { showToast('Gagal terhubung ke server','error'); }

    btn.disabled=false; btn.innerHTML='<i class="bi bi-check-lg"></i> Simpan';
}

function openEdit(id) {
    const s = dataSiswa.find(x=>String(x.id)===String(id));
    if (!s) { showToast('Data tidak ditemukan','error'); return; }
    document.getElementById('edit_id').value    = s.id;
    document.getElementById('edit_nama').value  = s.nama;
    document.getElementById('edit_nis').value   = s.nis;
    document.getElementById('edit_kelas').value = s.kelas;
    openModal('modalEdit');
}

async function simpanEdit() {
    const id    = document.getElementById('edit_id').value;
    const nama  = document.getElementById('edit_nama').value.trim();
    const nis   = document.getElementById('edit_nis').value.trim();
    const kelas = document.getElementById('edit_kelas').value;

    if (!nama||!nis||!kelas) { showToast('Isi semua field wajib','error'); return; }

    const btn = document.getElementById('btnSimpanEdit');
    btn.disabled=true; btn.innerHTML='<i class="bi bi-hourglass-split"></i> Menyimpan...';

    try {
        const res  = await fetch('?url=admin/siswa/update', {
            method:'POST', headers:{'Content-Type':'application/json'},
            body: JSON.stringify({ id, nama, nisn:nis, kelas })
        });
        const json = await res.json();
        if (json.success) {
            closeModal('modalEdit');
            showToast(json.message,'success');
            setTimeout(()=>location.reload(), 1000);
        } else { showToast(json.message,'error'); }
    } catch(e) { showToast('Gagal terhubung ke server','error'); }

    btn.disabled=false; btn.innerHTML='<i class="bi bi-check-lg"></i> Update';
}

function openDetail(id) {
    const s = dataSiswa.find(x=>String(x.id)===String(id));
    if (!s) { showToast('Data tidak ditemukan','error'); return; }
    document.getElementById('detailContent').innerHTML = `
        <div style="text-align:center;margin-bottom:18px">
            <div class="detail-avatar" style="background:${getColor(s.nama)}">${getInisial(s.nama)}</div>
            <div class="detail-name">${s.nama}</div>
            <div class="detail-sub"><span class="badge badge-purple">${s.kelas||'-'}</span></div>
        </div>
        <div class="detail-grid">
            <div class="detail-item"><div class="di-label">NIS</div><div class="di-val" style="font-family:monospace">${s.nis||'-'}</div></div>
            <div class="detail-item"><div class="di-label">Kelas</div><div class="di-val">${s.kelas||'-'}</div></div>
        </div>`;
    openModal('modalDetail');
}


function openHapusBtn(btn) {
    const actualBtn = btn.closest('[data-id]');
    const idSiswa   = actualBtn ? actualBtn.getAttribute('data-id') : null;
    const s         = dataSiswa.find(x => String(x.id) === String(idSiswa));

    if (!s || !idSiswa) {
        showToast('Data tidak ditemukan', 'error');
        return;
    }

    _hapusId = String(idSiswa);
    document.getElementById('hapusNama').textContent = s.nama;
    openModal('modalHapus');
}


async function konfirmHapusNow() {
    const btn        = document.getElementById('btnKonfirmHapus');
    const hapusIdnya = _hapusId;

    if (!hapusIdnya) {
        showToast('ID tidak valid', 'error');
        return;
    }

    btn.disabled  = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Menghapus...';

    try {
        const res  = await fetch('?url=admin/siswa/destroy', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: hapusIdnya })
        });
        const json = await res.json();

        if (json.success) {
            dataSiswa    = dataSiswa.filter(x => String(x.id) !== hapusIdnya);
            filteredData = filteredData.filter(x => String(x.id) !== hapusIdnya);
            _hapusId = '';
            closeModal('modalHapus');
            renderTable();
            showToast(json.message, 'success');
        } else {
            showToast(json.message, 'error');
        }
    } catch(e) {
        showToast('Gagal terhubung ke server', 'error');
    }

    btn.disabled  = false;
    btn.innerHTML = '<i class="bi bi-trash3"></i> Ya, Hapus';
}

filteredData = [...dataSiswa];
renderTable();
</script>

</body>
</html>