<?php

class Database {

    /**
     * Request ke Supabase REST API
     */
    public static function request($method, $endpoint, $data = null) {

        $method = strtoupper($method);

        // Rapihin URL
        $baseUrl = rtrim(SUPABASE_URL, '/');

        // Endpoint lengkap
        $url = $baseUrl . "/rest/v1/" . ltrim($endpoint, '/');

        $preferHeader = ($method === 'DELETE')
            ? "Prefer: return=minimal"
            : "Prefer: return=representation";

        // Header Supabase
        $headers = [
            "apikey: " . SUPABASE_KEY,
            "Authorization: Bearer " . SUPABASE_KEY,
            "Content-Type: application/json",
            "Accept: application/json",
            $preferHeader
        ];

        // Init CURL
        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ]);

        // Jika ada data POST/PATCH/PUT
        if ($data !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        // Eksekusi request
        $response = curl_exec($ch);

        // Error CURL
        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            return [
                "error"   => true,
                "type"    => "curl",
                "message" => $error
            ];
        }

        // HTTP Code
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 204 || ($httpCode >= 200 && $httpCode < 300 && trim($response) === '')) {
            return ['success' => true];
        }

        // Decode JSON response
        $decoded = json_decode($response, true);

        // Kalau gagal decode
        if ($decoded === null && !empty($response)) {
            $decoded = ["raw_response" => $response];
        }

        // Kalau HTTP error
        if ($httpCode >= 400) {
            return [
                "error"    => true,
                "status"   => $httpCode,
                "url"      => $url,
                "method"   => $method,
                "response" => $decoded,
                "raw"      => $response
            ];
        }


        if ($httpCode === 201) {
            return ['success' => true, 'data' => $decoded];
        }

        // Success
        return $decoded ?? ['success' => true];
    }
}