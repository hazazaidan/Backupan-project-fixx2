<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$currentUrl = trim($_GET['url'] ?? 'auth/loginPage', '/');

function isActive(string $route): string {
    global $currentUrl;
    
    return ($currentUrl === $route || str_starts_with($currentUrl, $route . '/'))
        ? 'active'
        : '';
}

$u        = $user ?? $_SESSION['user'] ?? [];
$initials = implode('', array_map(
    fn($w) => strtoupper($w[0]),
    array_slice(explode(' ', $u['nama'] ?? 'User'), 0, 2)
));
$namaUser  = htmlspecialchars($u['nama']         ?? 'Guru');
$roleUser  = ucfirst($u['role']                  ?? 'guru');
$kelasUser = htmlspecialchars($u['kelas']         ?? 'XI RPL 1');
$sekolah   = htmlspecialchars($u['nama_sekolah']  ?? 'Man 2 Banyumas');
?>
<aside class="sidebar">
  <div class="sidebar-brand">
    <div class="brand-icon">
      <svg width="22" height="22" viewBox="0 0 22 22" fill="none">
        <rect x="2"  y="2"  width="7" height="7" rx="1.5" stroke="white" stroke-width="1.5"/>
        <rect x="13" y="2"  width="7" height="7" rx="1.5" stroke="white" stroke-width="1.5"/>
        <rect x="2"  y="13" width="7" height="7" rx="1.5" stroke="white" stroke-width="1.5"/>
        <rect x="13" y="13" width="3" height="3" rx="0.5" fill="white"/>
        <rect x="17" y="13" width="3" height="3" rx="0.5" fill="white"/>
        <rect x="13" y="17" width="3" height="3" rx="0.5" fill="white"/>
      </svg>
    </div>
    <div class="brand-text">
      <h3>ABSENSI QR</h3>
      <p><?= $sekolah ?></p>
    </div>
  </div>

  <div class="sidebar-user">
    <div class="user-avatar"><?= $initials ?></div>
    <div class="user-info">
      <h4><?= $namaUser ?></h4>
      <p><?= $roleUser ?> – <?= $kelasUser ?></p>
    </div>
  </div>

  <nav class="sidebar-nav">
    <div class="nav-label">Menu Utama</div>

    <a class="nav-item <?= isActive('guru/dashboard') ?>" href="?url=guru/dashboard">
      <i data-lucide="layout-dashboard" style="width:18px;height:18px;"></i>
      Dashboard
    </a>

    <a class="nav-item <?= isActive('guru/kelas') ?>" href="?url=guru/kelas">
      <i data-lucide="door-open" style="width:18px;height:18px;"></i>
      Kelas
    </a>

    <a class="nav-item <?= isActive('guru/riwayat') ?>" href="?url=guru/riwayat">
      <i data-lucide="clipboard-list" style="width:18px;height:18px;"></i>
      Riwayat Absensi
    </a>

    <a class="nav-item <?= isActive('guru/rekap') ?>" href="?url=guru/rekap">
      <i data-lucide="bar-chart-2" style="width:18px;height:18px;"></i>
      Rekap Kelas
    </a>

    <a class="nav-item <?= isActive('guru/monitoring') ?>" href="?url=guru/monitoring">
      <i data-lucide="activity" style="width:18px;height:18px;"></i>
      Monitoring
    </a>

  </nav>

  <div class="sidebar-bottom">
    <div class="nav-label" style="margin-bottom:6px;">Sistem</div>
    <a class="nav-item <?= isActive('guru/pengaturan') ?>" href="?url=guru/pengaturan">
      <i data-lucide="settings" style="width:18px;height:18px;"></i>
      Pengaturan
    </a>
    <a class="nav-item" href="?url=auth/logout">
      <i data-lucide="log-out" style="width:18px;height:18px;"></i>
      Logout
    </a>
  </div>
</aside>