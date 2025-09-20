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
        // Create test users
        $users = [
            [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'password' => Hash::make('password'),
                'description' => 'Software developer and tech enthusiast',
                'auth_type' => 'password',
                'avatar' => null,
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Jane Smith',
                'email' => 'jane@example.com',
                'password' => Hash::make('password'),
                'description' => 'Photographer and travel blogger',
                'auth_type' => 'password',
                'avatar' => null,
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Mike Johnson',
                'email' => 'mike@example.com',
                'password' => Hash::make('password'),
                'description' => 'Fitness trainer and nutrition expert',
                'auth_type' => 'password',
                'avatar' => null,
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Sarah Wilson',
                'email' => 'sarah@example.com',
                'password' => Hash::make('password'),
                'description' => 'Artist and creative director',
                'auth_type' => 'password',
                'avatar' => null,
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Alex Brown',
                'email' => 'alex@example.com',
                'password' => Hash::make('password'),
                'description' => 'Entrepreneur and startup founder',
                'auth_type' => 'password',
                'avatar' => null,
                'email_verified_at' => now(),
            ],
        ];

        foreach ($users as $userData) {
            User::create($userData);
        }

        // Create additional random users
        User::factory(15)->create([
            'auth_type' => 'password',
            'email_verified_at' => now(),
        ]);
    }
}
