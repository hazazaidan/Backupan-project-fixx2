<?php
// ── views/guru/riwayat.php ────────────────────────────────────────────────
$title        = $title       ?? 'Riwayat Absensi';
$pageTitle    = 'Riwayat Absensi';
$pageSubtitle = 'Kelola data kehadiran siswa';
require_once BASE_PATH . '/views/layouts/header.php';

$u2        = $_SESSION['user'] ?? [];
$namaUser  = htmlspecialchars($u2['nama']         ?? 'Guru');
$kelasUser = htmlspecialchars($u2['kelas']         ?? 'XI RPL 1');
$sekolah   = htmlspecialchars($u2['nama_sekolah']  ?? 'Man 2 Banyumas');
$inisial2  = strtoupper(implode('', array_map(fn($w) => $w[0], array_slice(explode(' ', $u2['nama'] ?? 'G'), 0, 2))));
?>

<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>

<style>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
*,*::before,*::after { box-sizing: border-box; }

/* ════════════════════════════════════════
   SIDEBAR — identik 100% dengan Dashboard
   ════════════════════════════════════════ */
:root {
    --sidebar-bg: #0f1729;
    --sidebar-hover: #1a2540;
    --sidebar-active: #2563eb;
    --accent: #2563eb;
}
.sidebar {
    background: var(--sidebar-bg);
    width: 260px;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    flex-shrink: 0;
    position: fixed;
    top: 0; left: 0; bottom: 0;
    z-index: 100;
}
.sidebar-brand {
    padding: 20px 20px 16px;
    border-bottom: 1px solid rgba(255,255,255,0.06);
    display: flex;
    align-items: center;
    gap: 12px;
}
.brand-icon {
    background: var(--accent);
    width: 42px;
    height: 42px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.user-card {
    margin: 16px 14px;
    background: rgba(255,255,255,0.06);
    border-radius: 12px;
    padding: 12px 14px;
    display: flex;
    align-items: center;
    gap: 12px;
}
.user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: var(--accent);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 14px;
    color: white;
    flex-shrink: 0;
}
.nav-section-label {
    padding: 12px 20px 6px;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 1.2px;
    color: rgba(255,255,255,0.35);
    text-transform: uppercase;
}
.nav-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 11px 16px;
    margin: 2px 10px;
    border-radius: 10px;
    color: rgba(255,255,255,0.6);
    font-size: 14px;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.2s;
    position: relative;
}
.nav-item:hover  { background: var(--sidebar-hover); color: white; }
.nav-item.active { background: var(--sidebar-active); color: white; }
.nav-icon { width: 18px; text-align: center; }
.sidebar-bottom {
    border-top: 1px solid rgba(255,255,255,0.07);
    padding-bottom: 8px;
    margin-top: auto;
}
/* ════════════════════════════════════════ */

/* ── LAYOUT ── */
.main-content {
    margin-left: 260px;
    padding-top: 64px;
    min-height: 100vh;
    background: #f1f5f9;
    font-family: 'Plus Jakarta Sans', sans-serif;
}
.page-body { padding: 28px 28px 40px; max-width: 1200px; }

