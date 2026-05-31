<?php

class Database {

    public static function request(
        string $method,
        string $endpoint,
        array $data = null
    ) {

        $url = rtrim(SUPABASE_URL, '/') . '/rest/v1/' . ltrim($endpoint, '/');

        $headers = [
            'apikey: ' . SUPABASE_KEY,
            'Authorization: Bearer ' . SUPABASE_KEY,
            'Content-Type: application/json',
            'Prefer: return=representation'
        ];

        $ch = curl_init();

        curl_setopt_array($ch, [

            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => strtoupper($method),

            CURLOPT_HTTPHEADER => $headers,

            CURLOPT_TIMEOUT => 30,

            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ]);

        // Request body
        if ($data !== null) {

            curl_setopt(
                $ch,
                CURLOPT_POSTFIELDS,
                json_encode($data)
            );
        }

        $response = curl_exec($ch);

        // Error curl
        if (curl_errno($ch)) {

            $error = curl_error($ch);

            curl_close($ch);

            return [
                'error' => true,
                'message' => $error
            ];
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        $decoded = json_decode($response, true);

        // Error Supabase
        if ($httpCode >= 400) {

            return [
                'error' => true,
                'status' => $httpCode,
                'response' => $decoded
            ];
        }

        return $decoded;
    }
}