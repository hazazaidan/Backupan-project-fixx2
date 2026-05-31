<?php

// Load config (yang ada dotenv & SUPABASE)
require_once __DIR__ . '/../config/database.php';

// Load Database class
require_once __DIR__ . '/../core/Database.php';

// Test ambil data dari Supabase
echo "<pre>";
print_r(Database::request("GET", "guru"));
echo "</pre>";