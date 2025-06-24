<?php

namespace App\Services;

use App\Models\Alert;
use App\Models\PumpHouse;
use App\Models\ThresholdSetting;
use App\Models\PumpHouseThresholdSetting;
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
            'title' => "Status {$this->getSeverityLabel($severity)} - {$pumpHouse->name}",
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
        // Get pump house specific thresholds first
        $thresholds = PumpHouseThresholdSetting::where('pump_house_id', $pumpHouse->id)
            ->where('is_active', true)
            ->where('water_level', '<=', $waterLevel)
            ->orderBy('water_level', 'desc')
            ->first();

        // Fallback to global thresholds if no pump house specific threshold exists
        if (!$thresholds) {
            $thresholds = ThresholdSetting::where('is_active', true)
                ->where('water_level', '<=', $waterLevel)
                ->orderBy('water_level', 'desc')
                ->first();
        }

        // Return the severity from threshold setting, default to 'low' if no threshold matches
        return $thresholds ? $thresholds->severity : 'low';
    }

    /**
     * Determine weather alert severity
     */
    private function determineWeatherSeverity(array $weatherData): string
    {
        $precipitation = $weatherData['precipitation'] ?? 0;
        $precipitationProb = $weatherData['precipitation_probability'] ?? 0;
        $windSpeed = $weatherData['wind_speed'] ?? 0;

        // Critical conditions: Very high risk
        if ($precipitation > 10.0 || $precipitationProb > 90 || ($windSpeed > 25 && $precipitation > 3.0)) {
            return 'critical';
        }

        // High conditions: High risk
        if ($precipitation > 5.0 || $precipitationProb > 70 || ($windSpeed > 15 && $precipitation > 2.0)) {
            return 'high';
        }

        // Medium conditions: Moderate risk
        if ($precipitation > 2.0 || $precipitationProb > 50) {
            return 'medium';
        }

        return 'low';
    }

    /**
     * Get severity label for display
     */
    private function getSeverityLabel(string $severity): string
    {
        return match($severity) {
            'critical' => 'Kritis',
            'high' => 'Tinggi',
            'medium' => 'Sedang',
            'low' => 'Rendah',
            default => 'Normal'
        };
    }

    /**
     * Generate internal message for water level alert
     */
    private function generateWaterLevelInternalMessage(PumpHouse $pumpHouse, float $waterLevel, string $severity): string
    {
        $severityLabel = $this->getSeverityLabel($severity);
        
        return "Status {$severityLabel} di {$pumpHouse->name}. Level air {$waterLevel}m. " .
            "Lokasi: {$pumpHouse->address}. " .
            "Segera lakukan pengecekan dan tindakan sesuai SOP.";
    }

    /**
     * Generate public message for water level alert
     */
    private function generateWaterLevelPublicMessage(PumpHouse $pumpHouse, float $waterLevel, string $severity): ?string
    {
        // Only generate public message for 'critical' and 'high' severity
        if (!in_array($severity, ['critical', 'high'])) {
            return null;
        }

        $severityLabel = $this->getSeverityLabel($severity);
        return "Peringatan Banjir - Status {$severityLabel}: Terdeteksi kenaikan muka air signifikan di sekitar {$pumpHouse->address}. " .
            "Warga diimbau untuk tetap waspada.";
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
        // Only generate public message for 'critical' and 'high' severity
        if (!in_array($severity, ['critical', 'high'])) {
            return null;
        }

        $description = $weatherData['description'] ?? 'cuaca buruk';

        $severityLabel = $this->getSeverityLabel($severity);
        return "Info Cuaca - Status {$severityLabel}: Waspada potensi {$description} dengan intensitas tinggi di wilayah {$pumpHouse->address}. " .
            "Masyarakat diimbau tetap waspada.";
    }
} 