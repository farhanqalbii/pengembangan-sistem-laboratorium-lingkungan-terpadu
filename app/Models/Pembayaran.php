<?php

namespace App\Models;

use App\Models\Concerns\HasAttributeAliases;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Pembayaran extends Model
{
    use HasAttributeAliases, HasFactory;

    protected $table = 'pembayaran';

    protected $fillable = [
        'kode_pembayaran',
        'customer_id',
        'id_peminjaman',
        'sample_id',
        'tipe',
        'jumlah_bayar',
        'metode_pembayaran',
        'status',
        'referensi_pembayaran',
        'tanggal_bayar',
    ];

    protected $casts = [
        'jumlah_bayar' => 'decimal:2',
        'tanggal_bayar' => 'datetime',
    ];

    protected array $attributeAliases = [
        'payment_code' => 'kode_pembayaran',
        'borrowing_id' => 'id_peminjaman',
        'type' => 'tipe',
        'amount' => 'jumlah_bayar',
        'payment_method' => 'metode_pembayaran',
        'payment_reference' => 'referensi_pembayaran',
        'paid_at' => 'tanggal_bayar',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function borrowing(): BelongsTo
    {
        return $this->belongsTo(Peminjaman::class, 'id_peminjaman');
    }

    public function peminjaman(): BelongsTo
    {
        return $this->belongsTo(Peminjaman::class, 'id_peminjaman');
    }

    public function sample(): BelongsTo
    {
        return $this->belongsTo(Sample::class);
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class, 'id_pembayaran');
    }
}
