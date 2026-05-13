<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@showroom.com'],
            [
                'name'     => 'Admin',
                'password' => Hash::make('password'),
            ]
        );
        $admin->assignRole('admin');

        $salesperson = User::firstOrCreate(
            ['email' => 'sales@showroom.com'],
            [
                'name'     => 'Salesperson',
                'password' => Hash::make('password'),
            ]
        );
        $salesperson->assignRole('salesperson');

        $accountant = User::firstOrCreate(
            ['email' => 'accounts@showroom.com'],
            [
                'name'     => 'Accountant',
                'password' => Hash::make('password'),
            ]
        );
        $accountant->assignRole('accountant');
    }
}
