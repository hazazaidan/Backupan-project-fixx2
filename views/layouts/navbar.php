<?php
$u = $user ?? [];
$initials = implode('', array_map(fn($w) => strtoupper($w[0]), array_slice(explode(' ', $u['nama'] ?? 'User'), 0, 2)));
?>
<header class="topbar">
  <div class="topbar-title">
    <h2><?= htmlspecialchars($pageTitle ?? $title ?? 'Dashboard') ?></h2>
    <p><?= htmlspecialchars($pageSubtitle ?? 'Selamat datang kembali, ' . ($u['nama'] ?? '') . ' 👋') ?></p>
  </div>
  <div class="topbar-right">
    <div class="topbar-btn">
      <i data-lucide="search" style="width:16px;height:16px;"></i>
    </div>
    <div class="topbar-btn">
      <i data-lucide="bell" style="width:16px;height:16px;"></i>
      <span class="notif-dot"></span>
    </div>
    <div class="topbar-btn">
      <i data-lucide="help-circle" style="width:16px;height:16px;"></i>
    </div>
    <div class="topbar-avatar"><?= $initials ?></div>
  </div>
</header>