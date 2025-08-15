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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('google_event_id')->unique()->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->dateTime('start_datetime');
            $table->dateTime('end_datetime');
            $table->string('location')->nullable();
            $table->foreignId('reservation_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['active', 'cancelled', 'tentative'])->default('active');
            $table->boolean('is_recurring')->default(false);
            $table->json('recurrence_rules')->nullable(); // Reglas de recurrencia
            $table->json('google_metadata')->nullable(); // Metadatos de Google Calendar
            $table->timestamps();
            
            // Índices para optimización
            $table->index(['reservation_id']);
            $table->index(['google_event_id']);
            $table->index(['start_datetime', 'end_datetime']);
            $table->index(['status', 'start_datetime']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
