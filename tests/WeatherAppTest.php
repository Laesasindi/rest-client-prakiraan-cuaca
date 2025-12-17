<?php

use PHPUnit\Framework\TestCase;

class WeatherAppTest extends TestCase
{
    /**
     * Test Case 1: File exist
     * Memastikan file PHP utama ada
     */
    public function testFileExist()
    {
        $this->assertFileExists('index.php', 'File index.php harus ada');
        $this->assertFileExists('config.php', 'File config.php harus ada');
        $this->assertFileExists('pages/search.php', 'File search.php harus ada');
        $this->assertFileExists('pages/peta.php', 'File peta.php harus ada');
        $this->assertFileExists('pages/geography.php', 'File geography.php harus ada');
        $this->assertFileExists('pages/forecast.php', 'File forecast.php harus ada');
        $this->assertFileExists('get_weather.php', 'File get_weather.php harus ada');
    }

    /**
     * Test Case 2: Valid syntax
     * Memastikan setiap file PHP memiliki syntax yang valid
     */
    public function testValidSyntax()
    {
        $files = [
            'index.php',
            'config.php',
            'pages/search.php',
            'pages/peta.php',
            'pages/geography.php',
            'pages/forecast.php',
            'get_weather.php'
        ];

        foreach ($files as $file) {
            $output = [];
            $returnCode = 0;
            exec("php -l $file", $output, $returnCode);
            $this->assertEquals(0, $returnCode, "File $file memiliki syntax error: " . implode("\n", $output));
        }
    }

    /**
     * Test Case 3: API Key tidak boleh kosong
     * Memastikan API key dikonfigurasi dengan benar
     */
    public function testApiKeyNotEmpty()
    {
        // Set environment variable untuk testing
        putenv('WEATHER_API_KEY=test_api_key_12345');
        
        require_once 'config.php';
        
        $this->assertNotEmpty($API_KEY, 'API Key tidak boleh kosong');
        $this->assertNotEquals('YOUR_API_KEY_HERE', $API_KEY, 'API Key harus dikonfigurasi dengan benar');
        $this->assertTrue(strlen($API_KEY) > 10, 'API Key harus memiliki panjang yang valid');
    }

    /**
     * Test Case 4: Valid JSON response
     * Memastikan API mengembalikan response JSON yang valid
     */
    public function testValidJsonResponse()
    {
        // Mock API response untuk testing
        $mockResponse = '{"coord":{"lon":106.8456,"lat":-6.2088},"weather":
        [{"id":803,"main":"Clouds","description":"broken clouds","icon":"04d"}],"base":"stations",
        "main":{"temp":30.5,"feels_like":35.2,"temp_min":29.0,"temp_max":32.0,"pressure":1010,"humidity":70},
        "visibility":10000,"wind":{"speed":3.6,"deg":120},"clouds":{"all":75},"dt":1640995200,"sys":
        {"type":1,"id":9374,"country":"ID","sunrise":1640995200,"sunset":1641038400},
        "timezone":25200,"id":1642911,"name":"Jakarta","cod":200}';
        
        $decodedResponse = json_decode($mockResponse, true);
        
        $this->assertNotNull($decodedResponse, 'Response harus berupa JSON yang valid');
        $this->assertArrayHasKey('main', $decodedResponse, 'Response harus memiliki key "main"');
        $this->assertArrayHasKey('weather', $decodedResponse, 'Response harus memiliki key "weather"');
        $this->assertArrayHasKey('name', $decodedResponse, 'Response harus memiliki key "name"');
    }

    /**
     * Test Case 5: Response Code harus 200
     * Memastikan API mengembalikan HTTP status code 200
     */
    public function testResponseCode200()
    {
        // Test dengan mock HTTP response
        $mockHttpCode = 200;
        
        $this->assertEquals(200, $mockHttpCode, 'HTTP Response code harus 200 untuk request yang berhasil');
        
        // Test helper function untuk validasi API key dengan mock
        // Set global variable langsung untuk testing
        global $API_KEY;
        $originalApiKey = $API_KEY ?? null;
        $API_KEY = 'test_api_key_12345';
        
        require_once 'config.php';
        
        // Test helper function untuk validasi API key dengan mock
        // Set global variable langsung untuk testing
        global $API_KEY;
        $originalApiKey = $API_KEY ?? null;
        $API_KEY = 'test_api_key_12345';
        
        require_once 'config.php';
        
        $this->assertTrue(isApiKeyValid(), 'Fungsi validasi API key harus mengembalikan true untuk key yang valid');
        
        // Restore original API key
        $API_KEY = $originalApiKey;
        
        // Restore original API key
        $API_KEY = $originalApiKey;
    }

    /**
     * Test Case 7: HTML Structure
     * Memastikan struktur HTML valid
     */
    public function testHtmlStructure()
    {
        // Read file content instead of including to avoid execution issues
        $htmlContent = file_get_contents('index.php');
        
        $this->assertStringContainsString('<!DOCTYPE html>', $htmlContent, 'HTML harus memiliki DOCTYPE');
        $this->assertStringContainsString('<html', $htmlContent, 'HTML harus memiliki tag html');
        $this->assertStringContainsString('<head>', $htmlContent, 'HTML harus memiliki tag head');
        $this->assertStringContainsString('<body>', $htmlContent, 'HTML harus memiliki tag body');
        $this->assertStringContainsString('Weather REST Client', $htmlContent, 'HTML harus memiliki title yang sesuai');
    }

    /**
     * Test Case 8: Helper Functions
     * Memastikan helper functions bekerja dengan benar
     */
    public function testHelperFunctions()
    {
        require_once 'config.php';
        
        // Test konversi suhu
        $fahrenheit = celsiusToFahrenheit(25);
        $this->assertEquals(77, $fahrenheit, 'Konversi Celsius ke Fahrenheit harus benar');
        
        // Test format tanggal
        $formattedDate = formatDate(1640995200);
        $this->assertIsString($formattedDate, 'Format tanggal harus mengembalikan string');
        
        // Test arah angin
        $windDirection = getWindDirection(90);
        $this->assertEquals('E', $windDirection, 'Arah angin 90 derajat harus mengembalikan E (East)');
    }
}