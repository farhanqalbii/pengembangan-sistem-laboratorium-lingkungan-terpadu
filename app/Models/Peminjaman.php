<?php

namespace App\Models;

use App\Models\Concerns\HasAttributeAliases;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Peminjaman extends Model
{
    use HasAttributeAliases, HasFactory;

    protected $table = 'peminjaman';

    protected $fillable = [
        'kode_peminjaman',
        'customer_id',
        'tanggal_mulai',
        'tanggal_selesai',
        'tanggal_pengembalian',
        'tujuan',
        'catatan',
        'status',
        'perpanjangan_diajukan',
        'tanggal_perpanjangan',
        'status_perpanjangan',
        'biaya_deposit',
        'biaya_denda',
        'biaya_pengembalian',
        'status_pengembalian',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'tanggal_pengembalian' => 'date',
        'tanggal_perpanjangan' => 'date',
        'perpanjangan_diajukan' => 'boolean',
        'biaya_deposit' => 'decimal:2',
        'biaya_denda' => 'decimal:2',
        'biaya_pengembalian' => 'decimal:2',
    ];

    protected array $attributeAliases = [
        'borrow_code' => 'kode_peminjaman',
        'start_date' => 'tanggal_mulai',
        'end_date' => 'tanggal_selesai',
        'actual_return_date' => 'tanggal_pengembalian',
        'purpose' => 'tujuan',
        'notes' => 'catatan',
        'extension_requested' => 'perpanjangan_diajukan',
        'extended_end_date' => 'tanggal_perpanjangan',
        'extension_status' => 'status_perpanjangan',
        'deposit_amount' => 'biaya_deposit',
        'fine_amount' => 'biaya_denda',
        'refund_amount' => 'biaya_pengembalian',
        'refund_status' => 'status_pengembalian',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function borrowingDetails(): HasMany
    {
        return $this->hasMany(DetailPeminjaman::class, 'id_peminjaman');
    }

    public function detailPeminjaman(): HasMany
    {
        return $this->hasMany(DetailPeminjaman::class, 'id_peminjaman');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Pembayaran::class, 'id_peminjaman');
    }

    public function pembayaran(): HasMany
    {
        return $this->hasMany(Pembayaran::class, 'id_peminjaman');
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'Diajukan' => 'bg-amber-100 text-amber-800 border-amber-300',
            'Disetujui' => 'bg-blue-100 text-blue-800 border-blue-300',
            'Ditolak' => 'bg-rose-100 text-rose-800 border-rose-300',
            'Dipinjam' => 'bg-indigo-100 text-indigo-800 border-indigo-300',
            'Dikembalikan' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
            'Dibatalkan' => 'bg-gray-100 text-gray-800 border-gray-300',
            default => 'bg-gray-100 text-gray-800 border-gray-300',
        };
    }

    public function getRouteKeyName(): string
    {
        return 'kode_peminjaman';
    }

    public function resolveRouteBinding($value, $field = null): ?Model
    {
        $field = $field ?? 'kode_peminjaman';

        return $this->where($field, $value)->first();
    }
}
