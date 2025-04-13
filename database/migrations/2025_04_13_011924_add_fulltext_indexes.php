<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add fulltext index to users table for username and email
        Schema::table('users', function (Blueprint $table) {
            // Check if index exists before trying to drop it
            $indexExists = DB::select("SHOW INDEX FROM users WHERE Key_name = 'users_username_email_fulltext'");
            if (!empty($indexExists)) {
                DB::statement('ALTER TABLE users DROP INDEX users_username_email_fulltext');
            }
            
            // Create fulltext index on username and email
            DB::statement('ALTER TABLE users ADD FULLTEXT users_username_email_fulltext (username, email)');
        });

        // Add fulltext index to posts table for caption
        Schema::table('posts', function (Blueprint $table) {
            // Check if index exists before trying to drop it
            $indexExists = DB::select("SHOW INDEX FROM posts WHERE Key_name = 'posts_caption_fulltext'");
            if (!empty($indexExists)) {
                DB::statement('ALTER TABLE posts DROP INDEX posts_caption_fulltext');
            }
            
            // Create fulltext index on caption
            DB::statement('ALTER TABLE posts ADD FULLTEXT posts_caption_fulltext (caption)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove fulltext index from users table
        Schema::table('users', function (Blueprint $table) {
            // Check if index exists before trying to drop it
            $indexExists = DB::select("SHOW INDEX FROM users WHERE Key_name = 'users_username_email_fulltext'");
            if (!empty($indexExists)) {
                DB::statement('ALTER TABLE users DROP INDEX users_username_email_fulltext');
            }
        });

        // Remove fulltext index from posts table
        Schema::table('posts', function (Blueprint $table) {
            // Check if index exists before trying to drop it
            $indexExists = DB::select("SHOW INDEX FROM posts WHERE Key_name = 'posts_caption_fulltext'");
            if (!empty($indexExists)) {
                DB::statement('ALTER TABLE posts DROP INDEX posts_caption_fulltext');
            }
        });
    }
};
