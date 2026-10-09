<?php

namespace App\Models;

use App\Models\Concerns\HasAttributeAliases;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Kalibrasi extends Model
{
    use HasAttributeAliases, HasFactory;

    protected $table = 'kalibrasi';

    protected $fillable = [
        'id_peralatan',
        'tanggal_kalibrasi',
        'tanggal_kalibrasi_berikutnya',
        'hasil',
        'catatan',
    ];

    protected $casts = [
        'tanggal_kalibrasi' => 'date',
        'tanggal_kalibrasi_berikutnya' => 'date',
    ];

    protected array $attributeAliases = [
        'equipment_id' => 'id_peralatan',
        'calibration_date' => 'tanggal_kalibrasi',
        'next_calibration_date' => 'tanggal_kalibrasi_berikutnya',
        'tanggal_kalibrasi_selanjutnya' => 'tanggal_kalibrasi_berikutnya',
        'result' => 'hasil',
        'status' => 'hasil',
        'notes' => 'catatan',
        'keterangan' => 'catatan',
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
