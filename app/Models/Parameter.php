<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Parameter extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category',
        'unit',
        'test_method',
        'price',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function qualityStandard(): HasOne
    {
        return $this->hasOne(BakuMutu::class);
    }

    public function bakuMutu(): HasOne
    {
        return $this->hasOne(BakuMutu::class);
    }

    public function sampleParameters(): HasMany
    {
        return $this->hasMany(SampleParameter::class);
    }
}
