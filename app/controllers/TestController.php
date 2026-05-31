<?php

require_once __DIR__ . '/../../core/Database.php';

class TestController {

    public function index() {
        echo "<h2>Test Supabase</h2>";
        echo "<pre>";
        print_r(Database::request("GET", "guru"));
        echo "</pre>";
    }
}