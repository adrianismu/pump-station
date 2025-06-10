<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class WeatherService
{
    private const OPEN_METEO_URL = 'https://api.open-meteo.com/v1/forecast';
    private const CACHE_TTL = 10 * 60; // 10 minutes in seconds

    /**
     * Get weather forecast for a specific location
     * 
     * @param float $latitude
     * @param float $longitude
     * @return array|null
     */
    public function getWeatherForecast(float $latitude, float $longitude): ?array
    {
        // Round coordinates to 4 decimal places for consistent cache keys
        // 4 decimal places gives ~11m accuracy which is sufficient for weather data
        $lat = round($latitude, 4);
        $lng = round($longitude, 4);
        $cacheKey = "weather_{$lat}_{$lng}";
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($latitude, $longitude) {
            return $this->fetchWeatherData($latitude, $longitude);
        });
    }

    /**
     * Fetch weather data from Open-Meteo API
     * 
     * @param float $latitude
     * @param float $longitude
     * @return array|null
     */
    private function fetchWeatherData(float $latitude, float $longitude): ?array
    {
        try {
            $response = Http::withOptions([
                'verify' => false, // Disable SSL verification for development
                'timeout' => 10,
            ])->get(self::OPEN_METEO_URL, [
                'latitude' => $latitude,
                'longitude' => $longitude,
                'current' => 'temperature_2m,relative_humidity_2m,precipitation,rain,weather_code,wind_speed_10m',
                'hourly' => 'temperature_2m,precipitation_probability,precipitation,rain,weather_code',
                'daily' => 'weather_code,temperature_2m_max,temperature_2m_min,precipitation_sum,precipitation_probability_max',
                'timezone' => 'auto'
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $this->processWeatherData($data);
            }

            Log::warning('Weather API request failed', [
                'status' => $response->status(),
                'latitude' => $latitude,
                'longitude' => $longitude
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Weather API error', [
                'message' => $e->getMessage(),
                'latitude' => $latitude,
                'longitude' => $longitude
            ]);

            return null;
        }
    }

    /**
     * Process and enhance weather data
     * 
     * @param array $data
     * @return array
     */
    private function processWeatherData(array $data): array
    {
        $processedData = $data;

        // Add weather descriptions and icons
        if (isset($data['current']['weather_code'])) {
            $processedData['current']['weather_description'] = $this->getWeatherDescription($data['current']['weather_code']);
            $processedData['current']['weather_icon'] = $this->getWeatherIcon($data['current']['weather_code']);
        }

        // Enhanced precipitation formatting
        if (isset($data['current']['precipitation'])) {
            $precipitation = (float) $data['current']['precipitation'];
            $processedData['current']['precipitation_formatted'] = $this->formatRainfall($precipitation);
            $processedData['current']['precipitation_intensity'] = $this->getRainfallIntensity($precipitation);
        }

        // Enhanced rainfall data (separate from precipitation)
        if (isset($data['current']['rain'])) {
            $rain = (float) $data['current']['rain'];
            $processedData['current']['rain_formatted'] = $this->formatRainfall($rain);
            $processedData['current']['rain_intensity'] = $this->getRainfallIntensity($rain);
        }

        // Process daily data
        if (isset($data['daily']['weather_code'])) {
            $processedData['daily']['weather_descriptions'] = [];
            $processedData['daily']['weather_icons'] = [];
            
            foreach ($data['daily']['weather_code'] as $code) {
                $processedData['daily']['weather_descriptions'][] = $this->getWeatherDescription($code);
                $processedData['daily']['weather_icons'][] = $this->getWeatherIcon($code);
            }
        }

        // Enhanced daily precipitation formatting
        if (isset($data['daily']['precipitation_sum'])) {
            $processedData['daily']['precipitation_formatted'] = [];
            $processedData['daily']['precipitation_intensity'] = [];
            
            foreach ($data['daily']['precipitation_sum'] as $precip) {
                $precipitation = (float) $precip;
                $processedData['daily']['precipitation_formatted'][] = $this->formatRainfall($precipitation);
                $processedData['daily']['precipitation_intensity'][] = $this->getRainfallIntensity($precipitation);
            }
        }

        // Fix wind speed field name (API returns wind_speed_10m)
        if (isset($data['current']['wind_speed_10m'])) {
            $processedData['current']['wind_speed'] = $data['current']['wind_speed_10m'];
        }

        // Add flood risk analysis
        if (isset($data['current']['precipitation']) && isset($data['current']['weather_code'])) {
            $processedData['flood_risk'] = $this->calculateFloodRisk(
                $data['current']['precipitation'], 
                $data['current']['weather_code']
            );
        }

        return $processedData;
    }

    /**
     * Get weather code description
     * 
     * @param int $code
     * @return string
     */
    public function getWeatherDescription(int $code): string
    {
        $weatherCodes = [
            0 => "Cerah",
            1 => "Sebagian Berawan",
            2 => "Berawan",
            3 => "Mendung",
            45 => "Kabut",
            48 => "Kabut Beku",
            51 => "Gerimis Ringan",
            53 => "Gerimis Sedang",
            55 => "Gerimis Lebat",
            56 => "Gerimis Beku Ringan",
            57 => "Gerimis Beku Lebat",
            61 => "Hujan Ringan",
            63 => "Hujan Sedang",
            65 => "Hujan Lebat",
            66 => "Hujan Beku Ringan",
            67 => "Hujan Beku Lebat",
            71 => "Salju Ringan",
            73 => "Salju Sedang",
            75 => "Salju Lebat",
            77 => "Butiran Salju",
            80 => "Hujan Ringan",
            81 => "Hujan Sedang",
            82 => "Hujan Sangat Lebat",
            85 => "Hujan Salju Ringan",
            86 => "Hujan Salju Lebat",
            95 => "Badai Petir",
            96 => "Badai Petir dengan Hujan Es Ringan",
            99 => "Badai Petir dengan Hujan Es Lebat",
        ];

        return $weatherCodes[$code] ?? "Tidak Diketahui";
    }

    /**
     * Get weather icon based on weather code
     * 
     * @param int $code
     * @return string
     */
    public function getWeatherIcon(int $code): string
    {
        if ($code === 0) return "Sun";
        if ($code === 1) return "Cloud";
        if (in_array($code, [2, 3])) return "CloudSun";
        if (in_array($code, [45, 48])) return "CloudFog";
        if (in_array($code, [51, 53, 55, 56, 57])) return "CloudDrizzle";
        if (in_array($code, [61, 63, 65, 66, 67, 80, 81, 82])) return "CloudRain";
        if (in_array($code, [71, 73, 75, 77, 85, 86])) return "CloudSnow";
        if (in_array($code, [95, 96, 99])) return "CloudLightning";
        return "Cloud";
    }

    /**
     * Calculate flood risk based on rainfall and weather conditions
     * 
     * @param float $rainfall
     * @param int $weatherCode
     * @return string
     */
    public function calculateFloodRisk(float $rainfall, int $weatherCode): string
    {
        // High risk: Heavy rainfall or severe weather conditions
        if ($rainfall > 20 || in_array($weatherCode, [95, 96, 99])) {
            return 'Tinggi';
        }
        
        // Medium risk: Moderate rainfall or heavy rain weather codes
        if ($rainfall > 10 || in_array($weatherCode, [80, 81, 82])) {
            return 'Sedang';
        }
        
        // Low risk: Light or no rainfall
        return 'Rendah';
    }

    /**
     * Format rainfall amount with appropriate units
     * 
     * @param float $amount
     * @return string
     */
    public function formatRainfall(float $amount): string
    {
        if ($amount === 0.0) return "0 mm";
        if ($amount < 0.1) return "< 0.1 mm";
        return number_format($amount, 1) . " mm";
    }

    /**
     * Get rainfall intensity description
     * 
     * @param float $amount
     * @return string
     */
    public function getRainfallIntensity(float $amount): string
    {
        if ($amount === 0.0) return "Tidak ada hujan";
        if ($amount < 0.5) return "Sangat ringan";
        if ($amount < 4) return "Ringan";
        if ($amount < 10) return "Sedang";
        if ($amount < 20) return "Lebat";
        return "Sangat lebat";
    }

    /**
     * Get flood risk badge variant for UI styling
     * 
     * @param string $risk
     * @return string
     */
    public function getFloodRiskVariant(string $risk): string
    {
        return match($risk) {
            'Tinggi' => 'destructive',
            'Sedang' => 'warning',
            default => 'default'
        };
    }
} 