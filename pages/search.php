<?php
require_once '../config.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pencarian - Weather REST Client</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: url('https://images.unsplash.com/photo-1506905925346-21bda4d32df4?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80') center/cover no-repeat;
            min-height: 100vh;
            margin: 0;
            color: #fff;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
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

        .card {
            background: rgba(255, 255, 255, 0.06);
            color: #fff;
            max-width: 900px;
            width: 100%;
            border-radius: 12px;
            box-shadow: 0 12px 40px rgba(0,0,0,0.45);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255,255,255,0.08);
            overflow: hidden;
            display: grid;
            grid-template-columns: 1fr 420px;
            position: relative;
        }

        /* Side features: small vertical feature buttons */
        .side-features {
            position: fixed;
            top: 24px;
            right: 24px;
            display: flex;
            flex-direction: column;
            gap: 12px;
            z-index: 999;
            transition: transform 0.18s ease;
        }

        .feature-small {
            background: rgba(0,0,0,0.45);
            color: #fff;
            padding: 10px 12px;
            border-radius: 12px;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 160px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.4);
            backdrop-filter: blur(6px);
            border: 1px solid rgba(255,255,255,0.06);
            font-weight: 600;
        }

        .feature-small .icon {
            width: 28px;
            height: 28px;
            flex: 0 0 28px;
        }

        @media (max-width: 880px) {
            .side-features {
                position: fixed;
                top: 12px;
                left: 50%;
                transform: translateX(-50%);
                flex-direction: row;
                gap: 10px;
            }
            .feature-small { min-width: 120px; padding: 8px 10px; }
        }

        .left {
            padding: 30px;
        }

        h1 { margin-bottom: 10px; }
        p.lead { color: rgba(255,255,255,0.9); margin-bottom: 20px; }


        /* legacy .back-btn removed from card; use fixed back button on background */

        .back-btn-fixed {
            position: fixed;
            top: 24px;
            left: 24px;
            z-index: 999;
            background: rgba(0,0,0,0.45);
            color: #fff;
            padding: 10px 12px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 140px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.4);
            backdrop-filter: blur(6px);
            border: 1px solid rgba(255,255,255,0.06);
            font-weight: 600;
            text-decoration: none;
        }

        .back-btn-fixed .icon { font-size: 18px; width: 20px; }

        .back-btn-fixed:hover { transform: translateY(-3px); }

        form.search-form {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        input[type="search"] {
            flex: 1;
            padding: 12px 14px;
            border-radius: 8px;
            border: 1px solid rgba(255,255,255,0.08);
            background: rgba(255,255,255,0.06);
            color: #fff;
            font-size: 16px;
        }

        button.btn {
            background: #0ea5a4;
            color: white;
            border: none;
            padding: 12px 18px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
        }

        .right {
            background: linear-gradient(180deg, rgba(255,255,255,0.03), rgba(255,255,255,0));
            padding: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 320px;
        }

        .weather-result {
            width: 100%;
            max-width: 360px;
            background: rgba(255,255,255,0.04);
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.45);
            text-align: center;
            color: #fff;
        }

        .weather-result img { width: 96px; height: 96px; }
        .temp { font-size: 42px; font-weight: 700; margin: 10px 0; }
        .desc { text-transform: capitalize; color: #444; }
        .meta { display: grid; grid-template-columns: repeat(2,1fr); gap: 10px; margin-top: 16px; }
        .meta div { background: rgba(0,0,0,0.03); padding: 10px; border-radius: 8px; font-size: 14px; }

        .message { padding: 12px; border-radius: 8px; margin-top: 10px; }
        .message.error { background: #ffe3e3; color: #9b1c1c; }
        .message.info { background: #eef2ff; color: #312e81; }

        @media (max-width: 880px) {
            .card { grid-template-columns: 1fr; }
            .right { order: -1; }
        }
    </style>
</head>
<body>
    <div class="side-features" aria-hidden="false">
        <a href="forecast.php" class="feature-small" title="Prakiraan Cuaca">
            <div class="icon">📊</div>
            <div>Prakiraan</div>
        </a>
        <a href="peta.php" class="feature-small" title="Peta Kota">
            <div class="icon">🗺️</div>
            <div>Peta Kota</div>
        </a>
        <a href="geography.php" class="feature-small" title="Geography">
            <div class="icon">🌍</div>
            <div>Geography</div>
        </a>
    </div>
    <a href="../index.php" class="feature-small back-btn-fixed" title="Kembali ke Home">
        <div class="icon">←</div>
        <div>Home</div>
    </a>
    <div class="card">
        <div class="left">
            <h1>🔎 Cari Cuaca</h1>
            
            <p class="lead">Masukkan nama kota, misal: <em>Jakarta</em>, <em>Bandung</em>, atau <em>Surabaya</em>.</p>

            <form id="searchForm" class="search-form" role="search" onsubmit="return false;">
                <input id="cityInput" type="search" name="city" placeholder="Ketik nama kota..." aria-label="Nama kota" required minlength="2">
                <button id="searchBtn" class="btn" type="submit">Cari</button>
            </form>

            <div id="info" aria-live="polite"></div>
            <div style="margin-top:16px; color:#666; font-size:13px;"> <strong></strong></div>
        </div> 

        <div class="right">
            <div id="result" class="weather-result" aria-hidden="true">
                <div id="placeholder">
                    <img src="https://openweathermap.org/img/wn/03d@2x.png" alt="icon">
                    <div style="font-weight:700; margin-top:10px;">Hasil Pencarian</div>
                    <div style="color:#666; margin-top:6px;">Masukkan nama kota lalu tekan Cari</div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const form = document.getElementById('searchForm');
        const input = document.getElementById('cityInput');
        const resultBox = document.getElementById('result');
        const info = document.getElementById('info');
        const placeholder = document.getElementById('placeholder');

        function setInfo(message, type='info'){
            info.innerHTML = `<div class="message ${type}">${message}</div>`;
        }

        function renderWeather(data){
            resultBox.setAttribute('aria-hidden','false');
            resultBox.innerHTML = `
                <img src="https://openweathermap.org/img/wn/${data.weather[0].icon}@2x.png" alt="icon">
                <div style="font-size:18px; font-weight:700; margin-top:10px">${escapeHtml(data.name)}, ${escapeHtml(data.sys.country)}</div>
                <div class="temp">${Math.round(data.main.temp)}°C</div>
                <div class="desc">${escapeHtml(data.weather[0].description)}</div>
                <div class="meta">
                    <div><strong>Feels</strong><br>${Math.round(data.main.feels_like)}°C</div>
                    <div><strong>Humid</strong><br>${data.main.humidity}%</div>
                    <div><strong>Pressure</strong><br>${data.main.pressure} hPa</div>
                    <div><strong>Wind</strong><br>${data.wind.speed} m/s</div>
                </div>
            `;
        }

        function renderError(msg){
            resultBox.setAttribute('aria-hidden','false');
            resultBox.innerHTML = `<div style="color:#9b1c1c; font-weight:700;">⚠️ ${escapeHtml(msg)}</div>`;
        }

        function escapeHtml(s){
            return String(s).replace(/[&<>"']/g, function(c){
                return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":"&#39;"}[c];
            });
        }

        async function doSearch(city){
            if(!city || city.trim().length < 2){
                setInfo('Masukkan minimal 2 karakter untuk nama kota.', 'error');
                return;
            }

            setInfo('Mencari cuaca untuk <strong>' + escapeHtml(city) + '</strong>...','info');
            resultBox.setAttribute('aria-hidden','true');

            try{
                const res = await fetch('../get_weather.php?city=' + encodeURIComponent(city));
                const json = await res.json();

                if(!json.success){
                    renderError(json.message || 'Gagal mencari data.');
                    setInfo('Terjadi kesalahan.', 'error');
                    return;
                }

                renderWeather(json.weather);
                setInfo('Menampilkan hasil untuk <strong>' + escapeHtml(json.weather.name) + '</strong>.','info');
            }catch(err){
                renderError('Gagal terhubung ke server.');
                setInfo('Terjadi kesalahan koneksi.', 'error');
            }
        }

        form.addEventListener('submit', ()=> doSearch(input.value));

        // Auto-run search jika ada ?city= di URL
        (function(){
            const params = new URLSearchParams(window.location.search);
            const city = params.get('city');
            if(city){
                input.value = city;
                doSearch(city);
            }
        })();
    </script>
</body>
</html>
