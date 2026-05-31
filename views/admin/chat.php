<?php
// views/admin/chat.php
// Variabel: $pageTitle, $report, $messages, $jenis
$reportId = $report['id'] ?? '';
$title    = $report['title'] ?? '-';
$message  = $report['message'] ?? '-';
$status   = $report['status'] ?? 'pending';
$nama     = $report['students']['nama'] ?? $report['student_nis'] ?? '-';
$kelas    = $report['students']['kelas'] ?? '-';
$tgl      = isset($report['created_at']) ? date('d M Y H:i', strtotime($report['created_at'])) : '-';

$badgeClass = match($status) { 'accepted' => 'badge-success', 'rejected' => 'badge-danger', default => 'badge-warning' };
$badgeLabel = match($status) { 'accepted' => 'Diterima', 'rejected' => 'Ditolak', default => 'Pending' };
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat Keluhan — Admin</title>
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
                <button class="topbar-toggle" onclick="toggleSidebar()"><i class="bi bi-list"></i></button>
                <div class="topbar-title">
                    <h2>Chat Keluhan</h2>
                    <p>Balas laporan dari <?= htmlspecialchars($nama) ?></p>
                </div>
            </div>
            <div class="topbar-right">
                <a href="?url=admin/keluhan" class="btn-secondary" style="text-decoration:none;">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>
        </div>

        <!-- CONTENT -->
        <div class="admin-content" style="display:flex;gap:20px;align-items:flex-start;">

            <!-- INFO LAPORAN -->
            <div style="width:300px;flex-shrink:0;">
                <div class="table-card" style="padding:20px;">
                    <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;">
                        <div class="ava" style="background:#4f46e5;width:46px;height:46px;font-size:16px;flex-shrink:0;">
                            <?= strtoupper(substr($nama, 0, 2)) ?>
                        </div>
                        <div>
                            <div style="font-weight:700;font-size:14px;"><?= htmlspecialchars($nama) ?></div>
                            <div style="font-size:12px;color:var(--text2);"><?= htmlspecialchars($kelas) ?></div>
                        </div>
                    </div>

                    <div style="margin-bottom:10px;">
                        <span class="badge <?= $badgeClass ?>"><?= $badgeLabel ?></span>
                        <span style="font-size:11px;color:var(--text2);margin-left:6px;"><?= $tgl ?></span>
                    </div>

                    <div style="font-weight:700;font-size:14px;margin-bottom:6px;"><?= htmlspecialchars($title) ?></div>
                    <div style="font-size:13px;color:var(--text2);line-height:1.6;background:var(--bg);padding:12px;border-radius:9px;">
                        <?= nl2br(htmlspecialchars($message)) ?>
                    </div>

                    <?php if ($status === 'pending'): ?>
                        <div style="margin-top:16px;display:flex;flex-direction:column;gap:8px;">
                            <button class="btn-primary" style="width:100%;justify-content:center;"
                                onclick="updateStatus('<?= $reportId ?>','accepted','<?= $jenis ?>')">
                                <i class="bi bi-check-circle"></i> Terima Laporan
                            </button>
                            <button class="btn-danger" style="width:100%;justify-content:center;padding:9px;"
                                onclick="openModal('modalTolak')">
                                <i class="bi bi-x-circle"></i> Tolak Laporan
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- CHAT BOX -->
            <div style="flex:1;display:flex;flex-direction:column;min-height:0;">
                <div class="table-card" style="display:flex;flex-direction:column;height:calc(100vh - 160px);">

                    <!-- CHAT HEADER -->
                    <div style="padding:16px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:10px;">
                        <i class="bi bi-chat-dots-fill" style="color:var(--accent);font-size:18px;"></i>
                        <span style="font-weight:700;font-size:14px;">Percakapan</span>
                        <span style="font-size:12px;color:var(--text2);margin-left:4px;"><?= $jenis === 'ortu' ? 'Orang Tua' : 'Siswa' ?></span>
                    </div>

                    <!-- MESSAGES -->
                    <div id="chatMessages" style="flex:1;overflow-y:auto;padding:20px;display:flex;flex-direction:column;gap:12px;">
                        <?php if (empty($messages)): ?>
                            <div style="text-align:center;color:var(--text2);font-size:13px;margin:auto;">
                                <i class="bi bi-chat" style="font-size:32px;display:block;margin-bottom:8px;opacity:0.3;"></i>
                                Belum ada pesan
                            </div>
                        <?php else: ?>
                            <?php foreach ($messages as $msg): ?>
                                <?php $isAdmin = ($msg['sender'] ?? '') === 'admin'; ?>
                                <div style="display:flex;flex-direction:column;align-items:<?= $isAdmin ? 'flex-end' : 'flex-start' ?>;">
                                    <div style="
                                        max-width:70%;
                                        padding:10px 14px;
                                        border-radius:<?= $isAdmin ? '14px 14px 4px 14px' : '14px 14px 14px 4px' ?>;
                                        background:<?= $isAdmin ? 'var(--accent)' : 'var(--bg)' ?>;
                                        color:<?= $isAdmin ? 'white' : 'var(--text)' ?>;
                                        font-size:13px;
                                        line-height:1.5;
                                        box-shadow:0 1px 3px rgba(0,0,0,0.08);
                                    ">
                                        <?= nl2br(htmlspecialchars($msg['message'] ?? '')) ?>
                                    </div>
                                    <div style="font-size:10px;color:var(--text2);margin-top:4px;">
                                        <?= $isAdmin ? 'Admin' : htmlspecialchars($nama) ?>
                                        · <?= isset($msg['created_at']) ? date('d M H:i', strtotime($msg['created_at'])) : '' ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <!-- INPUT -->
                    <div style="padding:14px 20px;border-top:1px solid var(--border);display:flex;gap:10px;align-items:flex-end;">
                        <textarea id="chatInput" class="form-control" rows="2"
                            placeholder="Tulis pesan balasan..."
                            style="resize:none;flex:1;"
                            onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();sendMessage();}"></textarea>
                        <button class="btn-primary" onclick="sendMessage()" style="height:42px;padding:0 18px;">
                            <i class="bi bi-send-fill"></i>
                        </button>
                    </div>

                </div>
            </div>

        </div><!-- /.admin-content -->
    </div><!-- /.admin-main -->
