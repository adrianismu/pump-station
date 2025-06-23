<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\PumpHouse;
use Carbon\Carbon;

class UserPumpHouseSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Ambil users dengan role petugas
        $petugasUsers = User::where('role', 'petugas')->get();
        $pumpHouses = PumpHouse::all();

        if ($petugasUsers->isEmpty() || $pumpHouses->isEmpty()) {
            $this->command->info('Tidak ada petugas atau pump house untuk di-assign.');
            return;
        }

        // Sample assignments
        $assignments = [
            // Petugas 1: Akses ke Wonokromo dan Gubeng (write access)
            [
                'user_email' => 'petugas@example.com',
                'pump_houses' => ['Wonokromo', 'Gubeng'],
                'access_level' => 'write',
                'notes' => 'Petugas utama untuk area Surabaya Selatan'
            ],
            // Petugas 2: Akses ke Semampir dan Pabean Cantian (read access)
            [
                'user_email' => 'petugas2@example.com',
                'pump_houses' => ['Semampir', 'Pabean Cantian'],
                'access_level' => 'read',
                'notes' => 'Petugas monitoring area Surabaya Utara'
            ],
            // Petugas 3: Akses ke Genteng (write access)
            [
                'user_email' => 'petugas3@example.com',
                'pump_houses' => ['Genteng'],
                'access_level' => 'write',
                'notes' => 'Supervisor area Genteng'
            ],
        ];

        foreach ($assignments as $assignment) {
            $user = User::where('email', $assignment['user_email'])->first();
            
            if (!$user) {
                // Buat user baru jika belum ada
                $user = User::create([
                    'name' => 'Petugas ' . substr($assignment['user_email'], 0, strpos($assignment['user_email'], '@')),
                    'email' => $assignment['user_email'],
                    'password' => bcrypt('password'),
                    'role' => 'petugas',
                ]);
                $user->assignRole('petugas');
            }

            foreach ($assignment['pump_houses'] as $pumpHouseName) {
                $pumpHouse = $pumpHouses->where('name', $pumpHouseName)->first();
                
                if ($pumpHouse) {
                    // Cek apakah sudah ada assignment
                    $existing = $user->allPumpHouses()->where('pump_house_id', $pumpHouse->id)->first();
                    
                    if (!$existing) {
                        $user->pumpHouses()->attach($pumpHouse->id, [
                            'access_level' => $assignment['access_level'],
                            'is_active' => true,
                            'assigned_at' => now(),
                            'expires_at' => null, // Tidak ada expiry
                            'notes' => $assignment['notes'],
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                        
                        $this->command->info("Assigned {$user->name} to {$pumpHouse->name} with {$assignment['access_level']} access");
                    }
                }
            }
        }

        // Tambahan: Buat beberapa assignment dengan expiry date
        $tempUser = User::where('email', 'temp.petugas@example.com')->first();
        if (!$tempUser) {
            $tempUser = User::create([
                'name' => 'Petugas Sementara',
                'email' => 'temp.petugas@example.com',
                'password' => bcrypt('password'),
                'role' => 'petugas',
            ]);
            $tempUser->assignRole('petugas');
        }

        // Assignment sementara yang akan expire dalam 30 hari
        $randomPumpHouse = $pumpHouses->random();
        $existing = $tempUser->allPumpHouses()->where('pump_house_id', $randomPumpHouse->id)->first();
        
        if (!$existing) {
            $tempUser->pumpHouses()->attach($randomPumpHouse->id, [
                'access_level' => 'read',
                'is_active' => true,
                'assigned_at' => now(),
                'expires_at' => now()->addDays(30),
                'notes' => 'Akses sementara untuk training',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            $this->command->info("Assigned temporary access for {$tempUser->name} to {$randomPumpHouse->name}");
        }

        // Assignment yang sudah tidak aktif
        $inactiveUser = User::where('email', 'inactive.petugas@example.com')->first();
        if (!$inactiveUser) {
            $inactiveUser = User::create([
                'name' => 'Petugas Nonaktif',
                'email' => 'inactive.petugas@example.com',
                'password' => bcrypt('password'),
                'role' => 'petugas',
            ]);
            $inactiveUser->assignRole('petugas');
        }

        $anotherPumpHouse = $pumpHouses->where('id', '!=', $randomPumpHouse->id)->random();
        $existing = $inactiveUser->allPumpHouses()->where('pump_house_id', $anotherPumpHouse->id)->first();
        
        if (!$existing) {
            $inactiveUser->allPumpHouses()->attach($anotherPumpHouse->id, [
                'access_level' => 'write',
                'is_active' => false, // Tidak aktif
                'assigned_at' => now()->subDays(60),
                'expires_at' => null,
                'notes' => 'Akses dicabut karena pindah tugas',
                'created_at' => now()->subDays(60),
                'updated_at' => now()->subDays(10),
            ]);
            
            $this->command->info("Created inactive assignment for {$inactiveUser->name} to {$anotherPumpHouse->name}");
        }
    }
} 
 