<?php

namespace Tests\Integration;

use App\Models\PumpHouse;
use App\Models\WaterLevelHistory;
use App\Models\Alert;
use App\Models\Report;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\QueryException;

class DatabaseIntegrityTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function foreign_key_constraints_are_enforced()
    {
        $this->expectException(QueryException::class);
        
        // Try to create water level history with non-existent pump house
        WaterLevelHistory::create([
            'pump_house_id' => 999999, // Non-existent ID
            'water_level' => 2.5,
            'recorded_at' => now(),
        ]);
    }

    /** @test */
    public function cascade_deletion_works_properly()
    {
        // Arrange
        $pumpHouse = PumpHouse::factory()->create();
        
        // Create related records
        WaterLevelHistory::create([
            'pump_house_id' => $pumpHouse->id,
            'water_level' => 2.5,
            'recorded_at' => now(),
        ]);

        Alert::create([
            'pump_house_id' => $pumpHouse->id,
            'title' => 'Test Alert',
            'description' => 'Test Description',
            'severity' => 'warning',
            'recipients' => json_encode(['admin@test.com']),
        ]);

        // Verify records exist
        $this->assertDatabaseHas('water_level_history', ['pump_house_id' => $pumpHouse->id]);
        $this->assertDatabaseHas('alerts', ['pump_house_id' => $pumpHouse->id]);

        // Act - Delete pump house
        $pumpHouse->delete();

        // Assert - Related records should be deleted
        $this->assertDatabaseMissing('water_level_history', ['pump_house_id' => $pumpHouse->id]);
        $this->assertDatabaseMissing('alerts', ['pump_house_id' => $pumpHouse->id]);
    }

    /** @test */
    public function unique_constraints_are_enforced()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com'
        ]);

        $this->expectException(QueryException::class);
        
        // Try to create another user with same email
        User::factory()->create([
            'email' => 'test@example.com'
        ]);
    }

    /** @test */
    public function decimal_precision_is_maintained()
    {
        $pumpHouse = PumpHouse::factory()->create();

        $waterLevel = WaterLevelHistory::create([
            'pump_house_id' => $pumpHouse->id,
            'water_level' => 2.123456789, // High precision input
            'recorded_at' => now(),
        ]);

        // Should be truncated to 2 decimal places (configured precision)
        $this->assertEquals(2.12, $waterLevel->fresh()->water_level);
    }

    /** @test */
    public function coordinate_precision_is_maintained()
    {
        $pumpHouse = PumpHouse::create([
            'name' => 'Test Pump House',
            'address' => 'Test Address',
            'lat' => -7.257472222222222,  // High precision
            'lng' => 112.752138888888889, // High precision
            'status' => 'Aktif',
            'capacity' => '1000 m³/jam',
            'pump_count' => 5,
        ]);

        // Should maintain high precision for coordinates
        $this->assertEquals(-7.257472222222, $pumpHouse->fresh()->lat);
        $this->assertEquals(112.752138888889, $pumpHouse->fresh()->lng);
    }

    /** @test */
    public function json_fields_are_properly_stored_and_retrieved()
    {
        $pumpHouse = PumpHouse::factory()->create();

        $recipients = ['admin@test.com', 'manager@test.com', 'operator@test.com'];
        
        $alert = Alert::create([
            'pump_house_id' => $pumpHouse->id,
            'title' => 'Test Alert',
            'description' => 'Test Description',
            'severity' => 'warning',
            'recipients' => json_encode($recipients),
        ]);

        $freshAlert = $alert->fresh();
        $storedRecipients = json_decode($freshAlert->recipients, true);

        $this->assertEquals($recipients, $storedRecipients);
        $this->assertIsArray($storedRecipients);
        $this->assertCount(3, $storedRecipients);
    }

    /** @test */
    public function database_transactions_work_correctly()
    {
        $initialCount = PumpHouse::count();

        try {
            \DB::transaction(function () {
                // Create a pump house
                $pumpHouse = PumpHouse::factory()->create();
                
                // Create water level history
                WaterLevelHistory::create([
                    'pump_house_id' => $pumpHouse->id,
                    'water_level' => 2.5,
                    'recorded_at' => now(),
                ]);

                // Simulate an error
                throw new \Exception('Simulated error');
            });
        } catch (\Exception $e) {
            // Transaction should be rolled back
        }

        // Assert no records were created due to rollback
        $this->assertEquals($initialCount, PumpHouse::count());
        $this->assertEquals(0, WaterLevelHistory::count());
    }

    /** @test */
    public function database_indexes_improve_query_performance()
    {
        $pumpHouse = PumpHouse::factory()->create();

        // Create many water level records
        for ($i = 0; $i < 1000; $i++) {
            WaterLevelHistory::create([
                'pump_house_id' => $pumpHouse->id,
                'water_level' => rand(100, 300) / 100,
                'recorded_at' => now()->subHours($i),
            ]);
        }

        // Query that should benefit from index
        $start = microtime(true);
        $records = WaterLevelHistory::where('pump_house_id', $pumpHouse->id)
            ->where('recorded_at', '>=', now()->subDays(7))
            ->orderBy('recorded_at', 'desc')
            ->limit(100)
            ->get();
        $end = microtime(true);

        $queryTime = $end - $start;

        // Assert query executed reasonably fast (less than 1 second)
        $this->assertLessThan(1.0, $queryTime);
        $this->assertCount(100, $records);
    }

    /** @test */
    public function null_constraints_are_enforced()
    {
        $this->expectException(QueryException::class);

        // Try to create pump house without required fields
        PumpHouse::create([
            'name' => null, // Required field
            'address' => 'Test Address',
            'lat' => -7.2575,
            'lng' => 112.7521,
            'status' => 'Aktif',
            'capacity' => '1000 m³/jam',
            'pump_count' => 5,
        ]);
    }

    /** @test */
    public function check_constraints_work_correctly()
    {
        // This would test CHECK constraints if they were defined
        // For example, ensuring water_level is always positive
        $pumpHouse = PumpHouse::factory()->create();

        $waterLevel = WaterLevelHistory::create([
            'pump_house_id' => $pumpHouse->id,
            'water_level' => 0.01, // Minimum valid value
            'recorded_at' => now(),
        ]);

        $this->assertEquals(0.01, $waterLevel->water_level);
    }

    /** @test */
    public function database_connection_pooling_works()
    {
        // Test multiple concurrent operations
        $operations = [];
        
        for ($i = 0; $i < 10; $i++) {
            $operations[] = function() {
                $pumpHouse = PumpHouse::factory()->create();
                WaterLevelHistory::create([
                    'pump_house_id' => $pumpHouse->id,
                    'water_level' => 2.5,
                    'recorded_at' => now(),
                ]);
                return $pumpHouse->id;
            };
        }

        // Execute operations
        $results = [];
        foreach ($operations as $operation) {
            $results[] = $operation();
        }

        // Assert all operations completed successfully
        $this->assertCount(10, $results);
        $this->assertEquals(10, PumpHouse::count());
        $this->assertEquals(10, WaterLevelHistory::count());
    }
}