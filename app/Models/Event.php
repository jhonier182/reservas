<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class Event extends Model
{
    protected $fillable = [
        'google_event_id',
        'title',
        'description',
        'start_datetime',
        'end_datetime',
        'location',
        'reservation_id',
        'status',
        'is_recurring',
        'recurrence_rules',
        'google_metadata'
    ];

    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
        'is_recurring' => 'boolean',
        'recurrence_rules' => 'array',
        'google_metadata' => 'array'
    ];

    // Relaciones
    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    // Scopes
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeByDate(Builder $query, $startDate, $endDate = null): Builder
    {
        if ($endDate) {
            return $query->whereBetween('start_datetime', [$startDate, $endDate]);
        }
        return $query->whereDate('start_datetime', $startDate);
    }

    public function scopeRecurring(Builder $query): Builder
    {
        return $query->where('is_recurring', true);
    }

    // Accessors
    public function getFormattedStartTimeAttribute(): string
    {
        return $this->start_datetime->format('H:i');
    }

    public function getFormattedEndTimeAttribute(): string
    {
        return $this->end_datetime->format('H:i');
    }

    public function getDurationAttribute(): string
    {
        $duration = $this->start_datetime->diffInMinutes($this->end_datetime);
        $hours = intval($duration / 60);
        $minutes = $duration % 60;
        
        if ($hours > 0) {
            return $hours . 'h ' . $minutes . 'm';
        }
        return $minutes . 'm';
    }

    // Métodos
    public function syncWithGoogle(): bool
    {
        // Aquí se implementará la lógica de sincronización con Google Calendar
        return true;
    }

    public function isRecurring(): bool
    {
        return $this->is_recurring;
    }

    public function getNextOccurrence(): ?Carbon
    {
        if (!$this->is_recurring) {
            return null;
        }
        
        // Lógica para calcular la próxima ocurrencia basada en recurrence_rules
        return $this->start_datetime;
    }

    public function updateFromGoogle(array $googleData): void
    {
        $this->update([
            'title' => $googleData['title'] ?? $this->title,
            'description' => $googleData['description'] ?? $this->description,
            'start_datetime' => $googleData['start_datetime'] ?? $this->start_datetime,
            'end_datetime' => $googleData['end_datetime'] ?? $this->end_datetime,
            'location' => $googleData['location'] ?? $this->location,
            'google_metadata' => $googleData['metadata'] ?? $this->google_metadata
        ]);
    }
}