</div><!-- /.admin-layout -->

<!-- MODAL TOLAK -->
<div class="modal-overlay" id="modalTolak">
    <div class="modal-box">
        <div class="modal-header">
            <h4>Tolak Laporan</h4>
            <button class="modal-close" onclick="closeModal('modalTolak')"><i class="bi bi-x"></i></button>
        </div>
        <div class="modal-body">
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

<script>
const REPORT_ID = '<?= $reportId ?>';
const JENIS     = '<?= $jenis ?>';
let lastId      = '<?= !empty($messages) ? end($messages)['id'] : '' ?>';

// Scroll ke bawah
function scrollBottom() {
    const el = document.getElementById('chatMessages');
    el.scrollTop = el.scrollHeight;
}
scrollBottom();

// Kirim pesan
function sendMessage() {
    const pesan = document.getElementById('chatInput').value.trim();
    if (!pesan) return;

    const fd = new FormData();
    fd.append('report_id', REPORT_ID);
    fd.append('jenis', JENIS);
    fd.append('pesan', pesan);

    fetch('?url=admin/keluhan/sendMessage', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            document.getElementById('chatInput').value = '';
            pollMessages();
        } else {
            showToast(data.message || 'Gagal kirim', 'error');
        }
    })
    .catch(() => showToast('Terjadi kesalahan', 'error'));
}

// Polling pesan baru
function pollMessages() {
    fetch(`?url=admin/keluhan/getMessages&report_id=${REPORT_ID}&jenis=${JENIS}&last_id=${lastId}`)
    .then(r => r.json())
    .then(data => {
        if (data.success && data.messages.length > 0) {
            const container = document.getElementById('chatMessages');
            // Hapus empty state jika ada
            const empty = container.querySelector('div[style*="margin:auto"]');
            if (empty) empty.remove();

            data.messages.forEach(msg => {
                const isAdmin = msg.sender === 'admin';
                const div = document.createElement('div');
                div.style.cssText = `display:flex;flex-direction:column;align-items:${isAdmin ? 'flex-end' : 'flex-start'};`;
                div.innerHTML = `
                    <div style="max-width:70%;padding:10px 14px;border-radius:${isAdmin ? '14px 14px 4px 14px' : '14px 14px 14px 4px'};background:${isAdmin ? 'var(--accent)' : 'var(--bg)'};color:${isAdmin ? 'white' : 'var(--text)'};font-size:13px;line-height:1.5;box-shadow:0 1px 3px rgba(0,0,0,0.08);">
                        ${escHtml(msg.message || '')}
                    </div>
                    <div style="font-size:10px;color:var(--text2);margin-top:4px;">
                        ${isAdmin ? 'Admin' : '<?= htmlspecialchars($nama) ?>'}
                        · ${msg.created_at ? new Date(msg.created_at).toLocaleString('id-ID',{day:'2-digit',month:'short',hour:'2-digit',minute:'2-digit'}) : ''}
                    </div>
                `;
                container.appendChild(div);
                lastId = msg.id;
            });
            scrollBottom();
        }
    });
}

function escHtml(str) {
    const d = document.createElement('div');
    d.textContent = str;
    return d.innerHTML;
}

// Update status
function updateStatus(id, status, jenis) {
    fetch('?url=admin/keluhan/updateStatus', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id, status, jenis })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(data.message || 'Gagal', 'error');
        }
    });
}

function submitTolak() {
    const catatan = document.getElementById('tolakCatatan').value;
    closeModal('modalTolak');
    updateStatus(REPORT_ID, 'rejected', JENIS);
}

// Auto polling setiap 5 detik
setInterval(pollMessages, 5000);
</script>

</body>
</html>