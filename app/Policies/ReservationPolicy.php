<?php

namespace App\Policies;

use App\Models\Reservation;
use App\Models\User;
use Illuminate\Auth\Access\Response;

// app/Policies/ReservationPolicy.php
class ReservationPolicy
{
    public function viewAny(User $user = null): bool
    {
        return true; // todos pueden ver el listado/calendario
    }

    public function view(User $user = null, Reservation $reservation): bool
    {
        return true; // todos pueden ver cada reserva
    }

    // Si quieres que todos puedan crear, deja true. Si no, ajusta.
    public function create(User $user): bool { return true; }

    // Editar: solo administradores
    public function update(User $user, Reservation $r) {
        return $user->role === 'admin' || $user->id === $r->user_id;
    }

    // Eliminar: solo administradores
    public function delete(User $user, Reservation $r) {
        return $user->role === 'admin' || $user->id === $r->user_id;
    }
    
}

