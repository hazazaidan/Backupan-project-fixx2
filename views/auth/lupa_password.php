<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Lupa Password – Absensi QR' ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --blue:      #2563eb;
            --blue-dark: #1d4ed8;
            --blue-soft: #eff6ff;
            --text:      #0f172a;
            --muted:     #64748b;
            --border:    #e2e8f0;
            --bg:        #f8fafc;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            min-height: 100vh;
            display: flex;
            background: var(--bg);
        }

        /* LEFT PANEL */
        .left-panel {
            flex: 1; position: relative; display: none;
        }
        @media(min-width:1024px){ .left-panel { display: block; } }
        .left-panel img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .left-panel::after {
            content: ''; position: absolute; inset: 0;
            background: linear-gradient(135deg, rgba(15,23,42,0.72) 0%, rgba(37,99,235,0.45) 100%);
        }
        .left-badge {
            position: absolute; bottom: 48px; left: 48px;
            z-index: 10; color: white;
            animation: slideUp 0.8s ease both;
        }
        .left-badge h2 { font-size: 32px; font-weight: 800; line-height: 1.2; margin-bottom: 8px; }
        .left-badge p  { font-size: 15px; opacity: 0.75; max-width: 320px; line-height: 1.6; }

        /* RIGHT PANEL */
        .right-panel {
            width: 100%; max-width: 520px;
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            padding: 52px 48px;
            background: white;
            position: relative; z-index: 1;
            box-shadow: -8px 0 40px rgba(0,0,0,0.08);
            overflow-y: auto;
        }
        .right-panel::before {
            content: ''; position: absolute; top: 0; left: 0; right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--blue-dark), var(--blue), #60a5fa);
        }

        .form-wrap { width: 100%; animation: fadeIn 0.5s ease both; }

        /* LOGO */
        .logo-ring {
            width: 68px; height: 68px; border-radius: 50%;
            background: linear-gradient(135deg, var(--blue-dark), #60a5fa);
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 24px;
            box-shadow: 0 8px 24px rgba(37,99,235,0.35);
            position: relative;
        }
        .logo-ring::after {
            content: ''; position: absolute; inset: -3px;
            border-radius: 50%; border: 2px solid rgba(37,99,235,0.25);
        }
        .logo-ring i { font-size: 26px; color: white; }

        .form-title { text-align: center; font-size: 24px; font-weight: 800; color: var(--text); letter-spacing: -0.5px; margin-bottom: 4px; }
        .form-sub   { text-align: center; font-size: 13px; color: var(--muted); margin-bottom: 36px; }

        /* STEP INDICATOR */
        .steps {
            display: flex; align-items: center; justify-content: center;
            gap: 0; margin-bottom: 32px;
        }
        .step {
            display: flex; flex-direction: column; align-items: center; gap: 6px;
        }
        .step-circle {
            width: 34px; height: 34px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 13px; font-weight: 700;
            border: 2px solid var(--border);
            color: var(--muted); background: white;
            transition: all 0.3s;
        }
        .step-circle.active  { background: var(--blue); border-color: var(--blue); color: white; }
        .step-circle.done    { background: #10b981; border-color: #10b981; color: white; }
        .step-label { font-size: 11px; color: var(--muted); font-weight: 600; }
        .step-label.active { color: var(--blue); }
        .step-line  { width: 60px; height: 2px; background: var(--border); margin-bottom: 20px; }
        .step-line.done { background: #10b981; }

        /* INPUT GROUP */
        .input-group { position: relative; margin-bottom: 18px; }
        .input-group i {
            position: absolute; left: 14px; top: 50%; transform: translateY(-50%);
            color: #94a3b8; font-size: 14px; pointer-events: none;
        }
        .input-group input {
            width: 100%; padding: 12px 14px 12px 40px;
            border: 1.5px solid var(--border); border-radius: 12px;
            background: var(--bg); font-family: inherit;
            font-size: 14px; color: var(--text); outline: none;
            transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
        }
        .input-group input:focus {
            border-color: var(--blue); background: white;
            box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
        }
        .input-group input::placeholder { color: #94a3b8; }

        /* BANNER */
        .info-banner {
            background: var(--blue-soft); border: 1px solid #bfdbfe;
            border-radius: 10px; padding: 12px 16px;
            font-size: 12px; color: #1d4ed8; margin-bottom: 26px;
            display: flex; gap: 8px; align-items: flex-start;
        }
        .info-banner i { margin-top: 1px; flex-shrink: 0; }

        .success-banner {
            background: #f0fdf4; border: 1px solid #86efac;
            border-radius: 10px; padding: 12px 16px;
            font-size: 12px; color: #16a34a; margin-bottom: 26px;
            display: flex; gap: 8px; align-items: flex-start;
        }

        .alert-error {
            background: #fee2e2; border: 1px solid #fca5a5;
            border-radius: 10px; padding: 10px 14px;
            font-size: 12px; color: #dc2626; margin-bottom: 16px;
            display: flex; gap: 8px; align-items: center;
        }

        /* BUTTON */
        .btn-submit {
            width: 100%; padding: 13px;
            background: linear-gradient(135deg, var(--blue-dark), var(--blue));
            color: white; border: none; border-radius: 12px;
            font-family: inherit; font-size: 15px; font-weight: 700;
            cursor: pointer; box-shadow: 0 4px 16px rgba(37,99,235,0.35);
            transition: all 0.2s;
        }
        .btn-submit:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(37,99,235,0.45); }
        .btn-submit:active { transform: translateY(0); }

        .btn-back {
            display: flex; align-items: center; justify-content: center; gap: 8px;
            width: 100%; padding: 11px;
            background: white; color: var(--muted);
            border: 1.5px solid var(--border); border-radius: 12px;
            font-family: inherit; font-size: 14px; font-weight: 600;
            cursor: pointer; margin-top: 12px;
            text-decoration: none; transition: all 0.2s;
        }
        .btn-back:hover { background: var(--bg); color: var(--text); }

        /* SUCCESS STATE */
        .success-state { text-align: center; }
        .success-icon {
            width: 80px; height: 80px; border-radius: 50%;
            background: #f0fdf4; border: 3px solid #86efac;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 24px; font-size: 36px; color: #16a34a;
        }

        .form-footer { text-align: center; font-size: 11px; color: #94a3b8; margin-top: 36px; }

        @keyframes fadeIn  { from { opacity: 0; transform: translateY(16px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes slideUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }

        /* dev hint */
        .dev-hint {
            background: #fefce8; border: 1px solid #fde047;
            border-radius: 10px; padding: 10px 14px;
            font-size: 11px; color: #854d0e; margin-top: 16px;
            word-break: break-all;
        }
    </style>
</head>
<body>

<!-- LEFT PANEL -->
<div class="left-panel">
    <img src="../assets/image/sekolah.jpeg" alt="Sekolah">
    <div class="left-badge">
        <h2>Sistem Absensi<br>Digital MAN 2</h2>
        <p>Catat kehadiran siswa secara real-time dengan teknologi QR Code yang cepat & akurat.</p>
    </div>
</div>

<!-- RIGHT PANEL -->
<div class="right-panel">
<div class="form-wrap">

    <div class="logo-ring">
        <i class="fa fa-key"></i>
    </div>
    <h1 class="form-title">Lupa Password</h1>
    <p class="form-sub">Reset akses ke Absensi QR</p>

    <!-- Step Indicator -->
    <div class="steps">
        <div class="step">
            <div class="step-circle <?= isset($_SESSION['lupa_success']) ? 'done' : 'active' ?>">
                <?= isset($_SESSION['lupa_success']) ? '<i class="fa fa-check" style="font-size:12px;"></i>' : '1' ?>
            </div>
            <span class="step-label <?= !isset($_SESSION['lupa_success']) ? 'active' : '' ?>">Email</span>
        </div>
        <div class="step-line <?= isset($_SESSION['lupa_success']) ? 'done' : '' ?>"></div>
        <div class="step">
            <div class="step-circle <?= isset($_SESSION['lupa_success']) ? 'active' : '' ?>">2</div>
            <span class="step-label <?= isset($_SESSION['lupa_success']) ? 'active' : '' ?>">Cek Email</span>
        </div>
    </div>

    <?php if (isset($_SESSION['lupa_success'])): ?>
        <!-- ===== SUCCESS STATE ===== -->
        <div class="success-state">
            <div class="success-icon">
                <i class="fa fa-envelope-circle-check"></i>
            </div>
            <h2 style="font-size:18px;font-weight:800;color:var(--text);margin-bottom:8px;">Email Terkirim!</h2>
            <p style="font-size:13px;color:var(--muted);line-height:1.7;margin-bottom:24px;">
                Kami telah mengirimkan link reset password ke email kamu.<br>
                Silakan cek <strong>inbox</strong> atau folder <strong>spam</strong>.<br>
                Link berlaku selama <strong>1 jam</strong>.
            </p>

            <?php
            // Dev hint — hapus di production
            if (isset($_SESSION['dev_reset_link'])): ?>
            <div class="dev-hint">
                <strong>🛠 Dev Mode – Link Reset:</strong><br>
                <a href="<?= $_SESSION['dev_reset_link'] ?>" style="color:#1d4ed8;">
                    <?= $_SESSION['dev_reset_link'] ?>
                </a>
            </div>
            <?php unset($_SESSION['dev_reset_link']); endif; ?>

            <?php unset($_SESSION['lupa_success']); ?>
        </div>

        <a href="?url=login" class="btn-back" style="margin-top:24px;">
            <i class="fa fa-arrow-left"></i> Kembali ke Login
        </a>

    <?php else: ?>
        <!-- ===== FORM EMAIL ===== -->
        <?php if (!empty($_SESSION['lupa_error'])): ?>
        <div class="alert-error">
            <i class="fa fa-circle-exclamation"></i>
            <?= $_SESSION['lupa_error']; unset($_SESSION['lupa_error']); ?>
        </div>
        <?php endif; ?>

        <div class="info-banner">
            <i class="fa fa-circle-info"></i>
            <span>Masukkan email yang terdaftar. Kami akan mengirimkan link reset password ke email kamu. Link berlaku <strong>1 jam</strong>.</span>
        </div>

        <form action="?url=lupa-password/proses" method="POST">
            <div class="input-group">
                <i class="fa fa-envelope"></i>
                <input type="email" name="email" placeholder="Email terdaftar" required autocomplete="email" autofocus>
            </div>

            <button type="submit" class="btn-submit">
                <i class="fa fa-paper-plane" style="margin-right:8px;"></i>Kirim Link Reset
            </button>
        </form>

        <a href="?url=login" class="btn-back">
            <i class="fa fa-arrow-left"></i> Kembali ke Login
        </a>

    <?php endif; ?>

    <p class="form-footer">© 2026 Sistem Absensi Sekolah · MAN 2 Banyumas</p>

</div>
</div>

</body>
</html>