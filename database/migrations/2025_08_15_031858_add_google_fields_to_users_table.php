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
        Schema::table('users', function (Blueprint $table) {
            $table->string('google_id')->nullable()->unique()->after('id');
            $table->string('avatar')->nullable()->after('email');
            $table->string('timezone')->default('UTC')->after('avatar');
            $table->json('preferences')->nullable()->after('timezone');
            $table->string('google_access_token')->nullable()->after('preferences');
            $table->string('google_refresh_token')->nullable()->after('google_access_token');
            $table->timestamp('token_expires_at')->nullable()->after('google_refresh_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'google_id',
                'avatar', 
                'timezone',
                'preferences',
                'google_access_token',
                'google_refresh_token',
                'token_expires_at'
            ]);
        });
    }
};
