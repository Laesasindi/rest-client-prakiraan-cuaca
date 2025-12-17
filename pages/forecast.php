<?php
require_once '../config.php';

// Fungsi untuk mendapatkan demo data forecast yang lebih lengkap
function getDemoForecastData($cityName = 'Jakarta') {
    $baseTime = time();
    $demoData = [];
    
    // Generate 40 data points (5 hari x 8 data per hari)
    for ($i = 0; $i < 40; $i++) {
        $timestamp = $baseTime + ($i * 10800); // Setiap 3 jam
        $temp = 26 + rand(-3, 8) + sin($i * 0.5) * 3; // Variasi suhu realistis
        
        $weatherTypes = [
            ['icon' => '01d', 'description' => 'langit cerah', 'main' => 'Clear'],
            ['icon' => '02d', 'description' => 'sedikit berawan', 'main' => 'Clouds'],
            ['icon' => '03d', 'description' => 'awan tersebar', 'main' => 'Clouds'],
            ['icon' => '04d', 'description' => 'awan mendung', 'main' => 'Clouds'],
            ['icon' => '09d', 'description' => 'hujan ringan', 'main' => 'Rain'],
            ['icon' => '10d', 'description' => 'hujan', 'main' => 'Rain']
        ];
        
        $weather = $weatherTypes[array_rand($weatherTypes)];
        
        $demoData[] = [
            'dt' => $timestamp,
            'main' => [
                'temp' => round($temp, 1),
                'temp_min' => round($temp - 2, 1),
                'temp_max' => round($temp + 3, 1),
                'humidity' => rand(60, 85),
                'pressure' => rand(1005, 1015)
            ],
            'weather' => [$weather],
            'wind' => ['speed' => rand(20, 80) / 10, 'deg' => rand(0, 360)],
            'pop' => rand(0, 100) / 100,
            'dt_txt' => date('Y-m-d H:i:s', $timestamp)
        ];
    }
    
    return [
        'list' => $demoData,
        'city' => [
            'name' => $cityName,
            'country' => 'ID',
            'timezone' => 25200
        ]
    ];
}

// Fungsi untuk mendapatkan forecast berdasarkan nama kota
function getForecastByCity($cityName) {
    global $API_KEY, $FORECAST_URL;
    
    // Jika tidak ada API key yang valid, gunakan demo data
    if (!$API_KEY || $API_KEY === 'YOUR_API_KEY_HERE') {
        return getDemoForecastData($cityName);
    }
    
    $url = $FORECAST_URL . "?q=" . urlencode($cityName) . "&appid={$API_KEY}&units=metric&lang=id";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        $data = json_decode($response, true);
        if ($data && isset($data['list'])) {
            return $data;
        }
    }
    
    return getDemoForecastData($cityName);
}

// Fungsi untuk mengelompokkan data forecast per hari
function groupForecastByDay($forecastList) {
    $grouped = [];
    
    foreach ($forecastList as $item) {
        $date = date('Y-m-d', $item['dt']);
        if (!isset($grouped[$date])) {
            $grouped[$date] = [];
        }
        $grouped[$date][] = $item;
    }
    
    return $grouped;
}

// Ambil parameter kota dari URL atau session
$selectedCity = 'Jakarta'; // Default

if (isset($_GET['city']) && !empty(trim($_GET['city']))) {
    $selectedCity = trim($_GET['city']);
    // Simpan pilihan kota ke session untuk konsistensi
    session_start();
    $_SESSION['selected_city'] = $selectedCity;
} elseif (isset($_SESSION['selected_city'])) {
    session_start();
    $selectedCity = $_SESSION['selected_city'];
}

// Dapatkan data forecast
$forecastData = getForecastByCity($selectedCity);
$groupedForecast = groupForecastByDay($forecastData['list']);

