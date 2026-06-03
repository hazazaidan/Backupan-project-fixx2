<?php

require_once BASE_PATH . '/app/core/Database.php';

class KeluhanController
{
    // =========================================================
    //  AUTH HELPER
    // =========================================================

    private function requireAuth(): void
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'admin') {
            header('Location: ' . BASE_URL . '/?url=login');
            exit;
        }
    }

    private function view(string $view, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        $viewFile = BASE_PATH . '/views/' . $view . '.php';
        if (!file_exists($viewFile)) {
            http_response_code(404);
            die("View tidak ditemukan: <code>{$view}</code>");
        }
        require_once $viewFile;
    }

    private function json(array $data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    // =========================================================
    //  HALAMAN UTAMA KELUHAN (admin)
    // =========================================================

    public function index(): void
    {
        $this->requireAuth();

        $status = $_GET['status'] ?? '';
        $jenis  = $_GET['jenis']  ?? '';

        $queryReports = 'reports?select=id,title,message,status,description,created_at,student_nis,students(nama,kelas)&order=created_at.desc';
        if ($status) $queryReports .= '&status=eq.' . urlencode($status);

        $queryParent = 'parent_reports?select=id,title,message,status,created_at,student_nis,students(nama,kelas)&order=created_at.desc';
        if ($status) $queryParent .= '&status=eq.' . urlencode($status);

        $reports       = [];
        $parentReports = [];

        if (!$jenis || $jenis === 'siswa') {
            $raw = Database::request('GET', $queryReports);
            $reports = (!empty($raw) && !isset($raw['error'])) ? $raw : [];
        }

        if (!$jenis || $jenis === 'ortu') {
            $raw = Database::request('GET', $queryParent);
            $parentReports = (!empty($raw) && !isset($raw['error'])) ? $raw : [];
        }

        $countSiswa  = Database::request('GET', 'reports?select=status');
        $countParent = Database::request('GET', 'parent_reports?select=status');

        $stats = ['pending' => 0, 'accepted' => 0, 'rejected' => 0, 'total' => 0];
        foreach (array_merge(
            (!empty($countSiswa)  && !isset($countSiswa['error']))  ? $countSiswa  : [],
            (!empty($countParent) && !isset($countParent['error'])) ? $countParent : []
        ) as $row) {
            $s = $row['status'] ?? 'pending';
            $stats[$s] = ($stats[$s] ?? 0) + 1;
            $stats['total']++;
        }

        $this->view('admin/keluhan', [
            'pageTitle'     => 'Keluhan & Laporan',
            'reports'       => $reports,
            'parentReports' => $parentReports,
            'filterStatus'  => $status,
            'filterJenis'   => $jenis,
            'stats'         => $stats,
        ]);
    }

    // =========================================================
    //  UPDATE STATUS (ACCEPT / REJECT / PENDING)
    // =========================================================

    public function updateStatus(): void
    {
        $this->requireAuth();

        $body    = json_decode(file_get_contents('php://input'), true) ?? [];
        $id      = trim($body['id']      ?? '');
        $status  = trim($body['status']  ?? '');
        $jenis   = trim($body['jenis']   ?? 'siswa');
        $catatan = trim($body['catatan'] ?? '');

        if (!$id || !in_array($status, ['accepted', 'rejected', 'pending'])) {
            $this->json(['success' => false, 'message' => 'Parameter tidak valid']);
        }

        $tabel = $jenis === 'ortu' ? 'parent_reports' : 'reports';

        $payload = ['status' => $status];
        if ($catatan) $payload['description'] = $catatan;

        $result = Database::request('PATCH', $tabel . '?id=eq.' . urlencode($id), $payload);

        if (isset($result['error'])) {
            $this->json(['success' => false, 'message' => 'Gagal memperbarui status']);
        }

        $label = match($status) {
            'accepted' => 'diterima',
            'rejected' => 'ditolak',
            default    => 'diubah ke pending',
        };
        $this->json(['success' => true, 'message' => "Laporan berhasil {$label}"]);
    }

    // =========================================================
    //  CHAT — tampilkan halaman
    // =========================================================

    public function chat(): void
    {
        $this->requireAuth();

        $id    = $_GET['id']    ?? '';
        $jenis = $_GET['jenis'] ?? 'siswa';

        if (!$id) {
            header('Location: ' . BASE_URL . '/?url=admin/keluhan');
            exit;
        }

        if ($jenis === 'ortu') {
            $report = Database::request('GET',
                'parent_reports?id=eq.' . urlencode($id) .
                '&select=id,title,message,status,created_at,student_nis,students(nama,kelas)&limit=1'
            );
            $report = (!empty($report) && !isset($report['error'])) ? $report[0] : null;

            $messages = Database::request('GET',
                'parent_report_messages?report_id=eq.' . urlencode($id) .
                '&order=created_at.asc'
            );
        } else {
            $report = Database::request('GET',
                'reports?id=eq.' . urlencode($id) .
                '&select=id,title,message,status,description,created_at,student_nis,students(nama,kelas)&limit=1'
            );
            $report = (!empty($report) && !isset($report['error'])) ? $report[0] : null;

            $messages = Database::request('GET',
                'report_messages?report_id=eq.' . urlencode($id) .
                '&order=created_at.asc'
            );
        }

        if (!$report) {
            header('Location: ' . BASE_URL . '/?url=admin/keluhan');
            exit;
        }

        $messages = (!empty($messages) && !isset($messages['error'])) ? $messages : [];

        $this->view('admin/chat', [
            'pageTitle' => 'Chat Keluhan',
            'report'    => $report,
            'messages'  => $messages,
            'jenis'     => $jenis,
        ]);
    }

    // =========================================================
    //  SEND MESSAGE (AJAX)
    // =========================================================

    public function sendMessage(): void
    {
        $this->requireAuth();

        $reportId = trim($_POST['report_id'] ?? '');
        $jenis    = trim($_POST['jenis']     ?? 'siswa');
        $pesan    = trim($_POST['pesan']     ?? '');
        $fileUrl  = null;
        $fileName = null;
        $fileType = null;

        if (!$reportId) {
            $this->json(['success' => false, 'message' => 'Report ID tidak valid']);
        }

        if (!empty($_FILES['file']['name'])) {
            $allowedTypes = ['application/pdf', 'image/jpeg', 'image/png',
                             'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
            $allowedExts  = ['pdf', 'jpg', 'jpeg', 'png', 'docx'];
            $maxSize      = 5 * 1024 * 1024;

            $file     = $_FILES['file'];
            $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $mimeType = mime_content_type($file['tmp_name']);

            if ($file['size'] > $maxSize) {
                $this->json(['success' => false, 'message' => 'Ukuran file maksimal 5MB']);
            }

            if (!in_array($ext, $allowedExts) || !in_array($mimeType, $allowedTypes)) {
                $this->json(['success' => false, 'message' => 'Format file tidak diizinkan (PDF/JPG/PNG/DOCX)']);
            }

            $uploadDir = BASE_PATH . '/public/uploads/keluhan/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

            $newName = uniqid('keluhan_') . '.' . $ext;
            $dest    = $uploadDir . $newName;

            if (!move_uploaded_file($file['tmp_name'], $dest)) {
                $this->json(['success' => false, 'message' => 'Gagal mengupload file']);
            }

            $fileUrl  = BASE_URL . 'uploads/keluhan/' . $newName;
            $fileName = $file['name'];
            $fileType = $ext;
        }

        if (!$pesan && !$fileUrl) {
            $this->json(['success' => false, 'message' => 'Pesan atau file harus diisi']);
        }

        if ($jenis === 'ortu') {
            $payload = [
                'report_id' => $reportId,
                'sender'    => 'admin',
                'message'   => $pesan ?: ($fileName ?? ''),
            ];
            $result = Database::request('POST', 'parent_report_messages', $payload);
        } else {
            $payload = [
                'report_id' => $reportId,
                'sender'    => 'admin',
                'message'   => $pesan ?: ($fileName ?? ''),
            ];
            $result = Database::request('POST', 'report_messages', $payload);
        }

        if (isset($result['error'])) {
            $this->json(['success' => false, 'message' => 'Gagal mengirim pesan']);
        }

        $this->json([
            'success'   => true,
            'message'   => 'Pesan terkirim',
            'file_url'  => $fileUrl,
            'file_name' => $fileName,
        ]);
    }

    // =========================================================
    //  POLLING PESAN BARU (AJAX)
    // =========================================================

    public function getMessages(): void
    {
        $this->requireAuth();

        $reportId = trim($_GET['report_id'] ?? '');
        $jenis    = trim($_GET['jenis']     ?? 'siswa');
        $lastId   = trim($_GET['last_id']   ?? '');

        if (!$reportId) $this->json(['success' => false, 'message' => 'ID tidak valid']);

        $tabel = $jenis === 'ortu' ? 'parent_report_messages' : 'report_messages';
        $query = $tabel . '?report_id=eq.' . urlencode($reportId) . '&order=created_at.asc';
        if ($lastId) $query .= '&id=gt.' . urlencode($lastId);

        $messages = Database::request('GET', $query);
        $messages = (!empty($messages) && !isset($messages['error'])) ? $messages : [];

        $this->json(['success' => true, 'messages' => $messages]);
    }

    // =========================================================
    //  DOWNLOAD FILE
    // =========================================================

    public function downloadFile(): void
    {
        $this->requireAuth();

        $filename = basename($_GET['file'] ?? '');
        $path     = BASE_PATH . '/public/uploads/keluhan/' . $filename;

        if (!$filename || !file_exists($path)) {
            http_response_code(404);
            die('File tidak ditemukan');
        }

        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if (!in_array($ext, ['pdf', 'jpg', 'jpeg', 'png', 'docx'])) {
            http_response_code(403);
            die('Akses ditolak');
        }

        $mime = [
            'pdf'  => 'application/pdf',
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png'  => 'image/png',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ];

        header('Content-Type: ' . ($mime[$ext] ?? 'application/octet-stream'));
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($path));
        readfile($path);
        exit;
    }
}