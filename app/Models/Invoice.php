<?php

namespace App\Models;

use App\Models\Concerns\HasAttributeAliases;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    use HasAttributeAliases, HasFactory;

    protected $fillable = [
        'invoice_number',
        'customer_id',
        'id_pembayaran',
        'subtotal',
        'deposit',
        'fine',
        'total',
        'status',
        'pdf_path',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'deposit' => 'decimal:2',
        'fine' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    protected array $attributeAliases = [
        'payment_id' => 'id_pembayaran',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Pembayaran::class, 'id_pembayaran');
    }

    public function pembayaran(): BelongsTo
    {
        return $this->belongsTo(Pembayaran::class, 'id_pembayaran');
    }
}
