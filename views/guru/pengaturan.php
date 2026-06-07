<?php include BASE_PATH . '/views/layouts/header.php'; ?>
<?php include BASE_PATH . '/views/layouts/sidebar.php'; ?>

<div class="main-content">

  <!-- TOPBAR -->
  <div class="topbar">
    <div class="topbar-title">
      <h2>Pengaturan</h2>
      <p>Kelola profil dan keamanan akun Anda</p>
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
      <div class="topbar-avatar"><?= $initials ?? 'U' ?></div>
    </div>
  </div>

  <div class="page-body">

    <!-- TOAST ALERT -->
    <?php if (isset($_SESSION['success'])): ?>
    <div class="toast-container" id="toastBox">
      <div class="toast toast-success" id="toastMsg">
        <i data-lucide="check-circle-2" style="width:16px;height:16px;flex-shrink:0;"></i>
        <span><?= htmlspecialchars($_SESSION['success']) ?></span>
      </div>
    </div>
    <?php unset($_SESSION['success']); ?>
    <?php elseif (isset($_SESSION['error'])): ?>
    <div class="toast-container" id="toastBox">
      <div class="toast toast-danger" id="toastMsg">
        <i data-lucide="x-circle" style="width:16px;height:16px;flex-shrink:0;"></i>
        <span><?= htmlspecialchars($_SESSION['error']) ?></span>
      </div>
    </div>
    <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- PAGE HEADER -->
    <div style="margin-bottom:24px;">
      <div style="display:flex;align-items:center;gap:10px;">
        <div style="width:40px;height:40px;background:#eff6ff;border-radius:12px;
                    display:flex;align-items:center;justify-content:center;">
          <i data-lucide="settings" style="width:20px;height:20px;color:#2563eb;"></i>
        </div>
        <div>
          <h1 style="font-size:20px;font-weight:700;color:#0f172a;">Pengaturan Akun</h1>
          <p style="font-size:12px;color:#94a3b8;margin-top:2px;">Update informasi profil dan kata sandi Anda</p>
        </div>
      </div>
    </div>

    <!-- PROFILE BANNER -->
    <div class="card" style="margin-bottom:20px;padding:24px;
         background:linear-gradient(135deg,#1e40af 0%,#2563eb 60%,#3b82f6 100%);
         border:none;position:relative;overflow:hidden;">
      <div style="position:absolute;top:-30px;right:-30px;width:140px;height:140px;
                  border-radius:50%;background:rgba(255,255,255,.07);"></div>
      <div style="position:absolute;bottom:-50px;right:80px;width:180px;height:180px;
                  border-radius:50%;background:rgba(255,255,255,.05);"></div>

      <div style="display:flex;align-items:center;gap:16px;position:relative;">
        <div style="width:64px;height:64px;background:rgba(255,255,255,.2);border-radius:50%;
                    display:flex;align-items:center;justify-content:center;
                    font-size:22px;font-weight:700;color:#fff;border:3px solid rgba(255,255,255,.3);">
          <?= $initials ?? 'U' ?>
        </div>
        <div>
          <h2 style="font-size:18px;font-weight:700;color:#fff;">
            <?= htmlspecialchars($user['nama'] ?? 'Nama Guru') ?>
          </h2>
          <p style="font-size:12px;color:rgba(255,255,255,.75);margin-top:3px;">
            <?= htmlspecialchars($user['email'] ?? '') ?>
          </p>
          <span style="display:inline-flex;align-items:center;gap:4px;margin-top:6px;
                       background:rgba(255,255,255,.15);border-radius:99px;
                       padding:3px 10px;font-size:11px;font-weight:600;color:#fff;">
            <i data-lucide="shield-check" style="width:12px;height:12px;"></i>
            Akun Aktif
          </span>
        </div>
      </div>
    </div>

    <!-- TWO COLUMN -->
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">

      <!-- ── EDIT PROFIL ── -->
      <div class="card" style="padding:0;overflow:hidden;">
        <div style="padding:18px 24px;border-bottom:1px solid #f1f5f9;
                    display:flex;align-items:center;gap:10px;">
          <div style="width:32px;height:32px;background:#eff6ff;border-radius:9px;
                      display:flex;align-items:center;justify-content:center;">
            <i data-lucide="user-pen" style="width:16px;height:16px;color:#2563eb;"></i>
          </div>
          <div>
            <h3 style="font-size:14px;font-weight:700;color:#0f172a;">Edit Profil</h3>
            <p style="font-size:11px;color:#94a3b8;margin-top:1px;">Perbarui nama dan email akun</p>
          </div>
        </div>

        <form action="<?= BASE_URL ?>?url=pengaturan/updateProfil" method="POST"
              style="padding:24px;display:flex;flex-direction:column;gap:16px;">

          <div>
            <label style="font-size:12px;font-weight:600;color:#475569;
                          display:block;margin-bottom:6px;">Nama Lengkap</label>
            <div style="position:relative;">
              <i data-lucide="user" style="position:absolute;left:11px;top:50%;
                 transform:translateY(-50%);width:15px;height:15px;color:#94a3b8;pointer-events:none;"></i>
              <input type="text" name="nama" class="form-input"
                     style="padding-left:34px;"
                     value="<?= htmlspecialchars($user['nama'] ?? '') ?>" required>
            </div>
          </div>

          <div>
            <label style="font-size:12px;font-weight:600;color:#475569;
                          display:block;margin-bottom:6px;">Email</label>
            <div style="position:relative;">
              <i data-lucide="mail" style="position:absolute;left:11px;top:50%;
                 transform:translateY(-50%);width:15px;height:15px;color:#94a3b8;pointer-events:none;"></i>
              <input type="email" name="email" class="form-input"
                     style="padding-left:34px;"
                     value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
            </div>
          </div>

          <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;margin-top:4px;">
            <i data-lucide="save" style="width:15px;height:15px;"></i>
            Simpan Perubahan
          </button>

        </form>
      </div>

      <!-- ── GANTI PASSWORD ── -->
      <div class="card" style="padding:0;overflow:hidden;">
        <div style="padding:18px 24px;border-bottom:1px solid #f1f5f9;
                    display:flex;align-items:center;gap:10px;">
          <div style="width:32px;height:32px;background:#fff7ed;border-radius:9px;
                      display:flex;align-items:center;justify-content:center;">
            <i data-lucide="lock-keyhole" style="width:16px;height:16px;color:#ea580c;"></i>
          </div>
          <div>
            <h3 style="font-size:14px;font-weight:700;color:#0f172a;">Ganti Password</h3>
            <p style="font-size:11px;color:#94a3b8;margin-top:1px;">Pastikan password minimal 6 karakter</p>
          </div>
        </div>

        <form action="<?= BASE_URL ?>?url=pengaturan/gantiPassword" method="POST"
              style="padding:24px;display:flex;flex-direction:column;gap:16px;">

          <!-- Password Lama -->
          <div>
            <label style="font-size:12px;font-weight:600;color:#475569;
                          display:block;margin-bottom:6px;">Password Lama</label>
            <div style="position:relative;">
              <i data-lucide="lock" style="position:absolute;left:11px;top:50%;
                 transform:translateY(-50%);width:15px;height:15px;color:#94a3b8;pointer-events:none;"></i>
              <input type="password" name="password_lama" id="passLama" class="form-input"
                     style="padding-left:34px;padding-right:40px;" required>
              <button type="button" class="toggle-pass" data-target="passLama"
                style="position:absolute;right:10px;top:50%;transform:translateY(-50%);
                       background:none;border:none;cursor:pointer;color:#94a3b8;padding:0;
                       display:flex;align-items:center;">
                <i data-lucide="eye" style="width:15px;height:15px;" id="icon-passLama"></i>
              </button>
            </div>
          </div>

          <!-- Password Baru -->
          <div>
            <label style="font-size:12px;font-weight:600;color:#475569;
                          display:block;margin-bottom:6px;">Password Baru</label>
            <div style="position:relative;">
              <i data-lucide="lock-keyhole" style="position:absolute;left:11px;top:50%;
                 transform:translateY(-50%);width:15px;height:15px;color:#94a3b8;pointer-events:none;"></i>
              <input type="password" name="password_baru" id="passBaru" class="form-input"
                     style="padding-left:34px;padding-right:40px;" required minlength="6">
              <button type="button" class="toggle-pass" data-target="passBaru"
                style="position:absolute;right:10px;top:50%;transform:translateY(-50%);
                       background:none;border:none;cursor:pointer;color:#94a3b8;padding:0;
                       display:flex;align-items:center;">
                <i data-lucide="eye" style="width:15px;height:15px;" id="icon-passBaru"></i>
              </button>
            </div>
            <!-- strength bar -->
            <div id="strengthWrap" style="margin-top:8px;display:none;">
              <div style="height:4px;border-radius:99px;background:#f1f5f9;overflow:hidden;">
                <div id="strengthBar" style="height:100%;width:0;border-radius:99px;transition:.3s;"></div>
              </div>
              <p id="strengthLabel" style="font-size:10px;color:#94a3b8;margin-top:4px;"></p>
            </div>
          </div>

          <!-- Konfirmasi -->
          <div>
            <label style="font-size:12px;font-weight:600;color:#475569;
                          display:block;margin-bottom:6px;">Konfirmasi Password Baru</label>
            <div style="position:relative;">
              <i data-lucide="shield-check" style="position:absolute;left:11px;top:50%;
                 transform:translateY(-50%);width:15px;height:15px;color:#94a3b8;pointer-events:none;"></i>
              <input type="password" name="konfirmasi_pass" id="passKonfirm" class="form-input"
                     style="padding-left:34px;padding-right:40px;" required minlength="6">
              <button type="button" class="toggle-pass" data-target="passKonfirm"
                style="position:absolute;right:10px;top:50%;transform:translateY(-50%);
                       background:none;border:none;cursor:pointer;color:#94a3b8;padding:0;
                       display:flex;align-items:center;">
                <i data-lucide="eye" style="width:15px;height:15px;" id="icon-passKonfirm"></i>
              </button>
            </div>
            <p id="matchMsg" style="font-size:10px;margin-top:4px;display:none;"></p>
          </div>

          <button type="submit" id="btnGantiPass" class="btn"
                  style="width:100%;justify-content:center;margin-top:4px;
                         background:#ea580c;color:#fff;">
            <i data-lucide="shield-alert" style="width:15px;height:15px;"></i>
            Ganti Password
          </button>

        </form>
      </div>

    </div><!-- end grid -->
  </div><!-- end page-body -->
</div><!-- end main-content -->

<style>
.toast {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 12px 18px;
  border-radius: 12px;
  font-size: 13px;
  font-weight: 500;
  box-shadow: 0 8px 24px rgba(0,0,0,.12);
  animation: slideUp .3s ease;
  min-width: 260px;
}
.toast-success { background:#fff; color:#15803d; border-left:4px solid #16a34a; }
.toast-danger  { background:#fff; color:#b91c1c; border-left:4px solid #dc2626; }
@keyframes slideUp {
  from { opacity:0; transform:translateY(12px); }
  to   { opacity:1; transform:translateY(0); }
}
</style>

<script>
lucide.createIcons();

// Auto hide toast
const toast = document.getElementById('toastMsg');
if (toast) setTimeout(() => {
  toast.style.transition = 'opacity .4s';
  toast.style.opacity    = '0';
  setTimeout(() => toast.parentElement?.remove(), 400);
}, 3500);

// Toggle show/hide password
document.querySelectorAll('.toggle-pass').forEach(btn => {
  btn.addEventListener('click', function () {
    const input = document.getElementById(this.dataset.target);
    const icon  = document.getElementById('icon-' + this.dataset.target);
    if (input.type === 'password') {
      input.type = 'text';
      icon.setAttribute('data-lucide', 'eye-off');
    } else {
      input.type = 'password';
      icon.setAttribute('data-lucide', 'eye');
    }
    lucide.createIcons();
  });
});

// Password strength
const passBaru      = document.getElementById('passBaru');
const strengthBar   = document.getElementById('strengthBar');
const strengthLabel = document.getElementById('strengthLabel');
const strengthWrap  = document.getElementById('strengthWrap');

passBaru.addEventListener('input', function () {
  const v = this.value;
  strengthWrap.style.display = v.length ? 'block' : 'none';
  let score = 0;
  if (v.length >= 6)  score++;
  if (v.length >= 10) score++;
  if (/[A-Z]/.test(v) && /[a-z]/.test(v)) score++;
  if (/[0-9]/.test(v)) score++;
  if (/[^A-Za-z0-9]/.test(v)) score++;

  const levels = [
    { w: '20%',  bg: '#ef4444', label: 'Sangat Lemah' },
    { w: '40%',  bg: '#f97316', label: 'Lemah' },
    { w: '60%',  bg: '#eab308', label: 'Cukup' },
    { w: '80%',  bg: '#22c55e', label: 'Kuat' },
    { w: '100%', bg: '#16a34a', label: 'Sangat Kuat' },
  ];
  const lvl = levels[Math.min(score, 4)];
  strengthBar.style.width      = lvl.w;
  strengthBar.style.background = lvl.bg;
  strengthLabel.style.color    = lvl.bg;
  strengthLabel.textContent    = lvl.label;
  checkMatch();
});

// Password match check
const passKonfirm = document.getElementById('passKonfirm');
const matchMsg    = document.getElementById('matchMsg');

function checkMatch() {
  if (!passKonfirm.value) { matchMsg.style.display = 'none'; return; }
  matchMsg.style.display = 'block';
  if (passBaru.value === passKonfirm.value) {
    matchMsg.textContent          = '✓ Password cocok';
    matchMsg.style.color          = '#16a34a';
    passKonfirm.style.borderColor = '#16a34a';
  } else {
    matchMsg.textContent          = '✗ Password tidak cocok';
    matchMsg.style.color          = '#dc2626';
    passKonfirm.style.borderColor = '#dc2626';
  }
}
passKonfirm.addEventListener('input', checkMatch);
</script>

<?php include BASE_PATH . '/views/layouts/footer.php'; ?>