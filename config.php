<?php
// Konfigurasi API
$API_KEY = getenv('WEATHER_API_KEY') ?: '3e0c7b48086358f5ce90a70eb1d5620f';
$BASE_URL = "https://api.openweathermap.org/data/2.5/weather";
$FORECAST_URL = "https://api.openweathermap.org/data/2.5/forecast";
$GEO_URL = "https://api.openweathermap.org/geo/1.0/direct";

// Fungsi helper untuk validasi API key
if (!function_exists('isApiKeyValid')) {
    function isApiKeyValid() {
        global $API_KEY;
        return $API_KEY && $API_KEY !== 'YOUR_API_KEY_HERE' && strlen($API_KEY) > 10;
    }
}

// Fungsi helper untuk format tanggal
if (!function_exists('formatDate')) {
    function formatDate($timestamp) {
        return date('d M Y, H:i', $timestamp);
    }
}

// Fungsi helper untuk format waktu dengan timezone
if (!function_exists('formatTimeWithTimezone')) {
    function formatTimeWithTimezone($timestamp, $timezone_offset = 25200) {
        // Default timezone_offset = 25200 (UTC+7 untuk Jakarta)
        // Nantinya bisa disesuaikan dengan timezone dari API response
        return date('H:i', $timestamp + $timezone_offset);
    }
}

// Fungsi helper untuk mendapatkan timezone offset dari API response
if (!function_exists('getTimezoneOffset')) {
    function getTimezoneOffset($weatherData) {
        // Ambil timezone dari API response jika tersedia
        return isset($weatherData['timezone']) ? $weatherData['timezone'] : 25200; // Default Jakarta UTC+7
    }
}

// Fungsi helper untuk mendapatkan nama timezone berdasarkan offset
if (!function_exists('getTimezoneName')) {
    function getTimezoneName($offset) {
        $timezones = [
            25200 => 'WIB (UTC+7)', // Jakarta, Bandung, Semarang
            28800 => 'WITA (UTC+8)', // Denpasar, Makassar, Balikpapan  
            32400 => 'WIT (UTC+9)',  // Jayapura, Ambon, Manokwari
            23400 => 'UTC+6.5',     // Myanmar
            19800 => 'UTC+5.5',     // India
            16200 => 'UTC+4.5',     // Afghanistan
        ];
        
        return isset($timezones[$offset]) ? $timezones[$offset] : 'UTC+' . ($offset / 3600);
    }
}

// Fungsi helper untuk format waktu dengan nama timezone
if (!function_exists('formatTimeWithTimezoneInfo')) {
    function formatTimeWithTimezoneInfo($timestamp, $timezone_offset, $show_timezone = false) {
        $time = formatTimeWithTimezone($timestamp, $timezone_offset);
        
        if ($show_timezone) {
            $tz_name = getTimezoneName($timezone_offset);
            return $time . ' (' . $tz_name . ')';
        }
        
        return $time;
    }
}

// Fungsi untuk mendapatkan koordinat kota-kota Indonesia (untuk pengembangan fitur search)
if (!function_exists('getIndonesianCities')) {
    function getIndonesianCities() {
        return [
            'Jakarta' => ['lat' => -6.2088, 'lon' => 106.8456, 'timezone' => 25200],
            'Surabaya' => ['lat' => -7.2575, 'lon' => 112.7521, 'timezone' => 25200],
            'Bandung' => ['lat' => -6.9175, 'lon' => 107.6191, 'timezone' => 25200],
            'Medan' => ['lat' => 3.5952, 'lon' => 98.6722, 'timezone' => 25200],
            'Semarang' => ['lat' => -6.9667, 'lon' => 110.4167, 'timezone' => 25200],
            'Denpasar' => ['lat' => -8.6500, 'lon' => 115.2167, 'timezone' => 28800],
            'Makassar' => ['lat' => -5.1477, 'lon' => 119.4327, 'timezone' => 28800],
            'Balikpapan' => ['lat' => -1.2379, 'lon' => 116.8529, 'timezone' => 28800],
            'Jayapura' => ['lat' => -2.5489, 'lon' => 140.7017, 'timezone' => 32400],
            'Ambon' => ['lat' => -3.6954, 'lon' => 128.1814, 'timezone' => 32400],
            'Manokwari' => ['lat' => -0.8614, 'lon' => 134.0640, 'timezone' => 32400],
        ];
    }
}

