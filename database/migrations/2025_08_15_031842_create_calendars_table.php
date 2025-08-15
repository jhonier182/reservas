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
        Schema::create('calendars', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('google_calendar_id')->unique()->nullable();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->boolean('is_primary')->default(false);
            $table->string('color', 7)->default('#4285f4'); // Color en formato HEX
            $table->string('timezone')->default('UTC');
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable(); // Configuraciones adicionales
            $table->timestamps();
            
            // Índices para optimización
            $table->index(['user_id', 'is_primary']);
            $table->index(['google_calendar_id']);
            $table->index(['is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calendars');
    }
};
