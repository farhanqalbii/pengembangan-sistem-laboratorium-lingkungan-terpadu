<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Sample extends Model
{
    use HasFactory;

    protected $fillable = [
        'sample_code',
        'customer_id',
        'sample_type',
        'sampling_location',
        'sampling_date',
        'request_date',
        'received_date',
        'notes',
        'status',
        'rejection_reason',
    ];

    protected $casts = [
        'sampling_date' => 'datetime',
        'request_date' => 'datetime',
        'received_date' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function sampleParameters(): HasMany
    {
        return $this->hasMany(SampleParameter::class);
    }

    public function certificate(): HasOne
    {
        return $this->hasOne(Sertifikat::class);
    }

    public function sertifikat(): HasOne
    {
        return $this->hasOne(Sertifikat::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Pembayaran::class, 'sample_id');
    }

    public function pembayaran(): HasMany
    {
        return $this->hasMany(Pembayaran::class, 'sample_id');
    }

    public function calculateTotalPrice(): float
    {
        return $this->sampleParameters->sum(function ($sp) {
            return $sp->parameter ? (float) $sp->parameter->price : 0;
        });
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'Diajukan' => 'bg-amber-100 text-amber-800 border-amber-300',
            'Diterima' => 'bg-blue-100 text-blue-800 border-blue-300',
            'Diproses' => 'bg-cyan-100 text-cyan-800 border-cyan-300',
            'Diuji' => 'bg-indigo-100 text-indigo-800 border-indigo-300',
            'Menunggu Verifikasi' => 'bg-purple-100 text-purple-800 border-purple-300',
            'Revisi' => 'bg-rose-100 text-rose-800 border-rose-300',
            'Diverifikasi' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
            'Selesai' => 'bg-teal-100 text-teal-800 border-teal-300',
            default => 'bg-gray-100 text-gray-800 border-gray-300',
        };
    }
}
