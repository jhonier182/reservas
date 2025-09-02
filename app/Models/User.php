<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @mixin IdeHelperUser
 */
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'google_id',
        'avatar',
        'timezone',
        'preferences',
        'google_access_token',
        'google_refresh_token',
        'token_expires_at',
        'role'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'preferences' => 'array',
            'token_expires_at' => 'datetime'
        ];
    }

    // Relaciones

    public function isAdmin(): bool
{
    return $this->role === 'admin';
}

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function calendars()
    {
        return $this->hasMany(Calendar::class);
    }

    public function events()
    {
        return $this->hasMany(Event::class);
    }

    // Métodos
    public function hasGoogleAccount(): bool
    {
        return !empty($this->google_id);
    }

    public function isGoogleTokenExpired(): bool
    {
        return $this->token_expires_at && $this->token_expires_at->isPast();
    }

    public function getPrimaryCalendar()
    {
        return $this->calendars()->primary()->first();
    }

    public function getUpcomingReservations($limit = 10)
    {
        return $this->reservations()
            ->active()
            ->where('start_date', '>', now())
            ->orderBy('start_date')
            ->limit($limit)
            ->get();
    }
}
