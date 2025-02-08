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
        // Create admin user only
        User::create([
            'name' => 'Admin User',
            'username' => 'admin',
            'email' => 'admin@cms.test',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);
    }
}
