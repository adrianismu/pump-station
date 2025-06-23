<?php

namespace Tests\Feature\Api;

use App\Models\PumpHouse;
use App\Models\User;
use App\Models\WaterLevelHistory;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

class PumpHouseApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create and authenticate a user for API tests
        $user = User::factory()->create();
        Sanctum::actingAs($user);
    }

    /** @test */
    public function it_can_list_all_pump_houses()
    {
        // Arrange
        $pumpHouses = PumpHouse::factory()->count(3)->create();

        // Act
        $response = $this->getJson('/api/pump-houses');

        // Assert
        $response->assertStatus(200)
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'address',
                        'lat',
                        'lng',
                        'status',
                        'capacity',
                        'pump_count',
                        'current_water_level',
                        'water_level_status',
                        'created_at',
                        'updated_at'
                    ]
                ]
            ]);
    }

    /** @test */
    public function it_can_show_specific_pump_house()
    {
        // Arrange
        $pumpHouse = PumpHouse::factory()->create([
            'name' => 'Test Pump House',
            'status' => 'Aktif'
        ]);

        // Add water level history
        WaterLevelHistory::create([
            'pump_house_id' => $pumpHouse->id,
            'water_level' => 2.5,
            'recorded_at' => now(),
        ]);

        // Act
        $response = $this->getJson("/api/pump-houses/{$pumpHouse->id}");

        // Assert
        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $pumpHouse->id,
                    'name' => 'Test Pump House',
                    'status' => 'Aktif',
                    'current_water_level' => 2.5
                ]
            ])
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'address',
                    'lat',
                    'lng',
                    'status',
                    'capacity',
                    'pump_count',
                    'current_water_level',
                    'water_level_status',
                    'water_level_history' => [
                        '*' => [
                            'id',
                            'water_level',
                            'recorded_at'
                        ]
                    ]
                ]
            ]);
    }

    /** @test */
    public function it_returns_404_for_non_existent_pump_house()
    {
        // Act
        $response = $this->getJson('/api/pump-houses/999999');

        // Assert
        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Pump house not found'
            ]);
    }

    /** @test */
    public function it_can_create_new_pump_house()
    {
        // Arrange
        $pumpHouseData = [
            'name' => 'New Pump House',
            'address' => 'Test Address',
            'lat' => -7.2575,
            'lng' => 112.7521,
            'status' => 'Aktif',
            'capacity' => '1000 m³/jam',
            'pump_count' => 5,
            'manager_name' => 'John Doe',
            'contact_phone' => '081234567890',
            'contact_email' => 'john@example.com',
            'staff_count' => 10
        ];

        // Act
        $response = $this->postJson('/api/pump-houses', $pumpHouseData);

        // Assert
        $response->assertStatus(201)
            ->assertJson([
                'data' => [
                    'name' => 'New Pump House',
                    'address' => 'Test Address',
                    'status' => 'Aktif'
                ]
            ]);

        $this->assertDatabaseHas('pump_houses', [
            'name' => 'New Pump House',
            'address' => 'Test Address'
        ]);
    }

    /** @test */
    public function it_validates_required_fields_when_creating_pump_house()
    {
        // Act
        $response = $this->postJson('/api/pump-houses', []);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'name',
                'address', 
                'lat',
                'lng',
                'status',
                'capacity',
                'pump_count'
            ]);
    }

    /** @test */
    public function it_can_update_pump_house()
    {
        // Arrange
        $pumpHouse = PumpHouse::factory()->create([
            'name' => 'Old Name',
            'status' => 'Tidak Aktif'
        ]);

        $updateData = [
            'name' => 'Updated Name',
            'status' => 'Aktif',
            'pump_count' => 8
        ];

        // Act
        $response = $this->putJson("/api/pump-houses/{$pumpHouse->id}", $updateData);

        // Assert
        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $pumpHouse->id,
                    'name' => 'Updated Name',
                    'status' => 'Aktif',
                    'pump_count' => 8
                ]
            ]);

        $this->assertDatabaseHas('pump_houses', [
            'id' => $pumpHouse->id,
            'name' => 'Updated Name',
            'status' => 'Aktif'
        ]);
    }

    /** @test */
    public function it_can_delete_pump_house()
    {
        // Arrange
        $pumpHouse = PumpHouse::factory()->create();

        // Act
        $response = $this->deleteJson("/api/pump-houses/{$pumpHouse->id}");

        // Assert
        $response->assertStatus(204);
        $this->assertDatabaseMissing('pump_houses', ['id' => $pumpHouse->id]);
    }

    /** @test */
    public function it_can_record_water_level()
    {
        // Arrange
        $pumpHouse = PumpHouse::factory()->create();
        $waterLevelData = [
            'water_level' => 2.75,
            'recorded_at' => now()->toISOString()
        ];

        // Act
        $response = $this->postJson("/api/pump-houses/{$pumpHouse->id}/water-levels", $waterLevelData);

        // Assert
        $response->assertStatus(201)
            ->assertJson([
                'data' => [
                    'pump_house_id' => $pumpHouse->id,
                    'water_level' => 2.75
                ]
            ]);

        $this->assertDatabaseHas('water_level_history', [
            'pump_house_id' => $pumpHouse->id,
            'water_level' => 2.75
        ]);
    }

    /** @test */
    public function it_can_get_water_level_history()
    {
        // Arrange
        $pumpHouse = PumpHouse::factory()->create();
        
        // Create multiple water level records
        for ($i = 0; $i < 5; $i++) {
            WaterLevelHistory::create([
                'pump_house_id' => $pumpHouse->id,
                'water_level' => 2.0 + ($i * 0.1),
                'recorded_at' => now()->subHours($i),
            ]);
        }

        // Act
        $response = $this->getJson("/api/pump-houses/{$pumpHouse->id}/water-levels");

        // Assert
        $response->assertStatus(200)
            ->assertJsonCount(5, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'water_level',
                        'recorded_at'
                    ]
                ]
            ]);
    }

    /** @test */
    public function it_can_filter_water_level_history_by_date_range()
    {
        // Arrange
        $pumpHouse = PumpHouse::factory()->create();
        
        // Create records: 2 within range, 1 outside
        WaterLevelHistory::create([
            'pump_house_id' => $pumpHouse->id,
            'water_level' => 2.0,
            'recorded_at' => now()->subDays(2), // Within range
        ]);
        
        WaterLevelHistory::create([
            'pump_house_id' => $pumpHouse->id,
            'water_level' => 2.5,
            'recorded_at' => now()->subDay(), // Within range
        ]);
        
        WaterLevelHistory::create([
            'pump_house_id' => $pumpHouse->id,
            'water_level' => 3.0,
            'recorded_at' => now()->subDays(10), // Outside range
        ]);

        // Act
        $response = $this->getJson("/api/pump-houses/{$pumpHouse->id}/water-levels?" . http_build_query([
            'start_date' => now()->subDays(3)->toDateString(),
            'end_date' => now()->toDateString()
        ]));

        // Assert
        $response->assertStatus(200)
            ->assertJsonCount(2, 'data'); // Only records within range
    }

    /** @test */
    public function it_can_get_pump_house_statistics()
    {
        // Arrange
        $pumpHouse = PumpHouse::factory()->create();
        
        // Create water level data for statistics
        $levels = [1.5, 2.0, 2.5, 3.0, 2.8, 2.2, 1.8];
        foreach ($levels as $index => $level) {
            WaterLevelHistory::create([
                'pump_house_id' => $pumpHouse->id,
                'water_level' => $level,
                'recorded_at' => now()->subDays($index),
            ]);
        }

        // Act
        $response = $this->getJson("/api/pump-houses/{$pumpHouse->id}/statistics");

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'current_water_level',
                    'average_water_level',
                    'min_water_level',
                    'max_water_level',
                    'total_readings',
                    'status_distribution',
                    'trend'
                ]
            ]);
    }

    /** @test */
    public function it_requires_authentication_for_api_access()
    {
        // Arrange - Clear authentication
        auth()->logout();

        // Act
        $response = $this->getJson('/api/pump-houses');

        // Assert
        $response->assertStatus(401);
    }

    /** @test */
    public function it_handles_bulk_water_level_import()
    {
        // Arrange
        $pumpHouse = PumpHouse::factory()->create();
        
        $bulkData = [
            'water_levels' => [
                ['water_level' => 1.5, 'recorded_at' => now()->subHours(3)->toISOString()],
                ['water_level' => 2.0, 'recorded_at' => now()->subHours(2)->toISOString()],
                ['water_level' => 2.5, 'recorded_at' => now()->subHour()->toISOString()],
            ]
        ];

        // Act
        $response = $this->postJson("/api/pump-houses/{$pumpHouse->id}/water-levels/bulk", $bulkData);

        // Assert
        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Water levels imported successfully',
                'imported_count' => 3
            ]);

        $this->assertDatabaseCount('water_level_history', 3);
    }
} 