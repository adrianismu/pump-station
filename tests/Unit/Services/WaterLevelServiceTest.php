<?php

namespace Tests\Unit\Services;

use App\Models\PumpHouse;
use App\Models\WaterLevelHistory;
use App\Services\WaterLevelService;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class WaterLevelServiceTest extends TestCase
{
    use RefreshDatabase;

    private WaterLevelService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new WaterLevelService();
    }

    /** @test */
    public function it_can_record_water_level()
    {
        $pumpHouse = PumpHouse::factory()->create();
        $waterLevel = 2.5;
        $recordedAt = Carbon::now();

        $record = $this->service->recordWaterLevel($pumpHouse->id, $waterLevel, $recordedAt);

        $this->assertInstanceOf(WaterLevelHistory::class, $record);
        $this->assertEquals($pumpHouse->id, $record->pump_house_id);
        $this->assertEquals($waterLevel, $record->water_level);
        $this->assertEquals($recordedAt->toDateTimeString(), $record->recorded_at->toDateTimeString());
    }

    /** @test */
    public function it_can_get_latest_water_level()
    {
        $pumpHouse = PumpHouse::factory()->create();
        
        // Create old record
        WaterLevelHistory::create([
            'pump_house_id' => $pumpHouse->id,
            'water_level' => 2.0,
            'recorded_at' => Carbon::now()->subHours(2),
        ]);

        // Create latest record
        $latestLevel = 3.0;
        WaterLevelHistory::create([
            'pump_house_id' => $pumpHouse->id,
            'water_level' => $latestLevel,
            'recorded_at' => Carbon::now(),
        ]);

        $result = $this->service->getLatestWaterLevel($pumpHouse->id);

        $this->assertEquals($latestLevel, $result);
    }

    /** @test */
    public function it_returns_null_when_no_water_level_history()
    {
        $pumpHouse = PumpHouse::factory()->create();

        $result = $this->service->getLatestWaterLevel($pumpHouse->id);

        $this->assertNull($result);
    }

    /** @test */
    public function it_can_get_water_level_history_for_date_range()
    {
        $pumpHouse = PumpHouse::factory()->create();
        $startDate = Carbon::now()->subDays(7);
        $endDate = Carbon::now();

        // Create records within range
        WaterLevelHistory::create([
            'pump_house_id' => $pumpHouse->id,
            'water_level' => 2.0,
            'recorded_at' => $startDate->copy()->addDays(2),
        ]);

        WaterLevelHistory::create([
            'pump_house_id' => $pumpHouse->id,
            'water_level' => 2.5,
            'recorded_at' => $startDate->copy()->addDays(4),
        ]);

        // Create record outside range
        WaterLevelHistory::create([
            'pump_house_id' => $pumpHouse->id,
            'water_level' => 3.0,
            'recorded_at' => $startDate->copy()->subDay(),
        ]);

        $history = $this->service->getWaterLevelHistory($pumpHouse->id, $startDate, $endDate);

        $this->assertCount(2, $history);
        $this->assertContainsOnly(WaterLevelHistory::class, $history);
    }

    /** @test */
    public function it_can_calculate_average_water_level()
    {
        $pumpHouse = PumpHouse::factory()->create();
        $startDate = Carbon::now()->subDays(7);
        $endDate = Carbon::now();

        WaterLevelHistory::create([
            'pump_house_id' => $pumpHouse->id,
            'water_level' => 2.0,
            'recorded_at' => $startDate->copy()->addDays(1),
        ]);

        WaterLevelHistory::create([
            'pump_house_id' => $pumpHouse->id,
            'water_level' => 3.0,
            'recorded_at' => $startDate->copy()->addDays(2),
        ]);

        WaterLevelHistory::create([
            'pump_house_id' => $pumpHouse->id,
            'water_level' => 1.0,
            'recorded_at' => $startDate->copy()->addDays(3),
        ]);

        $average = $this->service->getAverageWaterLevel($pumpHouse->id, $startDate, $endDate);

        $this->assertEquals(2.0, $average);
    }

    /** @test */
    public function it_returns_zero_average_when_no_data()
    {
        $pumpHouse = PumpHouse::factory()->create();
        $startDate = Carbon::now()->subDays(7);
        $endDate = Carbon::now();

        $average = $this->service->getAverageWaterLevel($pumpHouse->id, $startDate, $endDate);

        $this->assertEquals(0, $average);
    }

    /** @test */
    public function it_can_detect_threshold_breach()
    {
        $pumpHouse = PumpHouse::factory()->create([
            'water_level_warning' => 2.0,
            'water_level_critical' => 3.0,
        ]);

        // Normal level
        $normalStatus = $this->service->checkWaterLevelStatus($pumpHouse, 1.5);
        $this->assertEquals('normal', $normalStatus);

        // Warning level
        $warningStatus = $this->service->checkWaterLevelStatus($pumpHouse, 2.5);
        $this->assertEquals('warning', $warningStatus);

        // Critical level
        $criticalStatus = $this->service->checkWaterLevelStatus($pumpHouse, 3.5);
        $this->assertEquals('critical', $criticalStatus);
    }

    /** @test */
    public function it_can_bulk_record_water_levels()
    {
        $pumpHouse = PumpHouse::factory()->create();
        
        $records = [
            ['water_level' => 2.0, 'recorded_at' => Carbon::now()->subHours(3)],
            ['water_level' => 2.5, 'recorded_at' => Carbon::now()->subHours(2)],
            ['water_level' => 3.0, 'recorded_at' => Carbon::now()->subHour()],
        ];

        $result = $this->service->bulkRecordWaterLevels($pumpHouse->id, $records);

        $this->assertTrue($result);
        $this->assertDatabaseCount('water_level_history', 3);
        
        $savedRecords = WaterLevelHistory::where('pump_house_id', $pumpHouse->id)
            ->orderBy('recorded_at')
            ->get();

        $this->assertEquals(2.0, $savedRecords->first()->water_level);
        $this->assertEquals(3.0, $savedRecords->last()->water_level);
    }
} 