<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use App\Services\PaymentGatewayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentWebhookController extends Controller
{
    public function __construct(
        protected PaymentGatewayService $paymentService
    ) {}

    /**
     * Menerima notifikasi HTTP POST webhook resmi dari payment gateway.
     */
    public function handle(Request $request): JsonResponse
    {
        $payload = $request->all();

        $orderId = $payload['order_id'] ?? null;
        $statusCode = $payload['status_code'] ?? null;
        $grossAmount = $payload['gross_amount'] ?? null;
        $signatureKey = $payload['signature_key'] ?? null;
        $transactionStatus = $payload['transaction_status'] ?? null;
        $paymentType = $payload['payment_type'] ?? 'Payment Gateway';
        $transactionId = $payload['transaction_id'] ?? ('PG-'.time());

        // 1. Validasi struktur kelengkapan payload webhook
        if (! $orderId || ! $transactionStatus || $grossAmount === null) {
            return response()->json([
                'success' => false,
                'message' => 'Format payload webhook tidak lengkap.',
            ], 400);
        }

        // 2. Verifikasi konfigurasi kredensial gateway di server
        if (! $this->paymentService->isConfigured()) {
            Log::error('Webhook ditolak: Server Key payment gateway belum dikonfigurasi di server.');

            return response()->json([
                'success' => false,
                'message' => 'Payment gateway belum dikonfigurasi di server.',
            ], 500);
        }

        // 3. Verifikasi signature digital untuk mencegah request palsu (AC-04, AC-05)
        if (! $signatureKey || ! $this->paymentService->verifyWebhookSignature((string) $orderId, (string) $statusCode, (string) $grossAmount, (string) $signatureKey)) {
            Log::warning("Percobaan webhook payment gateway dengan signature tidak valid untuk Order ID: {$orderId}");

            return response()->json([
                'success' => false,
                'message' => 'Tanda tangan digital webhook tidak valid.',
            ], 403);
        }

        // 4. Cari data pembayaran di database
        $payment = Pembayaran::where('kode_pembayaran', $orderId)->first();
        if (! $payment) {
            $baseOrderId = preg_replace('/-\d{6,}$/', '', (string) $orderId);
            $payment = Pembayaran::where('kode_pembayaran', $baseOrderId)->first();
        }

        if (! $payment) {
            return response()->json([
                'success' => false,
                'message' => "Transaksi pembayaran dengan kode {$orderId} tidak ditemukan.",
            ], 404);
        }

        // 5. Validasi nominal (Amount Verification) terhadap database (AC-06 & STEP 10)
        $dbAmount = (int) round((float) $payment->amount);
        $webhookAmount = (int) round((float) $grossAmount);
        if ($dbAmount !== $webhookAmount) {
            Log::error("Webhook ditolak: Nominal tidak cocok untuk Order {$orderId}. DB: {$dbAmount}, Webhook: {$webhookAmount}");

            return response()->json([
                'success' => false,
                'message' => 'Nominal transaksi tidak cocok dengan tagihan database.',
            ], 400);
        }

        $fraudStatus = $payload['fraud_status'] ?? null;

        // 6. Tangani status transaksi pembayaran secara resmi
        if ($transactionStatus === 'capture') {
            // Untuk kartu kredit: jangan menganggap status challenge sebagai settlement final
            if ($fraudStatus === 'challenge') {
                Log::info("Webhook: Transaksi kartu kredit berstatus challenge untuk Order {$orderId}. Menunggu konfirmasi fraud review.");

                return response()->json([
                    'success' => true,
                    'message' => 'Notifikasi webhook capture dengan status challenge diterima. Menunggu verifikasi lanjutan.',
                ]);
            }

            // Jika fraud_status adalah accept (atau tidak ada fraud review), proses pembayaran sebagai berhasil
            if ($fraudStatus === 'accept' || $fraudStatus === null) {
                $this->paymentService->handleSuccessfulPayment(
                    $payment,
                    $transactionId,
                    $paymentType
                );

                return response()->json([
                    'success' => true,
                    'message' => 'Pembayaran kartu kredit berhasil dikonfirmasi dan diverifikasi.',
                ]);
            }
        }

        if ($transactionStatus === 'settlement') {
            $this->paymentService->handleSuccessfulPayment(
                $payment,
                $transactionId,
                $paymentType
            );

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran berhasil dikonfirmasi dan diverifikasi.',
            ]);
        }

        if (in_array($transactionStatus, ['deny', 'cancel'])) {
            $this->paymentService->handleFailedPayment($payment, 'Gagal');

            return response()->json([
                'success' => true,
                'message' => 'Status pembayaran diperbarui menjadi Gagal.',
            ]);
        }

        if ($transactionStatus === 'expire') {
            $this->paymentService->handleFailedPayment($payment, 'Kadaluarsa');

            return response()->json([
                'success' => true,
                'message' => 'Status pembayaran diperbarui menjadi Kadaluarsa.',
            ]);
        }

        if ($transactionStatus === 'pending') {
            return response()->json([
                'success' => true,
                'message' => 'Notifikasi pembayaran pending diterima. Menunggu penyelesaian oleh pengguna.',
            ]);
        }

        if (in_array($transactionStatus, ['refund', 'partial_refund'])) {
            Log::info("Webhook: Notifikasi refund diterima untuk Order {$orderId}.");

            return response()->json([
                'success' => true,
                'message' => 'Notifikasi refund diterima dan dicatat.',
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Notifikasi webhook diterima (status: '.$transactionStatus.').',
        ]);
    }
}
