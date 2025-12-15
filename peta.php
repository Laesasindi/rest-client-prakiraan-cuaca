<?php
// peta.php - Peta cuaca interaktif
$config_path = file_exists('config.php') ? 'config.php' : null;
if ($config_path && file_exists($config_path)) { 
    require_once $config_path; 
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Peta Cuaca - Weather REST Client</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="">
    <style>
        :root { 
            --brand: #2d6a4f; 
            --muted: #666; 
        }
        * { 
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            margin: 0;
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
        header {
            padding: 12px 18px;
            background: rgba(0, 0, 0, 0.4);
            color: white;
            display: flex;
            align-items: center;
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        header h1 {
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        header nav a {
            color: white;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 6px;
            background: rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
        }
        header nav a:hover {
            background: rgba(255, 255, 255, 0.2);
            text-decoration: none;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 10px;
        }
        .controls {
            padding: 12px 18px;
            background: rgba(255, 255, 255, 0.1);
            display: flex;
            gap: 8px;
            align-items: center;
            border-radius: 20px;
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 8px 25px rgba(0,0,0,0.3);
        }
        .search {
            flex: 1;
            display: flex;
            gap: 8px;
        }
        input[type="text"] {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.9);
            color: #333;
        }
        input[type="text"]::placeholder {
            color: #666;
        }
        button {
            padding: 8px 12px;
            border-radius: 10px;
            border: none;
            background: rgba(0, 0, 0, 0.6);
            color: white;
            cursor: pointer;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }
        button:hover {
            background: rgba(0, 0, 0, 0.8);
            transform: translateY(-2px);
        }
        .legend {
            padding: 8px 12px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
        }
        .legend .city-btn {
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
            font-size: 12px;
            padding: 6px 10px;
        }
        .legend .city-btn:hover {
            background: rgba(255, 255, 255, 0.3);
        }
        #map {
            height: calc(100vh - 170px);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(0,0,0,0.4);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .popup-content {
            min-width: 220px;
            color: #333;
        }
        .weather-header {
            display: flex;
            gap: 10px;
            align-items: center;
            margin-bottom: 8px;
        }
        .weather-icon {
            width: 48px;
            height: 48px;
        }
        .weather-info {
            flex: 1;
        }
        .city-name {
            font-weight: bold;
            font-size: 16px;
        }
        .weather-desc {
            color: #666;
            font-size: 12px;
            text-transform: capitalize;
        }
        .weather-details {
            border-top: 1px solid #eee;
            padding-top: 8px;
            font-size: 14px;
        }
        .weather-row {
            margin: 4px 0;
        }
        .debug-section {
            margin-top: 8px;
        }
        .debug-toggle {
            cursor: pointer;
            color: #666;
            font-size: 12px;
        }
        .debug-content {
            font-size: 11px;
            color: #666;
            margin: 4px 0;
            background: #f5f5f5;
            padding: 4px;
            border-radius: 4px;
            max-height: 100px;
            overflow: auto;
        }
    </style>
</head>
<body>
    <header>
        <h1>🗺️ Peta Cuaca</h1>
        <nav style="margin-left:auto">
            <a href="index.php">Beranda</a>
        </nav>
    </header>

    <div class="container">
        <div class="controls" role="region" aria-label="Kontrol peta">
            <div class="search">
                <input id="city-input" type="text" placeholder="Masukkan nama kota (contoh: Jakarta, Surabaya, Bandung)">
                <button id="search-btn">Cari</button>
            </div>
            <div class="legend">
                <div style="font-weight:600;margin-bottom:6px">Kota contoh:</div>
                <div style="display:flex;gap:6px">
                    <button class="city-btn" data-city="Jakarta">Jakarta</button>
                    <button class="city-btn" data-city="Bandung">Bandung</button>
                    <button class="city-btn" data-city="Surabaya">Surabaya</button>
                </div>
                <div style="font-size:12px;color:rgba(255,255,255,0.7);margin-top:6px">
                    Klik marker untuk memuat data cuaca
                </div>
            </div>
        </div>
    </div>

    <div id="map"></div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
    <script>
        // Helper function untuk escape HTML
        function escapeHtml(str) {
            if (str === undefined || str === null) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        // Koordinat kota-kota utama Indonesia
        const CITY_COORDS = {
            "Jakarta": { lat: -6.2088, lon: 106.8456 },
            "Bandung": { lat: -6.9175, lon: 107.6191 },
            "Surabaya": { lat: -7.2575, lon: 112.7521 }
        };

        // Inisialisasi peta
        const map = L.map('map').setView([-2.5, 118], 5);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 18,
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        const markersLayer = L.layerGroup().addTo(map);

        // Fungsi untuk fetch data cuaca
        async function fetchWeather(city) {
            try {
                console.log('Fetching weather for:', city);
                
                const response = await fetch(`get_weather.php?city=${encodeURIComponent(city)}`, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    cache: 'no-store'
                });

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }

                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    const text = await response.text();
                    console.warn('Non-JSON response:', text);
                    return {
                        success: false,
                        message: 'Server mengembalikan response non-JSON',
                        debug: text
                    };
                }

                const data = await response.json();
                console.log('Weather data received:', data);
                return data;

            } catch (error) {
                console.error('Fetch error:', error);
                return {
                    success: false,
                    message: `Error: ${error.message}`
                };
            }
        }

        // Fungsi untuk membuat HTML popup
        function createPopupHtml(city, data) {
            if (!data) {
                return `<div class="popup-content">
                    <div class="city-name">${escapeHtml(city)}</div>
                    <div style="color:#666">Data cuaca tidak tersedia</div>
                </div>`;
            }

            if (data.success === false) {
                const debugHtml = data.debug ? 
                    `<div class="debug-section">
                        <details>
                            <summary class="debug-toggle">Debug Info</summary>
                            <div class="debug-content">${escapeHtml(data.debug)}</div>
                        </details>
                    </div>` : '';
                
                return `<div class="popup-content">
                    <div class="city-name">${escapeHtml(city)}</div>
                    <div style="color:#666">Gagal: ${escapeHtml(data.message || 'tidak tersedia')}</div>
                    ${debugHtml}
                </div>`;
            }

            // Ambil data weather dari response
            let weatherData = data;
            if (data.success === true && data.weather) {
                weatherData = data.weather;
            }

            // Parse data cuaca
            const name = weatherData.name || city;
            const country = weatherData.sys && weatherData.sys.country ? weatherData.sys.country : '';
            const main = weatherData.main || {};
            const weather = Array.isArray(weatherData.weather) ? weatherData.weather[0] : (weatherData.weather || {});
            const wind = weatherData.wind || {};

            const icon = weather.icon ? `https://openweathermap.org/img/wn/${weather.icon}@2x.png` : '';
            const description = weather.description || '';
            const temp = main.temp !== undefined ? Math.round(main.temp) : '-';
            const feelsLike = main.feels_like !== undefined ? Math.round(main.feels_like) : '-';
            const humidity = main.humidity !== undefined ? main.humidity : '-';
            const windSpeed = wind.speed !== undefined ? Math.round(wind.speed * 3.6) : '-'; // Convert m/s to km/h

            return `<div class="popup-content">
                <div class="weather-header">
                    ${icon ? `<img src="${icon}" alt="${escapeHtml(description)}" class="weather-icon">` : ''}
                    <div class="weather-info">
                        <div class="city-name">${escapeHtml(name)} ${country ? '(' + escapeHtml(country) + ')' : ''}</div>
                        <div class="weather-desc">${escapeHtml(description)}</div>
                    </div>
                </div>
                <div class="weather-details">
                    <div class="weather-row">🌡️ Suhu: <strong>${temp}°C</strong> (terasa ${feelsLike}°C)</div>
                    <div class="weather-row">💧 Kelembaban: <strong>${humidity}%</strong></div>
                    <div class="weather-row">💨 Angin: <strong>${windSpeed} km/h</strong></div>
                </div>
    
            </div>`;
        }

        // Fungsi untuk menambah marker kota
        async function addCityMarker(city) {
            console.log('Adding marker for city:', city);

            // Hapus marker lama jika ada
            markersLayer.clearLayers();

            // Tentukan koordinat
            let lat = null, lon = null;
            
            if (CITY_COORDS[city]) {
                lat = CITY_COORDS[city].lat;
                lon = CITY_COORDS[city].lon;
            }

            // Buat marker loading
            const position = lat && lon ? [lat, lon] : map.getCenter();
            const marker = L.marker(position).addTo(markersLayer);
            
            // Tampilkan popup loading
            marker.bindPopup(`<div class="popup-content">
                <div class="city-name">${escapeHtml(city)}</div>
                <div style="color:#666">Memuat data cuaca...</div>
            </div>`).openPopup();

            // Fetch data cuaca
            const weatherData = await fetchWeather(city);

            // Update koordinat dari API jika tersedia
            if (weatherData && weatherData.success && weatherData.weather && weatherData.weather.coord) {
                lat = weatherData.weather.coord.lat;
                lon = weatherData.weather.coord.lon;
                const newPosition = [lat, lon];
                marker.setLatLng(newPosition);
                map.setView(newPosition, 10);
            } else if (lat && lon) {
                map.setView([lat, lon], 10);
            }

            // Update popup dengan data cuaca
            const popupHtml = createPopupHtml(city, weatherData);
            marker.bindPopup(popupHtml).openPopup();

            // Simpan koordinat untuk penggunaan selanjutnya
            if (lat && lon && !CITY_COORDS[city]) {
                CITY_COORDS[city] = { lat: lat, lon: lon };
            }
        }

        // Fungsi untuk menambah marker demo
        function addDemoMarkers() {
            markersLayer.clearLayers();
            
            Object.keys(CITY_COORDS).forEach(city => {
                const coords = CITY_COORDS[city];
                const marker = L.marker([coords.lat, coords.lon]).addTo(markersLayer);
                
                marker.bindPopup(`<div class="popup-content">
                    <div class="city-name">${escapeHtml(city)}</div>
                    <div style="color:#666">Klik marker untuk memuat data cuaca</div>
                </div>`);
                
                marker.on('click', async () => {
                    await addCityMarker(city);
                });
            });
        }

        // Event listeners
        document.getElementById('search-btn').addEventListener('click', async () => {
            const city = document.getElementById('city-input').value.trim();
            if (!city) {
                alert('Silakan masukkan nama kota.');
                return;
            }
            await addCityMarker(city);
        });

        document.getElementById('city-input').addEventListener('keypress', async (e) => {
            if (e.key === 'Enter') {
                const city = e.target.value.trim();
                if (city) {
                    await addCityMarker(city);
                }
            }
        });

        document.querySelectorAll('.city-btn').forEach(button => {
            button.addEventListener('click', async (e) => {
                const city = e.target.getAttribute('data-city');
                document.getElementById('city-input').value = city;
                await addCityMarker(city);
            });
        });

        // Event untuk klik pada peta
        map.on('click', (e) => {
            const coords = e.latlng;
            const marker = L.marker(coords).addTo(markersLayer);
            marker.bindPopup(`<div class="popup-content">
                <div class="city-name">Koordinat</div>
                <div>Lat: ${coords.lat.toFixed(4)}</div>
                <div>Lon: ${coords.lng.toFixed(4)}</div>
            </div>`).openPopup();
        });

        // Inisialisasi marker demo
        addDemoMarkers();

        console.log('Peta cuaca berhasil dimuat');
    </script>
</body>
</html>