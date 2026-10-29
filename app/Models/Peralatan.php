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
        'foto',
    ];

    public const DEFAULT_PHOTOS = [
        'EQP-AAS-003' => 'images/equipment/aas-shimadzu-AA-7000.png',
        'EQP-PHM-002' => 'images/equipment/digital-portable-pH-meter-waterproof-hanna.png',
        'EQP-DO-002' => 'images/equipment/dissolved-oxygen-meter-hach.png',
        'EQP-INC-004' => 'images/equipment/incubator-BOD-refrigerated-memmert-ICP-260.png',
        'EQP-SPE-001' => 'images/equipment/spektrofotometer-UV-Vis-high-precision.png',
    ];

    protected $casts = [
        'tahun_pembelian' => 'integer',
        'jumlah' => 'integer',
        'stok_minimum' => 'integer',
        'harga_sewa_per_hari' => 'decimal:2',
        'biaya_deposit' => 'decimal:2',
    ];

    protected $appends = [
        'available_stock',
        'photo_url',
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
        'photo' => 'foto',
        'image' => 'foto',
    ];

    /**
     * Resolusi URL foto alat dengan prioritas:
     * 1. Foto hasil upload pengguna (disimpan di disk public storage).
     * 2. Foto bawaan awal berdasarkan pemetaan kode alat (5 alat awal).
     * 3. Null (menampilkan placeholder ikon labware di tampilan).
     */
    public function getPhotoUrlAttribute(): ?string
    {
        if (! empty($this->foto)) {
            if (str_starts_with($this->foto, 'http://') || str_starts_with($this->foto, 'https://')) {
                return $this->foto;
            }

            if (str_starts_with($this->foto, 'images/') || str_starts_with($this->foto, 'storage/')) {
                return asset($this->foto);
            }

            return asset('storage/'.$this->foto);
        }

        $code = $this->kode ?? $this->code;
        if ($code && isset(self::DEFAULT_PHOTOS[$code])) {
            return asset(self::DEFAULT_PHOTOS[$code]);
        }

        return null;
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->photo_url;
    }

    public function hasPhoto(): bool
    {
        return $this->photo_url !== null;
    }

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

    /**
     * Menghitung stok alat yang tersedia secara riil dengan memperhitungkan reservasi peminjaman Disetujui.
     */
    public function getAvailableStockAttribute(): int
    {
        return $this->getAvailableStockExcludingBorrowing();
    }

    /**
     * Menghitung stok alat yang tersedia, dengan opsi mengecualikan peminjaman tertentu (misal saat evaluasi peminjaman itu sendiri).
     */
    public function getAvailableStockExcludingBorrowing(?int $borrowingId = null): int
    {
        $reserved = DetailPeminjaman::where('id_peralatan', $this->id)
            ->whereHas('borrowing', function ($q) use ($borrowingId) {
                $q->where('status', 'Disetujui');
                if ($borrowingId) {
                    $q->where('id', '!=', $borrowingId);
                }
            })
            ->sum('jumlah');

        return max(0, (int) $this->jumlah - (int) $reserved);
    }

    public function getRouteKeyName(): string
    {
        return 'kode';
    }

    public function resolveRouteBinding($value, $field = null): ?Model
    {
        if (is_numeric($value)) {
            return null;
        }

        $field = $field ?? $this->getRouteKeyName();

        return $this->where($field, $value)->first();
    }
}
