<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;


class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
        $this->call(RoleSeeder::class);

        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
        ]);

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
