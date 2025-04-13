<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Faker\Factory as Faker;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();
        
        // Create 50 users with random profile pictures
        for ($i = 0; $i < 50; $i++) {
            User::create([
                'name' => $faker->name(),
                'email' => $faker->unique()->safeEmail(),
                'username' => $faker->unique()->userName(),
                'profile_picture' => "https://source.unsplash.com/random/300x300?profile&sig={$i}",
                'email_verified_at' => now(),
                'password' => bcrypt('password'), // Default password
                'created_at' => $faker->dateTimeBetween('-1 year', '-1 month'),
                'updated_at' => now(),
            ]);
        }
    }
} 