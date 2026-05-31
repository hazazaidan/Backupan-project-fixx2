<?php

/**
 * config/supabase.php
 * Konfigurasi koneksi ke Supabase via REST API
 *
 * Isi SUPABASE_URL dan SUPABASE_KEY sesuai project kalian:
 * Supabase Dashboard → Settings → API
 */

define('SUPABASE_URL',  'https://vcsyfgietumjcgjpxxxo.supabase.co');

// ⚠️  Ganti nilai ini dengan service_role key dari:
//     Supabase Dashboard → Settings → API → service_role (secret)
// JANGAN pakai anon key untuk backend – service_role yang bisa bypass RLS
define('SUPABASE_KEY',  'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InZjc3lmZ2lldHVtamNnanB4eHhvIiwicm9sZSI6InNlcnZpY2Vfcm9sZSIsImlhdCI6MTc3ODA0ODAwMSwiZXhwIjoyMDkzNjI0MDAxfQ.0J7ZDyflvzgrVAKWe2JJpNAoGEA4lkpbeCo2fJHWZS0');

// Anon key (untuk frontend / publik jika diperlukan)
define('SUPABASE_ANON', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InZjc3lmZ2lldHVtamNnanB4eHhvIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NzgwNDgwMDEsImV4cCI6MjA5MzYyNDAwMX0.JoTJhMS1Ya8nUwbxLd-J5YQde7L3Hz3uR6EamXXRoec');

/**
 * Helper: kirim request ke Supabase REST API
 *
 * @param string $endpoint  Contoh: '/rest/v1/admins'
 * @param string $method    GET | POST | PATCH | DELETE
 * @param array  $body      Data body (untuk POST/PATCH)
 * @param array  $headers   Header tambahan
 * @return array            ['status' => int, 'data' => array|null, 'error' => string|null]
 */
function supabaseRequest(string $endpoint, string $method = 'GET', array $body = [], array $headers = []): array
{
    $url = SUPABASE_URL . $endpoint;

    $defaultHeaders = [
        'apikey: '        . SUPABASE_KEY,
        'Authorization: Bearer ' . SUPABASE_KEY,
        'Content-Type: application/json',
        'Accept: application/json',
        'Prefer: return=representation',   // agar POST/PATCH mengembalikan data
    ];

    $allHeaders = array_merge($defaultHeaders, $headers);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => strtoupper($method),
        CURLOPT_HTTPHEADER     => $allHeaders,
        CURLOPT_TIMEOUT        => 10,
    ]);

    if (!empty($body)) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
    }

    $response   = curl_exec($ch);
    $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError  = curl_error($ch);
    curl_close($ch);

    if ($curlError) {
        return ['status' => 0, 'data' => null, 'error' => $curlError];
    }

    $decoded = json_decode($response, true);

    return [
        'status' => $httpStatus,
        'data'   => $decoded,
        'error'  => ($httpStatus >= 400) ? ($decoded['message'] ?? 'Supabase error') : null,
    ];
}

/**
 * Shortcut: query SELECT dengan filter
 *
 * @param string $table   Nama tabel, misal 'admins'
 * @param array  $params  Query params Supabase (eq, select, order, dll)
 *                        Contoh: ['username=eq.superadmin', 'select=*']
 */
function supabaseSelect(string $table, array $params = []): array
{
    $query = !empty($params) ? '?' . implode('&', $params) : '';
    return supabaseRequest("/rest/v1/{$table}{$query}", 'GET');
}

/**
 * Shortcut: INSERT satu baris
 */
function supabaseInsert(string $table, array $data): array
{
    return supabaseRequest("/rest/v1/{$table}", 'POST', $data);
}

/**
 * Shortcut: UPDATE dengan filter
 *
 * @param string $table   Nama tabel
 * @param string $filter  Filter string, misal 'id=eq.5'
 * @param array  $data    Field yang mau diupdate
 */
function supabaseUpdate(string $table, string $filter, array $data): array
{
    return supabaseRequest("/rest/v1/{$table}?{$filter}", 'PATCH', $data);
}

/**
 * Shortcut: DELETE dengan filter
 */
function supabaseDelete(string $table, string $filter): array
{
    return supabaseRequest("/rest/v1/{$table}?{$filter}", 'DELETE');
}