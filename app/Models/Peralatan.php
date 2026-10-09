<?php

namespace App\Models;

use App\Models\Concerns\HasAttributeAliases;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Peralatan extends Model
{
    use HasAttributeAliases, HasFactory;

    protected $table = 'peralatan';

    protected $fillable = [
        'kode',
        'nama',
        'kategori',
        'merk',
        'spesifikasi',
        'tahun_pembelian',
        'jumlah',
        'lokasi',
        'qr_code',
        'barcode',
        'stok_minimum',
        'status',
        'harga_sewa_per_hari',
        'biaya_deposit',
    ];

    protected $casts = [
        'tahun_pembelian' => 'integer',
        'jumlah' => 'integer',
        'stok_minimum' => 'integer',
        'harga_sewa_per_hari' => 'decimal:2',
        'biaya_deposit' => 'decimal:2',
    ];

    protected array $attributeAliases = [
        'code' => 'kode',
        'name' => 'nama',
        'category' => 'kategori',
        'brand' => 'merk',
        'specification' => 'spesifikasi',
        'purchase_year' => 'tahun_pembelian',
        'quantity' => 'jumlah',
        'location' => 'lokasi',
        'min_stock' => 'stok_minimum',
        'rental_price_per_day' => 'harga_sewa_per_hari',
        'deposit_amount' => 'biaya_deposit',
    ];

    public function calibrations(): HasMany
    {
        return $this->hasMany(Kalibrasi::class, 'id_peralatan');
    }

    public function kalibrasi(): HasMany
    {
        return $this->hasMany(Kalibrasi::class, 'id_peralatan');
    }

    public function serviceHistories(): HasMany
    {
        return $this->hasMany(RiwayatServis::class, 'id_peralatan');
    }

    public function riwayatServis(): HasMany
    {
        return $this->hasMany(RiwayatServis::class, 'id_peralatan');
    }

    public function stockHistories(): HasMany
    {
        return $this->hasMany(RiwayatStok::class, 'id_peralatan');
    }

    public function riwayatStok(): HasMany
    {
        return $this->hasMany(RiwayatStok::class, 'id_peralatan');
    }

    public function borrowingDetails(): HasMany
    {
        return $this->hasMany(DetailPeminjaman::class, 'id_peralatan');
    }

    public function detailPeminjaman(): HasMany
    {
        return $this->hasMany(DetailPeminjaman::class, 'id_peralatan');
    }

    public function isLowStock(): bool
    {
        return $this->jumlah <= $this->stok_minimum;
    }
}
