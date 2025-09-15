<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class Reservation extends Model
{
    protected $fillable = [
        'title',
        'description',
        'start_date',
        'end_date',
        'location',
        'user_id',
        'status',
        'type',
        'metadata',
        'people_count',
        'responsible_name',
        'squad',
        'google_event_id'
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'metadata' => 'array',
        'people_count' => 'integer'
    ];

    // Relaciones
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    // Scopes
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', '!=', 'cancelled');
    }

    public function scopeByDate(Builder $query, $startDate, $endDate = null): Builder
    {
        if ($endDate) {
            return $query->whereBetween('start_date', [$startDate, $endDate]);
        }
        return $query->whereDate('start_date', $startDate);
    }

    public function scopeByStatus(Builder $query, $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeByType(Builder $query, $type): Builder
    {
        return $query->where('type', $type);
    }

    // Accessors
    public function getFormattedDateAttribute(): string
    {
        return $this->start_date->format('d/m/Y H:i');
    }

    public function getDurationAttribute(): string
    {
        $duration = $this->start_date->diffInMinutes($this->end_date);
        $hours = intval($duration / 60);
        $minutes = $duration % 60;
        
        if ($hours > 0) {
            return $hours . 'h ' . $minutes . 'm';
        }
        return $minutes . 'm';
    }

    public function getIsUpcomingAttribute(): bool
    {
        return $this->start_date->isFuture();
    }

    public function getIsPastAttribute(): bool
    {
        return $this->end_date->isPast();
    }

    public function getUsuarioEmailAttribute(): string
    {
        try {
            return $this->user->email ?? '';
        } catch (\Exception $e) {
            \Log::warning("Error accediendo a usuario_email para reserva {$this->id}: " . $e->getMessage());
            return '';
        }
    }

    // Métodos
    public function isConflicting(Reservation $other): bool
    {
        return $this->start_date < $other->end_date && $this->end_date > $other->start_date;
    }

    /**
     * Verificar si hay conflicto de ubicación en la misma fecha/hora
     */
    public function hasLocationConflict(): bool
    {
        if (!$this->location) {
            return false;
        }

        return static::where('id', '!=', $this->id)
            ->where('location', $this->location)
            ->where('status', '!=', 'cancelled')
            ->where(function ($query) {
                $query->whereBetween('start_date', [$this->start_date, $this->end_date])
                    ->orWhereBetween('end_date', [$this->start_date, $this->end_date])
                    ->orWhere(function ($q) {
                        $q->where('start_date', '<=', $this->start_date)
                            ->where('end_date', '>=', $this->end_date);
                    });
            })
            ->exists();
    }

    /**
     * Verificar si una ubicación está disponible en un rango de fechas
     */
    public static function isLocationAvailable(string $location, $startDate, $endDate, $excludeId = null): bool
    {
        $query = static::where('location', $location)
            ->where('status', '!=', 'cancelled')
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate])
                    ->orWhere(function ($subQ) use ($startDate, $endDate) {
                        $subQ->where('start_date', '<=', $startDate)
                            ->where('end_date', '>=', $endDate);
                    });
            });

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return !$query->exists();
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['pending', 'confirmed']) && $this->start_date->isFuture();
    }

    public function markAsCompleted(): void
    {
        $this->update(['status' => 'completed']);
    }
}
