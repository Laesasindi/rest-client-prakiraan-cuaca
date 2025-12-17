# Weather REST Client

Aplikasi REST Client untuk menampilkan informasi cuaca menggunakan OpenWeatherMap API.

## 📋 Deskripsi Proyek

Proyek ini adalah tugas besar mata kuliah Pengujian Perangkat Lunak yang mengimplementasikan aplikasi web sederhana untuk menampilkan informasi cuaca real-time menggunakan REST API.

## 🚀 Fitur Utama

### 🌤️ Fitur Prakiraan Cuaca Lengkap

Aplikasi ini dilengkapi dengan halaman prakiraan cuaca yang komprehensif (`pages/forecast.php`) dengan fitur:

#### 📊 Prakiraan Per Jam (24 jam ke depan)
- Waktu dalam format jam:menit
- Icon cuaca yang sesuai kondisi
- Suhu dalam Celsius
- Peluang hujan dalam persentase
- Kecepatan angin dalam km/jam

#### 📅 Prakiraan 7 Hari Ke Depan
- Nama hari dan tanggal
- Kondisi cuaca dengan icon
- Suhu minimum dan maksimum
- Peluang hujan

#### 📈 Detail Cuaca Tambahan
- Kelembaban udara (%)
- Tekanan udara (hPa)
- Kecepatan dan arah angin
- Jarak pandang (km)
- Tutupan awan (%)

#### ☀️ Informasi Matahari
- Waktu matahari terbit
- Waktu matahari terbenam

#### ⚠️ Peringatan Cuaca Ekstrem
- Deteksi suhu tinggi (>35°C)
- Deteksi suhu rendah (<15°C)
- Peringatan angin kencang (>10 m/s)
- Alert hujan lebat

#### 🎨 Design Features
- Background gambar alam yang indah
- Card transparan dengan blur effect
- Responsive design untuk semua perangkat
- Hover effects dan animasi smooth
- Color coding untuk berbagai jenis informasi

### ✅ Sudah Tersedia
- **Home Page**: Tampilan utama dengan manajemen multiple kota dan auto-refresh
- **Forecast Page**: Prakiraan cuaca lengkap dengan data per jam dan harian
- **Responsive Design**: Tampilan yang menyesuaikan dengan berbagai ukuran layar
- **Real-time Weather**: Data cuaca real-time dari OpenWeatherMap API
- **API Key Validation**: Deteksi otomatis status API key dan pesan error yang informatif
- **Error Handling**: Penanganan error yang komprehensif untuk berbagai skenario

### 🔄 Dalam Pengembangan (oleh anggota tim)
- **Search**: Pencarian cuaca berdasarkan nama kota atau koordinat
- **Map**: Visualisasi cuaca dalam bentuk peta interaktif  
- **Geography**: Analisis cuaca berdasarkan data geografis

## 🛠️ Teknologi yang Digunakan

- **Backend**: PHP 7.4+
- **Frontend**: HTML5, CSS3, JavaScript
- **API**: OpenWeatherMap API
- **Testing**: PHPUnit
- **CI/CD**: GitHub Actions

## 📁 Struktur Proyek

```
weather-rest-client/
├── index.php              # Halaman utama
├── config.php             # Konfigurasi API dan helper functions
├── pages/                 # Halaman fitur
│   ├── search.php         # Fitur pencarian (dalam pengembangan)
│   ├── peta.php           # Fitur peta (dalam pengembangan)
│   ├── geography.php     # Fitur geography (dalam pengembangan)
│   └── forecast.php      # Prakiraan cuaca lengkap (sudah tersedia)
├── tests/                # Unit tests
│   └── WeatherAppTest.php
├── .github/              # GitHub Actions workflows
│   └── workflows/
│       └── php-test.yml
├── phpunit.xml           # Konfigurasi PHPUnit
└── README.md            # Dokumentasi proyek
```

## ⚙️ Setup dan Instalasi

### 1. Clone Repository
```bash
git clone <repository-url>
cd weather-rest-client
```

### 2. Konfigurasi API Key (WAJIB)
⚠️ **Aplikasi ini memerlukan API key yang valid untuk berfungsi.**

Dapatkan API key dari [OpenWeatherMap](https://openweathermap.org/api) dan set sebagai environment variable:

```bash
# Windows
set WEATHER_API_KEY=your_api_key_here

# Linux/Mac
export WEATHER_API_KEY=your_api_key_here
```

Atau edit file `config.php` dan ganti `3e0c7b48086358f5ce90a70eb1d5620f` dengan API key Anda yang valid.

**Catatan**: Tanpa API key yang valid, aplikasi akan menampilkan pesan error dan tidak dapat menampilkan data cuaca.

### 3. Jalankan Aplikasi
```bash
# Menggunakan PHP built-in server
php -S localhost:8000

# Atau deploy ke web server (Apache/Nginx)
```

### 4. Akses Aplikasi
Buka browser dan akses `http://localhost:8000`

## 🧪 Testing

### Menjalankan Unit Tests
```bash
# Install PHPUnit (jika belum ada)
composer install

# Jalankan tests
./vendor/bin/phpunit

# Atau menggunakan PHP langsung
php vendor/bin/phpunit
```

### Test Cases yang Diimplementasikan
1. **File Exist Test**: Memastikan file PHP utama ada
2. **Valid Syntax Test**: Memvalidasi syntax PHP
3. **API Key Test**: Memastikan API key tidak kosong
4. **Valid JSON Response Test**: Memvalidasi response API
5. **HTTP 200 Response Test**: Memastikan API mengembalikan status 200

## 🔄 CI/CD Pipeline

Proyek menggunakan GitHub Actions untuk automated testing:
- **Trigger**: Push dan Pull Request ke branch main
- **PHP Versions**: 7.4, 8.0, 8.1
- **Tests**: PHPUnit, Syntax validation, Code quality checks

## 👥 Tim Pengembang

- **Maintainer**: [Nama Anda] - Home page dan setup utama
- **Developer 1**: [Nama Anggota 1] - Search feature
- **Developer 2**: [Nama Anggota 2] - Map feature  
- **Developer 3**: [Nama Anggota 3] - Geography feature

## 📝 Cara Kontribusi

1. Fork repository ini
2. Buat branch untuk fitur Anda (`git checkout -b feature/nama-fitur`)
3. Commit perubahan (`git commit -am 'Menambahkan fitur baru'`)
4. Push ke branch (`git push origin feature/nama-fitur`)
5. Buat Pull Request

## 📄 Lisensi

Proyek ini dibuat untuk keperluan akademik - Tugas Besar Pengujian Perangkat Lunak.

## 🆘 Troubleshooting

### API Key Issues
- Pastikan API key valid dan aktif
- Periksa environment variable `WEATHER_API_KEY`
- Pastikan tidak ada typo dalam API key

### CORS Issues
- Jika menggunakan localhost, pastikan menggunakan PHP built-in server
- Untuk production, konfigurasi CORS headers di web server

### Performance Issues
- API memiliki rate limit, hindari request berlebihan
- Implementasikan caching untuk data yang sering diakses

---

**Happy Coding! 🌤️**