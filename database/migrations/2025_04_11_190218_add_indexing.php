<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            // Index on post_id (frequent filtering)
            $table->index('post_id');
            
            // Index on parent_id (for nested comments queries)
            $table->index('parent_id');
            
            // Compound index for frequent query pattern in getPostComments
            $table->index(['post_id', 'parent_id', 'created_at']);
            
            // Index for auth checks
            $table->index('user_id');
        });
        Schema::table('posts', function (Blueprint $table) {
            // For user's posts retrieval
            $table->index('user_id');
            
            // For timeline/feed queries
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            $table->dropIndex('post_id');
            $table->dropIndex('parent_id');
            $table->dropIndex(['post_id', 'parent_id', 'created_at']);
            $table->dropIndex('user_id');
        });
        Schema::table('posts', function (Blueprint $table) {
            $table->dropIndex('user_id');
            $table->dropIndex('created_at');
        });
    }
};
