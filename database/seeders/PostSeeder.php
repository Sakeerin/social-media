<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        
        if ($users->isEmpty()) {
            $this->command->info('No users found. Please run UserSeeder first.');
            return;
        }

        $samplePosts = [
            [
                'caption' => 'Just finished an amazing coding session! 🚀 Building something exciting with Laravel and React.',
                'image' => null,
            ],
            [
                'caption' => 'Beautiful sunset from my balcony today. Nature never fails to amaze me. 🌅',
                'image' => null,
            ],
            [
                'caption' => 'New workout routine is paying off! Feeling stronger every day. 💪',
                'image' => null,
            ],
            [
                'caption' => 'Working on a new art project. Creativity is flowing today! 🎨',
                'image' => null,
            ],
            [
                'caption' => 'Great meeting with potential investors today. Startup life is exciting! 🚀',
                'image' => null,
            ],
            [
                'caption' => 'Coffee and code - the perfect combination for a productive day ☕',
                'image' => null,
            ],
            [
                'caption' => 'Traveling to a new city tomorrow. Can\'t wait to explore! ✈️',
                'image' => null,
            ],
            [
                'caption' => 'Reading a fascinating book about AI and machine learning. So much to learn! 📚',
                'image' => null,
            ],
            [
                'caption' => 'Delicious homemade pasta for dinner tonight. Cooking is therapeutic 🍝',
                'image' => null,
            ],
            [
                'caption' => 'Morning run completed! Nothing beats starting the day with exercise 🏃‍♂️',
                'image' => null,
            ],
        ];

        // Create posts for each user
        foreach ($users as $user) {
            $numberOfPosts = rand(2, 5); // Each user will have 2-5 posts
            
            for ($i = 0; $i < $numberOfPosts; $i++) {
                $randomPost = $samplePosts[array_rand($samplePosts)];
                
                Post::create([
                    'user_id' => $user->id,
                    'caption' => $randomPost['caption'],
                    'image' => $randomPost['image'],
                ]);
            }
        }
    }
}
