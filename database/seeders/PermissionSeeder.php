<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $guardName = 'web';

        $permissions = [
            'ticket.view-all',
            'ticket.approve',
            'ticket.reject',
            'ticket.bulk-approve',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, $guardName);
        }

        $levelOne = Role::findOrCreate('admin_level_1', $guardName);
        $levelTwo = Role::findOrCreate('admin_level_2', $guardName);

        // پاک کردن کش Spatie قبل از ساخت یا اختصاص مجوزها
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $levelOne->givePermissionTo([
            'ticket.view-all',
            'ticket.approve',
            'ticket.reject',
            'ticket.bulk-approve',
        ]);

        $levelTwo->givePermissionTo([
            'ticket.view-all',
            'ticket.approve',
            'ticket.reject',
            'ticket.bulk-approve',
        ]);
    }
}