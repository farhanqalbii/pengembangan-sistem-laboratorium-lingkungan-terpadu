<?php

namespace App\Models;

use App\Models\Concerns\HasAttributeAliases;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiwayatStok extends Model
{
    use HasAttributeAliases, HasFactory;

    protected $table = 'riwayat_stok';

    protected $fillable = [
        'id_peralatan',
        'tipe',
        'jumlah_sebelum',
        'jumlah_perubahan',
        'jumlah_setelah',
        'user_id',
        'catatan',
    ];

    protected array $attributeAliases = [
        'equipment_id' => 'id_peralatan',
        'type' => 'tipe',
        'qty_before' => 'jumlah_sebelum',
        'qty_change' => 'jumlah_perubahan',
        'perubahan_jumlah' => 'jumlah_perubahan',
        'qty_after' => 'jumlah_setelah',
        'jumlah_sesudah' => 'jumlah_setelah',
        'id_pengguna' => 'user_id',
        'keterangan' => 'catatan',
        'notes' => 'catatan',
    ];

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Peralatan::class, 'id_peralatan');
    }

    public function peralatan(): BelongsTo
    {
        return $this->belongsTo(Peralatan::class, 'id_peralatan');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
