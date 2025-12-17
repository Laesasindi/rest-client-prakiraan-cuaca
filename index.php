<?php
require_once 'config.php';

// Mulai session untuk menyimpan pilihan kota
session_start();

// Fungsi untuk mendapatkan demo data cuaca berdasarkan kota
function getDemoWeatherData($cityName = 'Jakarta') {
    $demoData = [
        'Jakarta' => [
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
        ],
        'Surabaya' => [
            'name' => 'Surabaya',
            'sys' => ['country' => 'ID'],
            'main' => [
                'temp' => 31,
                'feels_like' => 35,
                'temp_min' => 29,
                'temp_max' => 33,
                'pressure' => 1010,
                'humidity' => 68
            ],
            'weather' => [
                [
                    'icon' => '01d',
                    'description' => 'langit cerah'
                ]
            ],
            'wind' => ['speed' => 3.8]
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
                [
                    'icon' => '03d',
                    'description' => 'awan tersebar'
                ]
            ],
            'wind' => ['speed' => 2.5]
        ]
    ];
    
    return isset($demoData[$cityName]) ? $demoData[$cityName] : $demoData['Jakarta'];
}

// Fungsi untuk mendapatkan cuaca berdasarkan nama kota
function getCurrentWeatherByCity($cityName = 'Jakarta') {
    global $API_KEY, $BASE_URL;
    
    // Jika tidak ada API key yang valid, gunakan demo data
    if (!$API_KEY || $API_KEY === 'YOUR_API_KEY_HERE') {
        return getDemoWeatherData($cityName);
    }
    
    $url = $BASE_URL . "?q=" . urlencode($cityName) . "&appid={$API_KEY}&units=metric&lang=id";
    
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
        if ($data && isset($data['name'])) {
            return $data;
        }
    }
    
    return getDemoWeatherData($cityName);
}

// Handle clear history action
if (isset($_GET['action']) && $_GET['action'] === 'clear_history') {
    unset($_SESSION['city_history']);
    header('Location: index.php');
    exit;
}

// Ambil kota yang dipilih dari URL atau session
$selectedCity = 'Jakarta'; // Default

if (isset($_GET['city']) && !empty(trim($_GET['city']))) {
    $selectedCity = trim($_GET['city']);
    $_SESSION['selected_city'] = $selectedCity;
    
    // Simpan ke riwayat kota
    if (!isset($_SESSION['city_history'])) {
        $_SESSION['city_history'] = [];
    }
    
    // Tambahkan ke riwayat jika belum ada
    if (!in_array($selectedCity, $_SESSION['city_history'])) {
        array_unshift($_SESSION['city_history'], $selectedCity);
        // Batasi riwayat maksimal 5 kota
        $_SESSION['city_history'] = array_slice($_SESSION['city_history'], 0, 5);
    }
} elseif (isset($_SESSION['selected_city'])) {
    $selectedCity = $_SESSION['selected_city'];
}

// Ambil riwayat kota
$cityHistory = isset($_SESSION['city_history']) ? $_SESSION['city_history'] : [];

