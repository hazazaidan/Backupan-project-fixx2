<?php
if (session_status() === PHP_SESSION_NONE) session_start();

class Controller {

    protected function view($view, $data = []) {
        extract($data);

        $file = BASE_PATH . '/views/' . $view . '.php';

        if (file_exists($file)) {
            require_once $file;
        } else {
            die("View tidak ditemukan: " . $file);
        }
    }

    protected function redirect($url) {
        header('Location: ' . BASE_URL . '/?url=' . $url);
        exit;
    }

    protected function isLoggedIn() {
        return isset($_SESSION['user']);
    }

    protected function requireLogin() {
        if (!$this->isLoggedIn()) {
            $this->redirect('login');
        }
    }

    protected function requireRole($role) {
        $this->requireLogin();

        if (($_SESSION['user']['role'] ?? '') !== $role) {
            $this->redirect('login');
        }
    }

    // ── JSON response untuk AJAX ────────────
    protected function json($data, $code = 200) {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}