<?php

namespace App\Models;

use App\Models\Concerns\HasAttributeAliases;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LogAktivitas extends Model
{
    use HasAttributeAliases, HasFactory;

    protected $table = 'log_aktivitas';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'aktivitas',
        'modul',
        'deskripsi',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    protected array $attributeAliases = [
        'activity' => 'aktivitas',
        'module' => 'modul',
        'description' => 'deskripsi',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function record(string $activity, string $module, string $description, ?int $userId = null): self
    {
        return static::create([
            'user_id' => $userId ?? auth()->id(),
            'aktivitas' => $activity,
            'modul' => $module,
            'deskripsi' => $description,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);
    }
}
