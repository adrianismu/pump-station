<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\WeatherService;
use App\Services\AlertService;
use App\Models\PumpHouse;

class CheckWeatherForAlerts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-weather-for-alerts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check weather forecasts and create weather-based alerts automatically';

    protected $weatherService;
    protected $alertService;

    public function __construct(WeatherService $weatherService, AlertService $alertService)
    {
        parent::__construct();
        $this->weatherService = $weatherService;
        $this->alertService = $alertService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking weather forecasts for alert conditions...');

        try {
            // Get unique coordinates to avoid duplicates
            $uniqueCoordinates = PumpHouse::select('lat', 'lng', 'address')
                ->groupBy('lat', 'lng', 'address')
                ->get();

            $alertsCreated = 0;

            foreach ($uniqueCoordinates as $location) {
                $this->line("Checking weather for {$location->address} ({$location->lat}, {$location->lng})");
                
                $weatherData = $this->weatherService->getWeatherForecast($location->lat, $location->lng);
                
                if ($this->shouldCreateWeatherAlert($weatherData)) {
                    // Get all pump houses for this location
                    $pumpHouses = PumpHouse::where('lat', $location->lat)
                        ->where('lng', $location->lng)
                        ->get();

                    foreach ($pumpHouses as $pumpHouse) {
                        $this->alertService->createWeatherAlert($pumpHouse, $weatherData);
                        $alertsCreated++;
                    }
                    
                    $this->warn("Weather alert created for {$location->address} - High precipitation risk detected!");
                }
            }

            if ($alertsCreated > 0) {
                $this->info("Weather check completed. {$alertsCreated} alerts created.");
            } else {
                $this->info("Weather check completed. No alerts needed.");
            }

        } catch (\Exception $e) {
            $this->error("Error checking weather: " . $e->getMessage());
            return 1;
        }

        return 0;
    }

    /**
     * Determine if weather conditions warrant an alert
     */
    private function shouldCreateWeatherAlert($weatherData): bool
    {
        // Create alert if:
        // 1. Severe weather codes (thunderstorms, heavy rain, etc.)
        // 2. High precipitation probability (>75%)
        // 3. High precipitation amount (>5mm)
        // 4. Strong winds (>20 km/h) combined with rain
        
        $weatherCode = $weatherData['current']['weather_code'] ?? 0;
        $precipitation = $weatherData['current']['precipitation'] ?? 0;
        $precipitationProb = $weatherData['current']['precipitation_probability'] ?? 0;
        $windSpeed = $weatherData['current']['wind_speed'] ?? 0;
        
        // Severe weather codes that always warrant alerts
        $severeWeatherCodes = [
            95, 96, 99,  // Thunderstorms
            82,           // Heavy rain
            65, 67,       // Heavy rain/freezing rain
            75, 86,       // Heavy snow
        ];
        
        return in_array($weatherCode, $severeWeatherCodes) ||
               ($precipitationProb > 75) || 
               ($precipitation > 5.0) || 
               ($windSpeed > 20 && $precipitation > 2.0);
    }
}
