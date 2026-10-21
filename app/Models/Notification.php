<?php

namespace App\Models;

use App\Mail\NotificationMail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'message',
        'link',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::created(function (Notification $notification) {
            try {
                $user = $notification->user;
                if ($user && ! empty($user->email)) {
                    Mail::to($user->email)
                        ->send(new NotificationMail($notification));
                }
            } catch (\Throwable $e) {
                Log::warning("Gagal mengirim email notifikasi ID {$notification->id}: ".$e->getMessage());
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function markAsRead(): bool
    {
        if ($this->read_at === null) {
            return $this->update(['read_at' => now()]);
        }

        return true;
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    /**
     * Compute categorized unread notification counts for a user.
     *
     * @return array<string, int>
     */
    public static function getUnreadCountsFor(int $userId): array
    {
        $unread = static::where('user_id', $userId)->whereNull('read_at')->latest()->get();

        return [
            'total' => $unread->count(),
            'sample' => $unread->filter(fn ($n) => (str_contains($n->link ?? '', 'sample') || str_contains(strtolower(($n->title ?? '').' '.($n->message ?? '')), 'sampel') || str_contains(strtolower(($n->title ?? '').' '.($n->message ?? '')), 'pengujian')) && ! str_contains(strtolower(($n->title ?? '').' '.($n->message ?? '')), 'sertifikat'))->count(),
            'borrowing' => $unread->filter(fn ($n) => str_contains($n->link ?? '', 'borrow') || str_contains(strtolower(($n->title ?? '').' '.($n->message ?? '')), 'pinjam'))->count(),
            'coa' => $unread->filter(fn ($n) => str_contains($n->link ?? '', 'certificate') || str_contains(strtolower(($n->title ?? '').' '.($n->message ?? '')), 'coa') || str_contains(strtolower(($n->title ?? '').' '.($n->message ?? '')), 'sertifikat'))->count(),
            'payment' => $unread->filter(fn ($n) => str_contains($n->link ?? '', 'payment') || str_contains(strtolower(($n->title ?? '').' '.($n->message ?? '')), 'bayar') || str_contains(strtolower(($n->title ?? '').' '.($n->message ?? '')), 'invoice') || str_contains(strtolower(($n->title ?? '').' '.($n->message ?? '')), 'tagihan'))->count(),
            'equipment' => $unread->filter(fn ($n) => str_contains($n->link ?? '', 'equipment') || str_contains(strtolower(($n->title ?? '').' '.($n->message ?? '')), 'stok'))->count(),
            'verification' => $unread->filter(fn ($n) => str_contains($n->link ?? '', 'verifikasi') || str_contains(strtolower(($n->title ?? '').' '.($n->message ?? '')), 'verifikasi'))->count(),
            'finance' => $unread->filter(fn ($n) => str_contains($n->link ?? '', 'finance') || str_contains(strtolower(($n->title ?? '').' '.($n->message ?? '')), 'refund') || str_contains(strtolower(($n->title ?? '').' '.($n->message ?? '')), 'keuangan'))->count(),
        ];
    }
}
