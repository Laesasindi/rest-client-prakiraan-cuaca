<?php
// get_weather.php - REST client untuk OpenWeatherMap API
// Set header JSON terlebih dahulu
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Disable error reporting untuk output bersih
error_reporting(0);
ini_set('display_errors', 0);

// Load config
$config_path = file_exists('config.php') ? 'config.php' : null;
if ($config_path && file_exists($config_path)) {
    require_once $config_path;
} else {
    // Fallback config jika file tidak ada
    $API_KEY = '3e0c7b48086358f5ce90a70eb1d5620f';
    $BASE_URL = "https://api.openweathermap.org/data/2.5/weather";
}

// Fungsi untuk validasi API key
function isValidApiKey($key) {
    return $key && strlen($key) > 10 && $key !== 'YOUR_API_KEY_HERE';
}

// Fungsi untuk demo data
function getDemoWeatherData($cityName) {
    $demoData = array(
        'Jakarta' => array(
            'coord' => array('lon' => 106.8456, 'lat' => -6.2088),
            'weather' => array(
                array('id' => 803, 'main' => 'Clouds', 'description' => 'awan tersebar', 'icon' => '04d')
            ),
            'main' => array(
                'temp' => 29.5,
                'feels_like' => 33.2,
                'temp_min' => 28.0,
                'temp_max' => 31.0,
                'pressure' => 1009,
                'humidity' => 74
            ),
            'wind' => array('speed' => 3.5, 'deg' => 180),
            'sys' => array('country' => 'ID'),
            'name' => 'Jakarta'
        ),
        'Surabaya' => array(
            'coord' => array('lon' => 112.7521, 'lat' => -7.2575),
            'weather' => array(
                array('id' => 800, 'main' => 'Clear', 'description' => 'langit cerah', 'icon' => '01d')
            ),
            'main' => array(
                'temp' => 31.2,
                'feels_like' => 35.8,
                'temp_min' => 30.0,
                'temp_max' => 33.0,
                'pressure' => 1008,
                'humidity' => 68
            ),
            'wind' => array('speed' => 4.2, 'deg' => 90),
            'sys' => array('country' => 'ID'),
            'name' => 'Surabaya'
        ),
        'Bandung' => array(
            'coord' => array('lon' => 107.6191, 'lat' => -6.9175),
            'weather' => array(
                array('id' => 802, 'main' => 'Clouds', 'description' => 'awan mendung', 'icon' => '03d')
            ),
            'main' => array(
                'temp' => 24.8,
                'feels_like' => 26.5,
                'temp_min' => 23.0,
                'temp_max' => 26.0,
                'pressure' => 1012,
                'humidity' => 78
            ),
            'wind' => array('speed' => 2.8, 'deg' => 270),
            'sys' => array('country' => 'ID'),
            'name' => 'Bandung'
        )
    );
    
    $normalizedCity = ucfirst(strtolower(trim($cityName)));
    
    // Cari kota yang cocok
    foreach ($demoData as $city => $data) {
        if (strtolower($city) === strtolower($normalizedCity)) {
            return $data;
        }
    }
    
    // Jika tidak ditemukan, buat data random
    return array(
        'coord' => array('lon' => 106.8456 + (rand(-100, 100) / 100), 'lat' => -6.2088 + (rand(-100, 100) / 100)),
        'weather' => array(
            array('id' => 803, 'main' => 'Clouds', 'description' => 'awan tersebar', 'icon' => '04d')
        ),
        'main' => array(
            'temp' => rand(24, 34),
            'feels_like' => rand(26, 38),
            'temp_min' => rand(22, 30),
            'temp_max' => rand(28, 36),
            'pressure' => rand(1005, 1015),
            'humidity' => rand(60, 85)
        ),
        'wind' => array('speed' => rand(2, 8), 'deg' => rand(0, 360)),
        'sys' => array('country' => 'ID'),
        'name' => $normalizedCity
    );
}

// Fungsi untuk fetch data dari OpenWeatherMap
function fetchWeatherFromAPI($cityName, $apiKey, $baseUrl) {
    $url = $baseUrl . "?q=" . urlencode($cityName) . "&appid=" . $apiKey . "&units=metric&lang=id";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Weather-App/1.0');
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    if ($curlError) {
        return array('error' => 'cURL Error: ' . $curlError);
    }
    
    if ($httpCode !== 200) {
        return array('error' => 'HTTP Error: ' . $httpCode);
    }
    
    $decodedResponse = json_decode($response, true);
    
    if (!$decodedResponse) {
        return array('error' => 'Invalid JSON response');
    }
    
    if (isset($decodedResponse['cod']) && $decodedResponse['cod'] !== 200) {
        return array('error' => 'API Error: ' . (isset($decodedResponse['message']) ? $decodedResponse['message'] : 'Unknown error'));
    }
    
    return $decodedResponse;
}

// Main logic
try {
    // Validasi parameter
    if (!isset($_GET['city']) || empty(trim($_GET['city']))) {
        echo json_encode(array(
            'success' => false,
            'message' => 'Parameter city tidak boleh kosong'
        ));
        exit;
    }
    
    $cityName = trim($_GET['city']);
    
    if (strlen($cityName) < 2) {
        echo json_encode(array(
            'success' => false,
            'message' => 'Nama kota terlalu pendek (minimal 2 karakter)'
        ));
        exit;
    }
    
    // Cek API key
    if (!isValidApiKey($API_KEY)) {
        // Gunakan demo data jika API key tidak valid
        $weatherData = getDemoWeatherData($cityName);
        echo json_encode(array(
            'success' => true,
            'weather' => $weatherData,
            'source' => 'demo'
        ));
        exit;
    }
    
    // Fetch dari API
    $weatherData = fetchWeatherFromAPI($cityName, $API_KEY, $BASE_URL);
    
    if (isset($weatherData['error'])) {
        // Jika API gagal, gunakan demo data
        $demoData = getDemoWeatherData($cityName);
        echo json_encode(array(
            'success' => true,
            'weather' => $demoData,
            'source' => 'demo',
            'api_error' => $weatherData['error']
        ));
        exit;
    }
    
    // Berhasil dari API
    echo json_encode(array(
        'success' => true,
        'weather' => $weatherData,
        'source' => 'api'
    ));
    
} catch (Exception $e) {
    echo json_encode(array(
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ));
} catch (Error $e) {
    echo json_encode(array(
        'success' => false,
        'message' => 'Fatal error: ' . $e->getMessage()
    ));
}
?>