<?php

namespace Facil\Support;

class Fetch {
    public static function request(string $method, string $url, array $data = [], array $headers = []): array {
        $ch = curl_init();
        
        $defaultHeaders = ['Content-Type: application/json'];
        $mergedHeaders = array_merge($defaultHeaders, $headers);

        $options = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => strtoupper($method),
            CURLOPT_HTTPHEADER => $mergedHeaders,
            // Descomente abaixo em produção se tiver problemas com SSL local
            // CURLOPT_SSL_VERIFYPEER => false, 
        ];

        if (!empty($data) && strtoupper($method) !== 'GET') {
            $options[CURLOPT_POSTFIELDS] = json_encode($data);
        }

        curl_setopt_array($ch, $options);
        $response = curl_exec($ch);
        $error = curl_error($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($error) {
            throw new \Exception("Fetch Error: $error");
        }

        $decoded = json_decode($response, true);
        
        return [
            'status' => $statusCode,
            'data' => $decoded ?? $response // Retorna array se for JSON, ou string se for HTML/texto
        ];
    }

    public static function get(string $url, array $headers = []): array {
        return self::request('GET', $url, [], $headers);
    }

    public static function post(string $url, array $data = [], array $headers = []): array {
        return self::request('POST', $url, $data, $headers);
    }
    
    public static function put(string $url, array $data = [], array $headers = []): array {
        return self::request('PUT', $url, $data, $headers);
    }

    public static function delete(string $url, array $headers = []): array {
        return self::request('DELETE', $url, [], $headers);
    }
}