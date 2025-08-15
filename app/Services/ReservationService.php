<?php

namespace App\Services;

use App\Models\Reservation;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReservationService
{
    /**
     * Crear una nueva reserva
     */
    public function createReservation(array $data): Reservation
    {
        // Verificar disponibilidad antes de crear
        if (!$this->checkAvailability($data['start_date'], $data['end_date'], $data['location'] ?? null)) {
            throw new \Exception('No hay disponibilidad para el horario y ubicación seleccionados');
        }

        return DB::transaction(function () use ($data) {
            $reservation = Reservation::create($data);
            
            // Aquí se podría disparar un evento ReservationCreated
            // event(new ReservationCreated($reservation));
            
            return $reservation;
        });
    }

    /**
     * Actualizar una reserva existente
     */
    public function updateReservation(Reservation $reservation, array $data): bool
    {
        // Si se cambia la fecha/hora, verificar disponibilidad
        if (isset($data['start_date']) || isset($data['end_date'])) {
            $startDate = $data['start_date'] ?? $reservation->start_date;
            $endDate = $data['end_date'] ?? $reservation->end_date;
            $location = $data['location'] ?? $reservation->location;
            
            if (!$this->checkAvailability($startDate, $endDate, $location, $reservation->id)) {
                throw new \Exception('No hay disponibilidad para el nuevo horario y ubicación');
            }
        }

        return DB::transaction(function () use ($reservation, $data) {
            $result = $reservation->update($data);
            
            // Aquí se podría disparar un evento ReservationUpdated
            // event(new ReservationUpdated($reservation));
            
            return $result;
        });
    }

    /**
     * Eliminar una reserva
     */
    public function deleteReservation(Reservation $reservation): bool
    {
        return DB::transaction(function () use ($reservation) {
            // Aquí se podría disparar un evento ReservationCancelled
            // event(new ReservationCancelled($reservation));
            
            return $reservation->delete();
        });
    }

    /**
     * Verificar disponibilidad para un horario y ubicación
     */
    public function checkAvailability(string $startDate, string $endDate, string $location = null, int $excludeReservationId = null): bool
    {
        $query = Reservation::where('status', '!=', 'cancelled')
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('start_date', [$startDate, $endDate])
                  ->orWhereBetween('end_date', [$startDate, $endDate])
                  ->orWhere(function ($q2) use ($startDate, $endDate) {
                      $q2->where('start_date', '<=', $startDate)
                          ->where('end_date', '>=', $endDate);
                  });
            });

        if ($location) {
            $query->where('location', $location);
        }

        if ($excludeReservationId) {
            $query->where('id', '!=', $excludeReservationId);
        }

        return $query->count() === 0;
    }

    /**
     * Obtener reservas conflictivas
     */
    public function getConflictingReservations(Reservation $reservation): Collection
    {
        return Reservation::where('id', '!=', $reservation->id)
            ->where('status', '!=', 'cancelled')
            ->where('location', $reservation->location)
            ->where(function ($query) use ($reservation) {
                $query->whereBetween('start_date', [$reservation->start_date, $reservation->end_date])
                      ->orWhereBetween('end_date', [$reservation->start_date, $reservation->end_date])
                      ->orWhere(function ($q) use ($reservation) {
                          $q->where('start_date', '<=', $reservation->start_date)
                              ->where('end_date', '>=', $reservation->end_date);
                      });
            })
            ->get();
    }

    /**
     * Obtener reservas por usuario y período
     */
    public function getUserReservations(User $user, Carbon $startDate = null, Carbon $endDate = null): Collection
    {
        $query = $user->reservations()->active();

        if ($startDate && $endDate) {
            $query->byDate($startDate, $endDate);
        } elseif ($startDate) {
            $query->byDate($startDate);
        }

        return $query->orderBy('start_date')->get();
    }

    /**
     * Obtener estadísticas de reservas
     */
    public function getReservationStats(User $user): array
    {
        $total = $user->reservations()->count();
        $confirmed = $user->reservations()->byStatus('confirmed')->count();
        $pending = $user->reservations()->byStatus('pending')->count();
        $cancelled = $user->reservations()->byStatus('cancelled')->count();
        $completed = $user->reservations()->byStatus('completed')->count();

        return [
            'total' => $total,
            'confirmed' => $confirmed,
            'pending' => $pending,
            'cancelled' => $cancelled,
            'completed' => $completed,
            'confirmation_rate' => $total > 0 ? round(($confirmed / $total) * 100, 2) : 0
        ];
    }

    /**
     * Buscar reservas por texto
     */
    public function searchReservations(User $user, string $searchTerm): Collection
    {
        return $user->reservations()
            ->where(function ($query) use ($searchTerm) {
                $query->where('title', 'like', "%{$searchTerm}%")
                      ->orWhere('description', 'like', "%{$searchTerm}%")
                      ->orWhere('location', 'like', "%{$searchTerm}%");
            })
            ->orderBy('start_date')
            ->get();
    }
}
