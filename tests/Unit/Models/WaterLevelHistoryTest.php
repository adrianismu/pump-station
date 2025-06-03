<?php

namespace Tests\Unit\Models;

use App\Models\PumpHouse;
use App\Models\WaterLevelHistory;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class WaterLevelHistoryTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_be_created_with_valid_attributes()
    {
        $pumpHouse = PumpHouse::factory()->create();
        $recordedAt = Carbon::now();

        $waterLevel = WaterLevelHistory::create([
            'pump_house_id' => $pumpHouse->id,
            'water_level' => 2.75,
            'recorded_at' => $recordedAt,
        ]);

        $this->assertInstanceOf(WaterLevelHistory::class, $waterLevel);
        $this->assertEquals($pumpHouse->id, $waterLevel->pump_house_id);
        $this->assertEquals(2.75, $waterLevel->water_level);
        $this->assertEquals($recordedAt->toDateTimeString(), $waterLevel->recorded_at->toDateTimeString());
    }

    /** @test */
    public function it_casts_water_level_to_decimal()
    {
        $pumpHouse = PumpHouse::factory()->create();

        $waterLevel = WaterLevelHistory::create([
            'pump_house_id' => $pumpHouse->id,
            'water_level' => '2.50',
            'recorded_at' => now(),
        ]);

        $this->assertIsFloat($waterLevel->water_level);
        $this->assertEquals(2.50, $waterLevel->water_level);
    }

    /** @test */
    public function it_casts_recorded_at_to_datetime()
    {
        $pumpHouse = PumpHouse::factory()->create();
        $dateTime = '2024-01-15 14:30:00';

        $waterLevel = WaterLevelHistory::create([
            'pump_house_id' => $pumpHouse->id,
            'water_level' => 2.25,
            'recorded_at' => $dateTime,
        ]);

        $this->assertInstanceOf(Carbon::class, $waterLevel->recorded_at);
        $this->assertEquals($dateTime, $waterLevel->recorded_at->toDateTimeString());
    }

    /** @test */
    public function it_belongs_to_pump_house()
    {
        $pumpHouse = PumpHouse::factory()->create();

        $waterLevel = WaterLevelHistory::create([
            'pump_house_id' => $pumpHouse->id,
            'water_level' => 2.25,
            'recorded_at' => now(),
        ]);

        $this->assertInstanceOf(PumpHouse::class, $waterLevel->pumpHouse);
        $this->assertEquals($pumpHouse->id, $waterLevel->pumpHouse->id);
        $this->assertEquals($pumpHouse->name, $waterLevel->pumpHouse->name);
    }

    /** @test */
    public function water_level_can_handle_precision()
    {
        $pumpHouse = PumpHouse::factory()->create();

        $waterLevel = WaterLevelHistory::create([
            'pump_house_id' => $pumpHouse->id,
            'water_level' => 2.123456,
            'recorded_at' => now(),
        ]);

        // Should be stored with 2 decimal precision
        $this->assertEquals(2.12, $waterLevel->water_level);
    }

    /** @test */
    public function it_validates_water_level_ranges()
    {
        $pumpHouse = PumpHouse::factory()->create();

        // Test minimum realistic value
        $waterLevel = WaterLevelHistory::create([
            'pump_house_id' => $pumpHouse->id,
            'water_level' => 0.01,
            'recorded_at' => now(),
        ]);

        $this->assertEquals(0.01, $waterLevel->water_level);

        // Test maximum realistic value
        $waterLevel2 = WaterLevelHistory::create([
            'pump_house_id' => $pumpHouse->id,
            'water_level' => 99.99,
            'recorded_at' => now(),
        ]);

        $this->assertEquals(99.99, $waterLevel2->water_level);
    }
} 