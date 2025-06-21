<?php

namespace App\Services;

use App\Models\Alert;
use App\Models\PumpHouse;
use App\Services\NotificationService;


class AlertService
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Create water level alert
     */
    public function createWaterLevelAlert(PumpHouse $pumpHouse, float $waterLevel): Alert
    {
        $severity = $this->determineSeverity($pumpHouse, $waterLevel);
        $internalMessage = $this->generateWaterLevelInternalMessage($pumpHouse, $waterLevel, $severity);
        $publicMessage = $this->generateWaterLevelPublicMessage($pumpHouse, $waterLevel, $severity);
        
        $alert = Alert::create([
            'type' => 'water_level',
            'pump_house_id' => $pumpHouse->id,
            'title' => "Status {$severity} - {$pumpHouse->name}",
            'description' => "Level air mencapai {$waterLevel}m",
            'severity' => $severity,
            'internal_message' => $internalMessage,
            'public_message' => $publicMessage,
            'water_level' => $waterLevel,
            'recipients' => json_encode(['admin@pumpstation.com']),
            'is_active' => true,
        ]);

        // Distribute notifications to appropriate users
        $this->notificationService->distributeAlertNotifications($alert);

        return $alert;
    }

    /**
     * Create weather forecast alert
     */
    public function createWeatherAlert(PumpHouse $pumpHouse, array $weatherData): Alert
    {
        // Check if similar alert already exists in last 2 hours
        $existingAlert = Alert::where('type', 'weather_forecast')
            ->where('pump_house_id', $pumpHouse->id)
            ->where('created_at', '>', now()->subHours(2))
            ->first();

        if ($existingAlert) {
            return $existingAlert; // Don't create duplicate
        }

        $severity = $this->determineWeatherSeverity($weatherData);
        $internalMessage = $this->generateWeatherInternalMessage($pumpHouse, $weatherData, $severity);
        $publicMessage = $this->generateWeatherPublicMessage($pumpHouse, $weatherData, $severity);

        $alert = Alert::create([
            'type' => 'weather_forecast',
            'pump_house_id' => $pumpHouse->id,
            'title' => "Peringatan Cuaca - {$pumpHouse->name}",
            'description' => "Prakiraan cuaca berpotensi menyebabkan banjir",
            'severity' => $severity,
            'internal_message' => $internalMessage,
            'public_message' => $publicMessage,
            'rainfall' => $weatherData['precipitation'] ?? 0,
            'recipients' => json_encode(['admin@pumpstation.com']),
            'is_active' => true,
        ]);

        // Distribute notifications to appropriate users
        $this->notificationService->distributeAlertNotifications($alert);

        return $alert;
    }

    /**
     * Determine alert severity based on water level and thresholds
     */
    private function determineSeverity(PumpHouse $pumpHouse, float $waterLevel): string
    {
        // Get threshold settings for this pump house
        $thresholds = $pumpHouse->threshold_settings()->first();
        
        if (!$thresholds) {
            return 'Siaga'; // Default to Siaga if no thresholds set
        }

        if ($waterLevel >= $thresholds->danger_level) {
            return 'Awas';
        }

        if ($waterLevel >= $thresholds->warning_level) {
            return 'Siaga';
        }

        return 'Siaga';
    }

    /**
     * Determine weather alert severity
     */
    private function determineWeatherSeverity(array $weatherData): string
    {
        $precipitation = $weatherData['precipitation'] ?? 0;
        $precipitationProb = $weatherData['precipitation_probability'] ?? 0;
        $windSpeed = $weatherData['wind_speed'] ?? 0;

        // Awas conditions: Very high risk
        if ($precipitation > 10.0 || $precipitationProb > 90 || ($windSpeed > 25 && $precipitation > 3.0)) {
            return 'Awas';
        }

        return 'Siaga';
    }

    /**
     * Generate internal message for water level alert
     */
    private function generateWaterLevelInternalMessage(PumpHouse $pumpHouse, float $waterLevel, string $severity): string
    {
        return "Status {$severity} di {$pumpHouse->name}. Level air {$waterLevel}m. " .
            "Lokasi: {$pumpHouse->address}. " .
            "Segera lakukan pengecekan dan tindakan sesuai SOP.";
    }

    /**
     * Generate public message for water level alert
     */
    private function generateWaterLevelPublicMessage(PumpHouse $pumpHouse, float $waterLevel, string $severity): ?string
    {
        // Only generate public message for 'Awas' severity
        if ($severity !== 'Awas') {
            return null;
        }

        return "Peringatan Banjir: Terdeteksi kenaikan muka air signifikan di sekitar {$pumpHouse->address}. " .
            "Warga diimbau untuk waspada dan siaga banjir.";
    }

    /**
     * Generate internal message for weather alert
     */
    private function generateWeatherInternalMessage(PumpHouse $pumpHouse, array $weatherData, string $severity): string
    {
        $precipitation = $weatherData['precipitation'] ?? 0;
        $precipitationProb = $weatherData['precipitation_probability'] ?? 0;
        $description = $weatherData['description'] ?? 'Cuaca buruk';

        return "Peringatan Dini Cuaca: {$description} di area {$pumpHouse->name}. " .
            "Prakiraan hujan {$precipitation}mm dengan probabilitas {$precipitationProb}%. " .
            "Harap siapkan sistem pompa dan pantau level air secara intensif.";
    }

    /**
     * Generate public message for weather alert
     */
    private function generateWeatherPublicMessage(PumpHouse $pumpHouse, array $weatherData, string $severity): ?string
    {
        // Only generate public message for 'Awas' severity
        if ($severity !== 'Awas') {
            return null;
        }

        $description = $weatherData['description'] ?? 'cuaca buruk';

        return "Info Cuaca: Waspada potensi {$description} dengan intensitas tinggi di wilayah {$pumpHouse->address}. " .
            "Masyarakat diimbau tetap waspada dan siaga banjir.";
    }
} 