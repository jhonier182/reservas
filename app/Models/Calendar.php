<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Calendar extends Model
{
    protected $fillable = [
        'name',
        'google_calendar_id',
        'user_id',
        'is_primary',
        'color',
        'timezone',
        'is_active',
        'settings'
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'is_active' => 'boolean',
        'settings' => 'array'
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
        return $query->where('is_active', true);
    }

    public function scopePrimary(Builder $query): Builder
    {
        return $query->where('is_primary', true);
    }

    public function scopeByUser(Builder $query, $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    // Accessors
    public function getDisplayNameAttribute(): string
    {
        return $this->is_primary ? $this->name . ' (Principal)' : $this->name;
    }

    public function getFormattedColorAttribute(): string
    {
        return '#' . ltrim($this->color, '#');
    }

    // Métodos
    public function makePrimary(): void
    {
        // Desactivar otros calendarios principales del usuario
        static::where('user_id', $this->user_id)
            ->where('id', '!=', $this->id)
            ->update(['is_primary' => false]);
        
        $this->update(['is_primary' => true]);
    }

    public function syncWithGoogle(): bool
    {
        // Aquí se implementará la lógica de sincronización con Google Calendar
        return true;
    }

    public function getSettings(string $key = null, $default = null)
    {
        if ($key === null) {
            return $this->settings ?? [];
        }
        
        return data_get($this->settings, $key, $default);
    }

    public function setSettings(array $settings): void
    {
        $this->update(['settings' => array_merge($this->settings ?? [], $settings)]);
    }
}
