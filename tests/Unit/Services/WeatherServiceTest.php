<?php

namespace Tests\Unit\Services;

use App\Services\WeatherService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class WeatherServiceTest extends TestCase
{
    protected WeatherService $weatherService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->weatherService = new WeatherService();
    }

    public function test_get_weather_description()
    {
        $this->assertEquals('Cerah', $this->weatherService->getWeatherDescription(0));
        $this->assertEquals('Hujan Ringan', $this->weatherService->getWeatherDescription(61));
        $this->assertEquals('Badai Petir', $this->weatherService->getWeatherDescription(95));
        $this->assertEquals('Tidak Diketahui', $this->weatherService->getWeatherDescription(999));
    }

    public function test_get_weather_icon()
    {
        $this->assertEquals('Sun', $this->weatherService->getWeatherIcon(0));
        $this->assertEquals('CloudRain', $this->weatherService->getWeatherIcon(61));
        $this->assertEquals('CloudLightning', $this->weatherService->getWeatherIcon(95));
        $this->assertEquals('Cloud', $this->weatherService->getWeatherIcon(999));
    }

    public function test_calculate_flood_risk()
    {
        // High risk scenarios
        $this->assertEquals('Tinggi', $this->weatherService->calculateFloodRisk(25.0, 61));
        $this->assertEquals('Tinggi', $this->weatherService->calculateFloodRisk(5.0, 95));
        
        // Medium risk scenarios
        $this->assertEquals('Sedang', $this->weatherService->calculateFloodRisk(15.0, 61));
        $this->assertEquals('Sedang', $this->weatherService->calculateFloodRisk(5.0, 80));
        
        // Low risk scenarios
        $this->assertEquals('Rendah', $this->weatherService->calculateFloodRisk(5.0, 61));
        $this->assertEquals('Rendah', $this->weatherService->calculateFloodRisk(0.0, 0));
    }

    public function test_format_rainfall()
    {
        $this->assertEquals('0 mm', $this->weatherService->formatRainfall(0.0));
        $this->assertEquals('< 0.1 mm', $this->weatherService->formatRainfall(0.05));
        $this->assertEquals('2.5 mm', $this->weatherService->formatRainfall(2.5));
        $this->assertEquals('10.0 mm', $this->weatherService->formatRainfall(10.0));
    }

    public function test_get_rainfall_intensity()
    {
        $this->assertEquals('Tidak ada hujan', $this->weatherService->getRainfallIntensity(0.0));
        $this->assertEquals('Sangat ringan', $this->weatherService->getRainfallIntensity(0.3));
        $this->assertEquals('Ringan', $this->weatherService->getRainfallIntensity(2.0));
        $this->assertEquals('Sedang', $this->weatherService->getRainfallIntensity(6.0));
        $this->assertEquals('Lebat', $this->weatherService->getRainfallIntensity(15.0));
        $this->assertEquals('Sangat lebat', $this->weatherService->getRainfallIntensity(25.0));
    }

    public function test_get_flood_risk_variant()
    {
        $this->assertEquals('destructive', $this->weatherService->getFloodRiskVariant('Tinggi'));
        $this->assertEquals('warning', $this->weatherService->getFloodRiskVariant('Sedang'));
        $this->assertEquals('default', $this->weatherService->getFloodRiskVariant('Rendah'));
    }

    public function test_get_weather_forecast_with_cache()
    {
        // Mock the HTTP response
        Http::fake([
            'https://api.open-meteo.com/*' => Http::response([
                'current' => [
                    'temperature_2m' => 28.5,
                    'relative_humidity_2m' => 75,
                    'precipitation' => 15.0, // Changed to trigger "Sedang" risk
                    'weather_code' => 61,
                    'wind_speed_10m' => 10.2
                ],
                'hourly' => [
                    'temperature_2m' => [28.5, 29.0, 29.5]
                ],
                'daily' => [
                    'weather_code' => [61, 63, 0],
                    'temperature_2m_max' => [32.0, 33.0, 31.0],
                    'temperature_2m_min' => [25.0, 26.0, 24.0]
                ]
            ], 200),
        ]);

        // Clear cache to ensure fresh request
        Cache::forget('weather_-6.2088_106.8456');

        $result = $this->weatherService->getWeatherForecast(-6.2088, 106.8456);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('current', $result);
        $this->assertArrayHasKey('weather_description', $result['current']);
        $this->assertArrayHasKey('weather_icon', $result['current']);
        $this->assertArrayHasKey('flood_risk', $result);
        
        $this->assertEquals('Hujan Ringan', $result['current']['weather_description']);
        $this->assertEquals('CloudRain', $result['current']['weather_icon']);
        $this->assertEquals('Sedang', $result['flood_risk']);
    }

    public function test_get_weather_forecast_handles_api_failure()
    {
        // Mock API failure
        Http::fake([
            'https://api.open-meteo.com/*' => Http::response([], 500),
        ]);

        // Clear cache to ensure fresh request
        Cache::forget('weather_-6.2088_106.8456');

        $result = $this->weatherService->getWeatherForecast(-6.2088, 106.8456);

        $this->assertNull($result);
    }
} 