<?php

namespace App\Services;

use App\Models\Reservation;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    /**
     * Enviar confirmación de reserva
     */
    public function sendReservationConfirmation(Reservation $reservation): void
    {
        try {
            $user = $reservation->user;
            
            // Aquí se implementará el envío de email
            // Por ahora solo logueamos la acción
            
            Log::info('Confirmación de reserva enviada', [
                'reservation_id' => $reservation->id,
                'user_id' => $user->id,
                'user_email' => $user->email
            ]);
            
            // Ejemplo de envío de email (descomentar cuando se configure el mail)
            /*
            Mail::to($user->email)->send(new ReservationConfirmation($reservation));
            */
            
        } catch (\Exception $e) {
            Log::error('Error enviando confirmación de reserva', [
                'reservation_id' => $reservation->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Enviar recordatorio de reserva
     */
    public function sendReminder(Reservation $reservation): void
    {
        try {
            $user = $reservation->user;
            
            // Verificar si es momento de enviar el recordatorio
            $hoursUntilReservation = now()->diffInHours($reservation->start_date, false);
            
            if ($hoursUntilReservation <= 24 && $hoursUntilReservation > 0) {
                Log::info('Recordatorio de reserva enviado', [
                    'reservation_id' => $reservation->id,
                    'user_id' => $user->id,
                    'hours_until' => $hoursUntilReservation
                ]);
                
                // Aquí se implementará el envío de email
                // Mail::to($user->email)->send(new ReservationReminder($reservation));
            }
            
        } catch (\Exception $e) {
            Log::error('Error enviando recordatorio de reserva', [
                'reservation_id' => $reservation->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Enviar notificación de cancelación
     */
    public function sendCancellationNotice(Reservation $reservation): void
    {
        try {
            $user = $reservation->user;
            
            Log::info('Notificación de cancelación enviada', [
                'reservation_id' => $reservation->id,
                'user_id' => $user->id,
                'user_email' => $user->email
            ]);
            
            // Aquí se implementará el envío de email
            // Mail::to($user->email)->send(new ReservationCancelled($reservation));
            
        } catch (\Exception $e) {
            Log::error('Error enviando notificación de cancelación', [
                'reservation_id' => $reservation->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Enviar notificación de cambio de reserva
     */
    public function sendReservationChanged(Reservation $reservation, array $changes): void
    {
        try {
            $user = $reservation->user;
            
            Log::info('Notificación de cambio de reserva enviada', [
                'reservation_id' => $reservation->id,
                'user_id' => $user->id,
                'changes' => $changes
            ]);
            
            // Aquí se implementará el envío de email
            // Mail::to($user->email)->send(new ReservationChanged($reservation, $changes));
            
        } catch (\Exception $e) {
            Log::error('Error enviando notificación de cambio de reserva', [
                'reservation_id' => $reservation->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Enviar notificación de conflicto de reserva
     */
    public function sendConflictNotification(Reservation $reservation, array $conflictingReservations): void
    {
        try {
            $user = $reservation->user;
            
            Log::warning('Notificación de conflicto de reserva enviada', [
                'reservation_id' => $reservation->id,
                'user_id' => $user->id,
                'conflicts_count' => count($conflictingReservations)
            ]);
            
            // Aquí se implementará el envío de email
            // Mail::to($user->email)->send(new ReservationConflict($reservation, $conflictingReservations));
            
        } catch (\Exception $e) {
            Log::error('Error enviando notificación de conflicto de reserva', [
                'reservation_id' => $reservation->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Enviar notificación de sincronización con Google Calendar
     */
    public function sendSyncNotification(User $user, array $syncResults): void
    {
        try {
            Log::info('Notificación de sincronización enviada', [
                'user_id' => $user->id,
                'sync_results' => $syncResults
            ]);
            
            // Solo enviar notificación si hay errores o resultados importantes
            if (!empty($syncResults['errors']) || $syncResults['created'] > 0 || $syncResults['updated'] > 0) {
                // Aquí se implementará el envío de email
                // Mail::to($user->email)->send(new SyncNotification($syncResults));
            }
            
        } catch (\Exception $e) {
            Log::error('Error enviando notificación de sincronización', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Enviar notificación de recordatorio diario
     */
    public function sendDailyDigest(User $user): void
    {
        try {
            $todayReservations = $user->reservations()
                ->whereDate('start_date', today())
                ->orderBy('start_date')
                ->get();
            
            if ($todayReservations->isNotEmpty()) {
                Log::info('Resumen diario enviado', [
                    'user_id' => $user->id,
                    'reservations_count' => $todayReservations->count()
                ]);
                
                // Aquí se implementará el envío de email
                // Mail::to($user->email)->send(new DailyDigest($todayReservations));
            }
            
        } catch (\Exception $e) {
            Log::error('Error enviando resumen diario', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Verificar si un usuario debe recibir notificaciones
     */
    public function shouldSendNotification(User $user, string $type): bool
    {
        $preferences = $user->preferences ?? [];
        $notificationSettings = $preferences['notifications'] ?? [];
        
        // Por defecto, enviar todas las notificaciones
        return $notificationSettings[$type] ?? true;
    }
}
