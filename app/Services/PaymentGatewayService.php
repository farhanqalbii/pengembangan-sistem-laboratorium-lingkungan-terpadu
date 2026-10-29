<?php

namespace App\Services;

use App\Models\LogAktivitas;
use App\Models\Notification;
use App\Models\Pembayaran;
use App\Models\Peminjaman;
use App\Models\Peralatan;
use App\Models\RiwayatStok;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentGatewayService
{
    protected ?string $serverKey;

    protected ?string $clientKey;

    protected bool $isProduction;

    protected string $snapBaseUrl;

    public function __construct()
    {
        $this->serverKey = config('services.payment_gateway.server_key');
        $this->clientKey = config('services.payment_gateway.client_key');
        $this->isProduction = (bool) config('services.payment_gateway.is_production', false);
        $this->snapBaseUrl = $this->isProduction
            ? 'https://app.midtrans.com/snap/v1'
            : 'https://app.sandbox.midtrans.com/snap/v1';
    }

    /**
     * Memeriksa apakah credential payment gateway sudah dikonfigurasi di environment.
     */
    public function isConfigured(): bool
    {
        return ! empty($this->serverKey);
    }

    /**
     * Membuat Snap Token / Payment Transaction ke Payment Gateway.
     *
     * @return array{success: bool, token?: string, redirect_url?: string, message?: string}
     */
    public function createTransaction(Pembayaran $payment): array
    {
        if (! $this->isConfigured()) {
            Log::warning('Payment Gateway Server Key belum dikonfigurasi. Silakan isi PAYMENT_GATEWAY_SERVER_KEY atau MIDTRANS_SERVER_KEY di file .env');

            return [
                'success' => false,
                'message' => 'Koneksi Payment Gateway belum dikonfigurasi (memerlukan PAYMENT_GATEWAY_SERVER_KEY).',
            ];
        }

        $customer = $payment->customer;
        $orderId = $payment->payment_code;
        $grossAmount = (int) round((float) $payment->amount);

        $buildPayload = function (string $currentOrderId) use ($payment, $customer, $grossAmount) {
            return [
                'transaction_details' => [
                    'order_id' => $currentOrderId,
                    'gross_amount' => $grossAmount,
                ],
                'customer_details' => [
                    'first_name' => $customer->name ?? 'Customer Lab',
                    'email' => $customer->email ?? 'customer@example.com',
                    'phone' => $customer->phone ?? '08123456789',
                ],
                'item_details' => [
                    [
                        'id' => $payment->id,
                        'price' => $grossAmount,
                        'quantity' => 1,
                        'name' => substr($payment->type.' - '.$currentOrderId, 0, 50),
                    ],
                ],
            ];
        };

        try {
            $response = Http::withBasicAuth($this->serverKey, '')
                ->timeout(15)
                ->post("{$this->snapBaseUrl}/transactions", $buildPayload($orderId));

            // Jika Midtrans menolak karena order_id sudah pernah digunakan/kadaluarsa sebelumnya
            if (! $response->successful() && $this->isDuplicateOrderIdResponse($response)) {
                $retryOrderId = $orderId.'-'.time();
                Log::info("Payment Gateway: Order ID {$orderId} sudah pernah tercatat di gateway, mencoba ulang otomatis dengan {$retryOrderId}");

                $retryResponse = Http::withBasicAuth($this->serverKey, '')
                    ->timeout(15)
                    ->post("{$this->snapBaseUrl}/transactions", $buildPayload($retryOrderId));

                if ($retryResponse->successful()) {
                    $response = $retryResponse;
                    $orderId = $retryOrderId;
                }
            }

            if ($response->successful()) {
                $data = $response->json();

                // Simpan active order ID ke cache untuk pengecekan status
                Cache::put("payment_gateway_order_id_{$payment->id}", $orderId, now()->addDays(7));

                return [
                    'success' => true,
                    'order_id' => $orderId,
                    'token' => $data['token'] ?? null,
                    'redirect_url' => $data['redirect_url'] ?? null,
                ];
            }

            Log::error('Payment Gateway Request Failed: '.$response->body());

            $errorData = $response->json();
            $detailMsg = null;
            if (isset($errorData['error_messages']) && is_array($errorData['error_messages'])) {
                $detailMsg = implode(', ', $errorData['error_messages']);
            } elseif (isset($errorData['status_message'])) {
                $detailMsg = $errorData['status_message'];
            } elseif (isset($errorData['message'])) {
                $detailMsg = $errorData['message'];
            }

            return [
                'success' => false,
                'message' => $detailMsg ? "Gagal dari payment gateway: {$detailMsg}" : 'Gagal membuat transaksi ke payment gateway: '.$response->status(),
            ];
        } catch (\Throwable $e) {
            Log::error('Payment Gateway Exception: '.$e->getMessage());

            return [
                'success' => false,
                'message' => 'Terjadi kesalahan jaringan saat menghubungi payment gateway.',
            ];
        }
    }

    /**
     * Memeriksa apakah response dari gateway mengindikasikan order_id sudah pernah dipakai.
     */
    protected function isDuplicateOrderIdResponse($response): bool
    {
        $body = strtolower($response->body());

        return str_contains($body, 'order_id has already been taken')
            || str_contains($body, 'order_id sudah digunakan')
            || (str_contains($body, 'order_id') && str_contains($body, 'already'));
    }

    /**
     * Memverifikasi tanda tangan digital payload notifikasi / webhook dari gateway.
     */
    public function verifyWebhookSignature(string $orderId, string $statusCode, string $grossAmount, string $signatureKey): bool
    {
        if (! $this->isConfigured()) {
            return false;
        }

        $expectedSignature = hash('sha512', $orderId.$statusCode.$grossAmount.$this->serverKey);

        return hash_equals($expectedSignature, $signatureKey);
    }

    /**
     * Menangani konfirmasi pembayaran resmi dari gateway secara atomik dan idempoten.
     */
    public function handleSuccessfulPayment(Pembayaran $payment, string $reference, string $method): void
    {
        if ($payment->status === 'Berhasil') {
            Log::info("Idempotency: Pembayaran {$payment->payment_code} sudah berstatus Berhasil. Mengabaikan pemrosesan duplikat.");

            return;
        }

        DB::transaction(function () use ($payment, $reference, $method) {
            // Kunci baris pembayaran secara eksklusif (SELECT ... FOR UPDATE) untuk mencegah eksekusi paralel bersamaan
            $lockedPayment = Pembayaran::where('id', $payment->id)
                ->lockForUpdate()
                ->first();

            if (! $lockedPayment || $lockedPayment->status === 'Berhasil') {
                Log::info("Idempotency (Locked): Pembayaran {$payment->payment_code} sudah berstatus Berhasil atau tidak ditemukan. Mengabaikan pemrosesan duplikat.");

                return;
            }

            $lockedPayment->update([
                'payment_method' => $method,
                'status' => 'Berhasil',
                'payment_reference' => $reference,
                'paid_at' => now(),
            ]);

            if ($lockedPayment->invoice) {
                $lockedPayment->invoice->update([
                    'status' => 'Lunas',
                ]);
            }

            // Jika pembayaran adalah deposit peminjaman alat
            if ($lockedPayment->borrowing && $lockedPayment->type === 'Deposit Peminjaman') {
                $borrowing = Peminjaman::where('id', $lockedPayment->borrowing->id)
                    ->lockForUpdate()
                    ->first();

                if ($borrowing) {
                    $stockSufficient = true;
                    $insufficientEquipments = [];

                    foreach ($borrowing->borrowingDetails as $detail) {
                        $eq = Peralatan::where('id', $detail->id_peralatan)
                            ->lockForUpdate()
                            ->first();

                        if (! $eq || $eq->status !== 'Tersedia' || $eq->quantity < $detail->quantity) {
                            $stockSufficient = false;
                            $insufficientEquipments[] = $eq ? $eq->name : 'Alat tidak ditemukan';
                        }
                    }

                    if ($stockSufficient) {
                        $borrowing->update(['status' => 'Dipinjam']);

                        // Potong stok alat dan catat riwayat mutasi stok
                        foreach ($borrowing->borrowingDetails as $detail) {
                            $eq = Peralatan::where('id', $detail->id_peralatan)
                                ->lockForUpdate()
                                ->first();

                            if ($eq) {
                                $oldQty = $eq->quantity;
                                $eq->decrement('jumlah', $detail->quantity);
                                $newQty = $eq->fresh()->quantity;

                                RiwayatStok::create([
                                    'id_peralatan' => $eq->id,
                                    'tipe' => 'Peminjaman',
                                    'jumlah_sebelum' => $oldQty,
                                    'jumlah_perubahan' => -$detail->quantity,
                                    'jumlah_setelah' => $newQty,
                                    'user_id' => $lockedPayment->customer_id,
                                    'catatan' => "Pengurangan stok karena peminjaman aktif {$borrowing->borrow_code}",
                                ]);

                                if ($newQty <= $eq->min_stock) {
                                    $admins = User::whereIn('role', ['admin', 'staff'])->get();
                                    foreach ($admins as $ad) {
                                        Notification::create([
                                            'user_id' => $ad->id,
                                            'title' => 'Peringatan Stok Minimum!',
                                            'message' => "Stok alat {$eq->name} tersisa {$newQty} unit (Stok min: {$eq->min_stock}).",
                                            'link' => '/staff/equipment/'.$eq->id,
                                        ]);
                                    }
                                }
                            }
                        }
                    } else {
                        // Deposit berhasil dibayar namun stok tidak mencukupi saat settlement
                        $warningMsg = 'Pembayaran deposit berhasil, namun stok alat ('.implode(', ', $insufficientEquipments).') tidak mencukupi saat aktivasi. Status tidak diubah menjadi Dipinjam.';
                        $borrowing->update([
                            'catatan' => trim($borrowing->catatan." [Kendala Stok: {$warningMsg}]"),
                        ]);

                        LogAktivitas::record(
                            'Kendala Stok Settlement Peminjaman',
                            'Peminjaman',
                            "Pembayaran deposit {$borrowing->borrow_code} berhasil, namun stok alat tidak mencukupi (".implode(', ', $insufficientEquipments).').',
                            $lockedPayment->customer_id
                        );

                        // Notifikasi ke Staf & Admin
                        $admins = User::whereIn('role', ['admin', 'staff'])->get();
                        foreach ($admins as $ad) {
                            Notification::create([
                                'user_id' => $ad->id,
                                'title' => 'Peringatan: Stok Tidak Mencukupi Saat Settlement!',
                                'message' => "Deposit peminjaman {$borrowing->borrow_code} lunas, tetapi stok (".implode(', ', $insufficientEquipments).') tidak mencukupi. Segera atur penyesuaian atau pengembalian.',
                                'link' => '/admin/borrowings/'.($borrowing->kode_peminjaman ?? $borrowing->borrow_code),
                            ]);
                        }

                        // Notifikasi ke Customer
                        Notification::create([
                            'user_id' => $borrowing->customer_id,
                            'title' => 'Pembayaran Berhasil - Menunggu Ketersediaan Alat',
                            'message' => "Pembayaran deposit peminjaman {$borrowing->borrow_code} telah diterima. Namun saat ini ketersediaan unit sedang terkendala. Staf kami akan segera menghubungi Anda.",
                            'link' => '/customer/borrowings/'.($borrowing->kode_peminjaman ?? $borrowing->borrow_code),
                        ]);
                    }
                }
            }

            // Jika pembayaran adalah pengujian sampel
            if ($lockedPayment->sample) {
                // Notifikasi ke Customer: pembayaran lunas, sampel menunggu pemeriksaan staf
                Notification::create([
                    'user_id' => $lockedPayment->customer_id,
                    'title' => 'Pembayaran Sampel Terkonfirmasi',
                    'message' => "Pembayaran tagihan sampel {$lockedPayment->sample->sample_code} telah berhasil diverifikasi. Sampel Anda menunggu pemeriksaan & persetujuan penerimaan oleh staf lab.",
                    'link' => '/customer/samples/'.$lockedPayment->sample->sample_code,
                ]);

                // Notifikasi ke Staf & Admin: pembayaran lunas, siap diperiksa dan diterima
                $staffUsers = User::whereIn('role', ['staff', 'admin'])->get();
                foreach ($staffUsers as $st) {
                    Notification::create([
                        'user_id' => $st->id,
                        'title' => 'Sampel Siap Diterima (Lunas)',
                        'message' => "Pembayaran sampel {$lockedPayment->sample->sample_code} dari {$lockedPayment->customer->name} telah lunas. Sampel siap diverifikasi dan diterima.",
                        'link' => '/staff/samples/'.$lockedPayment->sample->sample_code,
                    ]);
                }
            }

            LogAktivitas::record(
                'Pembayaran Terkonfirmasi',
                'Pembayaran',
                "Pembayaran {$lockedPayment->payment_code} sebesar Rp ".number_format($lockedPayment->amount, 0, ',', '.')." berhasil diverifikasi via payment gateway ({$reference}).",
                $lockedPayment->customer_id
            );

            // Sinkronkan objek di memori pemanggil
            $payment->fill($lockedPayment->toArray());
            $payment->syncOriginal();
        });
    }

    /**
     * Menangani status pembayaran gagal / dibatalkan / kadaluarsa dari gateway secara aman.
     */
    public function handleFailedPayment(Pembayaran $payment, string $status = 'Gagal'): void
    {
        if ($payment->status === 'Berhasil') {
            return;
        }

        DB::transaction(function () use ($payment, $status) {
            $lockedPayment = Pembayaran::where('id', $payment->id)
                ->lockForUpdate()
                ->first();

            $normalizedStatus = in_array($status, ['Gagal', 'Kadaluarsa']) ? $status : 'Gagal';

            // Idempoten: Jika sudah Berhasil atau sudah memiliki status gagal yang sama, jangan kirim ulang notifikasi atau efek samping
            if (! $lockedPayment || $lockedPayment->status === 'Berhasil' || $lockedPayment->status === $normalizedStatus) {
                return;
            }

            $lockedPayment->update([
                'status' => $normalizedStatus,
            ]);

            if ($lockedPayment->sample) {
                Notification::create([
                    'user_id' => $lockedPayment->customer_id,
                    'title' => 'Pembayaran Sampel '.$normalizedStatus,
                    'message' => "Pembayaran untuk sampel {$lockedPayment->sample->sample_code} berstatus {$normalizedStatus}. Silakan lakukan pembayaran ulang melalui portal customer.",
                    'link' => '/customer/samples/'.$lockedPayment->sample->sample_code,
                ]);
            }

            LogAktivitas::record(
                'Pembayaran '.$normalizedStatus,
                'Pembayaran',
                "Pembayaran {$lockedPayment->payment_code} diperbarui menjadi {$normalizedStatus} dari payment gateway.",
                $lockedPayment->customer_id
            );

            $payment->fill($lockedPayment->toArray());
            $payment->syncOriginal();
        });
    }

    /**
     * Memeriksa status transaksi terkini langsung ke Payment Gateway API (GET /v2/{order_id}/status).
     *
     * @return array{success: bool, status: string, message: string}
     */
    public function checkTransactionStatus(Pembayaran $payment): array
    {
        if (! $this->isConfigured()) {
            return [
                'success' => false,
                'status' => $payment->status,
                'message' => 'Payment gateway belum dikonfigurasi.',
            ];
        }

        $apiBaseUrl = $this->isProduction
            ? 'https://api.midtrans.com/v2'
            : 'https://api.sandbox.midtrans.com/v2';

        $activeOrderId = Cache::get("payment_gateway_order_id_{$payment->id}") ?? $payment->payment_code;

        try {
            $response = Http::withBasicAuth($this->serverKey, '')
                ->timeout(20)
                ->retry(2, 200)
                ->get("{$apiBaseUrl}/{$activeOrderId}/status");

            if ($response->status() === 404 && $activeOrderId !== $payment->payment_code) {
                $response = Http::withBasicAuth($this->serverKey, '')
                    ->timeout(20)
                    ->retry(2, 200)
                    ->get("{$apiBaseUrl}/{$payment->payment_code}/status");
            }

            if ($response->successful()) {
                $data = $response->json();
                $trxStatus = $data['transaction_status'] ?? null;
                $fraudStatus = $data['fraud_status'] ?? null;
                $grossAmount = $data['gross_amount'] ?? null;
                $paymentType = $data['payment_type'] ?? 'midtrans';
                $transactionId = $data['transaction_id'] ?? $payment->payment_code;

                // Validasi amount
                $dbAmount = (int) round((float) $payment->amount);
                $gwAmount = (int) round((float) $grossAmount);

                $isCapturedAccept = ($trxStatus === 'capture' && ($fraudStatus === 'accept' || $fraudStatus === null));
                $isSettled = ($trxStatus === 'settlement');

                if (($isCapturedAccept || $isSettled) && $dbAmount === $gwAmount) {
                    $this->handleSuccessfulPayment($payment, $transactionId, $paymentType);

                    return [
                        'success' => true,
                        'status' => 'Berhasil',
                        'message' => 'Pembayaran telah terverifikasi lunas oleh payment gateway.',
                    ];
                }

                if ($trxStatus === 'capture' && $fraudStatus === 'challenge') {
                    return [
                        'success' => false,
                        'status' => $payment->fresh()->status,
                        'message' => 'Transaksi kartu kredit dalam status challenge fraud review.',
                    ];
                }

                if (in_array($trxStatus, ['deny', 'cancel'])) {
                    $this->handleFailedPayment($payment, 'Gagal');

                    return [
                        'success' => false,
                        'status' => 'Gagal',
                        'message' => 'Status transaksi dibatalkan atau ditolak oleh payment gateway.',
                    ];
                }

                if ($trxStatus === 'expire') {
                    $this->handleFailedPayment($payment, 'Kadaluarsa');

                    return [
                        'success' => false,
                        'status' => 'Kadaluarsa',
                        'message' => 'Transaksi telah kadaluarsa di payment gateway.',
                    ];
                }

                return [
                    'success' => false,
                    'status' => $payment->fresh()->status,
                    'message' => 'Menunggu penyelesaian pembayaran di payment gateway (Status: '.$trxStatus.').',
                ];
            }

            return [
                'success' => false,
                'status' => $payment->status,
                'message' => 'Belum ada catatan transaksi pembayaran di payment gateway.',
            ];
        } catch (\Throwable $e) {
            Log::error('Check Payment Status Exception: '.$e->getMessage());

            return [
                'success' => false,
                'status' => $payment->status,
                'message' => 'Gagal menghubungi server payment gateway.',
            ];
        }
    }
}
