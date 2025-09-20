<?php

namespace Database\Seeders;

use App\Models\Follow;
use App\Models\User;
use Illuminate\Database\Seeder;

class FollowSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        
        if ($users->count() < 2) {
            $this->command->info('Need at least 2 users to create follows. Please run UserSeeder first.');
            return;
        }

        // Create follows between users
        foreach ($users as $user) {
            // Each user will follow 3-8 other users
            $numberOfFollows = rand(3, min(8, $users->count() - 1));
            
            // Get random users to follow (excluding self)
            $usersToFollow = $users->where('id', '!=', $user->id)->random($numberOfFollows);
            
            foreach ($usersToFollow as $userToFollow) {
                // Check if this follow relationship already exists
                $existingFollow = Follow::where('from_id', $user->id)
                    ->where('to_id', $userToFollow->id)
                    ->first();
                
                if (!$existingFollow) {
                    Follow::create([
                        'from_id' => $user->id,
                        'to_id' => $userToFollow->id,
                    ]);
                }
            }
        }
    }
}
