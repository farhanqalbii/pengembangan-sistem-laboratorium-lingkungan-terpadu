<?php

namespace App\Models;

use App\Models\Concerns\HasAttributeAliases;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailPeminjaman extends Model
{
    use HasAttributeAliases, HasFactory;

    protected $table = 'detail_peminjaman';

    protected $fillable = [
        'id_peminjaman',
        'id_peralatan',
        'jumlah',
        'kondisi_kembali',
        'catatan_kondisi',
    ];

    protected $casts = [
        'jumlah' => 'integer',
    ];

    protected array $attributeAliases = [
        'borrowing_id' => 'id_peminjaman',
        'equipment_id' => 'id_peralatan',
        'quantity' => 'jumlah',
        'condition_return' => 'kondisi_kembali',
        'condition_notes' => 'catatan_kondisi',
    ];

    public function borrowing(): BelongsTo
    {
        return $this->belongsTo(Peminjaman::class, 'id_peminjaman');
    }

    public function peminjaman(): BelongsTo
    {
        return $this->belongsTo(Peminjaman::class, 'id_peminjaman');
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Peralatan::class, 'id_peralatan');
    }

    public function peralatan(): BelongsTo
    {
        return $this->belongsTo(Peralatan::class, 'id_peralatan');
    }
}
