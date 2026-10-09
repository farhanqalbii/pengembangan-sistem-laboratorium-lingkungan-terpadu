<?php

namespace App\Models;

use App\Models\Concerns\HasAttributeAliases;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiwayatServis extends Model
{
    use HasAttributeAliases, HasFactory;

    protected $table = 'riwayat_servis';

    protected $fillable = [
        'id_peralatan',
        'tanggal_servis',
        'catatan',
        'status',
        'teknisi',
    ];

    protected $casts = [
        'tanggal_servis' => 'date',
    ];

    protected array $attributeAliases = [
        'equipment_id' => 'id_peralatan',
        'service_date' => 'tanggal_servis',
        'notes' => 'catatan',
        'keterangan' => 'catatan',
        'description' => 'catatan',
        'technician' => 'teknisi',
        'technician_name' => 'teknisi',
    ];

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Peralatan::class, 'id_peralatan');
    }

    public function peralatan(): BelongsTo
    {
        return $this->belongsTo(Peralatan::class, 'id_peralatan');
    }
}
