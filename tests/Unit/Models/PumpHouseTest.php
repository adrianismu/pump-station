<?php

namespace Tests\Unit\Models;

use App\Models\PumpHouse;
use App\Models\WaterLevelHistory;
use App\Models\Alert;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PumpHouseTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_be_created_with_valid_attributes()
    {
        $pumpHouse = PumpHouse::create([
            'name' => 'Test Pump House',
            'address' => 'Test Address',
            'lat' => -7.2575,
            'lng' => 112.7521,
            'status' => 'Aktif',
            'capacity' => '1000 m³/jam',
            'pump_count' => 5,
        ]);

        $this->assertInstanceOf(PumpHouse::class, $pumpHouse);
        $this->assertEquals('Test Pump House', $pumpHouse->name);
        $this->assertEquals('Aktif', $pumpHouse->status);
        $this->assertEquals(5, $pumpHouse->pump_count);
    }

    /** @test */
    public function it_casts_coordinates_to_decimal()
    {
        $pumpHouse = PumpHouse::create([
            'name' => 'Test Pump House',
            'address' => 'Test Address',
            'lat' => '-7.2575000000',
            'lng' => '112.7521000000',
            'status' => 'Aktif',
            'capacity' => '1000 m³/jam',
            'pump_count' => 5,
        ]);

        $this->assertIsFloat($pumpHouse->lat);
        $this->assertIsFloat($pumpHouse->lng);
        $this->assertEquals(-7.2575, $pumpHouse->lat);
        $this->assertEquals(112.7521, $pumpHouse->lng);
    }

    /** @test */
    public function it_has_water_level_history_relationship()
    {
        $pumpHouse = PumpHouse::factory()->create();
        
        WaterLevelHistory::create([
            'pump_house_id' => $pumpHouse->id,
            'water_level' => 2.5,
            'recorded_at' => now(),
        ]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $pumpHouse->waterLevelHistory);
        $this->assertCount(1, $pumpHouse->waterLevelHistory);
        $this->assertEquals(2.5, $pumpHouse->waterLevelHistory->first()->water_level);
    }

    /** @test */
    public function it_can_get_current_water_level()
    {
        $pumpHouse = PumpHouse::factory()->create();
        
        // Create multiple water level records
        WaterLevelHistory::create([
            'pump_house_id' => $pumpHouse->id,
            'water_level' => 2.0,
            'recorded_at' => now()->subHours(2),
        ]);

        WaterLevelHistory::create([
            'pump_house_id' => $pumpHouse->id,
            'water_level' => 2.5,
            'recorded_at' => now()->subHour(),
        ]);

        WaterLevelHistory::create([
            'pump_house_id' => $pumpHouse->id,
            'water_level' => 3.0,
            'recorded_at' => now(),
        ]);

        $currentLevel = $pumpHouse->getCurrentWaterLevel();
        $this->assertEquals(3.0, $currentLevel);
    }

    /** @test */
    public function it_returns_zero_when_no_water_level_history_exists()
    {
        $pumpHouse = PumpHouse::factory()->create();
        
        $currentLevel = $pumpHouse->getCurrentWaterLevel();
        $this->assertEquals(0, $currentLevel);
    }

    /** @test */
    public function it_can_get_water_level_status_normal()
    {
        $pumpHouse = PumpHouse::factory()->create([
            'water_level_warning' => 2.0,
            'water_level_critical' => 3.0,
        ]);

        WaterLevelHistory::create([
            'pump_house_id' => $pumpHouse->id,
            'water_level' => 1.5,
            'recorded_at' => now(),
        ]);

        $status = $pumpHouse->getWaterLevelStatusAttribute();
        $this->assertEquals('normal', $status);
    }

    /** @test */
    public function it_can_get_water_level_status_warning()
    {
        $pumpHouse = PumpHouse::factory()->create([
            'water_level_warning' => 2.0,
            'water_level_critical' => 3.0,
        ]);

        WaterLevelHistory::create([
            'pump_house_id' => $pumpHouse->id,
            'water_level' => 2.5,
            'recorded_at' => now(),
        ]);

        $status = $pumpHouse->getWaterLevelStatusAttribute();
        $this->assertEquals('warning', $status);
    }

    /** @test */
    public function it_can_get_water_level_status_critical()
    {
        $pumpHouse = PumpHouse::factory()->create([
            'water_level_warning' => 2.0,
            'water_level_critical' => 3.0,
        ]);

        WaterLevelHistory::create([
            'pump_house_id' => $pumpHouse->id,
            'water_level' => 3.5,
            'recorded_at' => now(),
        ]);

        $status = $pumpHouse->getWaterLevelStatusAttribute();
        $this->assertEquals('critical', $status);
    }

    /** @test */
    public function it_has_alerts_relationship()
    {
        $pumpHouse = PumpHouse::factory()->create();
        
        Alert::create([
            'pump_house_id' => $pumpHouse->id,
            'title' => 'Test Alert',
            'description' => 'Test Description',
            'severity' => 'warning',
            'recipients' => json_encode(['admin@test.com']),
        ]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $pumpHouse->alerts);
        $this->assertCount(1, $pumpHouse->alerts);
        $this->assertEquals('Test Alert', $pumpHouse->alerts->first()->title);
    }
} 