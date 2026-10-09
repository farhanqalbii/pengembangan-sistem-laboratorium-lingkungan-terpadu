<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'company_name',
        'address',
        'avatar',
    ];

    public function getAvatarUrlAttribute(): ?string
    {
        if ($this->avatar && Storage::disk('public')->exists($this->avatar)) {
            return asset('storage/'.$this->avatar);
        }

        return null;
    }

    protected $appends = [
        'avatar_url',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isManager(): bool
    {
        return $this->role === 'manager';
    }

    public function isStaff(): bool
    {
        return $this->role === 'staff';
    }

    public function isCustomer(): bool
    {
        return $this->role === 'customer';
    }

    public function samples(): HasMany
    {
        return $this->hasMany(Sample::class, 'customer_id');
    }

    public function borrowings(): HasMany
    {
        return $this->hasMany(Peminjaman::class, 'customer_id');
    }

    public function peminjaman(): HasMany
    {
        return $this->hasMany(Peminjaman::class, 'customer_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Pembayaran::class, 'customer_id');
    }

    public function pembayaran(): HasMany
    {
        return $this->hasMany(Pembayaran::class, 'customer_id');
    }

    public function systemNotifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'user_id');
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(LogAktivitas::class, 'user_id');
    }
}
