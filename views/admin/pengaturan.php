<?php
if (session_status() === PHP_SESSION_NONE) session_start();
date_default_timezone_set('Asia/Jakarta');

// Fallback jika view diakses langsung tanpa controller (dev mode)
if (!isset($sekolahData)) {
    $sekolahData = [
        'nama'      => 'MAN 2 Banyumas',
        'npsn'      => '20403280',
        'alamat'    => 'Jl. Pramuka No. 1, Purwokerto, Banyumas 53114',
        'telepon'   => '(0281) 641260',
        'email'     => 'man2banyumas@gmail.com',
        'kepala'    => 'Drs. H. Ahmad Sudirman, M.Pd.',
        'tahun_ajar'=> '2025/2026',
    ];
}

// Fallback hari libur jika controller belum pass data (misal belum ada tabel DB)
if (!isset($hariLibur) || !is_array($hariLibur)) {
    $hariLibur = [];
}

$bulanIndo = ['01'=>'Jan','02'=>'Feb','03'=>'Mar','04'=>'Apr','05'=>'Mei','06'=>'Jun',
              '07'=>'Jul','08'=>'Agu','09'=>'Sep','10'=>'Okt','11'=>'Nov','12'=>'Des'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Sistem – Admin Absensi QR</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .settings-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        .settings-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.06);
            overflow: hidden;
            transition: box-shadow 0.2s;
        }
        .settings-card:hover { box-shadow: 0 4px 16px rgba(79,70,229,0.1); }
        .settings-card-header {
            padding: 18px 22px 14px;
            border-bottom: 1px solid var(--border);
            display: flex; align-items: center; gap: 12px;
        }
        .settings-card-icon {
            width: 42px; height: 42px;
            border-radius: 11px;
            display: flex; align-items: center; justify-content: center;
            font-size: 19px; flex-shrink: 0;
        }
        .settings-card-header h4 { font-size: 15px; font-weight: 700; color: var(--text); margin: 0; }
        .settings-card-header p  { font-size: 11.5px; color: var(--text2); margin: 2px 0 0; }
        .settings-card-body { padding: 20px 22px; }

        .time-group {
            display: flex; gap: 14px;
            margin-bottom: 16px;
        }
        .time-field { flex: 1; }
        .time-field label { display: block; font-size: 11px; font-weight: 600; color: var(--text2); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px; }
        .time-input-wrap { position: relative; }
        .time-input-wrap i { position: absolute; left: 11px; top: 50%; transform: translateY(-50%); color: var(--accent); font-size: 14px; }
        .time-input {
            width: 100%; padding: 10px 12px 10px 34px;
            border: 1.5px solid var(--border); border-radius: 10px;
            font-size: 18px; font-weight: 700; font-family: 'Poppins', sans-serif;
            color: var(--text); outline: none; background: white;
            transition: border-color 0.15s, box-shadow 0.15s;
            letter-spacing: 1px;
        }
        .time-input:focus { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(79,70,229,0.1); }

        .time-status {
            font-size: 11.5px; font-weight: 500; margin-top: 6px;
            display: flex; align-items: center; gap: 5px;
        }
        .status-dot { width: 7px; height: 7px; border-radius: 50%; }

        .toggle-row {
            display: flex; align-items: center; justify-content: space-between;
            padding: 12px 0; border-bottom: 1px solid #f1f5f9;
        }
        .toggle-row:last-child { border-bottom: none; padding-bottom: 0; }
        .toggle-label { font-size: 13px; font-weight: 500; color: var(--text); }
        .toggle-desc  { font-size: 11.5px; color: var(--text2); margin-top: 2px; }
        .toggle-switch { position: relative; width: 42px; height: 24px; flex-shrink: 0; }
        .toggle-switch input { opacity: 0; width: 0; height: 0; }
        .toggle-slider {
            position: absolute; cursor: pointer;
            top: 0; left: 0; right: 0; bottom: 0;
            background: #d1d5db; border-radius: 24px;
            transition: 0.25s;
        }
        .toggle-slider:before {
            position: absolute; content: "";
            height: 18px; width: 18px; left: 3px; bottom: 3px;
            background: white; border-radius: 50%;
            transition: 0.25s; box-shadow: 0 1px 3px rgba(0,0,0,0.2);
        }
        input:checked + .toggle-slider { background: var(--accent); }
        input:checked + .toggle-slider:before { transform: translateX(18px); }

        .holiday-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.06);
            overflow: hidden;
            margin-bottom: 20px;
        }
        .holiday-header {
            padding: 18px 22px;
            border-bottom: 1px solid var(--border);
            display: flex; align-items: center; justify-content: space-between;
        }
        .holiday-add-form {
            padding: 16px 22px;
            background: #faf5ff;
            border-bottom: 1px solid var(--border);
            display: none;
        }
        .holiday-add-form.show { display: block; }
        .holiday-add-row { display: grid; grid-template-columns: 1fr 2fr auto; gap: 12px; align-items: end; }

        .holiday-item {
            display: flex; align-items: center;
            padding: 13px 22px;
            border-bottom: 1px solid #f9fafb;
            gap: 14px;
            transition: background 0.15s;
        }
        .holiday-item:last-child { border-bottom: none; }
        .holiday-item:hover { background: #faf5ff; }
        .holiday-date-badge {
            width: 48px; height: 54px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--accent), #7c3aed);
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            flex-shrink: 0; color: white;
        }
        .holiday-date-badge .hd-day  { font-size: 20px; font-weight: 800; line-height: 1; }
        .holiday-date-badge .hd-bulan{ font-size: 10px; font-weight: 600; opacity: 0.85; margin-top: 1px; }
        .holiday-date-badge.red-alert { background: linear-gradient(135deg, #dc2626, #991b1b); }
        .holiday-info { flex: 1; min-width: 0; }
        .holiday-info .hi-name { font-size: 13.5px; font-weight: 600; color: var(--text); }
        .holiday-info .hi-full  { font-size: 11.5px; color: var(--text2); margin-top: 2px; }
        .holiday-info .hi-days  { font-size: 11px; font-weight: 600; margin-top: 3px; }

        .save-bar {
            background: white;
            border-radius: 14px;
            padding: 16px 22px;
            display: flex; align-items: center; justify-content: space-between;
            box-shadow: 0 1px 4px rgba(0,0,0,0.06);
            margin-bottom: 20px;
            border-left: 4px solid var(--accent);
        }
        .save-bar-info { display: flex; align-items: center; gap: 10px; }
        .save-bar-info i { font-size: 20px; color: var(--accent); }
        .save-bar-info h5 { font-size: 14px; font-weight: 700; color: var(--text); margin: 0; }
        .save-bar-info p  { font-size: 12px; color: var(--text2); margin: 0; }

        .profile-card {
            background: linear-gradient(135deg, #1e1b4b 0%, #4f46e5 100%);
            border-radius: 16px;
            padding: 22px;
            color: white;
            margin-bottom: 20px;
            position: relative;
            overflow: hidden;
        }
        .profile-card::before {
            content: ''; position: absolute;
            top: -30px; right: -30px;
            width: 120px; height: 120px;
            border-radius: 50%;
            background: rgba(255,255,255,0.07);
        }
        .profile-card::after {
            content: ''; position: absolute;
            bottom: -20px; right: 40px;
            width: 80px; height: 80px;
            border-radius: 50%;
            background: rgba(255,255,255,0.05);
        }

        .confirm-overlay {
            display: none; position: fixed; inset: 0;
            background: rgba(0,0,0,0.5); z-index: 1100;
            align-items: center; justify-content: center;
        }
        .confirm-overlay.show { display: flex; }
        .confirm-box {
            background: white; border-radius: 16px;
            padding: 28px; width: 360px; text-align: center;
            box-shadow: 0 25px 60px rgba(0,0,0,0.2);
        }
        .confirm-icon { width: 60px; height: 60px; border-radius: 50%; background: #fee2e2; display: flex; align-items: center; justify-content: center; margin: 0 auto 14px; font-size: 26px; color: #dc2626; }

        .toast-container {
            position: fixed; bottom: 24px; right: 24px;
            z-index: 9999; display: flex;
            flex-direction: column; gap: 8px;
        }

        @media (max-width: 900px) {
            .settings-grid { grid-template-columns: 1fr; }
            .holiday-add-row { grid-template-columns: 1fr 1fr; }
            .holiday-add-row .btn-primary { grid-column: span 2; }
        }
    </style>
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
                    <h2>Pengaturan Sistem</h2>
                    <p>Konfigurasi jam absensi dan hari libur</p>
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
        </div>

        <div class="admin-content">

            <!-- PROFILE CARD -->
            <div class="profile-card">
                <div style="display:flex;align-items:center;gap:16px;position:relative;z-index:1;">
                    <div style="width:52px;height:52px;border-radius:14px;background:rgba(255,255,255,0.18);display:flex;align-items:center;justify-content:center;font-size:24px;flex-shrink:0;">
                        <i class="bi bi-gear-wide-connected"></i>
                    </div>
                    <div>
                        <h3 style="font-size:17px;font-weight:800;margin:0;"><?= htmlspecialchars($sekolahData['nama']) ?></h3>
                        <p style="font-size:12px;opacity:.75;margin:3px 0 0;">Tahun Ajaran <?= htmlspecialchars($sekolahData['tahun_ajar']) ?></p>
                    </div>
                    <div style="margin-left:auto;display:flex;gap:20px;text-align:center;">
                        <div>
                            <div style="font-size:22px;font-weight:800;" id="totalLibur"><?= count($hariLibur) ?></div>
                            <div style="font-size:11px;opacity:.7;">Hari Libur</div>
                        </div>
                        <div style="width:1px;background:rgba(255,255,255,0.2);"></div>
                        <div>
                            <div style="font-size:22px;font-weight:800;" id="jamMulaiDisplay">06:30</div>
                            <div style="font-size:11px;opacity:.7;">Jam Masuk</div>
                        </div>
                        <div style="width:1px;background:rgba(255,255,255,0.2);"></div>
                        <div>
                            <div style="font-size:22px;font-weight:800;" id="jamTutupDisplay">17:00</div>
                            <div style="font-size:11px;opacity:.7;">Jam Pulang</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SAVE BAR -->
            <div class="save-bar" id="saveBar" style="display:none;">
                <div class="save-bar-info">
                    <i class="bi bi-exclamation-circle-fill"></i>
                    <div>
                        <h5>Ada perubahan yang belum disimpan</h5>
                        <p>Klik "Simpan Semua Pengaturan" untuk menerapkan perubahan.</p>
                    </div>
                </div>
                <div style="display:flex;gap:8px;">
                    <button class="btn-secondary" onclick="resetForm()">
                        <i class="bi bi-arrow-counterclockwise"></i> Batal
                    </button>
                    <button class="btn-primary" onclick="simpanPengaturan()">
                        <i class="bi bi-check-lg"></i> Simpan Semua Pengaturan
                    </button>
                </div>
            </div>

            <!-- SETTINGS GRID -->
            <div class="settings-grid">

                <!-- JAM DATANG -->
                <div class="settings-card">
                    <div class="settings-card-header">
                        <div class="settings-card-icon" style="background:#ede9fe;">
                            <i class="bi bi-box-arrow-in-right" style="color:#7c3aed;"></i>
                        </div>
                        <div>
                            <h4>Jam Absensi Datang</h4>
                            <p>Konfigurasi waktu masuk siswa</p>
                        </div>
                    </div>
                    <div class="settings-card-body">
                        <div class="time-group">
                            <div class="time-field">
                                <label>Jam Mulai Absensi</label>
                                <div class="time-input-wrap">
                                    <i class="bi bi-clock"></i>
                                    <input type="time" class="time-input" id="jamMulaiDatang" value="06:30" onchange="markChanged(); updateDisplay()">
                                </div>
                                <div class="time-status">
                                    <div class="status-dot" style="background:#22c55e;"></div>
                                    <span style="color:#16a34a;">Absensi dibuka mulai jam ini</span>
                                </div>
                            </div>
                            <div class="time-field">
                                <label>Batas Tepat Waktu</label>
                                <div class="time-input-wrap">
                                    <i class="bi bi-clock-history"></i>
                                    <input type="time" class="time-input" id="batasTepat" value="07:00" onchange="markChanged(); updateTimeline()">
                                </div>
                                <div class="time-status">
                                    <div class="status-dot" style="background:#2563eb;"></div>
                                    <span style="color:#2563eb;">Dianggap hadir tepat waktu</span>
                                </div>
                            </div>
                        </div>
                        <div class="time-group">
                            <div class="time-field">
                                <label>Batas Terlambat</label>
                                <div class="time-input-wrap">
                                    <i class="bi bi-exclamation-clock"></i>
                                    <input type="time" class="time-input" id="batasTerlambat" value="08:00" onchange="markChanged(); updateTimeline()">
                                </div>
                                <div class="time-status">
                                    <div class="status-dot" style="background:#d97706;"></div>
                                    <span style="color:#d97706;">Di atas jam ini = terlambat</span>
                                </div>
                            </div>
                            <div class="time-field">
                                <label>Jam Tutup Absensi</label>
                                <div class="time-input-wrap">
                                    <i class="bi bi-lock-fill"></i>
                                    <input type="time" class="time-input" id="jamTutupDatang" value="10:00" onchange="markChanged(); updateTimeline()">
                                </div>
                                <div class="time-status">
                                    <div class="status-dot" style="background:#dc2626;"></div>
                                    <span style="color:#dc2626;">Absensi datang ditutup</span>
                                </div>
                            </div>
                        </div>

                        <!-- Timeline -->
                        <div style="margin-top:6px;padding:14px;background:#f5f3ff;border-radius:10px;">
                            <div style="font-size:11px;font-weight:600;color:var(--text2);margin-bottom:10px;text-transform:uppercase;letter-spacing:.5px;">Timeline Kehadiran</div>
                            <div style="display:flex;align-items:center;gap:0;position:relative;">
                                <div style="flex:1;height:6px;background:linear-gradient(90deg,#22c55e,#2563eb);border-radius:4px 0 0 4px;"></div>
                                <div style="flex:1;height:6px;background:linear-gradient(90deg,#2563eb,#d97706);"></div>
                                <div style="flex:1;height:6px;background:linear-gradient(90deg,#d97706,#dc2626);"></div>
                                <div style="flex:.5;height:6px;background:#e5e7eb;border-radius:0 4px 4px 0;"></div>
                            </div>
                            <div style="display:flex;justify-content:space-between;margin-top:6px;">
                                <span style="font-size:10px;color:var(--text2);" id="lbl1">06:30</span>
                                <span style="font-size:10px;color:var(--text2);" id="lbl2">07:00</span>
                                <span style="font-size:10px;color:var(--text2);" id="lbl3">08:00</span>
                                <span style="font-size:10px;color:var(--text2);" id="lbl4">10:00</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- JAM PULANG -->
                <div class="settings-card">
                    <div class="settings-card-header">
                        <div class="settings-card-icon" style="background:#dcfce7;">
                            <i class="bi bi-box-arrow-right" style="color:#16a34a;"></i>
                        </div>
                        <div>
                            <h4>Jam Absensi Pulang</h4>
                            <p>Konfigurasi waktu pulang siswa</p>
                        </div>
                    </div>
                    <div class="settings-card-body">
                        <div class="time-group">
                            <div class="time-field">
                                <label>Jam Mulai Absensi Pulang</label>
                                <div class="time-input-wrap">
                                    <i class="bi bi-clock"></i>
                                    <input type="time" class="time-input" id="jamMulaiPulang" value="15:00" onchange="markChanged(); updateDisplay()">
                                </div>
                                <div class="time-status">
                                    <div class="status-dot" style="background:#22c55e;"></div>
                                    <span style="color:#16a34a;">Siswa boleh pulang mulai jam ini</span>
                                </div>
                            </div>
                            <div class="time-field">
                                <label>Jam Tutup Absensi Pulang</label>
                                <div class="time-input-wrap">
                                    <i class="bi bi-lock-fill"></i>
                                    <input type="time" class="time-input" id="jamTutupPulang" value="17:00" onchange="markChanged(); updateDisplay()">
                                </div>
                                <div class="time-status">
                                    <div class="status-dot" style="background:#dc2626;"></div>
                                    <span style="color:#dc2626;">Absensi pulang ditutup</span>
                                </div>
                            </div>
                        </div>

                        <!-- Opsi tambahan -->
                        <div style="margin-top:6px;">
                            <div style="font-size:11px;font-weight:600;color:var(--text2);text-transform:uppercase;letter-spacing:.5px;margin-bottom:10px;">Opsi Tambahan</div>
                            <div class="toggle-row">
                                <div>
                                    <div class="toggle-label">Wajib Absen Pulang</div>
                                    <div class="toggle-desc">Siswa wajib scan QR saat pulang</div>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox" id="wajibPulang" checked onchange="markChanged()">
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                            <div class="toggle-row">
                                <div>
                                    <div class="toggle-label">Notifikasi Guru</div>
                                    <div class="toggle-desc">Kirim notifikasi ke guru saat siswa belum pulang</div>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox" id="notifGuru" onchange="markChanged()">
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                            <div class="toggle-row">
                                <div>
                                    <div class="toggle-label">Auto-Alpha Jika Tidak Hadir</div>
                                    <div class="toggle-desc">Otomatis tandai alpha setelah jam tutup</div>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox" id="autoAlpha" checked onchange="markChanged()">
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                            <div class="toggle-row">
                                <div>
                                    <div class="toggle-label">Absensi Sabtu</div>
                                    <div class="toggle-desc">Aktifkan absensi di hari Sabtu</div>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox" id="absenSabtu" onchange="markChanged()">
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TOLERANSI & NOTIFIKASI -->
                <div class="settings-card">
                    <div class="settings-card-header">
                        <div class="settings-card-icon" style="background:#dbeafe;">
                            <i class="bi bi-bell-fill" style="color:#2563eb;"></i>
                        </div>
                        <div>
                            <h4>Toleransi & Notifikasi</h4>
                            <p>Pengaturan toleransi dan peringatan</p>
                        </div>
                    </div>
                    <div class="settings-card-body">
                        <div class="form-group">
                            <label class="form-label">Toleransi Terlambat (menit)</label>
                            <div style="display:flex;align-items:center;gap:12px;">
                                <input type="range" id="toleransiSlider" min="0" max="30" value="10"
                                    style="flex:1;accent-color:var(--accent);" oninput="updateTolerasi(this.value); markChanged()">
                                <div style="background:#ede9fe;color:var(--accent);font-weight:800;font-size:16px;padding:6px 14px;border-radius:9px;min-width:52px;text-align:center;" id="tolerasiVal">10</div>
                            </div>
                            <div style="display:flex;justify-content:space-between;font-size:10.5px;color:var(--text2);margin-top:4px;">
                                <span>0 menit</span><span>15 menit</span><span>30 menit</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Batas Alpha Berturut-turut</label>
                            <div style="display:flex;align-items:center;gap:12px;">
                                <input type="number" class="form-control" id="batasAlpha" value="3" min="1" max="30" style="max-width:100px;" onchange="markChanged()">
                                <span style="font-size:13px;color:var(--text2);">hari → panggilan orang tua</span>
                            </div>
                        </div>
                        <div class="toggle-row">
                            <div>
                                <div class="toggle-label">Notif WhatsApp Orang Tua</div>
                                <div class="toggle-desc">Kirim WA jika siswa tidak hadir</div>
                            </div>
                            <label class="toggle-switch">
                                <input type="checkbox" id="notifWA" checked onchange="markChanged()">
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                        <div class="toggle-row">
                            <div>
                                <div class="toggle-label">Email Laporan Harian</div>
                                <div class="toggle-desc">Kirim rekap ke email kepala sekolah</div>
                            </div>
                            <label class="toggle-switch">
                                <input type="checkbox" id="emailLaporan" onchange="markChanged()">
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- INFO SEKOLAH -->
                <div class="settings-card">
                    <div class="settings-card-header">
                        <div class="settings-card-icon" style="background:#fef3c7;">
                            <i class="bi bi-building-fill" style="color:#d97706;"></i>
                        </div>
                        <div>
                            <h4>Informasi Sekolah</h4>
                            <p>Data identitas sekolah</p>
                        </div>
                    </div>
                    <div class="settings-card-body">
                        <div class="form-group">
                            <label class="form-label">Nama Sekolah</label>
                            <input type="text" class="form-control" id="namaSekolah" value="<?= htmlspecialchars($sekolahData['nama']) ?>" onchange="markChanged()">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Tahun Ajaran</label>
                            <input type="text" class="form-control" id="tahunAjaran" value="<?= htmlspecialchars($sekolahData['tahun_ajar']) ?>" onchange="markChanged()">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Kepala Sekolah</label>
                            <input type="text" class="form-control" id="kepalaSekolah" value="<?= htmlspecialchars($sekolahData['kepala']) ?>" onchange="markChanged()">
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label">Alamat Sekolah</label>
                            <textarea class="form-control" rows="2" id="alamatSekolah" onchange="markChanged()"><?= htmlspecialchars($sekolahData['alamat']) ?></textarea>
                        </div>
                    </div>
                </div>

            </div>

            <!-- HARI LIBUR -->
            <div class="holiday-card">
                <div class="holiday-header">
                    <div style="display:flex;align-items:center;gap:10px;">
                        <div style="width:38px;height:38px;border-radius:10px;background:#fef3c7;display:flex;align-items:center;justify-content:center;font-size:18px;color:#d97706;">
                            <i class="bi bi-calendar-x-fill"></i>
                        </div>
                        <div>
                            <h3 style="font-size:15px;font-weight:700;color:var(--text);margin:0;">Kelola Hari Libur</h3>
                            <p style="font-size:12px;color:var(--text2);margin:2px 0 0;"><span id="countLibur"><?= count($hariLibur) ?></span> hari libur terdaftar</p>
                        </div>
                    </div>
                    <button class="btn-primary" id="btnToggleAdd" onclick="toggleAddForm()">
                        <i class="bi bi-plus-lg"></i> Tambah Hari Libur
                    </button>
                </div>

                <!-- ADD FORM -->
                <div class="holiday-add-form" id="addForm">
                    <div style="font-size:12px;font-weight:600;color:var(--accent);margin-bottom:12px;text-transform:uppercase;letter-spacing:.5px;">
                        <i class="bi bi-plus-circle-fill" style="margin-right:5px;"></i>Tambah Hari Libur Baru
                    </div>
                    <div class="holiday-add-row">
                        <div>
                            <label style="display:block;font-size:11px;font-weight:600;color:var(--text2);text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px;">Tanggal</label>
                            <input type="date" class="form-control" id="inputTanggalLibur">
                        </div>
                        <div>
                            <label style="display:block;font-size:11px;font-weight:600;color:var(--text2);text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px;">Keterangan</label>
                            <input type="text" class="form-control" id="inputKetLibur" placeholder="Contoh: Hari Raya Idul Fitri">
                        </div>
                        <div style="display:flex;gap:8px;padding-top:20px;">
                            <button class="btn-primary" onclick="tambahHariLibur()">
                                <i class="bi bi-check-lg"></i> Tambah
                            </button>
                            <button class="btn-secondary" onclick="toggleAddForm()">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- LIST HARI LIBUR -->
                <div id="holidayList">
                <?php if (!empty($hariLibur)): ?>
                <?php foreach ($hariLibur as $h):
                    $d        = explode('-', $h['tanggal']);
                    $dateObj  = new DateTime($h['tanggal']);
                    $hariNama = ['Sunday'=>'Minggu','Monday'=>'Senin','Tuesday'=>'Selasa','Wednesday'=>'Rabu','Thursday'=>'Kamis','Friday'=>'Jumat','Saturday'=>'Sabtu'];
                    $hariStr  = $hariNama[$dateObj->format('l')];
                    $tglFormatted = $dateObj->format('d F Y');
                    $bulanStr = $bulanIndo[$d[1]] ?? $d[1];
                    $selisih  = ($dateObj->getTimestamp() - time()) / 86400;
                    $isRed    = $selisih < 30 && $selisih >= 0;
                    $isGrey   = $selisih < 0;
                    if ($isGrey) {
                        $daysLabel = 'Sudah lewat'; $daysColor = '#94a3b8';
                    } elseif ($isRed) {
                        $daysLabel = round($selisih) . ' hari lagi'; $daysColor = '#dc2626';
                    } else {
                        $daysLabel = round($selisih) . ' hari lagi'; $daysColor = '#16a34a';
                    }
                    $badgeClass = $isRed ? 'red-alert' : '';
                ?>
                <div class="holiday-item" id="holiday-<?= $h['id'] ?>">
                    <div class="holiday-date-badge <?= $badgeClass ?>">
                        <div class="hd-day"><?= $d[2] ?></div>
                        <div class="hd-bulan"><?= $bulanStr ?></div>
                    </div>
                    <div class="holiday-info">
                        <div class="hi-name"><?= htmlspecialchars($h['keterangan']) ?></div>
                        <div class="hi-full"><?= $hariStr ?>, <?= $tglFormatted ?></div>
                        <div class="hi-days" style="color:<?= $daysColor ?>;"><?= $daysLabel ?></div>
                    </div>
                    <button class="btn-danger btn-sm" onclick="konfirmasiHapusLibur(<?= $h['id'] ?>, '<?= htmlspecialchars($h['keterangan'], ENT_QUOTES) ?>')" title="Hapus">
                        <i class="bi bi-trash-fill"></i>
                    </button>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
                </div>

                <!-- Empty state -->
                <div id="emptyHoliday" style="display:<?= empty($hariLibur) ? 'block' : 'none' ?>;" class="empty-state">
                    <i class="bi bi-calendar-check"></i>
                    <p>Belum ada hari libur terdaftar</p>
                </div>
            </div>

            <!-- BOTTOM BUTTONS -->
            <div style="display:flex;justify-content:flex-end;gap:10px;padding-bottom:20px;">
                <button class="btn-secondary" onclick="resetForm()">
                    <i class="bi bi-arrow-counterclockwise"></i> Reset ke Default
                </button>
                <button class="btn-primary" onclick="simpanPengaturan()" style="padding:11px 28px;font-size:14px;">
                    <i class="bi bi-check-circle-fill"></i> Simpan Semua Pengaturan
                </button>
            </div>

        </div>
    </main>
</div>

<!-- KONFIRMASI HAPUS -->
<div class="confirm-overlay" id="confirmHapusLibur" onclick="if(event.target===this)closeConfirmLibur()">
    <div class="confirm-box">
        <div class="confirm-icon"><i class="bi bi-calendar-x-fill"></i></div>
        <h5 style="font-size:16px;font-weight:700;color:var(--text);margin-bottom:8px;">Hapus Hari Libur?</h5>
        <p style="font-size:13px;color:var(--text2);margin-bottom:20px;">
            <strong id="confirmNamaLibur" style="color:var(--text);"></strong><br>
            akan dihapus dari daftar hari libur.
        </p>
        <input type="hidden" id="confirmIdLibur">
        <div style="display:flex;gap:10px;justify-content:center;">
            <button class="btn-secondary" onclick="closeConfirmLibur()" style="flex:1;">Batal</button>
            <button class="btn-danger" onclick="hapusLibur()" style="flex:1;padding:9px;font-size:13px;">
                <i class="bi bi-trash-fill"></i> Ya, Hapus
            </button>
        </div>
    </div>
</div>

<!-- TOAST -->
<div class="toast-container" id="toastContainer"></div>

<script>
let hasChanges = false;

// ===== UTILS =====
function showToast(msg, type = 'success') {
    const container = document.getElementById('toastContainer');
    const toast = document.createElement('div');
    toast.style.cssText = `background:#1e1b4b;color:#fff;padding:13px 18px;border-radius:12px;font-size:13px;font-weight:500;display:flex;align-items:center;gap:10px;box-shadow:0 8px 24px rgba(0,0,0,.2);max-width:320px;border-left:4px solid ${type==='success'?'#22c55e':'#ef4444'};font-family:'Poppins',sans-serif;`;
    toast.innerHTML = `<i class="bi bi-${type==='success'?'check-circle-fill':'exclamation-circle-fill'}"></i> ${msg}`;
    container.appendChild(toast);
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transition = 'opacity .3s';
    }, 3000);
    setTimeout(() => toast.remove(), 3350);
}

function markChanged() {
    if (!hasChanges) {
        hasChanges = true;
        document.getElementById('saveBar').style.display = 'flex';
    }
}

function updateTolerasi(val) {
    document.getElementById('tolerasiVal').textContent = val;
}

function updateDisplay() {
    document.getElementById('jamMulaiDisplay').textContent = document.getElementById('jamMulaiDatang').value;
    document.getElementById('jamTutupDisplay').textContent = document.getElementById('jamTutupPulang').value;
}

function updateTimeline() {
    document.getElementById('lbl1').textContent = document.getElementById('jamMulaiDatang').value;
    document.getElementById('lbl2').textContent = document.getElementById('batasTepat').value;
    document.getElementById('lbl3').textContent = document.getElementById('batasTerlambat').value;
    document.getElementById('lbl4').textContent = document.getElementById('jamTutupDatang').value;
}

function resetForm() {
    hasChanges = false;
    document.getElementById('saveBar').style.display = 'none';
    location.reload();
}

async function simpanPengaturan() {
    const data = {
        jam_mulai_datang: document.getElementById('jamMulaiDatang').value,
        batas_tepat:      document.getElementById('batasTepat').value,
        batas_terlambat:  document.getElementById('batasTerlambat').value,
        jam_tutup_datang: document.getElementById('jamTutupDatang').value,
        jam_mulai_pulang: document.getElementById('jamMulaiPulang').value,
        jam_tutup_pulang: document.getElementById('jamTutupPulang').value,
        wajib_pulang:     document.getElementById('wajibPulang').checked,
        notif_guru:       document.getElementById('notifGuru').checked,
        auto_alpha:       document.getElementById('autoAlpha').checked,
        absen_sabtu:      document.getElementById('absenSabtu').checked,
        toleransi:        parseInt(document.getElementById('toleransiSlider').value),
        batas_alpha:      parseInt(document.getElementById('batasAlpha').value),
        notif_wa:         document.getElementById('notifWA').checked,
        email_laporan:    document.getElementById('emailLaporan').checked,
        nama_sekolah:     document.getElementById('namaSekolah').value,
        tahun_ajaran:     document.getElementById('tahunAjaran').value,
        kepala_sekolah:   document.getElementById('kepalaSekolah').value,
        alamat_sekolah:   document.getElementById('alamatSekolah').value,
    };

    try {
        const res = await fetch('?url=admin/pengaturan/save', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        const json = await res.json();
        if (json.success) {
            hasChanges = false;
            document.getElementById('saveBar').style.display = 'none';
            showToast('Semua pengaturan berhasil disimpan!');
        } else {
            showToast(json.message || 'Gagal menyimpan pengaturan', 'error');
        }
    } catch(e) {
        showToast('Koneksi ke server gagal', 'error');
    }
}

// ===== HARI LIBUR =====
function toggleAddForm() {
    const form = document.getElementById('addForm');
    const btn  = document.getElementById('btnToggleAdd');
    const open = form.classList.toggle('show');
    btn.innerHTML = open ? '<i class="bi bi-x-lg"></i> Tutup' : '<i class="bi bi-plus-lg"></i> Tambah Hari Libur';
    if (open) form.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

async function tambahHariLibur() {
    const tgl = document.getElementById('inputTanggalLibur').value;
    const ket = document.getElementById('inputKetLibur').value.trim();
    if (!tgl) { showToast('Pilih tanggal terlebih dahulu!', 'error'); return; }
    if (!ket) { showToast('Isi keterangan hari libur!', 'error'); return; }

    try {
        const res = await fetch('?url=admin/pengaturan/tambah-libur', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ tanggal: tgl, keterangan: ket })
        });
        const json = await res.json();
        if (json.success) {
            document.getElementById('inputTanggalLibur').value = '';
            document.getElementById('inputKetLibur').value = '';
            toggleAddForm();
            reloadHariLibur();
            showToast('Hari libur "' + ket + '" berhasil ditambahkan!');
        } else {
            showToast(json.message || 'Gagal menambahkan hari libur', 'error');
        }
    } catch(e) {
        showToast('Koneksi ke server gagal', 'error');
    }
}

function konfirmasiHapusLibur(id, nama) {
    document.getElementById('confirmIdLibur').value = id;
    document.getElementById('confirmNamaLibur').textContent = nama;
    document.getElementById('confirmHapusLibur').classList.add('show');
}

function closeConfirmLibur() {
    document.getElementById('confirmHapusLibur').classList.remove('show');
}

async function hapusLibur() {
    const id = document.getElementById('confirmIdLibur').value;
    closeConfirmLibur();

    try {
        const res = await fetch('?url=admin/pengaturan/hapus-libur', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id })
        });
        const json = await res.json();
        if (json.success) {
            reloadHariLibur();
            showToast('Hari libur berhasil dihapus');
        } else {
            showToast(json.message || 'Gagal menghapus hari libur', 'error');
        }
    } catch(e) {
        showToast('Koneksi ke server gagal', 'error');
    }
}

