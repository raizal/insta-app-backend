<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run(): void
    {
        $this->command->info('Clearing existing data...');
        
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Truncate all tables
        \App\Models\Comment::truncate();
        \App\Models\Like::truncate();
        \App\Models\Follow::truncate();
        \App\Models\Post::truncate();
        \App\Models\User::truncate();
        
        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        $this->command->info('Seeding users...');
        $this->call(UserSeeder::class);
        
        $this->command->info('Seeding posts...');
        $this->call(PostSeeder::class);
        
        $this->command->info('Seeding interactions (follows, likes, comments)...');
        $this->call(InteractionSeeder::class);
        
        $this->command->info('Seeding completed successfully!');
    }
}
