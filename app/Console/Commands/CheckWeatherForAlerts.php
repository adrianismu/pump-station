<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\WeatherService;
use App\Services\AlertService;
use App\Models\PumpHouse;
use Illuminate\Support\Facades\Log;

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

        // Log command start
        Log::info('Weather alert checker started', [
            'timestamp' => now(),
            'environment' => app()->environment()
        ]);

        try {
            // Check if we're on Railway and handle database connectivity
            if (app()->environment('production') && !$this->checkDatabaseConnection()) {
                $this->error('Database connection failed. Skipping weather check.');
                Log::error('Database connection failed in production environment', [
                    'environment' => app()->environment(),
                    'timestamp' => now()
                ]);
                return 0; // Return success to avoid Railway restart
            }

            // Check if required tables exist
            if (!\Schema::hasTable('pump_houses')) {
                $this->error('Pump houses table not found');
                return 1;
            }
            
            if (!\Schema::hasTable('notifications')) {
                $this->error('Notifications table not found');
                return 1;
            }

            // Get unique coordinates to avoid duplicates
            $uniqueCoordinates = PumpHouse::select('lat', 'lng', 'address')
                ->whereNotNull('lat')
                ->whereNotNull('lng')
                ->groupBy('lat', 'lng', 'address')
                ->get();

            if ($uniqueCoordinates->isEmpty()) {
                $this->warn('No pump houses with coordinates found');
                Log::warning('No pump houses with coordinates for weather check');
                return 0;
            }

            $this->info("Found {$uniqueCoordinates->count()} unique locations to check");
            $alertsCreated = 0;
            $locationsChecked = 0;

            foreach ($uniqueCoordinates as $location) {
                $this->line("Checking weather for {$location->address} ({$location->lat}, {$location->lng})");
                
                try {
                $weatherData = $this->weatherService->getWeatherForecast($location->lat, $location->lng);
                    $locationsChecked++;
                    
                    if (!$weatherData) {
                        $this->warn("No weather data received for {$location->address}");
                        continue;
                    }
                
                if ($this->shouldCreateWeatherAlert($weatherData)) {
                    // Get all pump houses for this location
                    $pumpHouses = PumpHouse::where('lat', $location->lat)
                        ->where('lng', $location->lng)
                        ->get();

                    foreach ($pumpHouses as $pumpHouse) {
                            try {
                                $alert = $this->alertService->createWeatherAlert($pumpHouse, $weatherData);
                        $alertsCreated++;
                                
                                $this->info("Weather alert created for {$pumpHouse->name} (ID: {$alert->id})");
                                
                                Log::info('Weather alert created successfully', [
                                    'alert_id' => $alert->id,
                                    'pump_house_id' => $pumpHouse->id,
                                    'pump_house_name' => $pumpHouse->name,
                                    'severity' => $alert->severity,
                                    'precipitation' => $weatherData['current']['precipitation'] ?? 0,
                                    'timestamp' => now()
                                ]);
                                
                            } catch (\Exception $alertError) {
                                $this->error("Failed to create alert for {$pumpHouse->name}: {$alertError->getMessage()}");
                                Log::error('Failed to create weather alert', [
                                    'pump_house_id' => $pumpHouse->id,
                                    'error' => $alertError->getMessage(),
                                    'location' => $location->address
                                ]);
                            }
                        }
                        
                        $this->warn("Weather alert created for {$location->address} - High precipitation risk detected!");
                    } else {
                        $this->line("Weather conditions normal for {$location->address}");
                    }
                    
                } catch (\Exception $weatherError) {
                    $this->error("Failed to check weather for {$location->address}: {$weatherError->getMessage()}");
                    Log::error('Weather API error', [
                        'location' => $location->address,
                        'lat' => $location->lat,
                        'lng' => $location->lng,
                        'error' => $weatherError->getMessage()
                    ]);
                }
            }

            $message = "Weather check completed. {$alertsCreated} alerts created from {$locationsChecked} locations checked.";

            if ($alertsCreated > 0) {
                $this->info($message);
            } else {
                $this->info("Weather check completed. No alerts needed from {$locationsChecked} locations checked.");
            }
            
            // Log completion
            Log::info('Weather alert checker completed', [
                'alerts_created' => $alertsCreated,
                'locations_checked' => $locationsChecked,
                'total_locations' => $uniqueCoordinates->count(),
                'timestamp' => now()
            ]);

        } catch (\Exception $e) {
            $errorMessage = "Error checking weather: " . $e->getMessage();
            $this->error($errorMessage);
            
            Log::error('Weather alert checker failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'timestamp' => now()
            ]);
            
            return 1;
        }

        return 0;
    }

    /**
     * Check database connection
     */
    private function checkDatabaseConnection(): bool
    {
        try {
            \DB::connection()->getPdo();
            return true;
        } catch (\Exception $e) {
            Log::error('Database connection test failed', [
                'error' => $e->getMessage(),
                'config_host' => config('database.connections.mysql.host'),
                'config_database' => config('database.connections.mysql.database'),
                'timestamp' => now()
            ]);
            return false;
        }
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
