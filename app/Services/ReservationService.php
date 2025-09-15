<?php

namespace App\Services;

use App\Models\Reservation;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\GoogleCalendarService;

class ReservationService
{
    protected GoogleCalendarService $googleCalendarService;

    public function __construct(GoogleCalendarService $googleCalendarService)
    {
        $this->googleCalendarService = $googleCalendarService;
    }

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

            // Sincronizar con Google Calendar
            $this->syncToGoogleCalendar($reservation);

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

            // Sincronizar con Google Calendar
            if ($result) {
                $this->syncToGoogleCalendar($reservation, true);
            }

            return $result;
        });
    }

    /**
     * Eliminar una reserva
     */
    public function deleteReservation(Reservation $reservation): bool
    {
        return DB::transaction(function () use ($reservation) {
            // Eliminar de Google Calendar
            $this->removeFromGoogleCalendar($reservation);

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

    /**
     * Sincronizar reserva con Google Calendar
     */
    private function syncToGoogleCalendar(Reservation $reservation, bool $isUpdate = false): void
    {
        try {
            // Asegurar que la relación user esté cargada
            $reservation->load('user');
            $user = $reservation->user;

            if (!$user || !$user->email) {
                Log::warning("No se puede sincronizar reserva {$reservation->id}: usuario sin email");
                return;
            }

            // Verificar si el usuario tiene tokens de Google Calendar en DB
            if (empty($user->google_access_token)) {
                Log::info("Usuario {$user->email} no tiene token de Google Calendar configurado (access_token vacío)");
                return;
            }

            // Preparar datos del evento
            $eventData = [
                'summary' => $reservation->title,
                'description' => $this->buildEventDescription($reservation),
                'start' => Carbon::parse($reservation->start_date),
                'end' => Carbon::parse($reservation->end_date),
                'location' => $this->getLocationName($reservation->location),
                'attendees' => $reservation->usuario_email ? [['email' => $reservation->usuario_email]] : [],
            ];

            if ($isUpdate && $reservation->google_event_id) {
                // Actualizar evento existente
                $this->googleCalendarService->updateEvent($user->email, $reservation->google_event_id, $eventData);
                Log::info("Evento de Google Calendar actualizado para reserva {$reservation->id}");
            } else {
                // Crear nuevo evento
                $googleEventId = $this->googleCalendarService->createEvent($user->email, $eventData);

                // Guardar el ID del evento de Google en la reserva
                if ($googleEventId) {
                    $reservation->update(['google_event_id' => $googleEventId]);
                }

                Log::info("Evento de Google Calendar creado para reserva {$reservation->id}: {$googleEventId}");
            }

        } catch (\Exception $e) {
            Log::error("Error sincronizando reserva {$reservation->id} con Google Calendar: " . $e->getMessage());
            // No lanzar la excepción para no interrumpir el flujo principal
        }
    }

    /**
     * Eliminar evento de Google Calendar
     */
    private function removeFromGoogleCalendar(Reservation $reservation): void
    {
        try {
            if (!$reservation->google_event_id) {
                return;
            }

            $user = $reservation->user;
            if (!$user || !$user->email) {
                return;
            }

            $this->googleCalendarService->deleteEvent($user->email, $reservation->google_event_id);
            Log::info("Evento de Google Calendar eliminado para reserva {$reservation->id}");

        } catch (\Exception $e) {
            Log::error("Error eliminando evento de Google Calendar para reserva {$reservation->id}: " . $e->getMessage());
        }
    }

    /**
     * Construir descripción del evento
     */
    private function buildEventDescription(Reservation $reservation): string
    {
        $description = $reservation->description ?? '';

        $details = [];
        if ($reservation->responsible_name) {
            $details[] = "Responsable: {$reservation->responsible_name}";
        }
        if ($reservation->people_count) {
            $details[] = "Personas: {$reservation->people_count}";
        }
        if ($reservation->squad) {
            $details[] = "Equipo: {$reservation->squad}";
        }
        if ($reservation->type) {
            $details[] = "Tipo: " . ucfirst($reservation->type);
        }

        if (!empty($details)) {
            $description .= "\n\n" . implode("\n", $details);
        }

        return $description;
    }

    /**
     * Obtener nombre de la ubicación
     */
    private function getLocationName(string $location): string
    {
        return match($location) {
            'jardin' => 'Jardín',
            'casino' => 'Casino',
            default => ucfirst($location)
        };
    }
}