// Ambil 5 hari pertama
$dailyForecast = array_slice($groupedForecast, 0, 5, true);
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
        
        .city-selector {
            background: rgba(0, 0, 0, 0.7);
            border-radius: 20px;
            padding: 25px;
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .city-selector h3 {
            margin-bottom: 20px;
            color: #74b9ff;
        }
        
        .city-search {
            display: flex;
            gap: 15px;
            max-width: 500px;
            margin: 0 auto;
            align-items: center;
        }
        
        .city-search input {
            flex: 1;
            padding: 12px 16px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 25px;
            background: rgba(255, 255, 255, 0.1);
            color: white;
            font-size: 16px;
            backdrop-filter: blur(10px);
        }
        
        .city-search input::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }
        
        .city-search button {
            background: #74b9ff;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 25px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .city-search button:hover {
            background: #0984e3;
            transform: translateY(-2px);
        }
        
        .quick-cities {
            margin-top: 20px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            justify-content: center;
        }
        
        .quick-city {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .quick-city:hover, .quick-city.active {
            background: #74b9ff;
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
        
        .daily-forecast {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .day-card {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .day-card:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-5px);
        }
        
        .day-name {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 10px;
            color: #74b9ff;
        }
        
        .day-date {
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: 15px;
        }
        
        .day-icon {
            width: 64px;
            height: 64px;
            margin: 10px auto;
        }
        
        .day-temps {
            display: flex;
            justify-content: space-between;
            margin: 15px 0;
        }
        
        .temp-max {
            font-size: 1.3rem;
            font-weight: bold;
        }
        
        .temp-min {
            font-size: 1.1rem;
            color: rgba(255, 255, 255, 0.7);
        }
        
        .day-desc {
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.8);
            text-transform: capitalize;
        }
        
        .hourly-forecast {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
        }
        
        .hour-item {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            padding: 15px;
            text-align: center;
            transition: all 0.3s ease;
        }
        
        .hour-item:hover {
            background: rgba(255, 255, 255, 0.1);
        }
        
        .hour-time {
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 8px;
        }
        
        .hour-icon {
            width: 40px;
            height: 40px;
            margin: 8px auto;
        }
        
        .hour-temp {
            font-size: 1.1rem;
            font-weight: 600;
            margin: 8px 0;
        }
        
        .hour-rain {
            font-size: 0.8rem;
            color: #74b9ff;
        }
        
        .loading {
            text-align: center;
            padding: 40px;
            color: rgba(255, 255, 255, 0.8);
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
            
            .city-search {
                flex-direction: column;
                gap: 10px;
            }
            
            .city-search input,
            .city-search button {
                width: 100%;
            }
            
            .quick-cities {
                gap: 8px;
            }
            
            .forecast-section {
                padding: 15px;
            }
            
            .daily-forecast {
                grid-template-columns: 1fr;
                gap: 15px;
            }
            
            .hourly-forecast {
                grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <a href="../index.php" class="back-btn">← Kembali ke Home</a>
            <h1>🌤️ Prakiraan Cuaca</h1>
            <p>Prakiraan cuaca 5 hari untuk <span id="currentCity"><?= htmlspecialchars($selectedCity) ?></span></p>
        </div>
        
        <!-- City Selector -->
        <div class="city-selector">
            <h3>🔍 Pilih Kota</h3>
            <div class="city-search">
                <input type="text" id="cityInput" placeholder="Masukkan nama kota..." value="<?= htmlspecialchars($selectedCity) ?>">
                <button onclick="searchCity()">Cari</button>
            </div>
            <div class="quick-cities">
                <?php 
                $quickCities = ['Jakarta', 'Surabaya', 'Bandung', 'Medan', 'Semarang', 'Denpasar', 'Makassar'];
                foreach ($quickCities as $city): 
                ?>
                    <a href="?city=<?= urlencode($city) ?>" 
                       class="quick-city <?= strtolower($city) === strtolower($selectedCity) ? 'active' : '' ?>">
                        <?= $city ?>
                    </a>
                <?php endforeach; ?>
                <a href="search.php" class="quick-city" style="background: #00b894;">
                    🔍 Cari Kota Lain
                </a>
            </div>
        </div>
        
        <div id="forecastContent">
            <?php if ($forecastData && isset($forecastData['list'])): ?>
                <!-- Daily Forecast -->
                <div class="forecast-section">
                    <h2 class="section-title">
                        <span>📅</span> Prakiraan 5 Hari
                    </h2>
                    <div class="daily-forecast">
                        <?php foreach ($dailyForecast as $date => $dayData): 
                            $firstItem = $dayData[0];
                            $temps = array_column(array_column($dayData, 'main'), 'temp');
                            $maxTemp = max($temps);
                            $minTemp = min($temps);
                        ?>
                            <div class="day-card" onclick="showHourlyForecast('<?= $date ?>')">
                                <div class="day-name"><?= getDayName(strtotime($date)) ?></div>
                                <div class="day-date"><?= date('d M', strtotime($date)) ?></div>
                                <img src="https://openweathermap.org/img/wn/<?= $firstItem['weather'][0]['icon'] ?>@2x.png" 
                                     alt="<?= $firstItem['weather'][0]['description'] ?>" class="day-icon">
                                <div class="day-temps">
                                    <span class="temp-max"><?= round($maxTemp) ?>°</span>
                                    <span class="temp-min"><?= round($minTemp) ?>°</span>
                                </div>
                                <div class="day-desc"><?= htmlspecialchars($firstItem['weather'][0]['description']) ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Hourly Forecast for Today -->
                <div class="forecast-section">
                    <h2 class="section-title">
                        <span>⏰</span> Prakiraan Per 3 Jam - Hari Ini
                    </h2>
                    <div class="hourly-forecast">
                        <?php 
                        $todayData = reset($dailyForecast);
                        foreach (array_slice($todayData, 0, 8) as $item): 
                        ?>
                            <div class="hour-item">
                                <div class="hour-time"><?= date('H:i', $item['dt']) ?></div>
                                <img src="https://openweathermap.org/img/wn/<?= $item['weather'][0]['icon'] ?>@2x.png" 
                                     alt="<?= $item['weather'][0]['description'] ?>" class="hour-icon">
                                <div class="hour-temp"><?= round($item['main']['temp']) ?>°C</div>
                                <div class="hour-rain"><?= round($item['pop'] * 100) ?>% hujan</div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Detailed Hourly Forecast (Hidden by default) -->
                <?php foreach ($dailyForecast as $date => $dayData): ?>
                    <div id="hourly-<?= $date ?>" class="forecast-section" style="display: none;">
                        <h2 class="section-title">
                            <span>⏰</span> Detail <?= getDayName(strtotime($date)) ?>, <?= date('d M Y', strtotime($date)) ?>
                        </h2>
                        <div class="hourly-forecast">
                            <?php foreach ($dayData as $item): ?>
                                <div class="hour-item">
                                    <div class="hour-time"><?= date('H:i', $item['dt']) ?></div>
                                    <img src="https://openweathermap.org/img/wn/<?= $item['weather'][0]['icon'] ?>@2x.png" 
                                         alt="<?= $item['weather'][0]['description'] ?>" class="hour-icon">
                                    <div class="hour-temp"><?= round($item['main']['temp']) ?>°C</div>
                                    <div class="hour-rain"><?= round($item['pop'] * 100) ?>% hujan</div>
                                    <div style="font-size: 0.7rem; color: rgba(255,255,255,0.6); margin-top: 5px;">
                                        💨 <?= round($item['wind']['speed']) ?> m/s<br>
                                        💧 <?= $item['main']['humidity'] ?>%
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                
            <?php else: ?>
                <div class="error-message">
                    <h3>⚠️ Data Tidak Tersedia</h3>
                    <p>Tidak dapat memuat data prakiraan cuaca untuk <?= htmlspecialchars($selectedCity) ?>.</p>
                    <p>Silakan coba kota lain atau periksa koneksi internet Anda.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Fungsi untuk mencari kota
        function searchCity() {
            const cityInput = document.getElementById('cityInput');
            const city = cityInput.value.trim();
            
            if (city.length < 2) {
                alert('Masukkan minimal 2 karakter untuk nama kota');
                return;
            }
            
            // Redirect ke halaman yang sama dengan parameter kota baru
            window.location.href = `?city=${encodeURIComponent(city)}`;
        }
        
        // Event listener untuk Enter key pada input
        document.getElementById('cityInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                searchCity();
            }
        });
        
        // Fungsi untuk menampilkan prakiraan per jam untuk hari tertentu
        function showHourlyForecast(date) {
            // Sembunyikan semua detail hourly
            const allHourly = document.querySelectorAll('[id^="hourly-"]');
            allHourly.forEach(el => el.style.display = 'none');
            
            // Tampilkan detail untuk tanggal yang dipilih
            const targetElement = document.getElementById(`hourly-${date}`);
            if (targetElement) {
                targetElement.style.display = 'block';
                targetElement.scrollIntoView({ behavior: 'smooth' });
            }
        }
        
        // Auto-refresh setiap 10 menit untuk data terbaru (opsional)
        // Uncomment jika ingin auto-refresh
        /*
        setInterval(function() {
            const currentCity = document.getElementById('currentCity').textContent;
            if (currentCity) {
                window.location.href = `?city=${encodeURIComponent(currentCity)}`;
            }
        }, 600000); // 10 menit
        */
        
        // Fungsi untuk menampilkan loading saat mencari
        function showLoading() {
            document.getElementById('forecastContent').innerHTML = `
                <div class="loading">
                    <h3>🔄 Memuat data cuaca...</h3>
                    <p>Sedang mengambil prakiraan cuaca terbaru</p>
                </div>
            `;
        }
        
        // Update quick city links untuk menampilkan loading
        document.querySelectorAll('.quick-city').forEach(link => {
            link.addEventListener('click', function(e) {
                showLoading();
            });
        });
    </script>
</body>
</html>