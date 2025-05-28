<?php

namespace Database\Seeders;

use App\Models\PumpHouse;
use App\Models\WaterLevelHistory;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class WaterLevelHistorySeeder extends Seeder
{
    public function run(): void
    {
        $pumpHouses = PumpHouse::all();
        
        foreach ($pumpHouses as $pumpHouse) {
            // Generate data for the last 30 days
            for ($i = 30; $i >= 0; $i--) {
                $date = Carbon::now()->subDays($i);
                
                // Generate realistic water level data based on time and weather patterns
                $baseLevel = 1.5; // Base water level
                $timeVariation = sin(($date->hour / 24) * 2 * pi()) * 0.3; // Daily variation
                $randomVariation = (rand(-20, 20) / 100); // Random variation ±0.2m
                $seasonalVariation = sin(($date->dayOfYear / 365) * 2 * pi()) * 0.5; // Seasonal variation
                
                $waterLevel = $baseLevel + $timeVariation + $randomVariation + $seasonalVariation;
                $waterLevel = max(0.5, min(3.5, $waterLevel)); // Keep within realistic bounds
                
                // Generate multiple readings per day (every 4 hours)
                for ($hour = 0; $hour < 24; $hour += 4) {
                    $timestamp = $date->copy()->addHours($hour);
                    
                    // Add some hourly variation
                    $hourlyVariation = (rand(-10, 10) / 100);
                    $finalLevel = max(0.5, min(3.5, $waterLevel + $hourlyVariation));
                    
                    WaterLevelHistory::create([
                        'pump_house_id' => $pumpHouse->id,
                        'water_level' => round($finalLevel, 2),
                        'recorded_at' => $timestamp,
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp,
                    ]);
                }
            }
        }
    }
} 
 