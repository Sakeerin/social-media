<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;

class CommentSeeder extends Seeder
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

        $sampleComments = [
            'Great post! 👍',
            'This is so inspiring!',
            'I totally agree with this!',
            'Thanks for sharing!',
            'Amazing work! 🔥',
            'This made my day!',
            'Beautiful! ❤️',
            'Keep up the great work!',
            'I love this!',
            'So true!',
            'This is exactly what I needed to see today!',
            'Incredible! 😍',
            'You\'re doing amazing!',
            'This is fantastic!',
            'Love your perspective on this!',
            'Keep being awesome!',
            'This is so helpful!',
            'You inspire me!',
            'Brilliant! ✨',
            'This is gold! 🏆',
        ];

        // Create comments for posts
        foreach ($posts as $post) {
            $numberOfComments = rand(0, 8); // Each post will have 0-8 comments
            
            for ($i = 0; $i < $numberOfComments; $i++) {
                $randomUser = $users->random();
                $randomComment = $sampleComments[array_rand($sampleComments)];
                
                Comment::create([
                    'post_id' => $post->id,
                    'user_id' => $randomUser->id,
                    'content' => $randomComment,
                ]);
            }
        }
    }
}
