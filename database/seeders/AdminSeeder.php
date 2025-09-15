<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $email = 'angie.diaz@beltcolombia.com';
        
        // Verificar si el usuario ya existe
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            // Crear el usuario administrador
            User::create([
                'name' => 'Angie Diaz',
                'email' => $email,
                'password' => Hash::make('password123'), // Contraseña temporal
                'role' => 'admin',
                'email_verified_at' => now()
            ]);
            
            $this->command->info("Usuario administrador creado exitosamente:");
            $this->command->info("Email: {$email}");
            $this->command->info("Contraseña temporal: password123");
            $this->command->warn("IMPORTANTE: Cambia la contraseña después del primer login.");
        } else {
            // Actualizar el rol del usuario existente
            $user->update(['role' => 'admin']);
            
            $this->command->info("Usuario existente actualizado a administrador:");
            $this->command->info("Email: {$user->email}");
            $this->command->info("Nombre: {$user->name}");
        }
        
        // Verificación final
        $finalUser = User::where('email', $email)->first();
        $this->command->info("Verificación final:");
        $this->command->info("Email: {$finalUser->email}");
        $this->command->info("Rol: {$finalUser->role}");
        $this->command->info("Es administrador: " . ($finalUser->isAdmin() ? 'Sí' : 'No'));
    }
}

