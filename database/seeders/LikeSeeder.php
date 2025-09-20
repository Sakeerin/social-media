<?php

namespace Database\Seeders;

use App\Models\Like;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;

class LikeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $posts = Post::all();
        $users = User::all();
        
        if ($posts->isEmpty() || $users->isEmpty()) {
            $this->command->info('No posts or users found. Please run UserSeeder and PostSeeder first.');
            return;
        }

        // Create likes for posts
        foreach ($posts as $post) {
            $numberOfLikes = rand(0, 12); // Each post will have 0-12 likes
            
            // Get random users to like this post
            $randomUsers = $users->random(min($numberOfLikes, $users->count()));
            
            foreach ($randomUsers as $user) {
                // Check if this user already liked this post
                $existingLike = Like::where('post_id', $post->id)
                    ->where('user_id', $user->id)
                    ->first();
                
                if (!$existingLike) {
                    Like::create([
                        'post_id' => $post->id,
                        'user_id' => $user->id,
                    ]);
                }
            }
        }
    }
}
