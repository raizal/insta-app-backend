<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Post;
use Faker\Factory as Faker;

class PostSeeder extends Seeder
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
        
        // Create 10-25 posts for each user
        foreach ($users as $user) {
            $postCount = $faker->numberBetween(10, 25);
            
            for ($i = 0; $i < $postCount; $i++) {
                Post::create([
                    'user_id' => $user->id,
                    'caption' => $faker->boolean(80) ? $faker->paragraph() : null,
                    'image_path' => "https://picsum.photos/seed/{$user->id}-{$i}/800/600",
                    'created_at' => $faker->dateTimeBetween($user->created_at, now()),
                    'updated_at' => now(),
                ]);
            }
        }
    }
} 