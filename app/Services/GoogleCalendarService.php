<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Reservation;
use App\Models\User;
use App\Models\Calendar;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use Google_Client;
use Google_Service_Calendar;
use Illuminate\Support\Facades\Log;

class GoogleCalendarService
{
    /**
     * Obtener cliente de Google configurado
     */
    private function getGoogleClient(User $user): Google_Client
    {
        try {
            Log::info('Iniciando configuración del cliente Google para usuario: ' . $user->email);
            
            $client = new Google_Client();
            Log::info('Cliente Google creado exitosamente');
            
            // Configuración para OAuth 2.0 normal
            $client->setClientId(config('services.google.client_id'));
            $client->setClientSecret(config('services.google.client_secret'));
            $client->setRedirectUri(config('services.google.redirect'));
            
            Log::info('Credenciales OAuth configuradas');
            
            $client->setScopes([
                Google_Service_Calendar::CALENDAR,
                Google_Service_Calendar::CALENDAR_EVENTS,
                'https://www.googleapis.com/auth/calendar.readonly',
                'https://www.googleapis.com/auth/calendar.events'
            ]);
            Log::info('Scopes configurados: ' . implode(', ', $client->getScopes()));
            
            // Verificar si el usuario tiene token de acceso
            if ($user->google_access_token) {
                $client->setAccessToken($user->google_access_token);
                Log::info('Token de acceso configurado para usuario: ' . $user->email);
                
                // Refrescar token si es necesario
                if ($client->isAccessTokenExpired()) {
                    Log::info('Token expirado, refrescando...');
                    if ($user->google_refresh_token) {
                        $client->refreshToken($user->google_refresh_token);
                        $newToken = $client->getAccessToken();
                        
                        // Actualizar tokens en la base de datos
                        $user->update([
                            'google_access_token' => $newToken['access_token'],
                            'google_refresh_token' => $newToken['refresh_token'] ?? $user->google_refresh_token,
                        ]);
                        
                        Log::info('Token refrescado exitosamente');
                    } else {
                        throw new \Exception('Token expirado y no hay refresh token disponible');
                    }
                }
            } else {
                throw new \Exception('Usuario no tiene token de acceso de Google. Debe autenticarse primero.');
            }
    
            Log::info('Cliente Google configurado exitosamente');
            return $client;
            
        } catch (\Exception $e) {
            Log::error('Error configurando cliente Google: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            throw $e;
        }
    }

    /**
     * Crear datos de conferencia para reuniones
     */
    private function createConferenceData(Reservation $reservation): ?array
    {
        // Solo crear conferencia para reuniones y citas
        if (!in_array($reservation->type, ['meeting', 'appointment'])) {
            return null;
        }

        return [
            'createRequest' => [
                'requestId' => 'conf_' . $reservation->id . '_' . time(),
                'conferenceSolutionKey' => [
                    'type' => 'hangoutsMeet'
                ]
            ]
        ];
    }

    /**
     * Obtener ID de color según el tipo de evento
     */
    private function getEventColorId(string $type): string
    {
        return match($type) {
            'meeting' => '1',      // Azul
            'event' => '2',        // Verde
            'appointment' => '3',  // Amarillo
            'other' => '4',        // Rojo
            default => '1'
        };
    }

    /**
     * Crear evento recurrente
     */
    public function createRecurringEvent(Reservation $reservation, array $recurrenceRules): array
    {
        try {
            $user = $reservation->user;
            $client = $this->getGoogleClient($user);
            $service = new Google_Service_Calendar($client);

            $event = new \Google_Service_Calendar_Event([
                'summary' => $reservation->title,
                'description' => $reservation->description,
                'location' => $reservation->location,
                'start' => [
                    'dateTime' => Carbon::parse($reservation->start_date)->toRfc3339String(),
                    'timeZone' => config('app.timezone'),
                ],
                'end' => [
                    'dateTime' => Carbon::parse($reservation->end_date)->toRfc3339String(),
                    'timeZone' => config('app.timezone'),
                ],
                'recurrence' => $recurrenceRules,
                'reminders' => [
                    'useDefault' => false,
                    'overrides' => [
                        ['method' => 'email', 'minutes' => 24 * 60],
                        ['method' => 'popup', 'minutes' => 30],
                    ],
                ],
                'conferenceData' => $this->createConferenceData($reservation),
                'extendedProperties' => [
                    'private' => [
                        'reservation_id' => $reservation->id,
                        'type' => $reservation->type,
                        'is_recurring' => true,
                    ]
                ],
                'colorId' => $this->getEventColorId($reservation->type),
            ]);

            $createdEvent = $service->events->insert('primary', $event, [
                'conferenceDataVersion' => 1,
                'supportsAttachments' => true
            ]);
            
            // Actualizar la reserva
            $reservation->update([
                'google_event_id' => $createdEvent->getId(),
                'metadata' => json_encode([
                    'google_calendar_id' => 'primary',
                    'google_event_id' => $createdEvent->getId(),
                    'is_recurring' => true,
                    'recurrence_rules' => $recurrenceRules,
                    'last_synced' => now()->toISOString(),
                ])
            ]);

            return [
                'success' => true,
                'google_event_id' => $createdEvent->getId(),
                'html_link' => $createdEvent->getHtmlLink(),
                'conference_link' => $createdEvent->getConferenceData()?->getEntryPoints()[0]?->getUri() ?? null,
            ];

        } catch (\Exception $e) {
            Log::error('Error creando evento recurrente: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Agregar participantes a un evento
     */
    public function addAttendees(string $googleEventId, array $attendees): array
    {
        try {
            $user = Auth::user();
            $client = $this->getGoogleClient($user);
            $service = new Google_Service_Calendar($client);

            $event = $service->events->get('primary', $googleEventId);
            
            $eventAttendees = [];
            foreach ($attendees as $attendee) {
                $eventAttendees[] = new \Google_Service_Calendar_EventAttendee([
                    'email' => $attendee['email'],
                    'displayName' => $attendee['name'] ?? null,
                    'responseStatus' => 'needsAction'
                ]);
            }
            
            $event->setAttendees($eventAttendees);
            
            $updatedEvent = $service->events->update('primary', $googleEventId, $event, [
                'sendUpdates' => 'all'
            ]);

            return [
                'success' => true,
                'attendees_count' => count($attendees),
                'html_link' => $updatedEvent->getHtmlLink(),
            ];

        } catch (\Exception $e) {
            Log::error('Error agregando participantes: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Agregar archivo adjunto de Google Drive
     */
    public function addDriveAttachment(string $googleEventId, string $driveFileId): array
    {
        try {
            $user = Auth::user();
            $client = $this->getGoogleClient($user);
            $service = new Google_Service_Calendar($client);

            $event = $service->events->get('primary', $googleEventId);
            
            // Obtener información del archivo de Drive
            $driveService = new \Google_Service_Drive($client);
            $file = $driveService->files->get($driveFileId);
            
            $attachment = new \Google_Service_Calendar_EventAttachment([
                'fileUrl' => $file->getAlternateLink(),
                'title' => $file->getName(),
                'mimeType' => $file->getMimeType(),
            ]);
            
            $attachments = $event->getAttachments() ?? [];
            $attachments[] = $attachment;
            $event->setAttachments($attachments);
            
            $updatedEvent = $service->events->update('primary', $googleEventId, $event, [
                'supportsAttachments' => true
            ]);

            return [
                'success' => true,
                'attachment_title' => $file->getName(),
                'attachment_url' => $file->getAlternateLink(),
            ];

        } catch (\Exception $e) {
            Log::error('Error agregando archivo adjunto: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Crear evento en Google Calendar desde una reserva
     */
    public function createEvent(Reservation $reservation): array
    {
        try {
            $user = $reservation->user;
            $client = $this->getGoogleClient($user);
            $service = new Google_Service_Calendar($client);

            // Crear evento con funcionalidades avanzadas
            $event = new \Google_Service_Calendar_Event([
                'summary' => $reservation->title,
                'description' => $reservation->description,
                'location' => $reservation->location,
                'start' => [
                    'dateTime' => Carbon::parse($reservation->start_date)->toRfc3339String(),
                    'timeZone' => config('app.timezone'),
                ],
                'end' => [
                    'dateTime' => Carbon::parse($reservation->end_date)->toRfc3339String(),
                    'timeZone' => config('app.timezone'),
                ],
                // Recordatorios inteligentes
                'reminders' => [
                    'useDefault' => false,
                    'overrides' => [
                        ['method' => 'email', 'minutes' => 24 * 60], // 24 horas antes
                        ['method' => 'popup', 'minutes' => 30],      // 30 minutos antes
                        ['method' => 'popup', 'minutes' => 60],      // 1 hora antes
                    ],
                ],
                // Configuración de conferencia automática para reuniones
                'conferenceData' => $this->createConferenceData($reservation),
                // Metadatos personalizados
                'extendedProperties' => [
                    'private' => [
                        'reservation_id' => $reservation->id,
                        'type' => $reservation->type,
                        'status' => $reservation->status,
                        'created_by' => $reservation->user->name,
                    ]
                ],
                // Colores según el tipo de evento
                'colorId' => $this->getEventColorId($reservation->type),
            ]);

            $createdEvent = $service->events->insert('primary', $event, [
                'conferenceDataVersion' => 1,
                'supportsAttachments' => true
            ]);
            
            // Actualizar la reserva con el ID de Google
            $reservation->update([
                'google_event_id' => $createdEvent->getId(),
                'metadata' => json_encode([
                    'google_calendar_id' => 'primary',
                    'google_event_id' => $createdEvent->getId(),
                    'last_synced' => now()->toISOString(),
                ])
            ]);

            return [
                'success' => true,
                'google_event_id' => $createdEvent->getId(),
                'html_link' => $createdEvent->getHtmlLink(),
            ];

        } catch (\Exception $e) {
            Log::error('Error creando evento en Google Calendar: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Actualizar evento en Google Calendar
     */
    public function updateEvent(Reservation $reservation): array
    {
        try {
            if (!$reservation->google_event_id) {
                return $this->createEvent($reservation);
            }

            $user = $reservation->user;
            $client = $this->getGoogleClient($user);
            $service = new Google_Service_Calendar($client);

            $event = $service->events->get('primary', $reservation->google_event_id);
            
            $event->setSummary($reservation->title);
            $event->setDescription($reservation->description);
            $event->setLocation($reservation->location);
            $event->setStart([
                'dateTime' => Carbon::parse($reservation->start_date)->toRfc3339String(),
                'timeZone' => config('app.timezone'),
            ]);
            $event->setEnd([
                'dateTime' => Carbon::parse($reservation->end_date)->toRfc3339String(),
                'timeZone' => config('app.timezone'),
            ]);

            $updatedEvent = $service->events->update('primary', $reservation->google_event_id, $event);
            
            // Actualizar metadata
            $reservation->update([
                'metadata' => json_encode([
                    'google_calendar_id' => 'primary',
                    'google_event_id' => $updatedEvent->getId(),
                    'last_synced' => now()->toISOString(),
                ])
            ]);

            return [
                'success' => true,
                'google_event_id' => $updatedEvent->getId(),
                'html_link' => $updatedEvent->getHtmlLink(),
            ];

        } catch (\Exception $e) {
            Log::error('Error actualizando evento en Google Calendar: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Eliminar evento de Google Calendar
     */
    public function deleteEvent(Reservation $reservation): array
    {
        try {
            if (!$reservation->google_event_id) {
                return ['success' => true, 'message' => 'Evento no sincronizado con Google'];
            }

            $user = $reservation->user;
            $client = $this->getGoogleClient($user);
            $service = new Google_Service_Calendar($client);

            $service->events->delete('primary', $reservation->google_event_id);
            
            // Limpiar metadata de Google
            $reservation->update([
                'google_event_id' => null,
                'metadata' => json_encode([
                    'deleted_from_google' => now()->toISOString(),
                ])
            ]);

            return ['success' => true, 'message' => 'Evento eliminado de Google Calendar'];

        } catch (\Exception $e) {
            Log::error('Error eliminando evento de Google Calendar: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Obtener eventos del calendario de Google
     */
    public function getEvents($user, $calendarId = 'primary', $timeMin = null, $timeMax = null)
    {
        try {
            // Verificar que el archivo de credenciales existe
            $credentialsPath = storage_path('app/google-credentials.json');
            if (!file_exists($credentialsPath)) {
                throw new \Exception('Archivo de credenciales de Google no encontrado en: ' . $credentialsPath);
            }

            $client = $this->getGoogleClient($user);
            $service = new Google_Service_Calendar($client);

            $optParams = [
                'maxResults' => 2500, // Aumentar el límite para obtener más eventos
                'orderBy' => 'startTime',
                'singleEvents' => true,
                'showDeleted' => false,
            ];

            if ($timeMin) {
                $optParams['timeMin'] = $timeMin->toRfc3339String();
                Log::info('Buscando eventos desde: ' . $timeMin->format('Y-m-d H:i:s'));
            }
            if ($timeMax) {
                $optParams['timeMax'] = $timeMax->toRfc3339String();
                Log::info('Buscando eventos hasta: ' . $timeMax->format('Y-m-d H:i:s'));
            }

            Log::info('Obteniendo eventos de Google Calendar con parámetros: ' . json_encode($optParams));
            
            $results = $service->events->listEvents($calendarId, $optParams);
            $events = $results->getItems();
            
            Log::info('Total de eventos encontrados en Google Calendar: ' . count($events));

            $processedEvents = collect($events)->map(function ($event) use ($calendarId) {
                $start = $event->start->dateTime ? 
                    Carbon::parse($event->start->dateTime) : 
                    Carbon::parse($event->start->date);
                
                $end = $event->end->dateTime ? 
                    Carbon::parse($event->end->dateTime) : 
                    Carbon::parse($event->end->date);

                $eventData = [
                    'id' => $event->id,
                    'title' => $event->summary ?? 'Sin título',
                    'start' => $start,
                    'end' => $end,
                    'description' => $event->description ?? null,
                    'location' => $event->location ?? null,
                    'google_event_id' => $event->id,
                    'calendar_id' => $calendarId,
                    'all_day' => !$event->start->dateTime,
                    'attendees' => $event->attendees ?? [],
                    'organizer' => $event->organizer ?? null,
                ];

                Log::info('Procesando evento: ' . $eventData['title'] . ' (' . $eventData['start']->format('Y-m-d H:i:s') . ')');
                
                return $eventData;
            });

            Log::info('Eventos procesados exitosamente: ' . $processedEvents->count());
            return $processedEvents;

        } catch (\Exception $e) {
            Log::error('Error obteniendo eventos de Google Calendar: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return collect([]);
        }
    }

    /**
     * Sincronizar eventos con Google Calendar
     */
    public function syncEvents(User $user): array
    {
        $results = [
            'created' => 0,
            'updated' => 0,
            'deleted' => 0,
            'errors' => []
        ];

        try {
            Log::info('Iniciando sincronización para usuario: ' . $user->email);
            
            // Obtener eventos de Google Calendar (últimos 6 meses y próximos 6 meses)
            $googleEvents = $this->getEvents($user, 'primary', now()->subMonths(6), now()->addMonths(6));
            Log::info('Eventos encontrados en Google Calendar: ' . $googleEvents->count());
            
            // Obtener reservas locales del usuario
            $localReservations = $user->reservations()
                ->whereBetween('start_date', [now()->subMonths(6), now()->addMonths(6)])
                ->get();
            Log::info('Reservas locales encontradas: ' . $localReservations->count());

            // Crear eventos de Google que no existen localmente
            foreach ($googleEvents as $googleEvent) {
                $existingReservation = $localReservations->first(function ($reservation) use ($googleEvent) {
                    return $reservation->google_event_id === $googleEvent['google_event_id'];
                });

                if (!$existingReservation) {
                    // Determinar el tipo de evento basado en el título o descripción
                    $eventType = $this->determineEventType($googleEvent['title'], $googleEvent['description']);
                    
                    // Crear nueva reserva desde Google
                    $newReservation = $user->reservations()->create([
                        'title' => $googleEvent['title'],
                        'description' => $googleEvent['description'],
                        'start_date' => $googleEvent['start'],
                        'end_date' => $googleEvent['end'],
                        'location' => $googleEvent['location'],
                        'type' => $eventType,
                        'status' => 'confirmed',
                        'google_event_id' => $googleEvent['google_event_id'],
                        'metadata' => json_encode([
                            'synced_from_google' => now()->toISOString(),
                            'google_calendar_id' => $googleEvent['calendar_id'],
                            'original_google_event' => $googleEvent,
                        ])
                    ]);
                    
                    Log::info('Nueva reserva creada desde Google: ' . $newReservation->title);
                    $results['created']++;
                }
            }

            // Sincronizar reservas locales con Google
            foreach ($localReservations as $reservation) {
                if (!$reservation->google_event_id) {
                    // Crear en Google si no existe
                    $createResult = $this->createEvent($reservation);
                    if ($createResult['success']) {
                        Log::info('Evento creado en Google Calendar: ' . $reservation->title);
                        $results['updated']++;
                    } else {
                        Log::error('Error creando evento en Google: ' . $createResult['error']);
                        $results['errors'][] = "Error creando evento '{$reservation->title}': " . $createResult['error'];
                    }
                }
            }

            Log::info('Sincronización completada. Resultados: ' . json_encode($results));

        } catch (\Exception $e) {
            Log::error('Error en sincronización: ' . $e->getMessage());
            $results['errors'][] = $e->getMessage();
        }

        return $results;
    }

    /**
     * Sincronización completa forzada (ignora eventos existentes)
     */
    public function forceFullSync(User $user): array
    {
        $results = [
            'created' => 0,
            'updated' => 0,
            'deleted' => 0,
            'errors' => []
        ];

        try {
            Log::info('Iniciando sincronización completa forzada para usuario: ' . $user->email);
            
            // Obtener TODOS los eventos de Google Calendar (sin límite de tiempo)
            $googleEvents = $this->getEvents($user, 'primary');
            Log::info('Total de eventos en Google Calendar: ' . $googleEvents->count());
            
            // Obtener todas las reservas locales del usuario
            $localReservations = $user->reservations()->get();
            Log::info('Total de reservas locales: ' . $localReservations->count());

            // Crear eventos de Google que no existen localmente
            foreach ($googleEvents as $googleEvent) {
                $existingReservation = $localReservations->first(function ($reservation) use ($googleEvent) {
                    return $reservation->google_event_id === $googleEvent['google_event_id'];
                });

                if (!$existingReservation) {
                    // Determinar el tipo de evento
                    $eventType = $this->determineEventType($googleEvent['title'], $googleEvent['description']);
                    
                    // Crear nueva reserva desde Google
                    $newReservation = $user->reservations()->create([
                        'title' => $googleEvent['title'],
                        'description' => $googleEvent['description'],
                        'start_date' => $googleEvent['start'],
                        'end_date' => $googleEvent['end'],
                        'location' => $googleEvent['location'],
                        'type' => $eventType,
                        'status' => 'confirmed',
                        'google_event_id' => $googleEvent['google_event_id'],
                        'metadata' => json_encode([
                            'synced_from_google' => now()->toISOString(),
                            'google_calendar_id' => $googleEvent['calendar_id'],
                            'original_google_event' => $googleEvent,
                            'sync_type' => 'force_full_sync',
                        ])
                    ]);
                    
                    Log::info('Nueva reserva creada desde Google (sync completo): ' . $newReservation->title);
                    $results['created']++;
                }
            }

            // Sincronizar reservas locales con Google
            foreach ($localReservations as $reservation) {
                if (!$reservation->google_event_id) {
                    // Crear en Google si no existe
                    $createResult = $this->createEvent($reservation);
                    if ($createResult['success']) {
                        Log::info('Evento creado en Google Calendar (sync completo): ' . $reservation->title);
                        $results['updated']++;
                    } else {
                        Log::error('Error creando evento en Google (sync completo): ' . $createResult['error']);
                        $results['errors'][] = "Error creando evento '{$reservation->title}': " . $createResult['error'];
                    }
                }
            }

            Log::info('Sincronización completa forzada finalizada. Resultados: ' . json_encode($results));

        } catch (\Exception $e) {
            Log::error('Error en sincronización completa forzada: ' . $e->getMessage());
            $results['errors'][] = $e->getMessage();
        }

        return $results;
    }

    /**
     * Determinar el tipo de evento basado en el título y descripción
     */
    private function determineEventType(?string $title, ?string $description): string
    {
        $text = strtolower(($title ?? '') . ' ' . ($description ?? ''));
        
        if (str_contains($text, 'reunión') || str_contains($text, 'meeting') || str_contains($text, 'team')) {
            return 'meeting';
        } elseif (str_contains($text, 'cita') || str_contains($text, 'appointment') || str_contains($text, 'doctor')) {
            return 'appointment';
        } elseif (str_contains($text, 'evento') || str_contains($text, 'event') || str_contains($text, 'conferencia')) {
            return 'event';
        } else {
            return 'other';
        }
    }

    /**
     * Obtener calendarios disponibles del usuario
     */
    public function getCalendars(User $user): Collection
    {
        try {
            if (!$user->google_access_token) {
                return collect([]);
            }

            $client = $this->getGoogleClient($user);
            $service = new Google_Service_Calendar($client);

            $calendarList = $service->calendarList->listCalendarList();
            $calendars = $calendarList->getItems();

            return collect($calendars)->map(function ($calendar) {
                return [
                    'id' => $calendar->getId(),
                    'summary' => $calendar->getSummary(),
                    'description' => $calendar->getDescription(),
                    'location' => $calendar->getLocation(),
                    'timezone' => $calendar->getTimeZone(),
                    'primary' => $calendar->getPrimary() ?? false,
                    'access_role' => $calendar->getAccessRole(),
                    'selected' => $calendar->getSelected() ?? true,
                ];
            });

        } catch (\Exception $e) {
            Log::error('Error obteniendo calendarios de Google: ' . $e->getMessage());
            return collect([]);
        }
    }

    /**
     * Verificar permisos de Google Calendar
     */
    public function checkCalendarPermissions(User $user): bool
    {
        try {
            $client = $this->getGoogleClient($user);
            $service = new Google_Service_Calendar($client);
            
            // Intentar obtener el calendario principal
            $service->calendars->get('primary');
            Log::info('Permisos verificados correctamente para usuario: ' . $user->email);
            return true;

        } catch (\Exception $e) {
            Log::error('Error verificando permisos para usuario ' . $user->email . ': ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener estadísticas de sincronización
     */
    public function getSyncStats(User $user): array
    {
        $totalReservations = $user->reservations()->count();
        $syncedReservations = $user->reservations()->whereNotNull('google_event_id')->count();
        $lastSync = $user->reservations()
            ->whereNotNull('google_event_id')
            ->orderBy('updated_at', 'desc')
            ->first();

        return [
            'total_events' => $totalReservations,
            'synced_events' => $syncedReservations,
            'sync_rate' => $totalReservations > 0 ? round(($syncedReservations / $totalReservations) * 100, 2) : 0,
            'last_sync' => $lastSync ? $lastSync->updated_at : null,
            'has_google_access' => file_exists(storage_path('app/google-credentials.json')),
        ];
    }
}
