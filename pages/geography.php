<?php
// Handle different execution contexts (web vs CLI)
$config_path = file_exists('../config.php') ? '../config.php' : 'config.php';
require_once $config_path;

// Fungsi untuk mendapatkan data cuaca kota
function getWeatherData($lat, $lon, $cityName) {
    global $API_KEY, $BASE_URL;
    
    // Demo data jika API key tidak valid
    if (!$API_KEY || $API_KEY === 'YOUR_API_KEY_HERE') {
        return [
            'name' => $cityName,
            'main' => [
                'temp' => rand(25, 35),
                'humidity' => rand(60, 85),
                'pressure' => rand(1010, 1020)
            ],
            'weather' => [['icon' => '02d', 'description' => 'berawan']],
            'wind' => ['speed' => rand(3, 8)]
        ];
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
    
    // Fallback demo data
    return [
        'name' => $cityName,
        'main' => [
            'temp' => rand(25, 35),
            'humidity' => rand(60, 85),
            'pressure' => rand(1010, 1020)
        ],
        'weather' => [['icon' => '02d', 'description' => 'berawan']],
        'wind' => ['speed' => rand(3, 8)]
    ];
}

// Data negara ASEAN dengan kota-kotanya
function getAseanCities() {
    return [
        'Indonesia' => [
            'Jakarta' => ['lat' => -6.2088, 'lon' => 106.8456],
            'Surabaya' => ['lat' => -7.2575, 'lon' => 112.7521],
            'Bandung' => ['lat' => -6.9175, 'lon' => 107.6191],
            'Medan' => ['lat' => 3.5952, 'lon' => 98.6722],
            'Semarang' => ['lat' => -6.9667, 'lon' => 110.4167],
            'Denpasar' => ['lat' => -8.6500, 'lon' => 115.2167],
            'Makassar' => ['lat' => -5.1477, 'lon' => 119.4327]
        ],
        'Thailand' => [
            'Bangkok' => ['lat' => 13.7563, 'lon' => 100.5018],
            'Chiang Mai' => ['lat' => 18.7883, 'lon' => 98.9853],
            'Phuket' => ['lat' => 7.8804, 'lon' => 98.3923],
            'Pattaya' => ['lat' => 12.9236, 'lon' => 100.8825]
        ],
        'Malaysia' => [
            'Kuala Lumpur' => ['lat' => 3.1390, 'lon' => 101.6869],
            'George Town' => ['lat' => 5.4164, 'lon' => 100.3327],
            'Johor Bahru' => ['lat' => 1.4927, 'lon' => 103.7414],
            'Kota Kinabalu' => ['lat' => 5.9804, 'lon' => 116.0735]
        ],
        'Singapore' => [
            'Singapore' => ['lat' => 1.3521, 'lon' => 103.8198]
        ],
        'Philippines' => [
            'Manila' => ['lat' => 14.5995, 'lon' => 120.9842],
            'Cebu City' => ['lat' => 10.3157, 'lon' => 123.8854],
            'Davao' => ['lat' => 7.1907, 'lon' => 125.4553]
        ],
        'Vietnam' => [
            'Ho Chi Minh City' => ['lat' => 10.8231, 'lon' => 106.6297],
            'Hanoi' => ['lat' => 21.0285, 'lon' => 105.8542],
            'Da Nang' => ['lat' => 16.0544, 'lon' => 108.2022]
        ],
        'Myanmar' => [
            'Yangon' => ['lat' => 16.8661, 'lon' => 96.1951],
            'Mandalay' => ['lat' => 21.9588, 'lon' => 96.0891],
            'Naypyidaw' => ['lat' => 19.7633, 'lon' => 96.0785]
        ],
        'Laos' => [
            'Vientiane' => ['lat' => 17.9757, 'lon' => 102.6331],
            'Luang Prabang' => ['lat' => 19.8563, 'lon' => 102.1355],
            'Pakse' => ['lat' => 15.1202, 'lon' => 105.7994]
        ]
    ];
}

// Ambil data cuaca semua kota
$aseanCities = getAseanCities();
$weatherData = [];

foreach ($aseanCities as $country => $cities) {
    foreach ($cities as $cityName => $coords) {
        $data = getWeatherData($coords['lat'], $coords['lon'], $cityName);
        $weatherData[$country][$cityName] = $data;
    }
}

// Ambil data kota Indonesia menggunakan fungsi dari config.php
$indonesianCities = getIndonesianCities();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Geography - Weather REST Client</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
            background: linear-gradient(135deg, rgba(30, 60, 114, 0.4) 0%, rgba(42, 82, 152, 0.3) 30%, rgba(255, 154, 108, 0.2) 70%, rgba(255, 183, 77, 0.3) 100%);
            z-index: -1;
        }
        
        .container {
            max-width: 1400px;
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
            background: rgba(255, 255, 255, 0.25);
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 25px;
            margin-bottom: 30px;
            transition: all 0.3s ease;
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.4);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
        }
        
        .back-btn:hover {
            background: rgba(255, 255, 255, 0.35);
            text-decoration: none;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }
        
        .section {
            background: rgba(255, 255, 255, 0.15);
            border-radius: 20px;
            padding: 25px;
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
            margin-bottom: 30px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }
        
        .section-title {
            font-size: 1.5rem;
            margin-bottom: 20px;
            color: #ffffff;
            display: flex;
            align-items: center;
            gap: 10px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }
        
        .country-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .country-card {
            background: rgba(255, 255, 255, 0.15);
            border-radius: 15px;
            padding: 20px;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
            cursor: pointer;
            position: relative;
        }
        
        .country-card:hover {
            background: rgba(255, 255, 255, 0.25);
            transform: translateY(-5px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
        }
        
        .country-stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-top: 15px;
        }
        
        .stat-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            padding: 10px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            backdrop-filter: blur(5px);
        }
        
        .stat-icon {
            font-size: 1.5rem;
            margin-bottom: 5px;
        }
        
        .stat-value {
            font-size: 1.1rem;
            font-weight: bold;
            color: #ffeaa7;
            margin-bottom: 2px;
        }
        
        .stat-label {
            font-size: 0.8rem;
            color: rgba(255, 255, 255, 0.8);
        }
        
        .click-hint {
            text-align: center;
            margin-top: 15px;
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.7);
            font-style: italic;
        }
        
        .cities-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            animation: modalIn 0.3s ease-out;
        }
        
        .cities-modal-content {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 30px;
            max-width: 600px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            position: relative;
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid rgba(0, 0, 0, 0.1);
        }
        
        .modal-title {
            font-size: 1.5rem;
            font-weight: bold;
            color: #333;
        }
        
        .modal-close {
            background: rgba(231, 76, 60, 0.8);
            color: white;
            border: none;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }
        
        .modal-close:hover {
            background: rgba(231, 76, 60, 1);
            transform: scale(1.1);
        }
        
        .cities-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
        }
        
        .city-modal-item {
            background: rgba(255, 255, 255, 0.3);
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.4);
        }
        
        .city-modal-item:hover {
            background: rgba(255, 255, 255, 0.4);
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }
        
        .city-modal-name {
            font-size: 1.2rem;
            font-weight: bold;
            color: #333;
            margin-bottom: 15px;
        }
        
        .city-modal-stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 10px;
        }
        
        .city-stat {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 8px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 8px;
        }
        
        .city-stat-icon {
            font-size: 1.2rem;
            margin-bottom: 3px;
        }
        
        .city-stat-value {
            font-weight: bold;
            color: #e74c3c;
            font-size: 0.9rem;
        }
        
        .city-modal-desc {
            font-size: 0.9rem;
            color: #666;
            font-style: italic;
            margin-top: 10px;
        }
        
        @keyframes modalIn {
            from {
                opacity: 0;
                transform: scale(0.9);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }
        
        .country-name {
            font-size: 1.2rem;
            font-weight: bold;
            margin-bottom: 15px;
            color: #ffffff;
            text-align: center;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }
        
        .city-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .city-item:last-child {
            border-bottom: none;
        }
        
        .city-name {
            font-weight: 500;
        }
        
        .city-temp {
            font-size: 1.1rem;
            font-weight: bold; 
            color: #ffeaa7;
        }
        
        .comparison-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .comparison-table th,
        .comparison-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .comparison-table th {
            background: rgba(255, 255, 255, 0.2);
            font-weight: bold;
            color: #ffffff;
        }
        
        .comparison-table tr:hover {
            background: rgba(255, 255, 255, 0.1);
        }
        
        .climate-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }
        
        .climate-card {
            background: rgba(255, 255, 255, 0.15);
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        
        .climate-card:hover {
            background: rgba(255, 255, 255, 0.25);
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }
        
        .climate-icon {
            font-size: 3rem;
            margin-bottom: 10px;
        }
        
        .climate-title {
            font-size: 1.1rem;
            font-weight: bold;
            margin-bottom: 10px;
            color: #ffffff;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }
        
        .chart-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 20px;
            margin-top: 20px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
        }
        
        .chart-wrapper {
            position: relative;
            height: 400px;
        }
        
        .city-toggle-btn {
            background: rgba(116, 185, 255, 0.8);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 20px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }
        
        .city-toggle-btn:hover {
            background: rgba(116, 185, 255, 1);
            transform: scale(1.05);
        }
        
        .cities-row {
            background: rgba(255, 255, 255, 0.05) !important;
        }
        
        .cities-bubble-container {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            padding: 20px;
            justify-content: center;
        }
        
        .city-bubble {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 15px;
            padding: 15px;
            min-width: 180px;
            text-align: center;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            transition: all 0.3s ease;
            animation: bubbleIn 0.5s ease-out;
        }
        
        .city-bubble:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
        }
        
        .city-bubble-name {
            font-weight: bold;
            font-size: 1.1rem;
            margin-bottom: 10px;
            color: #ffffff;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }
        
        .city-bubble-data {
            display: flex;
            flex-direction: column;
            gap: 5px;
            font-size: 0.9rem;
        }
        
        .city-bubble-data span {
            background: rgba(255, 255, 255, 0.2);
            padding: 4px 8px;
            border-radius: 10px;
            backdrop-filter: blur(5px);
        }
        
        @keyframes bubbleIn {
            from {
                opacity: 0;
                transform: scale(0.8) translateY(20px);
            }
            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }
            
            .header h1 {
                font-size: 2rem;
            }
            
            .section {
                padding: 15px;
            }
            
            .country-grid {
                grid-template-columns: 1fr;
            }
            
            .comparison-table {
                font-size: 0.9rem;
            }
            
            .comparison-table th,
            .comparison-table td {
                padding: 8px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <a href="../index.php" class="back-btn">← Kembali ke Home</a>
            <h1>🌍 Geography & Climate</h1>
            <p>Perbandingan Cuaca Regional ASEAN & Informasi Iklim Indonesia</p>
        </div>  
      <!-- Data Cuaca Negara ASEAN -->
        <div class="section">
            <h2 class="section-title">
                <span>🌏</span> Data Cuaca Negara ASEAN
            </h2>
            <div class="country-grid">
                <?php foreach ($weatherData as $country => $cities): ?>
                    <?php
                    // Hitung rata-rata untuk setiap negara
                    $totalTemp = 0;
                    $totalHumidity = 0;
                    $totalPressure = 0;
                    $totalWind = 0;
                    $cityCount = count($cities);
                    
                    foreach ($cities as $data) {
                        $totalTemp += $data['main']['temp'];
                        $totalHumidity += $data['main']['humidity'];
                        $totalPressure += $data['main']['pressure'];
                        $totalWind += $data['wind']['speed'];
                    }
                    
                    $avgTemp = round($totalTemp / $cityCount);
                    $avgHumidity = round($totalHumidity / $cityCount);
                    $avgPressure = round($totalPressure / $cityCount);
                    $avgWind = round($totalWind / $cityCount, 1);
                    ?>
                    <div class="country-card" onclick="toggleCountryCities('<?= strtolower(str_replace(' ', '', $country)) ?>')">
                        <div class="country-name"><?= htmlspecialchars($country) ?></div>
                        <div class="country-stats">
                            <div class="stat-item">
                                <span class="stat-icon">🌡️</span>
                                <span class="stat-value"><?= $avgTemp ?>°C</span>
                                <span class="stat-label">Suhu</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-icon">💧</span>
                                <span class="stat-value"><?= $avgHumidity ?>%</span>
                                <span class="stat-label">Kelembaban</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-icon">📊</span>
                                <span class="stat-value"><?= $avgPressure ?></span>
                                <span class="stat-label">Tekanan (hPa)</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-icon">💨</span>
                                <span class="stat-value"><?= $avgWind ?> m/s</span>
                                <span class="stat-label">Angin</span>
                            </div>
                        </div>
                        <div class="click-hint">Klik untuk lihat kota</div>
                        

                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Tabel Perbandingan -->
        <div class="section">
            <h2 class="section-title">
                <span>📊</span> Tabel Perbandingan Cuaca
            </h2>
            <div style="overflow-x: auto;">
                <table class="comparison-table">
                    <thead>
                        <tr>
                            <th>Negara</th>
                            <th>Rata-rata Suhu (°C)</th>
                            <th>Rata-rata Kelembaban (%)</th>
                            <th>Rata-rata Tekanan (hPa)</th>
                            <th>Rata-rata Angin (m/s)</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($weatherData as $country => $cities): ?>
                            <?php
                            // Hitung rata-rata untuk setiap negara
                            $totalTemp = 0;
                            $totalHumidity = 0;
                            $totalPressure = 0;
                            $totalWind = 0;
                            $cityCount = count($cities);
                            
                            foreach ($cities as $data) {
                                $totalTemp += $data['main']['temp'];
                                $totalHumidity += $data['main']['humidity'];
                                $totalPressure += $data['main']['pressure'];
                                $totalWind += $data['wind']['speed'];
                            }
                            
                            $avgTemp = round($totalTemp / $cityCount);
                            $avgHumidity = round($totalHumidity / $cityCount);
                            $avgPressure = round($totalPressure / $cityCount);
                            $avgWind = round($totalWind / $cityCount, 1);
                            ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($country) ?></strong></td>
                                <td><?= $avgTemp ?>°C</td>
                                <td><?= $avgHumidity ?>%</td>
                                <td><?= $avgPressure ?> hPa</td>
                                <td><?= $avgWind ?> m/s</td>
                                <td>
                                    <button class="city-toggle-btn" onclick="toggleCities('<?= strtolower(str_replace(' ', '', $country)) ?>')">
                                        Lihat Kota
                                    </button>
                                </td>
                            </tr>
                            <tr id="cities-<?= strtolower(str_replace(' ', '', $country)) ?>" class="cities-row" style="display: none;">
                                <td colspan="6">
                                    <div class="cities-bubble-container">
                                        <?php foreach ($cities as $cityName => $data): ?>
                                            <div class="city-bubble">
                                                <div class="city-bubble-name"><?= htmlspecialchars($cityName) ?></div>
                                                <div class="city-bubble-data">
                                                    <span>🌡️ <?= round($data['main']['temp']) ?>°C</span>
                                                    <span>💧 <?= $data['main']['humidity'] ?>%</span>
                                                    <span>📊 <?= $data['main']['pressure'] ?> hPa</span>
                                                    <span>💨 <?= round($data['wind']['speed'], 1) ?> m/s</span>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Informasi Iklim Regional Indonesia -->
        <div class="section">
            <h2 class="section-title">
                <span>🇮🇩</span> Informasi Iklim Regional Indonesia
            </h2>
            <div class="climate-info">
                <div class="climate-card">
                    <div class="climate-icon">🌴</div>
                    <div class="climate-title">Iklim Tropis</div>
                    <p>Indonesia memiliki iklim tropis dengan suhu rata-rata 26-30°C sepanjang tahun. Kelembaban tinggi 70-90%.</p>
                </div>
                <div class="climate-card">
                    <div class="climate-icon">🌧️</div>
                    <div class="climate-title">Musim Hujan</div>
                    <p>Oktober - Maret: Musim hujan dengan curah hujan tinggi, terutama di wilayah barat Indonesia.</p>
                </div>
                <div class="climate-card">
                    <div class="climate-icon">☀️</div>
                    <div class="climate-title">Musim Kemarau</div>
                    <p>April - September: Musim kemarau dengan curah hujan rendah dan angin timur yang kering.</p>
                </div>
                <div class="climate-card">
                    <div class="climate-icon">🕐</div>
                    <div class="climate-title">Zona Waktu</div>
                    <p>3 zona waktu: WIB (UTC+7), WITA (UTC+8), dan WIT (UTC+9) dari barat ke timur.</p>
                </div>
            </div>

        </div>

        <!-- Grafik Perbandingan -->
        <div class="section">
            <h2 class="section-title">
                <span>📈</span> Grafik Perbandingan Data Cuaca
            </h2>
            
            <div class="chart-container">
                <h3 style="color: #333; text-align: center; margin-bottom: 20px;">Perbandingan Suhu Kota-kota ASEAN</h3>
                <div class="chart-wrapper">
                    <canvas id="temperatureChart"></canvas>
                </div>
            </div>
            
            <div class="chart-container">
                <h3 style="color: #333; text-align: center; margin-bottom: 20px;">Perbandingan Kelembaban</h3>
                <div class="chart-wrapper">
                    <canvas id="humidityChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal untuk menampilkan kota-kota -->
    <div id="citiesModal" class="cities-modal" style="display: none;">
        <div class="cities-modal-content">
            <div class="modal-header">
                <div class="modal-title" id="modalTitle">Kota-kota</div>
                <button class="modal-close" onclick="closeCitiesModal()">×</button>
            </div>
            <div class="cities-grid" id="citiesGrid">
                <!-- Cities akan diisi oleh JavaScript -->
            </div>
        </div>
    </div>

    <script>
        // Fungsi untuk toggle cities di tabel
        function toggleCities(countryId) {
            const citiesRow = document.getElementById('cities-' + countryId);
            const button = event.target;
            
            if (citiesRow.style.display === 'none' || citiesRow.style.display === '') {
                citiesRow.style.display = 'table-row';
                button.textContent = 'Sembunyikan';
                button.style.background = 'rgba(231, 76, 60, 0.8)';
            } else {
                citiesRow.style.display = 'none';
                button.textContent = 'Lihat Kota';
                button.style.background = 'rgba(116, 185, 255, 0.8)';
            }
        }

        // Data kota untuk setiap negara
        const countryCitiesData = {
            <?php foreach ($weatherData as $country => $cities): ?>
            '<?= strtolower(str_replace(' ', '', $country)) ?>': {
                name: '<?= htmlspecialchars($country) ?>',
                cities: [
                    <?php foreach ($cities as $cityName => $data): ?>
                    {
                        name: '<?= addslashes($cityName) ?>',
                        temp: <?= round($data['main']['temp']) ?>,
                        humidity: <?= $data['main']['humidity'] ?>,
                        pressure: <?= $data['main']['pressure'] ?>,
                        wind: <?= round($data['wind']['speed'], 1) ?>,
                        description: '<?= addslashes($data['weather'][0]['description']) ?>'
                    },
                    <?php endforeach; ?>
                ]
            },
            <?php endforeach; ?>
        };

        // Fungsi untuk membuka modal kota
        function toggleCountryCities(countryId) {
            const countryData = countryCitiesData[countryId];
            if (!countryData) return;
            
            // Set judul modal
            document.getElementById('modalTitle').textContent = `Kota-kota di ${countryData.name}`;
            
            // Buat HTML untuk kota-kota
            const citiesGrid = document.getElementById('citiesGrid');
            citiesGrid.innerHTML = '';
            
            countryData.cities.forEach(city => {
                const cityElement = document.createElement('div');
                cityElement.className = 'city-modal-item';
                cityElement.innerHTML = `
                    <div class="city-modal-name">${city.name}</div>
                    <div class="city-modal-stats">
                        <div class="city-stat">
                            <div class="city-stat-icon">🌡️</div>
                            <div class="city-stat-value">${city.temp}°C</div>
                        </div>
                        <div class="city-stat">
                            <div class="city-stat-icon">💧</div>
                            <div class="city-stat-value">${city.humidity}%</div>
                        </div>
                        <div class="city-stat">
                            <div class="city-stat-icon">📊</div>
                            <div class="city-stat-value">${city.pressure}</div>
                        </div>
                        <div class="city-stat">
                            <div class="city-stat-icon">💨</div>
                            <div class="city-stat-value">${city.wind} m/s</div>
                        </div>
                    </div>
                    <div class="city-modal-desc">${city.description}</div>
                `;
                citiesGrid.appendChild(cityElement);
            });
            
            // Tampilkan modal
            document.getElementById('citiesModal').style.display = 'flex';
        }

        // Fungsi untuk menutup modal
        function closeCitiesModal() {
            document.getElementById('citiesModal').style.display = 'none';
        }

        // Tutup modal saat klik di luar content
        document.getElementById('citiesModal').addEventListener('click', function(event) {
            if (event.target === this) {
                closeCitiesModal();
            }
        });

        // Tutup modal dengan tombol ESC
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeCitiesModal();
            }
        });

        // Data untuk grafik
        const cities = [];
        const temperatures = [];
        const humidity = [];
        const countries = [];
        
        <?php foreach ($weatherData as $country => $cities_data): ?>
            <?php foreach ($cities_data as $cityName => $data): ?>
                cities.push('<?= addslashes($cityName) ?>');
                temperatures.push(<?= round($data['main']['temp']) ?>);
                humidity.push(<?= $data['main']['humidity'] ?>);
                countries.push('<?= addslashes($country) ?>');
            <?php endforeach; ?>
        <?php endforeach; ?>

        // Warna untuk setiap negara
        const countryColors = {
            'Indonesia': '#e74c3c',
            'Thailand': '#3498db', 
            'Malaysia': '#f39c12',
            'Singapore': '#9b59b6',
            'Philippines': '#2ecc71',
            'Vietnam': '#e67e22'
        };

        const backgroundColors = cities.map((city, index) => countryColors[countries[index]] || '#95a5a6');

        // Grafik Suhu
        const tempCtx = document.getElementById('temperatureChart').getContext('2d');
        new Chart(tempCtx, {
            type: 'bar',
            data: {
                labels: cities,
                datasets: [{
                    label: 'Suhu (°C)',
                    data: temperatures,
                    backgroundColor: backgroundColors,
                    borderColor: backgroundColors,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: false,
                        min: Math.min(...temperatures) - 2,
                        max: Math.max(...temperatures) + 2,
                        title: {
                            display: true,
                            text: 'Suhu (°C)'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Kota'
                        }
                    }
                }
            }
        });

        // Grafik Kelembaban
        const humidityCtx = document.getElementById('humidityChart').getContext('2d');
        new Chart(humidityCtx, {
            type: 'line',
            data: {
                labels: cities,
                datasets: [{
                    label: 'Kelembaban (%)',
                    data: humidity,
                    borderColor: '#74b9ff',
                    backgroundColor: 'rgba(116, 185, 255, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true
                    }
                },
                scales: {
                    y: {
                        beginAtZero: false,
                        min: Math.min(...humidity) - 5,
                        max: Math.max(...humidity) + 5,
                        title: {
                            display: true,
                            text: 'Kelembaban (%)'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Kota'
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>