$currentWeather = getCurrentWeatherByCity($selectedCity);
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
            
            <!-- City Selector -->
            <div style="margin-top: 20px;">
                <div style="background: rgba(0, 0, 0, 0.6); border-radius: 15px; padding: 20px; max-width: 600px; margin: 0 auto; backdrop-filter: blur(10px);">
                    <h3 style="margin-bottom: 15px; color: #74b9ff;">🏙️ Pilih Kota</h3>
                    <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap; justify-content: center;">
                        <input type="text" id="cityInput" placeholder="Masukkan nama kota..." 
                               value="<?= htmlspecialchars($selectedCity) ?>"
                               style="flex: 1; min-width: 200px; padding: 10px 15px; border: 1px solid rgba(255,255,255,0.3); 
                                      border-radius: 25px; background: rgba(255,255,255,0.1); color: white; font-size: 16px;">
                        <button onclick="changeCity()" 
                                style="background: #74b9ff; color: white; border: none; padding: 10px 20px; 
                                       border-radius: 25px; cursor: pointer; font-weight: 600;">
                            Ubah Kota
                        </button>
                    </div>
                    <?php if (!empty($cityHistory)): ?>
                    <div style="margin-top: 15px;">
                        <div style="display: flex; align-items: center; justify-content: center; gap: 10px; margin-bottom: 8px;">
                            <span style="font-size: 14px; color: rgba(255,255,255,0.8);">📍 Riwayat Pencarian:</span>
                            <a href="?action=clear_history" 
                               style="font-size: 12px; color: #ff7675; text-decoration: none; padding: 2px 6px; 
                                      border-radius: 8px; background: rgba(255,118,117,0.2);"
                               onclick="return confirm('Hapus semua riwayat pencarian?')">
                                🗑️ Hapus
                            </a>
                        </div>
                        <div style="display: flex; gap: 8px; flex-wrap: wrap; justify-content: center; margin-bottom: 10px;">
                            <?php foreach ($cityHistory as $city): ?>
                                <a href="?city=<?= urlencode($city) ?>" 
                                   style="background: <?= strtolower($city) === strtolower($selectedCity) ? '#00b894' : 'rgba(0,184,148,0.3)' ?>; 
                                          color: white; padding: 4px 10px; border-radius: 12px; text-decoration: none; 
                                          font-size: 13px; transition: all 0.3s ease; border: 1px solid rgba(0,184,148,0.5);"
                                   onmouseover="this.style.background='#00b894'"
                                   onmouseout="this.style.background='<?= strtolower($city) === strtolower($selectedCity) ? '#00b894' : 'rgba(0,184,148,0.3)' ?>'">
                                    <?= htmlspecialchars($city) ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div style="margin-top: 15px;">
                        <div style="font-size: 14px; color: rgba(255,255,255,0.8); margin-bottom: 8px;">🏙️ Kota Populer:</div>
                        <div style="display: flex; gap: 8px; flex-wrap: wrap; justify-content: center;">
                            <?php 
                            $quickCities = ['Jakarta', 'Surabaya', 'Bandung', 'Medan', 'Semarang', 'Denpasar', 'Makassar'];
                            foreach ($quickCities as $city): 
                            ?>
                                <a href="?city=<?= urlencode($city) ?>" 
                                   style="background: <?= strtolower($city) === strtolower($selectedCity) ? '#74b9ff' : 'rgba(255,255,255,0.1)' ?>; 
                                          color: white; padding: 6px 12px; border-radius: 15px; text-decoration: none; 
                                          font-size: 14px; transition: all 0.3s ease; border: 1px solid rgba(255,255,255,0.2);"
                                   onmouseover="this.style.background='#74b9ff'"
                                   onmouseout="this.style.background='<?= strtolower($city) === strtolower($selectedCity) ? '#74b9ff' : 'rgba(255,255,255,0.1)' ?>'">
                                    <?= $city ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <?php if ($currentWeather): ?>
            <div class="weather-card">
                <div class="weather-location">
                    <?= htmlspecialchars($currentWeather['name']) ?>
                    <?php if (isset($currentWeather['sys']['country'])): ?>
                        <span style="font-size: 1rem; color: rgba(255,255,255,0.7); font-weight: normal;">
                            , <?= htmlspecialchars($currentWeather['sys']['country']) ?>
                        </span>
                    <?php endif; ?>
                </div>
                
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
                <p>Tidak dapat memuat data cuaca untuk kota "<?= htmlspecialchars($selectedCity) ?>".</p>
                <p>Silakan coba kota lain atau periksa ejaan nama kota.</p>
                <br>
                <div style="margin-top: 15px;">
                    <button onclick="document.getElementById('cityInput').focus(); document.getElementById('cityInput').select();" 
                            style="background: #74b9ff; color: white; border: none; padding: 10px 20px; 
                                   border-radius: 20px; cursor: pointer; font-weight: 600;">
                        🔍 Coba Kota Lain
                    </button>
                </div>
                <br>
                <small>Untuk data real-time, dapatkan API key gratis dari <a href="https://openweathermap.org/api" target="_blank" style="color: #74b9ff;">OpenWeatherMap</a></small>
            </div>
        <?php endif; ?>
        
        <div class="navigation">
            <a href="pages/cari.php" class="nav-card">
                <div class="icon">🔍</div>
                <h3>Search Cuaca</h3>
                <p>Cari informasi cuaca berdasarkan nama kota atau koordinat geografis</p>
            </a>
            
            <a href="pages/forecast.php?city=<?= urlencode($currentWeather['name']) ?>" class="nav-card">
                <div class="icon">📊</div>
                <h3>Prakiraan Cuaca</h3>
                <p>Lihat prakiraan cuaca 5 hari ke depan untuk <?= htmlspecialchars($currentWeather['name']) ?></p>
            </a>
            
            <a href="pages/peta.php" class="nav-card">
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

    <script>
        // Fungsi untuk mengubah kota
        function changeCity() {
            const cityInput = document.getElementById('cityInput');
            const city = cityInput.value.trim();
            
            if (city.length < 2) {
                alert('Masukkan minimal 2 karakter untuk nama kota');
                return;
            }
            
            // Tampilkan loading
            showLoading();
            
            // Redirect ke halaman yang sama dengan parameter kota baru
            window.location.href = `?city=${encodeURIComponent(city)}`;
        }
        
        // Event listener untuk Enter key pada input
        document.getElementById('cityInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                changeCity();
            }
        });
        
        // Fungsi untuk menampilkan loading
        function showLoading() {
            const weatherCard = document.querySelector('.weather-card');
            if (weatherCard) {
                weatherCard.innerHTML = `
                    <div style="text-align: center; padding: 40px;">
                        <div style="font-size: 3rem; margin-bottom: 20px;">🔄</div>
                        <div style="font-size: 1.5rem; margin-bottom: 10px;">Memuat data cuaca...</div>
                        <div style="color: rgba(255,255,255,0.7);">Sedang mengambil informasi cuaca terbaru</div>
                    </div>
                `;
            }
        }
        
        // Tambahkan loading pada quick city links
        document.querySelectorAll('a[href*="?city="]').forEach(link => {
            link.addEventListener('click', function(e) {
                showLoading();
            });
        });
        
        // Auto-focus pada input kota jika tidak ada data cuaca
        <?php if (!$currentWeather): ?>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('cityInput').focus();
        });
        <?php endif; ?>
    </script>
</body>
</html>