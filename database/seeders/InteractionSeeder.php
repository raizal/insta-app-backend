<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Post;
use App\Models\Follow;
use App\Models\Like;
use App\Models\Comment;
use Faker\Factory as Faker;

class InteractionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();
        $users = User::all();
        $posts = Post::all();
        
        // Create random follows
        foreach ($users as $user) {
            $followCount = $faker->numberBetween(5, 15);
            $potentialFollowees = $users->where('id', '!=', $user->id)->random($followCount);
            
            foreach ($potentialFollowees as $followee) {
                Follow::create([
                    'follower_id' => $user->id,
                    'user_id' => $followee->id,
                    'created_at' => $faker->dateTimeBetween($user->created_at, now()),
                ]);
            }
        }
        
        // Create random likes
        foreach ($users as $user) {
            $likeCount = $faker->numberBetween(20, 100);
            $postsToLike = $posts->random(min($likeCount, $posts->count()));
            
            foreach ($postsToLike as $post) {
                Like::create([
                    'user_id' => $user->id,
                    'post_id' => $post->id,
                    'created_at' => $faker->dateTimeBetween($post->created_at, now()),
                ]);
            }
        }
        
        // Create random comments
        foreach ($users as $user) {
            $commentCount = $faker->numberBetween(10, 50);
            $postsToComment = $posts->random(min($commentCount, $posts->count()));
            
            foreach ($postsToComment as $post) {
                Comment::create([
                    'user_id' => $user->id,
                    'post_id' => $post->id,
                    'body' => $faker->sentences($faker->numberBetween(1, 3), true),
                    'created_at' => $faker->dateTimeBetween($post->created_at, now()),
                    'updated_at' => now(),
                ]);
            }
        }
    }
} 