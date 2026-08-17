<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $levelOneAdmin = User::updateOrCreate(
            [
                'email' => 'admin1@example.com',
            ],
            [
                'name' => 'Level One Admin',
                'password' => Hash::make('password'),
            ]
        );

        $levelOneAdmin->syncRoles([
            'admin_level_1',
        ]);

        $levelTwoAdmin = User::updateOrCreate(
            [
                'email' => 'admin2@example.com',
            ],
            [
                'name' => 'Level Two Admin',
                'password' => Hash::make('password'),
            ]
        );

        $levelTwoAdmin->syncRoles([
            'admin_level_2',
        ]);
    }
}
