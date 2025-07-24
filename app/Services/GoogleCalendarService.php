<?php

namespace App\Services;

use Google\Client;
use Google\Service\Calendar;
use Google\Service\Calendar\Event;
use Google\Service\Calendar\EventDateTime;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class GoogleCalendarService
{
    private $client;
    private $calendar;
    private $calendarId;

    public function __construct()
    {
        $this->client = new Client();
        $this->client->setApplicationName(config('google.calendar.application_name'));
        
        // Configurar las credenciales según el método disponible
        if (config('google.oauth.client_id')) {
            // Usar OAuth 2.0
            $this->client->setClientId(config('google.oauth.client_id'));
            $this->client->setClientSecret(config('google.oauth.client_secret'));
            $this->client->setRedirectUri(config('google.oauth.redirect_uri'));
            $this->client->setScopes(config('google.oauth.scopes'));
        } else {
            // Usar Service Account (archivo de credenciales)
            $credentialsPath = config('google.calendar.credentials_path');
            if (file_exists($credentialsPath)) {
                $this->client->setAuthConfig($credentialsPath);
                $this->client->setScopes(config('google.oauth.scopes'));
            }
        }

        $this->calendar = new Calendar($this->client);
        $this->calendarId = config('google.calendar.calendar_id');
    }

    /**
     * Crear un evento de sesión de retroalimentación en Google Calendar
     */
    public function createFeedbackSession($data)
    {
        try {
            $event = new Event([
                'summary' => $data['title'],
                'description' => $data['description'],
                'location' => $data['location'] ?? '',
                'start' => new EventDateTime([
                    'dateTime' => $data['start_datetime']->format('c'),
                    'timeZone' => config('google.calendar.time_zone'),
                ]),
                'end' => new EventDateTime([
                    'dateTime' => $data['end_datetime']->format('c'),
                    'timeZone' => config('google.calendar.time_zone'),
                ]),
                'attendees' => $this->formatAttendees($data['attendees'] ?? []),
                'reminders' => [
                    'useDefault' => false,
                    'overrides' => [
                        ['method' => 'email', 'minutes' => 24 * 60], // 24 horas antes
                        ['method' => 'popup', 'minutes' => 30], // 30 minutos antes
                    ],
                ],
                'visibility' => 'private',
                'status' => 'confirmed'
            ]);

            $createdEvent = $this->calendar->events->insert($this->calendarId, $event);
            
            Log::info('Evento de retroalimentación creado en Google Calendar', [
                'event_id' => $createdEvent->getId(),
                'title' => $data['title'],
                'datetime' => $data['start_datetime']->format('Y-m-d H:i:s')
            ]);

            return [
                'success' => true,
                'event_id' => $createdEvent->getId(),
                'html_link' => $createdEvent->getHtmlLink(),
                'event' => $createdEvent
            ];
            
        } catch (\Exception $e) {
            Log::error('Error creando evento en Google Calendar', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Actualizar un evento existente
     */
    public function updateEvent($eventId, $data)
    {
        try {
            $event = $this->calendar->events->get($this->calendarId, $eventId);
            
            if (isset($data['title'])) {
                $event->setSummary($data['title']);
            }
            
            if (isset($data['description'])) {
                $event->setDescription($data['description']);
            }
            
            if (isset($data['start_datetime'])) {
                $event->setStart(new EventDateTime([
                    'dateTime' => $data['start_datetime']->format('c'),
                    'timeZone' => config('google.calendar.time_zone'),
                ]));
            }
            
            if (isset($data['end_datetime'])) {
                $event->setEnd(new EventDateTime([
                    'dateTime' => $data['end_datetime']->format('c'),
                    'timeZone' => config('google.calendar.time_zone'),
                ]));
            }

            $updatedEvent = $this->calendar->events->update($this->calendarId, $eventId, $event);
            
            return [
                'success' => true,
                'event' => $updatedEvent
            ];
            
        } catch (\Exception $e) {
            Log::error('Error actualizando evento en Google Calendar', [
                'event_id' => $eventId,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Eliminar un evento
     */
    public function deleteEvent($eventId)
    {
        try {
            $this->calendar->events->delete($this->calendarId, $eventId);
            
            return ['success' => true];
            
        } catch (\Exception $e) {
            Log::error('Error eliminando evento en Google Calendar', [
                'event_id' => $eventId,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Formatear asistentes para Google Calendar
     */
    private function formatAttendees($attendees)
    {
        $formattedAttendees = [];
        
        foreach ($attendees as $attendee) {
            $formattedAttendees[] = [
                'email' => $attendee['email'],
                'displayName' => $attendee['name'] ?? $attendee['email'],
                'responseStatus' => 'needsAction'
            ];
        }
        
        return $formattedAttendees;
    }

    /**
     * Verificar si el servicio está configurado correctamente
     */
    public function isConfigured()
    {
        try {
            // Verificar si tenemos las credenciales necesarias
            if (config('google.oauth.client_id')) {
                return !empty(config('google.oauth.client_id')) && 
                       !empty(config('google.oauth.client_secret'));
            } else {
                $credentialsPath = config('google.calendar.credentials_path');
                return file_exists($credentialsPath);
            }
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Obtener la URL de autorización OAuth (si se usa OAuth)
     */
    public function getAuthUrl()
    {
        if (!config('google.oauth.client_id')) {
            return null;
        }
        
        return $this->client->createAuthUrl();
    }

    /**
     * Configurar token de acceso
     */
    public function setAccessToken($token)
    {
        $this->client->setAccessToken($token);
    }
}
