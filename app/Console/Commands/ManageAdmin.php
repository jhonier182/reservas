<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class ManageAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:manage {action} {email?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gestionar usuarios administradores';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $action = $this->argument('action');
        $email = $this->argument('email');

        if (!in_array($action, ['add', 'remove', 'list'])) {
            $this->error('Acción no válida. Use: add, remove, o list');
            return 1;
        }

        if ($action !== 'list' && !$email) {
            $this->error('El email es requerido para las acciones add y remove');
            return 1;
        }

        switch ($action) {
            case 'add':
                $this->addAdmin($email);
                break;
            case 'remove':
                $this->removeAdmin($email);
                break;
            case 'list':
                $this->listAdmins();
                break;
        }

        return 0;
    }

    private function addAdmin(string $email): void
    {
        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("Usuario con email {$email} no encontrado.");
            return;
        }

        $user->update(['role' => 'admin']);
        $this->info("Usuario {$email} ahora es administrador.");
    }

    private function removeAdmin(string $email): void
    {
        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("Usuario con email {$email} no encontrado.");
            return;
        }

        $user->update(['role' => 'user']);
        $this->info("Usuario {$email} ya no es administrador.");
    }

    private function listAdmins(): void
    {
        $admins = User::where('role', 'admin')->get(['name', 'email', 'created_at']);

        if ($admins->isEmpty()) {
            $this->info('No hay usuarios administradores.');
            return;
        }

        $this->table(
            ['Nombre', 'Email', 'Fecha de Creación'],
            $admins->map(function ($admin) {
                return [
                    $admin->name,
                    $admin->email,
                    $admin->created_at->format('Y-m-d H:i:s')
                ];
            })
        );
    }
}
