<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if super admin already exists
        if (!User::where('email', 'admin@bookiraj.com')->exists()) {
            User::create([
                'name' => 'Super Admin',
                'email' => 'admin@bookiraj.com',
                'phone' => '123456789',
                'password' => Hash::make('Admin123!'),
                'role' => 'super_admin',
                'email_verified_at' => now(),
            ]);
            
            $this->command->info('Super Admin user created successfully!');
        } else {
            $this->command->info('Super Admin user already exists.');
        }
    }
}