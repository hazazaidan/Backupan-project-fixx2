<?php

ob_start();

$GLOBALS['raw_input'] = file_get_contents('php://input');

ini_set('display_errors', 1);
error_reporting(E_ALL);

/*
|--------------------------------------------------------------------------
| BASE PATH
|--------------------------------------------------------------------------
*/
define('BASE_PATH', dirname(__DIR__));

/*
|--------------------------------------------------------------------------
| BASE URL
|--------------------------------------------------------------------------
| FIX:
| sebelumnya:
| /absensi-qr2/public
|
| sekarang:
| /absensi-qr2/public/
|
| supaya route:
| ?url=admin/doLogin
| tidak rusak saat POST form login
|--------------------------------------------------------------------------
*/
define('BASE_URL', '/absensi-qr2/public/');

/*
|--------------------------------------------------------------------------
| AUTOLOAD COMPOSER
|--------------------------------------------------------------------------
*/
require_once BASE_PATH . '/vendor/autoload.php';

use Dotenv\Dotenv;

/*
|--------------------------------------------------------------------------
| LOAD ENV
|--------------------------------------------------------------------------
*/
if (file_exists(BASE_PATH . '/.env')) {
    $dotenv = Dotenv::createImmutable(BASE_PATH);
    $dotenv->load();
} else {
    die('.env tidak ditemukan di root project');
}

/*
|--------------------------------------------------------------------------
| VALIDASI ENV
|--------------------------------------------------------------------------
*/
if (
    empty($_ENV['SUPABASE_URL']) ||
    empty($_ENV['SUPABASE_ANON_KEY']) ||
    empty($_ENV['SUPABASE_SERVICE_KEY'])
) {
    die("ENV Supabase belum lengkap. Cek file .env");
}

/*
|--------------------------------------------------------------------------
| DEFINE SUPABASE
|--------------------------------------------------------------------------
*/
define('SUPABASE_URL', rtrim($_ENV['SUPABASE_URL'], '/'));
define('SUPABASE_KEY', $_ENV['SUPABASE_SERVICE_KEY']);
define('SUPABASE_ANON', $_ENV['SUPABASE_ANON_KEY']);

/*
|--------------------------------------------------------------------------
| SESSION
|--------------------------------------------------------------------------
*/
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/*
|--------------------------------------------------------------------------
| LOAD APP
|--------------------------------------------------------------------------
*/
require_once BASE_PATH . '/app/core/App.php';

/*
|--------------------------------------------------------------------------
| RUN APPLICATION
|--------------------------------------------------------------------------
*/
new App();

ob_end_flush();