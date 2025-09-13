<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\Hash;
use App\Models\User;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Fixing Super Admin user...\n";

// Check if super admin exists
$superAdmin = User::where('email', 'admin@bookiraj.com')->first();

if ($superAdmin) {
    echo "Super Admin user found. Updating password...\n";
    
    // Update the password
    $superAdmin->update([
        'password' => Hash::make('Admin123!'),
        'role' => 'super_admin',
        'email_verified_at' => now(),
    ]);
    
    echo "Super Admin password updated successfully!\n";
} else {
    echo "Super Admin user not found. Creating new user...\n";
    
    // Create new super admin
    User::create([
        'name' => 'Super Admin',
        'email' => 'admin@bookiraj.com',
        'phone' => '123456789',
        'password' => Hash::make('Admin123!'),
        'role' => 'super_admin',
        'email_verified_at' => now(),
    ]);
    
    echo "Super Admin user created successfully!\n";
}

echo "Super Admin credentials:\n";
echo "Email: admin@bookiraj.com\n";
echo "Password: Admin123!\n";
echo "Done!\n";



