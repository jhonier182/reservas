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
        'metadata'
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'metadata' => 'array'
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

    // Métodos
    public function isConflicting(Reservation $other): bool
    {
        return $this->start_date < $other->end_date && $this->end_date > $other->start_date;
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
