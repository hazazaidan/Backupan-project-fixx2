<?php

class Database {

    /**
     * Request ke Supabase REST API
     */
    public static function request($method, $endpoint, $data = null) {

        // Rapihin URL
        $baseUrl = rtrim(SUPABASE_URL, '/');

        // Endpoint lengkap
        $url = $baseUrl . "/rest/v1/" . ltrim($endpoint, '/');

        // Header Supabase
        $headers = [
            "apikey: " . SUPABASE_KEY,
            "Authorization: Bearer " . SUPABASE_KEY,
            "Content-Type: application/json",
            "Accept: application/json",
            "Prefer: return=representation"
        ];

        // Init CURL
        $ch = curl_init();

        curl_setopt_array($ch, [

            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => strtoupper($method),
            CURLOPT_HTTPHEADER     => $headers,

            // Optional debug SSL localhost
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ]);

        // Jika ada data POST/PATCH/PUT
        if ($data !== null) {

            $jsonData = json_encode($data);

            curl_setopt(
                $ch,
                CURLOPT_POSTFIELDS,
                $jsonData
            );
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

        // Decode JSON response
        $decoded = json_decode($response, true);

        // Kalau gagal decode
        if ($decoded === null && !empty($response)) {

            $decoded = [
                "raw_response" => $response
            ];
        }

        // Kalau HTTP error
        if ($httpCode >= 400) {

            return [
                "error"      => true,
                "status"     => $httpCode,
                "url"        => $url,
                "method"     => $method,
                "response"   => $decoded,
                "raw"        => $response
            ];
        }

        // Success
        return $decoded;
    }
}