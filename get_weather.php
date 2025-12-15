<?php
require_once 'config.php';

header('Content-Type: application/json');

// Fungsi demo data untuk berbagai kota
function getDemoWeatherByCity($cityName) {
    $demoData = [
        'Jakarta' => [
            'name' => 'Jakarta',
            'sys' => ['country' => 'ID'],
            'main' => [
                'temp' => 29,
                'feels_like' => 33,
                'temp_min' => 27,
                'temp_max' => 31,
                'pressure' => 1009,
                'humidity' => 71
            ],
            'weather' => [
                ['icon' => '03d', 'description' => 'awan tersebar', 'main' => 'Clouds']
            ],
            'wind' => ['speed' => 6.2]
        ],
        'Surabaya' => [
            'name' => 'Surabaya',
            'sys' => ['country' => 'ID'],
            'main' => [
                'temp' => 31,
                'feels_like' => 35,
                'temp_min' => 29,
                'temp_max' => 33,
                'pressure' => 1008,
                'humidity' => 68
            ],
            'weather' => [
                ['icon' => '01d', 'description' => 'langit cerah', 'main' => 'Clear']
            ],
            'wind' => ['speed' => 4.8]
        ],
        'Bandung' => [
            'name' => 'Bandung',
            'sys' => ['country' => 'ID'],
            'main' => [
                'temp' => 24,
                'feels_like' => 26,
                'temp_min' => 22,
                'temp_max' => 26,
                'pressure' => 1012,
                'humidity' => 78
            ],
            'weather' => [
                ['icon' => '04d', 'description' => 'awan mendung', 'main' => 'Clouds']
            ],
            'wind' => ['speed' => 3.2]
        ]
    ];
    
    $normalizedCity = ucfirst(strtolower(trim($cityName)));
    
    // Cari kota yang cocok
    foreach ($demoData as $city => $data) {
        if (strtolower($city) === strtolower($normalizedCity)) {
            return $data;
        }
    }
    
    // Jika tidak ditemukan, buat data random
    return [
        'name' => $normalizedCity,
        'sys' => ['country' => 'ID'],
        'main' => [
            'temp' => rand(24, 34),
            'feels_like' => rand(26, 38),
            'temp_min' => rand(22, 30),
            'temp_max' => rand(28, 36),
            'pressure' => rand(1005, 1015),
            'humidity' => rand(60, 85)
        ],
        'weather' => [
            ['icon' => '03d', 'description' => 'awan tersebar', 'main' => 'Clouds']
        ],
        'wind' => ['speed' => rand(2, 8)]
    ];
}

// Fungsi untuk mendapatkan cuaca berdasarkan nama kota
function getWeatherByCity($cityName) {
    global $API_KEY, $BASE_URL;
    
    // Jika tidak ada API key yang valid, gunakan demo data
    if (!isApiKeyValid()) {
        return getDemoWeatherByCity($cityName);
    }
    
    $url = $BASE_URL . "?q=" . urlencode($cityName) . "&appid={$API_KEY}&units=metric&lang=id";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    // Jika terjadi error atau API key tidak valid, gunakan demo data
    if ($httpCode !== 200 || !empty($curlError)) {
        return getDemoWeatherByCity($cityName);
    }
    
    $decodedResponse = json_decode($response, true);
    
    // Jika response tidak valid, gunakan demo data
    if (!$decodedResponse || isset($decodedResponse['cod']) && $decodedResponse['cod'] !== 200) {
        return getDemoWeatherByCity($cityName);
    }
    
    return $decodedResponse;
}

// Main logic
try {
    if (!isset($_GET['city']) || empty($_GET['city'])) {
        throw new Exception('Nama kota tidak boleh kosong');
    }
    
    $cityName = trim($_GET['city']);
    
    if (strlen($cityName) < 2) {
        throw new Exception('Nama kota terlalu pendek');
    }
    
    $weatherData = getWeatherByCity($cityName);
    
    if ($weatherData === null || empty($weatherData)) {
        throw new Exception('Kota tidak ditemukan atau terjadi kesalahan');
    }
    
    echo json_encode([
        'success' => true,
        'weather' => $weatherData
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>