/* ── TOPBAR ── */
.topbar {
    position: fixed; top: 0; left: 260px; right: 0; height: 64px;
    background: white;
    border-bottom: 1px solid #e2e8f0;
    display: flex; align-items: center; justify-content: space-between;
    padding: 0 28px; z-index: 99;
    box-shadow: 0 1px 3px rgba(0,0,0,.04);
}
.topbar-title h2 { font-size: 16px; font-weight: 700; color: #1e293b; margin: 0; }
.topbar-title p  { font-size: 12px; color: #94a3b8; margin-top: 2px; }
.topbar-right    { display: flex; align-items: center; gap: 8px; }
.topbar-btn {
    width: 36px; height: 36px; border-radius: 10px;
    background: #f8fafc; border: 1px solid #e2e8f0;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; color: #64748b; position: relative; transition: .15s;
}
.topbar-btn:hover { background: #f1f5f9; color: #334155; }
.topbar-avatar {
    width: 36px; height: 36px; border-radius: 50%;
    background: #2563eb; color: #fff;
    font-size: 13px; font-weight: 700;
    display: flex; align-items: center; justify-content: center; cursor: pointer;
}
.notif-dot {
    position: absolute; top: 6px; right: 6px;
    width: 7px; height: 7px; background: #ef4444;
    border-radius: 50%; border: 1.5px solid #fff;
}

/* ── CARD ── */
.card-riwayat {
    background: #fff; border-radius: 20px;
    border: 1px solid #e2e8f0; overflow: hidden;
    box-shadow: 0 1px 4px rgba(15,23,42,.06), 0 4px 16px rgba(15,23,42,.04);
}
.card-header {
    padding: 22px 28px 18px; border-bottom: 1px solid #f1f5f9;
    display: flex; align-items: center; justify-content: space-between;
    flex-wrap: wrap; gap: 12px;
}
.card-header-left h2 { font-size: 16px; font-weight: 800; color: #0f172a; margin: 0; }
.card-header-left p  { font-size: 12px; color: #94a3b8; margin: 3px 0 0; font-weight: 500; }
.export-wrap { display: flex; gap: 8px; flex-wrap: wrap; }
.btn-export {
    display: flex; align-items: center; gap: 7px;
    padding: 9px 16px; border-radius: 10px;
    font-size: 12.5px; font-weight: 700;
    cursor: pointer; border: none;
    transition: all .18s; font-family: inherit;
}
.btn-excel { background: #16a34a; color: #fff; box-shadow: 0 2px 8px rgba(22,163,74,.25); }
.btn-excel:hover { background: #15803d; transform: translateY(-1px); }
.btn-pdf   { background: #dc2626; color: #fff; box-shadow: 0 2px 8px rgba(220,38,38,.25); }
.btn-pdf:hover   { background: #b91c1c; transform: translateY(-1px); }

/* ── FILTER ── */
.filter-section { padding: 16px 28px; background: #fafbfc; border-bottom: 1px solid #f1f5f9; }
.filter-wrap { display: grid; grid-template-columns: repeat(4,1fr); gap: 10px; }
@media(max-width:900px){ .filter-wrap { grid-template-columns: 1fr 1fr; } }
@media(max-width:560px){ .filter-wrap { grid-template-columns: 1fr; } }
.filter-input {
    border: 1px solid #e2e8f0; border-radius: 10px;
    padding: 9px 13px; font-size: 13px; color: #334155;
    background: #fff; width: 100%; transition: .18s;
    font-family: inherit; font-weight: 500;
    appearance: none; -webkit-appearance: none;
}
.filter-input:focus { border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,.1); outline: none; }
select.filter-input {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='none' stroke='%2394a3b8' viewBox='0 0 24 24'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E");
    background-repeat: no-repeat; background-position: right 12px center; padding-right: 34px;
}
.input-search-wrap { position: relative; }
.input-search-wrap .filter-input { padding-left: 36px; }
.input-search-wrap .search-ico { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; pointer-events: none; width: 15px; height: 15px; }

/* ── STATS STRIP ── */
.stats-strip {
    padding: 12px 28px; background: #fafbfc;
    border-bottom: 1px solid #f1f5f9;
    display: flex; gap: 20px; flex-wrap: wrap; align-items: center;
}
.stat-pill { display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 600; padding: 5px 12px; border-radius: 8px; }
.sp-hadir { background: #dcfce7; color: #15803d; }
.sp-izin  { background: #fef9c3; color: #a16207; }
.sp-alpha { background: #fee2e2; color: #b91c1c; }
.sp-total { background: #eff6ff; color: #1d4ed8; }

/* ── TABLE ── */
.table-wrap { overflow-x: auto; }
.tbl { width: 100%; border-collapse: collapse; font-size: 13px; }
.tbl thead tr { border-bottom: 1px solid #f1f5f9; background: #fafbfc; }
.tbl th { padding: 11px 20px; text-align: left; font-size: 10.5px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: .06em; white-space: nowrap; }
.tbl td { padding: 13px 20px; color: #475569; border-bottom: 1px solid #f8fafc; vertical-align: middle; }
.tbl tbody tr:hover td { background: #f8fafc; }
.tbl tbody tr:last-child td { border-bottom: none; }
.nama-cell   { display: flex; align-items: center; gap: 11px; }
.avatar-cell { width: 34px; height: 34px; border-radius: 10px; background: linear-gradient(135deg,#dbeafe,#bfdbfe); color: #2563eb; font-weight: 800; font-size: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.av-green    { background: linear-gradient(135deg,#d1fae5,#a7f3d0); color: #059669; }
.av-yellow   { background: linear-gradient(135deg,#fef3c7,#fde68a); color: #d97706; }
.av-red      { background: linear-gradient(135deg,#fee2e2,#fecaca); color: #dc2626; }
.av-purple   { background: linear-gradient(135deg,#ede9fe,#ddd6fe); color: #7c3aed; }
.nama-text   { font-weight: 700; color: #1e293b; font-size: 13px; }
.nis-text    { font-size: 12px; color: #64748b; }
.kelas-chip  { background: #eff6ff; color: #2563eb; font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 6px; display: inline-block; }
.waktu-text  { font-weight: 600; color: #334155; }
.waktu-dash  { color: #cbd5e1; }
.tanggal-text { font-size: 12.5px; color: #64748b; }
.badge { font-size: 11.5px; font-weight: 700; padding: 4px 12px; border-radius: 8px; display: inline-flex; align-items: center; gap: 5px; }
.badge::before { content:''; width: 6px; height: 6px; border-radius: 50%; background: currentColor; opacity: .7; flex-shrink: 0; }
.badge-hadir { background: #dcfce7; color: #15803d; }
.badge-izin  { background: #fef9c3; color: #a16207; }
.badge-alpha { background: #fee2e2; color: #b91c1c; }
.no-cell     { color: #cbd5e1; font-weight: 600; font-size: 12px; }

/* ── FOOTER ── */
.card-footer {
    padding: 14px 28px; border-top: 1px solid #f1f5f9;
    display: flex; align-items: center; justify-content: space-between;
    flex-wrap: wrap; gap: 10px; background: #fafbfc;
}
.footer-info { font-size: 12px; color: #94a3b8; }
.footer-info strong { color: #475569; font-weight: 700; }
.pagination { display: flex; gap: 5px; align-items: center; }
.page-btn {
    width: 32px; height: 32px; border-radius: 9px;
    display: flex; align-items: center; justify-content: center;
    font-size: 13px; font-weight: 700; text-decoration: none;
    transition: all .15s; border: none; cursor: pointer; font-family: inherit;
}
.page-btn.active { background: #2563eb; color: #fff; box-shadow: 0 2px 8px rgba(37,99,235,.3); }
.page-btn:not(.active) { background: #f1f5f9; color: #64748b; }
.page-btn:not(.active):hover { background: #e2e8f0; color: #1e293b; }
</style>

<!-- ════════════════════════════════════════
     SIDEBAR — identik 100% dengan Dashboard
     ════════════════════════════════════════ -->
<aside class="sidebar">
    <div class="sidebar-brand">
        <div class="brand-icon"><i class="fa fa-qrcode" style="color:white;font-size:18px;"></i></div>
        <div>
            <p style="color:white;font-weight:700;font-size:14px;line-height:1.2;">ABSENSI QR</p>
            <p style="color:rgba(255,255,255,0.45);font-size:10px;"><?= $sekolah ?></p>
        </div>
    </div>
    <div class="user-card">
        <div class="user-avatar"><?= $inisial2 ?></div>
        <div>
            <p style="color:white;font-weight:600;font-size:13px;line-height:1.2;"><?= $namaUser ?></p>
            <p style="color:rgba(255,255,255,0.45);font-size:11px;">Guru – <?= $kelasUser ?></p>
        </div>
    </div>
    <p class="nav-section-label">Menu Utama</p>
    <nav>
        <a href="?url=guru/dashboard"  class="nav-item"><i class="fa fa-home nav-icon"></i> Dashboard</a>
        <a href="?url=guru/kelas"      class="nav-item"><i class="fa fa-door-open nav-icon"></i> Kelas</a>
        <a href="?url=guru/riwayat"    class="nav-item active"><i class="fa fa-clock-rotate-left nav-icon"></i> Riwayat Absensi</a>
        <a href="?url=guru/rekap"      class="nav-item"><i class="fa fa-layer-group nav-icon"></i> Rekap Kelas</a>
        <a href="?url=guru/monitoring" class="nav-item"><i class="fa fa-chart-line nav-icon"></i> Monitoring</a>
    </nav>
    <div class="sidebar-bottom">
        <p class="nav-section-label">Sistem</p>
        <a href="?url=guru/pengaturan" class="nav-item"><i class="fa fa-gear nav-icon"></i> Pengaturan</a>
        <a href="?url=auth/logout"     class="nav-item"><i class="fa fa-right-from-bracket nav-icon"></i> Logout</a>
    </div>
</aside>

<!-- ── TOPBAR ── -->
<div class="topbar">
    <div class="topbar-title">
        <h2>Riwayat Absensi</h2>
        <p>Data kehadiran siswa – <strong style="color:#2563eb;"><?= date('d F Y') ?></strong></p>
    </div>
    <div class="topbar-right">
        <div class="topbar-btn">
            <i class="fa fa-bell" style="font-size:14px;"></i>
        </div>
        <div style="display:flex;align-items:center;gap:10px;padding-left:12px;border-left:1px solid #e2e8f0;">
            <div class="topbar-avatar"><?= $inisial2 ?></div>
            <div>
                <p style="font-size:13px;font-weight:600;color:#1e293b;"><?= $namaUser ?></p>
                <p style="font-size:11px;color:#94a3b8;">Guru</p>
            </div>
        </div>
    </div>
</div>

<div class="main-content">
  <div class="page-body">
    <div class="card-riwayat">

      <!-- HEADER -->
      <div class="card-header">
        <div class="card-header-left">
          <h2>Riwayat Absensi</h2>
          <p>Data kehadiran siswa – <strong style="color:#2563eb;"><?= htmlspecialchars(date('d F Y')) ?></strong></p>
        </div>
        <div class="export-wrap">
          <button class="btn-export btn-excel" onclick="exportExcel()">
            <i class="fa fa-download" style="font-size:12px;"></i> Export Excel
          </button>
          <button class="btn-export btn-pdf" onclick="exportPDF()">
            <i class="fa fa-file-pdf" style="font-size:12px;"></i> Export PDF
          </button>
        </div>
      </div>

      <!-- FILTER -->
      <?php
      $formAction = (strpos($_SERVER['REQUEST_URI'] ?? '', '/guru/riwayat') !== false)
          ? BASE_URL . '/guru/riwayat' : '';
      ?>
      <div class="filter-section">
        <form method="GET" id="filterForm"
              action="<?= htmlspecialchars($formAction) ?>"
              class="filter-wrap">
          <?php if (!$formAction): ?>
          <input type="hidden" name="url" value="guru/riwayat">
          <?php endif; ?>

          <input type="date" name="tanggal" class="filter-input"
              value="<?= htmlspecialchars($tanggal ?? date('Y-m-d')) ?>"
              onchange="this.form.submit()">

          <select name="kelas" class="filter-input" onchange="this.form.submit()">
            <option value="">Semua Kelas</option>
            <?php
            $kelasList = $kelasList ?? [
                ['nama_kelas'=>'XI RPL 1'],['nama_kelas'=>'XI RPL 2'],
                ['nama_kelas'=>'XII RPL 1'],['nama_kelas'=>'XII RPL 2'],
            ];
            foreach ($kelasList as $k): ?>
            <option value="<?= htmlspecialchars($k['nama_kelas'] ?? $k['nama'] ?? '') ?>"
                <?= (($kelas ?? '') === ($k['nama_kelas'] ?? $k['nama'] ?? '')) ? 'selected' : '' ?>>
              <?= htmlspecialchars($k['nama_kelas'] ?? $k['nama'] ?? '') ?>
            </option>
            <?php endforeach; ?>
          </select>

          <select name="status" class="filter-input" onchange="this.form.submit()">
            <option value="">Semua Status</option>
            <option value="hadir" <?= (strtolower($status??'')==='hadir')?'selected':'' ?>>Hadir</option>
            <option value="izin"  <?= (strtolower($status??'')==='izin') ?'selected':'' ?>>Izin</option>
            <option value="alpha" <?= (strtolower($status??'')==='alpha')?'selected':'' ?>>Alpha</option>
          </select>

          <div class="input-search-wrap">
            <svg class="search-ico" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <circle cx="11" cy="11" r="8"/><path stroke-linecap="round" d="m21 21-4.35-4.35"/>
            </svg>
            <input type="text" name="nama" class="filter-input"
                placeholder="Cari nama siswa..."
                value="<?= htmlspecialchars($cariNama ?? '') ?>"
                onkeyup="debounceSearch(this)">
          </div>
        </form>
      </div>

      <!-- STATS STRIP -->
      <?php
      $rawList = $data ?? [];
      $list = array_values(array_map(function($row, $i) {
          $nama  = $row['nama']  ?? $row['students']['nama']  ?? '-';
          $nis   = $row['nis']   ?? $row['students']['nis']   ?? '-';
          $kelas = $row['kelas'] ?? $row['students']['kelas'] ?? $row['nama_kelas'] ?? '-';
          $waktuRaw = $row['waktu_masuk'] ?? $row['waktu'] ?? '';
          $waktu    = ($waktuRaw && $waktuRaw !== '-') ? substr($waktuRaw, 0, 5) : '-';
          $status   = ucfirst(strtolower($row['status'] ?? 'alpha'));
          return ['no'=>$i+1,'nama'=>$nama,'nis'=>$nis,'kelas'=>$kelas,'waktu'=>$waktu,'tanggal'=>$row['tanggal']??'-','status'=>$status];
      }, $rawList, array_keys($rawList)));

      $cntHadir = count(array_filter($list, fn($r) => $r['status'] === 'Hadir'));
      $cntIzin  = count(array_filter($list, fn($r) => $r['status'] === 'Izin'));
      $cntAlpha = count(array_filter($list, fn($r) => $r['status'] === 'Alpha'));
      ?>
      <div class="stats-strip">
        <span class="stat-pill sp-total">📋 Total: <?= count($list) ?> siswa</span>
        <span class="stat-pill sp-hadir">✅ Hadir: <?= $cntHadir ?></span>
        <span class="stat-pill sp-izin">📄 Izin: <?= $cntIzin ?></span>
        <span class="stat-pill sp-alpha">❌ Alpha: <?= $cntAlpha ?></span>
      </div>

      <!-- TABEL -->
      <div class="table-wrap">
        <table class="tbl" id="tabelAbsensi">
          <thead>
            <tr>
              <th>No</th><th>Nama Siswa</th><th>NIS</th><th>Kelas</th>
              <th>Waktu Masuk</th><th>Tanggal</th><th>Status</th>
            </tr>
          </thead>
          <tbody id="tabelBody">
          <?php if (empty($list)): ?>
          <tr>
            <td colspan="7" style="text-align:center;padding:52px;color:#94a3b8;">
              <div style="font-size:32px;margin-bottom:10px;">📭</div>
              <div style="font-size:13px;font-weight:600;">Tidak ada data absensi</div>
              <div style="font-size:11px;margin-top:4px;">Coba ubah filter atau pilih tanggal lain</div>
            </td>
          </tr>
          <?php else:
          $avColors = ['', 'av-green', 'av-yellow', 'av-red', 'av-purple'];
          foreach ($list as $i => $row):
            $s      = $row['status'];
            $bdgCls = match($s){ 'Hadir'=>'badge-hadir','Izin'=>'badge-izin',default=>'badge-alpha' };
            $avCls  = $avColors[$i % 5];
          ?>
          <tr>
            <td class="no-cell"><?= $row['no'] ?></td>
            <td>
              <div class="nama-cell">
                <div class="avatar-cell <?= $avCls ?>"><?= strtoupper(substr($row['nama'],0,1)) ?></div>
                <span class="nama-text"><?= htmlspecialchars($row['nama']) ?></span>
              </div>
            </td>
            <td><span class="nis-text"><?= htmlspecialchars($row['nis']) ?></span></td>
            <td><span class="kelas-chip"><?= htmlspecialchars($row['kelas']) ?></span></td>
            <td>
              <?php if ($row['waktu'] === '-'): ?>
              <span class="waktu-dash">—</span>
              <?php else: ?>
              <span class="waktu-text"><?= htmlspecialchars($row['waktu']) ?></span>
              <?php endif; ?>
            </td>
            <td><span class="tanggal-text"><?= htmlspecialchars($row['tanggal']) ?></span></td>
            <td><span class="badge <?= $bdgCls ?>"><?= htmlspecialchars($s) ?></span></td>
          </tr>
          <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>

      <!-- FOOTER -->
      <?php
      $paginationBase = $formAction
          ? $formAction . '?tanggal=' . urlencode($tanggal??'') . '&kelas=' . urlencode($kelas??'') . '&status=' . urlencode($status??'')
          : '?url=guru/riwayat&tanggal=' . urlencode($tanggal??'') . '&kelas=' . urlencode($kelas??'') . '&status=' . urlencode($status??'');
      $totalPages = isset($total, $perPage) ? ceil($total / $perPage) : 1;
      ?>
      <div class="card-footer">
        <p class="footer-info">
          Menampilkan
          <strong><?= (($page??1)-1)*($perPage??10)+1 ?>–<?= min(($page??1)*($perPage??10), $total??count($list)) ?></strong>
          dari <strong><?= $total??count($list) ?></strong> data
        </p>
        <?php if ($totalPages > 1): ?>
        <div class="pagination">
          <?php for ($p = 1; $p <= $totalPages; $p++): ?>
          <a href="<?= $paginationBase ?>&page=<?= $p ?>"
             class="page-btn <?= $p===($page??1)?'active':'' ?>">
            <?= $p ?>
          </a>
          <?php endfor; ?>
        </div>
        <?php endif; ?>
      </div>

    </div>
  </div>
</div>

<script>
function getTableData() {
    const rows = [];
    document.querySelectorAll('#tabelBody tr').forEach((tr, i) => {
        const tds = tr.querySelectorAll('td');
        if (tds.length < 7) return;
        rows.push({
            'No': i+1,
            'Nama Siswa': tds[1].querySelector('.nama-text')?.textContent.trim() ?? tds[1].textContent.trim(),
            'NIS': tds[2].textContent.trim(),
            'Kelas': tds[3].textContent.trim(),
            'Waktu Masuk': tds[4].textContent.trim(),
            'Tanggal': tds[5].textContent.trim(),
            'Status': tds[6].textContent.trim(),
        });
    });
    return rows;
}

function exportExcel() {
    const rows = getTableData();
    if (!rows.length) { alert('Tidak ada data!'); return; }
    const ws = XLSX.utils.json_to_sheet(rows);
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, 'Riwayat Absensi');
    const maxW = rows.reduce((acc, row) => {
        Object.keys(row).forEach((k, i) => { acc[i] = Math.max(acc[i]||10, String(row[k]).length+2); });
        return acc;
    }, []);
    ws['!cols'] = maxW.map(w => ({ wch: w }));
    XLSX.writeFile(wb, 'Riwayat_Absensi_<?= date('Y-m-d') ?>.xlsx');
}

function exportPDF() {
    const rows = getTableData();
    if (!rows.length) { alert('Tidak ada data!'); return; }
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF({ orientation: 'landscape', unit: 'mm', format: 'a4' });
    doc.setFont('helvetica','bold'); doc.setFontSize(14);
    doc.text('Riwayat Absensi', 14, 16);
    doc.setFont('helvetica','normal'); doc.setFontSize(9); doc.setTextColor(150);
    doc.text('Dicetak: <?= date('d/m/Y H:i') ?>', 14, 22); doc.setTextColor(0);
    doc.autoTable({
        startY: 28,
        head: [['No','Nama Siswa','NIS','Kelas','Waktu Masuk','Tanggal','Status']],
        body: rows.map(r => [r.No, r['Nama Siswa'], r.NIS, r.Kelas, r['Waktu Masuk'], r.Tanggal, r.Status]),
        styles: { fontSize: 9, cellPadding: 4 },
        headStyles: { fillColor: [37,99,235], textColor: 255, fontStyle: 'bold' },
        alternateRowStyles: { fillColor: [248,250,252] },
        columnStyles: { 6: { halign: 'center' } },
    });
    doc.save('Riwayat_Absensi_<?= date('Y-m-d') ?>.pdf');
}

let searchTimer;
function debounceSearch(input) {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => input.closest('form').submit(), 500);
}
</script>

<?php require_once BASE_PATH . '/views/layouts/footer.php'; ?>