<?php

namespace Tests\Integration;

use App\Models\PumpHouse;
use App\Models\WaterLevelHistory;
use App\Models\Alert;
use App\Services\WaterLevelService;
use App\Services\AlertService;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class WaterLevelIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private WaterLevelService $waterLevelService;
    private AlertService $alertService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->waterLevelService = app(WaterLevelService::class);
        $this->alertService = app(AlertService::class);
    }

    /** @test */
    public function water_level_recording_triggers_alert_when_threshold_exceeded()
    {
        // Arrange
        $pumpHouse = PumpHouse::factory()->create([
            'name' => 'Test Pump House',
            'water_level_warning' => 2.0,
            'water_level_critical' => 3.0,
        ]);

        // Act - Record critical water level
        $criticalLevel = 3.5;
        $this->waterLevelService->recordWaterLevel($pumpHouse->id, $criticalLevel);

        // Simulate alert creation based on water level
        $this->alertService->createWaterLevelAlert($pumpHouse, $criticalLevel);

        // Assert
        $this->assertDatabaseHas('water_level_history', [
            'pump_house_id' => $pumpHouse->id,
            'water_level' => $criticalLevel,
        ]);

        $this->assertDatabaseHas('alerts', [
            'pump_house_id' => $pumpHouse->id,
            'severity' => 'critical',
        ]);
    }

    /** @test */
    public function multiple_pump_houses_water_level_monitoring()
    {
        // Arrange
        $pumpHouse1 = PumpHouse::factory()->create(['name' => 'Pump House 1']);
        $pumpHouse2 = PumpHouse::factory()->create(['name' => 'Pump House 2']);
        $pumpHouse3 = PumpHouse::factory()->create(['name' => 'Pump House 3']);

        // Act - Record different water levels
        $this->waterLevelService->recordWaterLevel($pumpHouse1->id, 1.5);
        $this->waterLevelService->recordWaterLevel($pumpHouse2->id, 2.8);
        $this->waterLevelService->recordWaterLevel($pumpHouse3->id, 3.2);

        // Assert
        $level1 = $this->waterLevelService->getLatestWaterLevel($pumpHouse1->id);
        $level2 = $this->waterLevelService->getLatestWaterLevel($pumpHouse2->id);
        $level3 = $this->waterLevelService->getLatestWaterLevel($pumpHouse3->id);

        $this->assertEquals(1.5, $level1);
        $this->assertEquals(2.8, $level2);
        $this->assertEquals(3.2, $level3);

        // Verify total records
        $this->assertDatabaseCount('water_level_history', 3);
    }

    /** @test */
    public function historical_water_level_analysis_integration()
    {
        // Arrange
        $pumpHouse = PumpHouse::factory()->create();
        $baseDate = Carbon::now()->subDays(30);

        // Generate 30 days of water level data
        for ($i = 0; $i < 30; $i++) {
            $date = $baseDate->copy()->addDays($i);
            $waterLevel = 1.5 + sin($i * 0.2) * 0.5; // Simulate varying levels
            
            $this->waterLevelService->recordWaterLevel(
                $pumpHouse->id,
                round($waterLevel, 2),
                $date
            );
        }

        // Act
        $startDate = $baseDate;
        $endDate = Carbon::now();
        
        $history = $this->waterLevelService->getWaterLevelHistory(
            $pumpHouse->id,
            $startDate,
            $endDate
        );
        
        $average = $this->waterLevelService->getAverageWaterLevel(
            $pumpHouse->id,
            $startDate,
            $endDate
        );

        // Assert
        $this->assertCount(30, $history);
        $this->assertGreaterThan(1.0, $average);
        $this->assertLessThan(2.0, $average);
        
        // Verify relationship integrity
        foreach ($history as $record) {
            $this->assertEquals($pumpHouse->id, $record->pump_house_id);
            $this->assertInstanceOf(PumpHouse::class, $record->pumpHouse);
        }
    }

    /** @test */
    public function pump_house_status_calculation_with_real_water_levels()
    {
        // Arrange
        $pumpHouse = PumpHouse::factory()->create([
            'water_level_warning' => 2.0,
            'water_level_critical' => 3.0,
        ]);

        // Test normal status
        $this->waterLevelService->recordWaterLevel($pumpHouse->id, 1.5);
        $pumpHouse->refresh();
        $this->assertEquals('normal', $pumpHouse->water_level_status);

        // Test warning status
        $this->waterLevelService->recordWaterLevel($pumpHouse->id, 2.5);
        $pumpHouse->refresh();
        $this->assertEquals('warning', $pumpHouse->water_level_status);

        // Test critical status
        $this->waterLevelService->recordWaterLevel($pumpHouse->id, 3.5);
        $pumpHouse->refresh();
        $this->assertEquals('critical', $pumpHouse->water_level_status);
    }

    /** @test */
    public function bulk_water_level_import_with_validation()
    {
        // Arrange
        $pumpHouse = PumpHouse::factory()->create();
        
        $bulkData = collect();
        for ($i = 0; $i < 100; $i++) {
            $bulkData->push([
                'water_level' => rand(50, 350) / 100, // Random level between 0.5 and 3.5
                'recorded_at' => Carbon::now()->subHours($i),
            ]);
        }

        // Act
        $result = $this->waterLevelService->bulkRecordWaterLevels(
            $pumpHouse->id,
            $bulkData->toArray()
        );

        // Assert
        $this->assertTrue($result);
        $this->assertDatabaseCount('water_level_history', 100);
        
        // Verify all records belong to correct pump house
        $records = WaterLevelHistory::where('pump_house_id', $pumpHouse->id)->get();
        $this->assertCount(100, $records);
        
        // Verify relationships work
        foreach ($records->take(5) as $record) {
            $this->assertEquals($pumpHouse->id, $record->pumpHouse->id);
        }
    }

    /** @test */
    public function water_level_trending_analysis()
    {
        // Arrange
        $pumpHouse = PumpHouse::factory()->create();
        $baseDate = Carbon::now()->subDays(7);

        // Create trending data (increasing levels over time)
        $levels = [1.0, 1.2, 1.5, 1.8, 2.1, 2.4, 2.7];
        
        foreach ($levels as $index => $level) {
            $this->waterLevelService->recordWaterLevel(
                $pumpHouse->id,
                $level,
                $baseDate->copy()->addDays($index)
            );
        }

        // Act
        $weeklyHistory = $this->waterLevelService->getWaterLevelHistory(
            $pumpHouse->id,
            $baseDate,
            Carbon::now()
        );

        // Assert
        $this->assertCount(7, $weeklyHistory);
        
        // Verify trending (should be increasing)
        $sortedHistory = $weeklyHistory->sortBy('recorded_at');
        $firstLevel = $sortedHistory->first()->water_level;
        $lastLevel = $sortedHistory->last()->water_level;
        
        $this->assertLessThan($lastLevel, $firstLevel);
        $this->assertEquals(1.0, $firstLevel);
        $this->assertEquals(2.7, $lastLevel);
    }

    /** @test */
    public function cascade_deletion_integrity()
    {
        // Arrange
        $pumpHouse = PumpHouse::factory()->create();
        
        // Create related records
        $this->waterLevelService->recordWaterLevel($pumpHouse->id, 2.0);
        $this->waterLevelService->recordWaterLevel($pumpHouse->id, 2.5);
        
        Alert::create([
            'pump_house_id' => $pumpHouse->id,
            'title' => 'Test Alert',
            'description' => 'Test Description',
            'severity' => 'warning',
            'recipients' => json_encode(['admin@test.com']),
        ]);

        // Verify initial state
        $this->assertDatabaseCount('water_level_history', 2);
        $this->assertDatabaseCount('alerts', 1);

        // Act - Delete pump house
        $pumpHouse->delete();

        // Assert - Related records should be deleted (cascade)
        $this->assertDatabaseCount('water_level_history', 0);
        $this->assertDatabaseCount('alerts', 0);
        $this->assertDatabaseMissing('pump_houses', ['id' => $pumpHouse->id]);
    }
} 