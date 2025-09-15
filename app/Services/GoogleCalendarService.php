<?php

namespace App\Services;

use App\Models\User;
use Google\Client;
use Google\Service\Calendar;
use Google\Service\Calendar\Event;
use Google\Service\Calendar\EventDateTime;
use Google\Service\Calendar\EventAttendee;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class GoogleCalendarService
{
    private array $scopes = [];
    private ?string $redirectUri = null;

    public function __construct()
    {
        $this->scopes = config('google.scopes', [
            Calendar::CALENDAR,
            Calendar::CALENDAR_EVENTS,
            Calendar::CALENDAR_READONLY,
        ]);
        $this->redirectUri = config('google.redirect_uri', env('GOOGLE_REDIRECT_URI'));
    }

    /**
     * Configurar Google Client para un usuario usando tokens guardados en DB
     */
    public function getClientForUser(string $email): Client
    {
        $user = User::where('email', $email)->firstOrFail();

        $client = new Client();
        $client->setClientId(config('services.google.client_id'));
        $client->setClientSecret(config('services.google.client_secret'));
        $client->setRedirectUri($this->redirectUri);
        $client->setScopes($this->scopes);
        $client->setAccessType('offline');
        $client->setPrompt('consent');

        if (!$user->google_access_token) {
            throw new \Exception("El usuario {$email} no tiene tokens de Google. Debe autenticarse.");
        }

        $client->setAccessToken([
            'access_token'  => $user->google_access_token,
            'refresh_token' => $user->google_refresh_token,
        ]);

        // Refrescar token si expiró
        if ($client->isAccessTokenExpired()) {
            Log::info("Token expirado para {$email}, refrescando...");
            $client->fetchAccessTokenWithRefreshToken($user->google_refresh_token);
            $newToken = $client->getAccessToken();

            $user->update([
                'google_access_token' => $newToken['access_token'] ?? null,
                'google_refresh_token' => $newToken['refresh_token'] ?? $user->google_refresh_token,
            ]);
        }

        return $client;
    }

    private function buildEventDateTime(string|Carbon $when, string $tz, bool $allDay = false): EventDateTime
    {
        $edt = new EventDateTime();
        $dt  = $when instanceof Carbon ? $when->copy()->timezone($tz) : Carbon::parse($when, $tz);

        if ($allDay) {
            $edt->setDate($dt->toDateString());
        } else {
            $edt->setDateTime($dt->toRfc3339String());
            $edt->setTimeZone($tz);
        }
        return $edt;
    }

    /**
     * Crear evento en Google Calendar
     */
    public function createEvent(string $email, array $data): string
    {
        $client     = $this->getClientForUser($email);
        $service    = new Calendar($client);
        $calendarId = config('google.calendar_id', 'primary');
        $tz         = config('google.timezone', 'America/Bogota');
        $allDay     = !empty($data['allDay']);

        $event = new Event();
        $event->setSummary($data['summary'] ?? 'Reserva');
        if (isset($data['description'])) $event->setDescription($data['description']);
        if (isset($data['location']))    $event->setLocation($data['location']);

        $event->setStart($this->buildEventDateTime($data['start'], $tz, $allDay));
        $event->setEnd($this->buildEventDateTime($data['end'], $tz, $allDay));

        if (!empty($data['attendees'])) {
            $atts = array_map(function ($a) {
                $email = is_array($a) ? ($a['email'] ?? null) : $a;
                return $email ? (new EventAttendee())->setEmail($email) : null;
            }, $data['attendees']);
            $event->setAttendees(array_filter($atts));
        }

        $created = $service->events->insert($calendarId, $event);
        return $created->getId();
    }

    /**
     * Actualizar evento existente
     */
    public function updateEvent(string $email, string $eventId, array $data): string
    {
        $client     = $this->getClientForUser($email);
        $service    = new Calendar($client);
        $calendarId = config('google.calendar_id', 'primary');
        $tz         = config('google.timezone', 'America/Bogota');

        $event = $service->events->get($calendarId, $eventId);

        if (isset($data['summary']))     $event->setSummary($data['summary']);
        if (isset($data['description'])) $event->setDescription($data['description']);
        if (isset($data['location']))    $event->setLocation($data['location']);

        if (isset($data['start'])) {
            $event->setStart($this->buildEventDateTime($data['start'], $tz, !empty($data['allDay'])));
        }
        if (isset($data['end'])) {
            $event->setEnd($this->buildEventDateTime($data['end'], $tz, !empty($data['allDay'])));
        }

        if (!empty($data['attendees'])) {
            $atts = array_map(function ($a) {
                $email = is_array($a) ? ($a['email'] ?? null) : $a;
                return $email ? (new EventAttendee())->setEmail($email) : null;
            }, $data['attendees']);
            $event->setAttendees(array_filter($atts));
        }

        $updated = $service->events->update($calendarId, $eventId, $event);
        return $updated->getId();
    }

    /**
     * Eliminar evento
     */
    public function deleteEvent(string $email, string $eventId): void
    {
        $client     = $this->getClientForUser($email);
        $service    = new Calendar($client);
        $calendarId = config('google.calendar_id', 'primary');

        $service->events->delete($calendarId, $eventId);
    }

    /**
     * Obtener eventos entre fechas
     */
    public function getEvents(string $email, string $startDate, string $endDate): array
    {
        $client     = $this->getClientForUser($email);
        $service    = new Calendar($client);
        $calendarId = config('google.calendar_id', 'primary');

        $events = $service->events->listEvents($calendarId, [
            'timeMin' => Carbon::parse($startDate)->toRfc3339String(),
            'timeMax' => Carbon::parse($endDate)->toRfc3339String(),
            'singleEvents' => true,
            'orderBy' => 'startTime',
        ]);

        $results = [];
        foreach ($events->getItems() as $event) {
            $results[] = [
                'id'          => $event->getId(),
                'title'       => $event->getSummary(),
                'description' => $event->getDescription(),
                'location'    => $event->getLocation(),
                'start'       => $event->getStart()->getDateTime() ?? $event->getStart()->getDate(),
                'end'         => $event->getEnd()->getDateTime() ?? $event->getEnd()->getDate(),
                'attendees'   => $event->getAttendees(),
                'htmlLink'    => $event->getHtmlLink(),
                'extendedProps' => [
                    'type' => 'google_calendar'
                ]
            ];
        }

        return $results;
    }
}
