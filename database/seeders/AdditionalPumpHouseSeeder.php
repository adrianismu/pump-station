<?php

namespace Database\Seeders;

use App\Models\PumpHouse;
use Illuminate\Database\Seeder;

class AdditionalPumpHouseSeeder extends Seeder
{
    public function run(): void
    {
        // Additional pump houses in Surabaya (should show similar weather)
        $surabayaPumpHouses = [
            [
                'name' => 'Rumah Pompa Tandes',
                'address' => 'Jl. Tandes Kidul, Tandes, Surabaya',
                'lat' => -7.2502,
                'lng' => 112.6998,
                'status' => 'Aktif',
                'capacity' => '4500 m³/jam',
                'pump_count' => 6,
                'image' => 'https://images.unsplash.com/photo-1621905252507-b35492cc74b4?q=80&w=2069&auto=format&fit=crop',
                'built_year' => 2016,
                'manager_name' => 'Agus Salim',
                'contact_phone' => '081234567891',
                'contact_email' => 'agus.s@surabaya.go.id',
                'staff_count' => 9,
                'last_updated' => now()->subMinutes(8),
            ],
            [
                'name' => 'Rumah Pompa Semampir',
                'address' => 'Jl. Semampir Tengah, Semampir, Surabaya',
                'lat' => -7.2401,
                'lng' => 112.7511,
                'status' => 'Aktif',
                'capacity' => '3800 m³/jam',
                'pump_count' => 7,
                'image' => 'https://images.unsplash.com/photo-1574019927486-12cf57a7f3b6?q=80&w=2070&auto=format&fit=crop',
                'built_year' => 2018,
                'manager_name' => 'Rina Kusuma',
                'contact_phone' => '081234567892',
                'contact_email' => 'rina.k@surabaya.go.id',
                'staff_count' => 11,
                'last_updated' => now()->subMinutes(12),
            ],
            [
                'name' => 'Rumah Pompa Sawahan',
                'address' => 'Jl. Sawahan, Sawahan, Surabaya',
                'lat' => -7.2613,
                'lng' => 112.7401,
                'status' => 'Perlu Perhatian',
                'capacity' => '5200 m³/jam',
                'pump_count' => 8,
                'image' => 'https://images.unsplash.com/photo-1581092918056-0c4c3acd3789?q=80&w=2070&auto=format&fit=crop',
                'built_year' => 2017,
                'manager_name' => 'Bambang Wijaya',
                'contact_phone' => '081234567893',
                'contact_email' => 'bambang.w@surabaya.go.id',
                'staff_count' => 10,
                'last_updated' => now()->subMinutes(20),
            ]
        ];

        // Pump houses in other cities (should show different weather)
        $otherCityPumpHouses = [
            [
                'name' => 'Rumah Pompa Pluit',
                'address' => 'Jl. Pluit Raya, Penjaringan, Jakarta Utara',
                'lat' => -6.1297,
                'lng' => 106.7889,
                'status' => 'Aktif',
                'capacity' => '7500 m³/jam',
                'pump_count' => 12,
                'image' => 'https://images.unsplash.com/photo-1504328345606-18bbc8c9d7d1?q=80&w=2070&auto=format&fit=crop',
                'built_year' => 2019,
                'manager_name' => 'Jakarta Team',
                'contact_phone' => '021-55667788',
                'contact_email' => 'pluit@jakarta.go.id',
                'staff_count' => 18,
                'last_updated' => now()->subMinutes(6),
            ],
            [
                'name' => 'Rumah Pompa Cihampelas',
                'address' => 'Jl. Cihampelas, Bandung Barat, Bandung',
                'lat' => -6.8951,
                'lng' => 107.5937,
                'status' => 'Aktif',
                'capacity' => '4200 m³/jam',
                'pump_count' => 6,
                'image' => 'https://images.unsplash.com/photo-1590496794008-383c8070b257?q=80&w=2069&auto=format&fit=crop',
                'built_year' => 2020,
                'manager_name' => 'Dedi Setiawan',
                'contact_phone' => '022-8765432',
                'contact_email' => 'dedi.s@bandung.go.id',
                'staff_count' => 8,
                'last_updated' => now()->subMinutes(18),
            ],
            [
                'name' => 'Rumah Pompa Denpasar',
                'address' => 'Jl. Bypass Ngurah Rai, Denpasar, Bali',
                'lat' => -8.6705,
                'lng' => 115.2126,
                'status' => 'Aktif',
                'capacity' => '3500 m³/jam',
                'pump_count' => 5,
                'image' => 'https://images.unsplash.com/photo-1621905252507-b35492cc74b4?q=80&w=2069&auto=format&fit=crop',
                'built_year' => 2021,
                'manager_name' => 'I Made Suwandi',
                'contact_phone' => '0361-445566',
                'contact_email' => 'made.s@denpasar.go.id',
                'staff_count' => 7,
                'last_updated' => now()->subMinutes(25),
            ]
        ];

        echo "🏗️  Creating additional pump houses...\n";
        
        // Insert Surabaya pump houses
        echo "📍 Adding Surabaya pump houses:\n";
        foreach ($surabayaPumpHouses as $pumpHouse) {
            PumpHouse::create($pumpHouse);
            echo "   ✅ {$pumpHouse['name']} - Surabaya\n";
        }

        // Insert other city pump houses
        echo "🌏 Adding pump houses from other cities:\n";  
        foreach ($otherCityPumpHouses as $pumpHouse) {
            PumpHouse::create($pumpHouse);
            echo "   ✅ {$pumpHouse['name']} - " . explode(', ', $pumpHouse['address'])[2] . "\n";
        }

        echo "\n🎯 Weather comparison setup complete!\n";
        echo "   - Surabaya pump houses should show similar weather data\n";
        echo "   - Other cities should show different weather data\n";
        echo "   - Total additional pump houses: " . (count($surabayaPumpHouses) + count($otherCityPumpHouses)) . "\n\n";
    }
} 