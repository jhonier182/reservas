<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear usuario de Angie Diaz
        User::create([
            'name' => 'Angie Diaz',
            'email' => 'It.intern@beltcolombia.com',
            'password' => Hash::make('Mercurio1*'),
            'email_verified_at' => now(),
        ]);

        // Crear usuario de prueba adicional
        User::create([
            'name' => 'Usuario Prueba',
            'email' => 'test@beltcolombia.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);
    }
}
