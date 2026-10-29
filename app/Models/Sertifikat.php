<?php

namespace App\Models;

use App\Models\Concerns\HasAttributeAliases;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sertifikat extends Model
{
    use HasAttributeAliases, HasFactory;

    protected $table = 'sertifikat';

    protected $fillable = [
        'nomor_sertifikat',
        'sample_id',
        'customer_id',
        'pdf_path',
        'verified_by',
        'tanggal_verifikasi',
        'tanggal_terbit',
        'hash_tanda_tangan',
    ];

    protected $casts = [
        'tanggal_verifikasi' => 'datetime',
        'tanggal_terbit' => 'datetime',
    ];

    protected array $attributeAliases = [
        'certificate_number' => 'nomor_sertifikat',
        'verified_at' => 'tanggal_verifikasi',
        'issued_at' => 'tanggal_terbit',
        'digital_signature_hash' => 'hash_tanda_tangan',
    ];

    public function sample(): BelongsTo
    {
        return $this->belongsTo(Sample::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Menghasilkan tanda tangan digital HMAC-SHA256 yang mengunci integritas dokumen COA.
     */
    public static function generateSignature(Sample $sample, string $coaNumber, int $verifierId, string $timestamp): string
    {
        $payload = implode('|', [
            $coaNumber,
            $sample->sample_code,
            $sample->customer_id,
            $verifierId,
            $sample->sampleParameters()->count(),
            $timestamp,
        ]);

        $key = config('app.key') ?: 'lab-lingkungan-secret-key-2026';

        return hash_hmac('sha256', $payload, $key);
    }

    /**
     * Memeriksa apakah sertifikat telah sah dan final setelah disetujui Manajer Lab.
     */
    public function isFinal(): bool
    {
        return ! empty($this->digital_signature_hash)
            && ! empty($this->verified_by)
            && ! empty($this->tanggal_verifikasi)
            && $this->sample?->status === 'Selesai';
    }

    public function getRouteKeyName(): string
    {
        return 'nomor_sertifikat';
    }

    public function resolveRouteBinding($value, $field = null): ?Model
    {
        if (is_numeric($value)) {
            return null;
        }

        $field = $field ?? $this->getRouteKeyName();

        return $this->where($field, $value)
            ->orWhere($field, str_replace('-', '/', $value))
            ->first();
    }
}
