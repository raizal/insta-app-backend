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
        Schema::table('followers', function (Blueprint $table) {
            // Add single-column indexes for better query performance
            $table->index('user_id');
            $table->index('follower_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('followers', function (Blueprint $table) {
            // Drop the indexes if the migration is rolled back
            $table->dropIndex(['user_id']);
            $table->dropIndex(['follower_id']);
        });
    }
};
