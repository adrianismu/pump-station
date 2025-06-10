<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create roles
        $roles = ['admin', 'petugas', 'masyarakat'];
        
        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        }

        // Create permissions
        $permissions = [
            'view pump houses',
            'create pump houses',
            'edit pump houses',
            'delete pump houses',
            'view water levels',
            'create water levels',
            'edit water levels',
            'delete water levels',
            'view alerts',
            'create alerts',
            'edit alerts',
            'delete alerts',
            'view reports',
            'create reports',
            'edit reports',
            'delete reports',
        ];

        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate(['name' => $permissionName, 'guard_name' => 'web']);
        }

        // Assign permissions to roles
        $adminRole = Role::findByName('admin', 'web');
        $petugasRole = Role::findByName('petugas', 'web');
        $masyarakatRole = Role::findByName('masyarakat', 'web');

        // Admin gets all permissions
        $adminRole->syncPermissions(Permission::all());

        // Petugas gets limited permissions
        $petugasRole->syncPermissions([
            'view pump houses',
            'view water levels',
            'create water levels',
            'edit water levels',
            'view alerts',
            'view reports',
            'create reports',
        ]);

        // Masyarakat gets very limited permissions
        $masyarakatRole->syncPermissions([
            'view reports',
            'create reports',
        ]);

        $this->command->info('Roles and permissions created successfully!');
    }
} 