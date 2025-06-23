<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create or Update Administrator
        User::updateOrCreate(
            ['email' => 'admin@pumpstation.com'],
            [
                'name' => 'Administrator',
                'role' => 'admin',
                'email_verified_at' => now(),
                'password' => Hash::make('admin123'), // Default password
            ]
        );

        // Create Petugas Users
        $petugasUsers = [
            [
                'name' => 'Budi Santoso',
                'email' => 'budi.santoso@pumpstation.com',
                'role' => 'petugas',
                'email_verified_at' => now(),
                'password' => Hash::make('petugas123'),
            ],
            [
                'name' => 'Siti Rahayu',
                'email' => 'siti.rahayu@pumpstation.com',
                'role' => 'petugas',
                'email_verified_at' => now(),
                'password' => Hash::make('petugas123'),
            ],
            [
                'name' => 'Ahmad Hidayat',
                'email' => 'ahmad.hidayat@pumpstation.com',
                'role' => 'petugas',
                'email_verified_at' => now(),
                'password' => Hash::make('petugas123'),
            ],
            [
                'name' => 'Dewi Lestari',
                'email' => 'dewi.lestari@pumpstation.com',
                'role' => 'petugas',
                'email_verified_at' => now(),
                'password' => Hash::make('petugas123'),
            ],
            [
                'name' => 'Petugas petugas',
                'email' => 'petugas@example.com',
                'role' => 'petugas',
                'password' => Hash::make('petugas123'),
            ],
            [
                'name' => 'Petugas petugas2',
                'email' => 'petugas2@example.com',
                'role' => 'petugas',
                'password' => Hash::make('petugas123'),
            ],
            [
                'name' => 'Petugas petugas3',
                'email' => 'petugas3@example.com',
                'role' => 'petugas',
                'password' => Hash::make('petugas123'),
            ],
            [
                'name' => 'Petugas Sementara',
                'email' => 'temp.petugas@example.com',
                'role' => 'petugas',
                'password' => Hash::make('petugas123'),
            ],
            [
                'name' => 'Petugas Nonaktif',
                'email' => 'inactive.petugas@example.com',
                'role' => 'petugas',
                'password' => Hash::make('petugas123'),
            ],
            [
                'name' => 'akmal',
                'email' => 'akmal@gmail.com',
                'role' => 'petugas',
                'password' => Hash::make('petugas123'),
            ],
        ];

        // Insert or update all petugas users
        foreach ($petugasUsers as $userData) {
            $email = $userData['email'];
            unset($userData['email']); // Remove email from data array
            
            User::updateOrCreate(
                ['email' => $email],
                $userData
            );
        }

        $this->command->info('Users seeded successfully!');
        $this->command->info('Total users: ' . User::count());
        $this->command->info('Admin: admin@pumpstation.com / admin123');
        $this->command->info('Petugas: [any petugas email] / petugas123');
    }
} 