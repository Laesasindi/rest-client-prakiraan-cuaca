<?php
// Handle different execution contexts (web vs CLI)
$config_path = file_exists('../config.php') ? '../config.php' : 'config.php';
require_once $config_path;

// Fungsi untuk mendapatkan demo data forecast
function getDemoForecastData() {
    return [
        'list' => [
            [
                'dt' => time(),
                'main' => ['temp' => 28, 'temp_min' => 26, 'temp_max' => 30, 'humidity' => 75],
                'weather' => [['icon' => '02n', 'description' => 'sedikit berawan', 'main' => 'Clouds']],
                'wind' => ['speed' => 4.5],
                'pop' => 0.1
            ],
            [
                'dt' => time() + 10800,
                'main' => ['temp' => 27, 'temp_min' => 25, 'temp_max' => 29, 'humidity' => 78],
                'weather' => [['icon' => '03n', 'description' => 'awan tersebar', 'main' => 'Clouds']],
                'wind' => ['speed' => 3.8],
                'pop' => 0.05
            ],
            [
                'dt' => time() + 21600,
                'main' => ['temp' => 31, 'temp_min' => 28, 'temp_max' => 33, 'humidity' => 70],
                'weather' => [['icon' => '01d', 'description' => 'langit cerah', 'main' => 'Clear']],
                'wind' => ['speed' => 5.2],
                'pop' => 0.0
            ]
        ]
    ];
}

// Fungsi untuk mendapatkan forecast 5 hari (dengan data per 3 jam)
function getForecastData($lat = -6.2088, $lon = 106.8456, $cityName = null) {
    global $API_KEY, $FORECAST_URL;
    
    // Jika tidak ada API key yang valid, gunakan demo data
    if (!$API_KEY || $API_KEY === 'YOUR_API_KEY_HERE') {
        return getDemoForecastData();
    }
    
    $url = $FORECAST_URL . "?lat={$lat}&lon={$lon}&appid={$API_KEY}&units=metric&lang=id";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        return json_decode($response, true);
    }
    
    return getDemoForecastData();
}

// Ambil parameter kota dari URL, default Jakarta
$selectedCity = isset($_GET['city']) ? trim($_GET['city']) : 'Jakarta';

// Dapatkan koordinat kota
$cities = getIndonesianCities();
$cityCoords = null;

// Cari koordinat berdasarkan nama kota
foreach ($cities as $cityName => $coords) {
    if (strtolower($cityName) === strtolower($selectedCity)) {
        $cityCoords = $coords;
        break;
    }
}

// Jika kota tidak ditemukan di database, gunakan default Jakarta
if (!$cityCoords) {
    $cityCoords = $cities['Jakarta']; // Fallback ke Jakarta
}

$forecastData = getForecastData($cityCoords['lat'], $cityCoords['lon'], $selectedCity);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prakiraan Cuaca - Weather REST Client</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: url('https://images.unsplash.com/photo-1506905925346-21bda4d32df4?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80') center/cover no-repeat;
            min-height: 100vh;
            color: #333;
            position: relative;
        }
        
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.4);
            z-index: -1;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            color: white;
            margin-bottom: 30px;
        }
        
        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }
        
        .back-btn {
            display: inline-block;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 25px;
            margin-bottom: 30px;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        
        .back-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            text-decoration: none;
            color: white;
            transform: translateY(-2px);
        }
        
        .forecast-section {
            background: rgba(0, 0, 0, 0.7);
            border-radius: 20px;
            padding: 25px;
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
            margin-bottom: 30px;
        }
        
        .section-title {
            font-size: 1.3rem;
            margin-bottom: 15px;
            color: #74b9ff;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .forecast-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }
        
        .forecast-item {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            transition: all 0.3s ease;
        }
        
        .forecast-item:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-5px);
        }
        
        .forecast-time {
            font-size: 1rem;
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 10px;
        }
        
        .forecast-icon {
            width: 60px;
            height: 60px;
            margin: 10px auto;
        }
        
        .forecast-temp {
            font-size: 1.5rem;
            font-weight: bold;
            margin: 10px 0;
        }
        
        .forecast-desc {
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.8);
            text-transform: capitalize;
        }
        
        .error-message {
            background: rgba(255, 118, 117, 0.9);
            color: white;
            padding: 20px;
            border-radius: 15px;
            text-align: center;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }
            
            .header h1 {
                font-size: 2rem;
            }
            
            .forecast-section {
                padding: 15px;
            }
            
            .forecast-grid {
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
                gap: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <a href="../index.php" class="back-btn">← Kembali ke Home</a>
            <h1>🌤️ Prakiraan Cuaca</h1>
            <p>Prakiraan cuaca untuk <?= htmlspecialchars($selectedCity) ?></p>
        </div>
        
        <?php if ($forecastData): ?>
            <div class="forecast-section">
                <h2 class="section-title">
                    <span>📊</span> Prakiraan 3 Jam Ke Depan
                </h2>
                <div class="forecast-grid">
                    <?php foreach (array_slice($forecastData['list'], 0, 6) as $item): ?>
                        <div class="forecast-item">
                            <div class="forecast-time"><?= date('H:i', $item['dt']) ?></div>
                            <img src="https://openweathermap.org/img/wn/<?= $item['weather'][0]['icon'] ?>@2x.png" 
                                 alt="<?= $item['weather'][0]['description'] ?>" class="forecast-icon">
                            <div class="forecast-temp"><?= round($item['main']['temp']) ?>°C</div>
                            <div class="forecast-desc"><?= htmlspecialchars($item['weather'][0]['description']) ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="error-message">
                <h3>⚠️ Data Tidak Tersedia</h3>
                <p>Tidak dapat memuat data prakiraan cuaca. Pastikan API key sudah dikonfigurasi dengan benar.</p>
                <br>
                <small>Untuk menggunakan fitur ini, dapatkan API key gratis dari <a href="https://openweathermap.org/api" target="_blank" style="color: #74b9ff;">OpenWeatherMap</a></small>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>