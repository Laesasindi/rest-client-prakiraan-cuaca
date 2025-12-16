<?php
// Handle different execution contexts (web vs CLI)
$config_path = file_exists('../config.php') ? '../config.php' : 'config.php';
if (file_exists($config_path)) {
    require_once $config_path;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Peta Cuaca - Weather REST Client</title>
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
            max-width: 800px;
            margin: 0 auto;
            text-align: center;
            padding: 20px;
        }
        .placeholder {
            background: rgba(255, 255, 255, 0.1);
            padding: 40px;
            border-radius: 20px;
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 15px 35px rgba(0,0,0,0.4);
            color: white;
        }
        .placeholder h1 {
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            margin-bottom: 20px;
        }
        .placeholder p {
            opacity: 0.9;
            margin-bottom: 15px;
        }
        .back-btn {
            display: inline-block;
            background: rgba(0, 0, 0, 0.4);
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 15px;
            margin-top: 20px;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .back-btn:hover {
            background: rgba(0, 0, 0, 0.6);
            text-decoration: none;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.3);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="placeholder">
            <h1>🗺️ Peta Cuaca</h1>
            <p>Halaman ini akan dikembangkan oleh anggota tim.</p>
            <p>Fitur yang akan tersedia:</p>
            <ul style="text-align: left; display: inline-block;">
                <li>Peta interaktif dengan data cuaca</li>
                <li>Layer suhu, kelembaban, dan angin</li>
                <li>Zoom dan navigasi peta</li>
                <li>Marker lokasi dengan info cuaca</li>
            </ul>
            <a href="../index.php" class="back-btn">← Kembali ke Home</a>
        </div>
    </div>
</body>
</html>