<?php
require_once 'config.php';

// Fungsi untuk mendapatkan demo data cuaca
function getDemoWeatherData() {
    return [
        'name' => 'Jakarta',
        'sys' => ['country' => 'ID'],
        'main' => [
            'temp' => 28,
            'feels_like' => 31,
            'temp_min' => 26,
            'temp_max' => 30,
            'pressure' => 1008,
            'humidity' => 74
        ],
        'weather' => [
            [
                'icon' => '02n',
                'description' => 'sedikit berawan'
            ]
        ],
        'wind' => ['speed' => 4.12]
    ];
}

// Fungsi untuk mendapatkan cuaca berdasarkan koordinat (contoh Jakarta)
function getCurrentWeather($lat = -6.2088, $lon = 106.8456) {
    global $API_KEY, $BASE_URL;
    
    // Jika tidak ada API key yang valid, gunakan demo data
    if (!$API_KEY || $API_KEY === 'YOUR_API_KEY_HERE') {
        return getDemoWeatherData();
    }
    
    $url = $BASE_URL . "?lat={$lat}&lon={$lon}&appid={$API_KEY}&units=metric&lang=id";
    
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
    
    return getDemoWeatherData();
}

$currentWeather = getCurrentWeather();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Weather REST Client - Aplikasi Cuaca</title>
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
            background: rgba(0, 0, 0, 0.3);
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
            margin-bottom: 40px;
        }
        
        .header h1 {
            font-size: 3rem;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .header p {
            font-size: 1.2rem;
            opacity: 0.9;
        }
        
        .weather-card {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 30px;
            margin: 30px auto;
            max-width: 400px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.4);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
            text-align: center;
        }
        
        .weather-location {
            font-size: 2rem;
            font-weight: 600;
            margin-bottom: 20px;
        }
        
        .weather-icon {
            width: 100px;
            height: 100px;
            margin: 20px auto;
            filter: drop-shadow(0 4px 8px rgba(0,0,0,0.3));
        }
        
        .temperature {
            font-size: 3rem;
            font-weight: 300;
            margin: 20px 0;
        }
        
        .weather-desc {
            font-size: 1.2rem;
            margin-bottom: 20px;
            text-transform: capitalize;
        }
        
        .weather-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .detail-item {
            text-align: center;
        }
        
        .detail-item strong {
            display: block;
            margin-bottom: 5px;
            color: rgba(255, 255, 255, 0.7);
        }
        
        .navigation {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 50px;
        }
        
        .nav-card {
            background: rgba(0, 0, 0, 0.4);
            border-radius: 20px;
            padding: 25px;
            text-align: center;
            text-decoration: none;
            color: white;
            transition: all 0.3s ease;
            box-shadow: 0 8px 25px rgba(0,0,0,0.3);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .nav-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.4);
            text-decoration: none;
            color: white;
            background: rgba(0, 0, 0, 0.6);
        }
        
        .nav-card h3 {
            margin-bottom: 10px;
            font-size: 1.3rem;
            color: white;
        }
        
        .nav-card p {
            color: rgba(255, 255, 255, 0.8);
            line-height: 1.5;
        }
        
        .icon {
            font-size: 2rem;
            margin-bottom: 15px;
        }
        
        .error-message {
            background: rgba(255, 118, 117, 0.9);
            color: white;
            padding: 20px;
            border-radius: 15px;
            text-align: center;
            margin: 50px auto;
            max-width: 600px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        @media (max-width: 768px) {
            .header h1 {
                font-size: 2rem;
            }
            
            .weather-card {
                margin: 20px;
                padding: 20px;
            }
            
            .navigation {
                grid-template-columns: 1fr;
                margin: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🌤️ Weather REST Client</h1>
            <p>Aplikasi Cuaca Terpadu untuk Informasi Cuaca Real-time</p>
        </div>
        
        <?php if ($currentWeather): ?>
            <div class="weather-card">
                <div class="weather-location"><?= htmlspecialchars($currentWeather['name']) ?></div>
                
                <img src="https://openweathermap.org/img/wn/<?= $currentWeather['weather'][0]['icon'] ?>@2x.png" 
                     alt="Weather Icon" class="weather-icon">
                
                <div class="temperature"><?= round($currentWeather['main']['temp']) ?>°C</div>
                
                <div class="weather-desc"><?= htmlspecialchars($currentWeather['weather'][0]['description']) ?></div>
                
                <div class="weather-details">
                    <div class="detail-item">
                        <strong>Feels Like</strong>
                        <?= round($currentWeather['main']['feels_like']) ?>°C
                    </div>
                    <div class="detail-item">
                        <strong>Humidity</strong>
                        <?= $currentWeather['main']['humidity'] ?>%
                    </div>
                    <div class="detail-item">
                        <strong>Pressure</strong>
                        <?= $currentWeather['main']['pressure'] ?> hPa
                    </div>
                    <div class="detail-item">
                        <strong>Wind Speed</strong>
                        <?= $currentWeather['wind']['speed'] ?> m/s
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="error-message">
                <h3>⚠️ Data Tidak Tersedia</h3>
                <p>Tidak dapat memuat data cuaca. Pastikan API key sudah dikonfigurasi dengan benar.</p>
                <br>
                <small>Untuk menggunakan fitur ini, dapatkan API key gratis dari <a href="https://openweathermap.org/api" target="_blank" style="color: #74b9ff;">OpenWeatherMap</a></small>
            </div>
        <?php endif; ?>
        
        <div class="navigation">
            <a href="cari.php" class="nav-card">
                <div class="icon">🔍</div>
                <h3>Search Cuaca</h3>
                <p>Cari informasi cuaca berdasarkan nama kota atau koordinat geografis</p>
            </a>
            
            <a href="pages/forecast.php" class="nav-card">
                <div class="icon">📊</div>
                <h3>Prakiraan Cuaca</h3>
                <p>Lihat prakiraan cuaca 5 hari ke depan dengan detail lengkap</p>
            </a>
            
            <a href="peta.php" class="nav-card">
                <div class="icon">🗺️</div>
                <h3>Peta Cuaca</h3>
                <p>Lihat kondisi cuaca dalam bentuk peta interaktif dengan visualisasi data</p>
            </a>
            
            <a href="pages/geography.php" class="nav-card">
                <div class="icon">🌍</div>
                <h3>Geography</h3>
                <p>Eksplorasi data cuaca berdasarkan informasi geografis dan wilayah</p>
            </a>
        </div>
        
        <div style="text-align: center; margin-top: 40px; color: rgba(255,255,255,0.8);">
            <p>&copy; 2025 Weather REST Client - Tugas Besar Pengujian Perangkat Lunak</p>
        </div>
    </div>
</body>
</html>