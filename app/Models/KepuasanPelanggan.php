<?php

namespace App\Models;

use App\Models\Concerns\HasAttributeAliases;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KepuasanPelanggan extends Model
{
    use HasAttributeAliases, HasFactory;

    protected $table = 'kepuasan_pelanggan';

    protected $fillable = [
        'customer_id',
        'tipe_layanan',
        'id_layanan',
        'rating',
        'saran_masukan',
    ];

    protected $casts = [
        'rating' => 'integer',
        'id_layanan' => 'integer',
    ];

    protected array $attributeAliases = [
        'service_type' => 'tipe_layanan',
        'service_id' => 'id_layanan',
        'feedback' => 'saran_masukan',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }
}
