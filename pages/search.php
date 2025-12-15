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
    <title>Search Cuaca - Weather REST Client</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%);
            min-height: 100vh;
            margin: 0;
            padding: 20px;
            color: white;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            text-align: center;
        }
        .placeholder {
            background: rgba(255, 255, 255, 0.1);
            padding: 40px;
            border-radius: 20px;
            backdrop-filter: blur(10px);
        }
        .back-btn {
            display: inline-block;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 10px;
            margin-top: 20px;
            transition: all 0.3s ease;
        }
        .back-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            text-decoration: none;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="placeholder">
            <h1>🔍 Search Cuaca</h1>
            <p>Halaman ini akan dikembangkan oleh anggota tim.</p>
            <p>Fitur yang akan tersedia:</p>
            <ul style="text-align: left; display: inline-block;">
                <li>Pencarian cuaca berdasarkan nama kota</li>
                <li>Pencarian berdasarkan koordinat</li>
                <li>Riwayat pencarian</li>
                <li>Filter dan sorting hasil</li>
            </ul>
            <a href="../index.php" class="back-btn">← Kembali ke Home</a>
        </div>
    </div>
</body>
</html>