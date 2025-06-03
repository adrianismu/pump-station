<?php

namespace Database\Factories;

use App\Models\PumpHouse;
use Illuminate\Database\Eloquent\Factories\Factory;

class PumpHouseFactory extends Factory
{
    protected $model = PumpHouse::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company . ' Pump Station',
            'address' => $this->faker->address,
            'lat' => $this->faker->latitude(-8.0, -6.0), // Surabaya area
            'lng' => $this->faker->longitude(112.0, 113.0), // Surabaya area
            'status' => $this->faker->randomElement(['Aktif', 'Perlu Perhatian', 'Tidak Aktif']),
            'capacity' => $this->faker->numberBetween(1000, 8000) . ' m³/jam',
            'pump_count' => $this->faker->numberBetween(3, 12),
            'water_level_warning' => $this->faker->randomFloat(2, 1.5, 2.5),
            'water_level_critical' => $this->faker->randomFloat(2, 2.5, 3.5),
            'image' => 'https://via.placeholder.com/800x600.png?text=Pump+House',
            'built_year' => $this->faker->numberBetween(2000, 2023),
            'manager_name' => $this->faker->name,
            'contact_phone' => $this->faker->phoneNumber,
            'contact_email' => $this->faker->email,
            'staff_count' => $this->faker->numberBetween(5, 20),
            'last_updated' => $this->faker->dateTimeBetween('-1 week', 'now'),
        ];
    }

    /**
     * Indicate that the pump house is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'Aktif',
        ]);
    }

    /**
     * Indicate that the pump house needs attention.
     */
    public function needsAttention(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'Perlu Perhatian',
        ]);
    }

    /**
     * Indicate that the pump house is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'Tidak Aktif',
        ]);
    }

    /**
     * Configure pump house with high capacity.
     */
    public function highCapacity(): static
    {
        return $this->state(fn (array $attributes) => [
            'capacity' => $this->faker->numberBetween(5000, 10000) . ' m³/jam',
            'pump_count' => $this->faker->numberBetween(8, 15),
            'staff_count' => $this->faker->numberBetween(15, 30),
        ]);
    }

    /**
     * Configure pump house with specific coordinates.
     */
    public function withCoordinates(float $lat, float $lng): static
    {
        return $this->state(fn (array $attributes) => [
            'lat' => $lat,
            'lng' => $lng,
        ]);
    }

    /**
     * Configure pump house with specific water level thresholds.
     */
    public function withThresholds(float $warning, float $critical): static
    {
        return $this->state(fn (array $attributes) => [
            'water_level_warning' => $warning,
            'water_level_critical' => $critical,
        ]);
    }
} 