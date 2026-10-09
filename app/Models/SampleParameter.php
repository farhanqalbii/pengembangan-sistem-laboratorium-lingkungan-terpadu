<?php

namespace App\Models;

use App\Models\Concerns\HasAttributeAliases;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SampleParameter extends Model
{
    use HasAttributeAliases, HasFactory;

    protected $fillable = [
        'sample_id',
        'parameter_id',
        'test_method',
        'id_peralatan',
        'analyst_id',
        'test_value',
        'unit',
        'quality_standard_summary',
        'compliance_status',
        'status',
        'notes',
    ];

    protected array $attributeAliases = [
        'equipment_id' => 'id_peralatan',
    ];

    public function sample(): BelongsTo
    {
        return $this->belongsTo(Sample::class);
    }

    public function parameter(): BelongsTo
    {
        return $this->belongsTo(Parameter::class);
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Peralatan::class, 'id_peralatan');
    }

    public function peralatan(): BelongsTo
    {
        return $this->belongsTo(Peralatan::class, 'id_peralatan');
    }

    public function analyst(): BelongsTo
    {
        return $this->belongsTo(User::class, 'analyst_id');
    }
}
