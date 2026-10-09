<?php

namespace App\Models;

use App\Models\Concerns\HasAttributeAliases;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BakuMutu extends Model
{
    use HasAttributeAliases, HasFactory;

    protected $table = 'baku_mutu';

    protected $fillable = [
        'parameter_id',
        'nilai_minimum',
        'nilai_maksimum',
        'unit',
        'sumber_regulasi',
        'status',
    ];

    protected $casts = [
        'nilai_minimum' => 'float',
        'nilai_maksimum' => 'float',
    ];

    protected array $attributeAliases = [
        'min_value' => 'nilai_minimum',
        'max_value' => 'nilai_maksimum',
        'regulation_source' => 'sumber_regulasi',
    ];

    public function parameter(): BelongsTo
    {
        return $this->belongsTo(Parameter::class);
    }

    public function getSummaryAttribute(): string
    {
        if ($this->nilai_minimum !== null && $this->nilai_maksimum !== null) {
            return "{$this->nilai_minimum} - {$this->nilai_maksimum} {$this->unit}";
        } elseif ($this->nilai_minimum !== null) {
            return "≥ {$this->nilai_minimum} {$this->unit}";
        } elseif ($this->nilai_maksimum !== null) {
            return "≤ {$this->nilai_maksimum} {$this->unit}";
        }

        return 'N/A';
    }

    public function checkCompliance(?float $value): string
    {
        if ($value === null) {
            return 'Pending';
        }

        if ($this->nilai_minimum !== null && $value < $this->nilai_minimum) {
            return 'Tidak Memenuhi';
        }

        if ($this->nilai_maksimum !== null && $value > $this->nilai_maksimum) {
            return 'Tidak Memenuhi';
        }

        return 'Memenuhi';
    }
}
