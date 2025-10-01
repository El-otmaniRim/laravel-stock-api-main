<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $admin->assignRole('admin');



        $client = User::updateOrCreate(
            ['email' => 'testuser@example.com'],
            [
                'name' => 'Client',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );
        $client->assignRole('customer');

        $client1 = User::updateOrCreate(
            ['email' => 'Ahmed@example.com'],
            [
                'name' => 'Ahmed',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );
        $client1->assignRole('customer');

        $delivery = User::updateOrCreate(
            ['email' => 'delivry@gmail.com'],
            [
                'name' => 'Delivery',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );
        $delivery->assignRole('delivery');
    }
}