<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PumpHouse;
use App\Models\PumpHouseThresholdSetting;

class PumpHouseThresholdSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pumpHouses = PumpHouse::all();

        // Contoh threshold khusus untuk beberapa rumah pompa
        $customThresholds = [
            'Rumah Pompa Wonokromo' => [
                [
                    'name' => 'normal',
                    'label' => 'Normal',
                    'water_level' => 0.00,
                    'color' => '#10b981',
                    'severity' => 'low',
                    'description' => 'Ketinggian air dalam batas normal untuk area Wonokromo.',
                ],
                [
                    'name' => 'warning',
                    'label' => 'Peringatan',
                    'water_level' => 1.80, // Lebih rendah karena area rawan banjir
                    'color' => '#f59e0b',
                    'severity' => 'medium',
                    'description' => 'Area Wonokromo rawan banjir, perlu monitoring ketat pada level ini.',
                ],
                [
                    'name' => 'critical',
                    'label' => 'Kritis',
                    'water_level' => 2.20, // Lebih rendah dari default
                    'color' => '#ef4444',
                    'severity' => 'high',
                    'description' => 'Level kritis untuk Wonokromo, segera aktifkan pompa tambahan.',
                ],
                [
                    'name' => 'emergency',
                    'label' => 'Darurat',
                    'water_level' => 2.80,
                    'color' => '#dc2626',
                    'severity' => 'critical',
                    'description' => 'Darurat! Evakuasi warga sekitar dan aktifkan semua pompa.',
                ],
            ],
            'Rumah Pompa Gubeng' => [
                [
                    'name' => 'normal',
                    'label' => 'Normal',
                    'water_level' => 0.00,
                    'color' => '#10b981',
                    'severity' => 'low',
                    'description' => 'Ketinggian air normal untuk area Gubeng.',
                ],
                [
                    'name' => 'warning',
                    'label' => 'Peringatan',
                    'water_level' => 2.20, // Sedikit lebih tinggi karena kapasitas besar
                    'color' => '#f59e0b',
                    'severity' => 'medium',
                    'description' => 'Gubeng memiliki kapasitas besar, monitoring pada level ini.',
                ],
                [
                    'name' => 'critical',
                    'label' => 'Kritis',
                    'water_level' => 2.70,
                    'color' => '#ef4444',
                    'severity' => 'high',
                    'description' => 'Level kritis untuk Gubeng, aktifkan pompa cadangan.',
                ],
                [
                    'name' => 'emergency',
                    'label' => 'Darurat',
                    'water_level' => 3.20,
                    'color' => '#dc2626',
                    'severity' => 'critical',
                    'description' => 'Darurat! Kapasitas maksimal Gubeng terlampaui.',
                ],
            ],
        ];

        foreach ($pumpHouses as $pumpHouse) {
            if (isset($customThresholds[$pumpHouse->name])) {
                // Gunakan threshold khusus
                foreach ($customThresholds[$pumpHouse->name] as $threshold) {
                    PumpHouseThresholdSetting::updateOrCreate(
                        [
                            'pump_house_id' => $pumpHouse->id,
                            'name' => $threshold['name'],
                        ],
                        [
                            'label' => $threshold['label'],
                            'water_level' => $threshold['water_level'],
                            'color' => $threshold['color'],
                            'severity' => $threshold['severity'],
                            'is_active' => true,
                            'description' => $threshold['description'],
                        ]
                    );
                }
            } else {
                // Untuk rumah pompa lainnya, biarkan menggunakan threshold default
                // (akan di-copy otomatis saat pertama kali diakses)
            }
        }
    }
}
