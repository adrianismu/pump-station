<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ThresholdSetting;

class ThresholdSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $thresholds = [
            [
                'name' => 'normal',
                'label' => 'Normal',
                'water_level' => 0.00,
                'color' => '#10b981', // green
                'severity' => 'low',
                'is_active' => true,
                'description' => 'Ketinggian air dalam batas normal, tidak ada tindakan khusus diperlukan.',
            ],
            [
                'name' => 'warning',
                'label' => 'Peringatan',
                'water_level' => 2.00,
                'color' => '#f59e0b', // yellow
                'severity' => 'medium',
                'is_active' => true,
                'description' => 'Ketinggian air mulai tinggi, perlu waspada dan monitoring lebih ketat.',
            ],
            [
                'name' => 'critical',
                'label' => 'Kritis',
                'water_level' => 2.50,
                'color' => '#ef4444', // red
                'severity' => 'high',
                'is_active' => true,
                'description' => 'Ketinggian air sangat tinggi, berpotensi banjir. Segera lakukan tindakan pencegahan.',
            ],
            [
                'name' => 'emergency',
                'label' => 'Darurat',
                'water_level' => 3.00,
                'color' => '#dc2626', // dark red
                'severity' => 'critical',
                'is_active' => true,
                'description' => 'Ketinggian air mencapai level darurat. Evakuasi dan tindakan darurat diperlukan.',
            ],
        ];

        foreach ($thresholds as $threshold) {
            ThresholdSetting::updateOrCreate(
                ['name' => $threshold['name']],
                $threshold
            );
        }
    }
}