// Fungsi helper untuk konversi suhu
if (!function_exists('celsiusToFahrenheit')) {
    function celsiusToFahrenheit($celsius) {
        return ($celsius * 9/5) + 32;
    }
}

// Fungsi helper untuk arah angin
if (!function_exists('getWindDirection')) {
    function getWindDirection($degrees) {
        $directions = ['N', 'NNE', 'NE', 'ENE', 'E', 'ESE', 'SE', 'SSE', 'S', 'SSW', 'SW', 'WSW', 'W', 'WNW', 'NW', 'NNW'];
        $index = round($degrees / 22.5) % 16;
        return $directions[$index];
    }
}

// Fungsi helper untuk mendapatkan nama hari dalam bahasa Indonesia
if (!function_exists('getDayName')) {
    function getDayName($timestamp) {
        $days = [
            'Sunday' => 'Minggu',
            'Monday' => 'Senin', 
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu'
        ];
        
        $englishDay = date('l', $timestamp);
        return $days[$englishDay] ?? $englishDay;
    }
}

// Fungsi helper untuk mendapatkan nama bulan dalam bahasa Indonesia
if (!function_exists('getMonthName')) {
    function getMonthName($timestamp) {
        $months = [
            'January' => 'Januari',
            'February' => 'Februari',
            'March' => 'Maret',
            'April' => 'April',
            'May' => 'Mei',
            'June' => 'Juni',
            'July' => 'Juli',
            'August' => 'Agustus',
            'September' => 'September',
            'October' => 'Oktober',
            'November' => 'November',
            'December' => 'Desember'
        ];
        
        $englishMonth = date('F', $timestamp);
        return $months[$englishMonth] ?? $englishMonth;
    }
}

// Fungsi helper untuk format cuaca ekstrem
if (!function_exists('getWeatherAlert')) {
    function getWeatherAlert($weatherData) {
        $alerts = [];
        
        // Temperature alerts
        if ($weatherData['main']['temp'] > 35) {
            $alerts[] = [
                'type' => 'heat',
                'title' => 'Peringatan Suhu Tinggi',
                'message' => 'Suhu sangat tinggi (' . round($weatherData['main']['temp']) . '°C). Hindari aktivitas di luar ruangan pada siang hari.'
            ];
        } elseif ($weatherData['main']['temp'] < 15) {
            $alerts[] = [
                'type' => 'cold',
                'title' => 'Peringatan Suhu Rendah', 
                'message' => 'Suhu rendah (' . round($weatherData['main']['temp']) . '°C). Kenakan pakaian hangat saat keluar rumah.'
            ];
        }
        
        // Wind alerts
        if (isset($weatherData['wind']['speed']) && $weatherData['wind']['speed'] > 10) {
            $alerts[] = [
                'type' => 'wind',
                'title' => 'Peringatan Angin Kencang',
                'message' => 'Angin kencang (' . round($weatherData['wind']['speed'] * 3.6) . ' km/h). Berhati-hati saat berkendara.'
            ];
        }
        
        // Rain alerts
        if (isset($weatherData['rain']['1h']) && $weatherData['rain']['1h'] > 10) {
            $alerts[] = [
                'type' => 'rain',
                'title' => 'Peringatan Hujan Lebat',
                'message' => 'Hujan lebat! Siapkan payung dan hindari daerah rawan banjir.'
            ];
        }
        
        return $alerts;
    }
}