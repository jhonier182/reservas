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
        Schema::table('reservations', function (Blueprint $table) {
            // Primero eliminar el campo location existente
            $table->dropColumn('location');
        });

        Schema::table('reservations', function (Blueprint $table) {
            // Agregar el nuevo campo location como enum
            $table->enum('location', ['jardin', 'casino'])->nullable()->after('end_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            // Revertir a string
            $table->dropColumn('location');
            $table->string('location')->nullable()->after('end_date');
        });
    }
};