async function reloadHariLibur() {
    try {
        const res  = await fetch('?url=admin/pengaturan/get-libur');
        const json = await res.json();
        if (json.success) {
            document.getElementById('countLibur').textContent = json.data.length;
            document.getElementById('totalLibur').textContent = json.data.length;
            const list = document.getElementById('holidayList');
            list.innerHTML = '';

            const bulanMap = {'01':'Jan','02':'Feb','03':'Mar','04':'Apr','05':'Mei','06':'Jun',
                              '07':'Jul','08':'Agu','09':'Sep','10':'Okt','11':'Nov','12':'Des'};
            const hariMap  = {0:'Minggu',1:'Senin',2:'Selasa',3:'Rabu',4:'Kamis',5:'Jumat',6:'Sabtu'};

            json.data.forEach(h => {
                const d           = h.tanggal.split('-');
                const dateObj     = new Date(h.tanggal + 'T00:00:00');
                const hariStr     = hariMap[dateObj.getDay()];
                const tglFormatted = dateObj.toLocaleDateString('id-ID', {day:'2-digit',month:'long',year:'numeric'});
                const bulanStr    = bulanMap[d[1]] ?? d[1];
                const selisih     = (dateObj.getTime() - new Date().setHours(0,0,0,0)) / 86400000;
                const isRed       = selisih < 30 && selisih >= 0;
                const isGrey      = selisih < 0;
                const daysColor   = isGrey ? '#94a3b8' : (isRed ? '#dc2626' : '#16a34a');
                const daysLabel   = isGrey ? 'Sudah lewat' : Math.round(selisih) + ' hari lagi';
                const badgeClass  = isRed ? 'red-alert' : '';

                list.insertAdjacentHTML('beforeend', `
                <div class="holiday-item" id="holiday-${h.id}">
                    <div class="holiday-date-badge ${badgeClass}">
                        <div class="hd-day">${d[2]}</div>
                        <div class="hd-bulan">${bulanStr}</div>
                    </div>
                    <div class="holiday-info">
                        <div class="hi-name">${h.keterangan}</div>
                        <div class="hi-full">${hariStr}, ${tglFormatted}</div>
                        <div class="hi-days" style="color:${daysColor};">${daysLabel}</div>
                    </div>
                    <button class="btn-danger btn-sm" onclick="konfirmasiHapusLibur(${h.id}, '${h.keterangan.replace(/'/g,"\\'")}')" title="Hapus">
                        <i class="bi bi-trash-fill"></i>
                    </button>
                </div>`);
            });

            document.getElementById('emptyHoliday').style.display = json.data.length === 0 ? 'block' : 'none';
        }
    } catch(e) {
        console.error(e);
    }
}

// ESC close
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeConfirmLibur(); });

// Init
updateDisplay();
updateTimeline();
</script>

</body>
</html>