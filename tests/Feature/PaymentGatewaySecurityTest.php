<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\Pembayaran;
use App\Models\Peminjaman;
use App\Models\Peralatan;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PaymentGatewaySecurityTest extends TestCase
{
    use DatabaseTransactions;

    protected User $customer;

    protected User $otherCustomer;

    protected Pembayaran $payment;

    protected Invoice $invoice;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = User::factory()->create(['role' => 'customer']);
        $this->otherCustomer = User::factory()->create(['role' => 'customer']);

        $unique = uniqid();

        $this->payment = Pembayaran::create([
            'payment_code' => 'PAY-TEST-'.$unique,
            'customer_id' => $this->customer->id,
            'type' => 'Pengujian Sampel',
            'amount' => 500000,
            'payment_method' => 'Payment Gateway',
            'status' => 'Menunggu Pembayaran',
        ]);

        $this->invoice = Invoice::create([
            'invoice_number' => 'INV/TEST/'.$unique,
            'customer_id' => $this->customer->id,
            'payment_id' => $this->payment->id,
            'subtotal' => 500000,
            'total' => 500000,
            'status' => 'Belum Lunas',
        ]);
    }

    /** AC-01, AC-03, AC-12: Klik Bayar tidak langsung membuat payment berhasil saat unconfigured */
    public function test_customer_click_pay_without_server_key_does_not_mark_success(): void
    {
        Config::set('services.payment_gateway.server_key', null);

        $response = $this->actingAs($this->customer)->postJson(route('customer.payments.process', $this->payment));

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
        ]);

        $this->payment->refresh();
        $this->invoice->refresh();

        $this->assertEquals('Menunggu Pembayaran', $this->payment->status, 'Payment MUST remain Menunggu Pembayaran');
        $this->assertEquals('Belum Lunas', $this->invoice->status, 'Invoice MUST remain Belum Lunas');
    }

    /** AC-02, AC-03: Klik Bayar mengembalikan token/URL dan status tetap Menunggu Pembayaran */
    public function test_customer_click_pay_with_gateway_returns_redirect_url_and_remains_pending(): void
    {
        Config::set('services.payment_gateway.server_key', 'SB-Mid-server-testkey');
        Config::set('services.payment_gateway.is_production', false);

        Http::fake([
            'https://app.sandbox.midtrans.com/snap/v1/transactions' => Http::response([
                'token' => 'snap-token-abc123xyz',
                'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v2/vtweb/snap-token-abc123xyz',
            ], 201),
        ]);

        $response = $this->actingAs($this->customer)->postJson(route('customer.payments.process', $this->payment));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'status' => 'Menunggu Pembayaran',
            'token' => 'snap-token-abc123xyz',
            'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v2/vtweb/snap-token-abc123xyz',
        ]);

        $this->payment->refresh();
        $this->invoice->refresh();

        $this->assertEquals('Menunggu Pembayaran', $this->payment->status, 'Payment MUST NOT directly succeed');
        $this->assertEquals('Belum Lunas', $this->invoice->status);
    }

    /** AC-07: Customer lain dilarang memproses pembayaran orang lain (IDOR Protection) */
    public function test_other_customer_cannot_process_payment(): void
    {
        $response = $this->actingAs($this->otherCustomer)->postJson(route('customer.payments.process', $this->payment));

        $response->assertStatus(403);
    }

    /** AC-04, AC-08, AC-09: Webhook valid dengan signature yang benar berhasil menyelesaikan pembayaran dan melunasi invoice */
    public function test_valid_webhook_marks_payment_and_invoice_as_successful(): void
    {
        $serverKey = 'SB-Mid-server-testkey';
        Config::set('services.payment_gateway.server_key', $serverKey);

        $orderId = $this->payment->payment_code;
        $statusCode = '200';
        $grossAmount = '500000.00';
        $signatureKey = hash('sha512', $orderId.$statusCode.$grossAmount.$serverKey);

        $response = $this->postJson(route('payment.webhook'), [
            'order_id' => $orderId,
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'signature_key' => $signatureKey,
            'transaction_status' => 'settlement',
            'transaction_id' => 'PG-TEST-TRX-001',
            'payment_type' => 'qris',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $this->payment->refresh();
        $this->invoice->refresh();

        $this->assertEquals('Berhasil', $this->payment->status);
        $this->assertEquals('PG-TEST-TRX-001', $this->payment->payment_reference);
        $this->assertEquals('Lunas', $this->invoice->status);
    }

    /** AC-05: Webhook dengan signature palsu / tidak valid ditolak (403) */
    public function test_invalid_signature_webhook_is_rejected(): void
    {
        Config::set('services.payment_gateway.server_key', 'SB-Mid-server-testkey');

        $response = $this->postJson(route('payment.webhook'), [
            'order_id' => $this->payment->payment_code,
            'status_code' => '200',
            'gross_amount' => '500000.00',
            'signature_key' => 'invalid-fake-signature-key-123',
            'transaction_status' => 'settlement',
        ]);

        $response->assertStatus(403);
        $response->assertJson([
            'success' => false,
            'message' => 'Tanda tangan digital webhook tidak valid.',
        ]);

        $this->payment->refresh();
        $this->assertEquals('Menunggu Pembayaran', $this->payment->status);
    }

    /** AC-06: Webhook dengan nominal tidak cocok dengan database ditolak (400) */
    public function test_mismatched_amount_webhook_is_rejected(): void
    {
        $serverKey = 'SB-Mid-server-testkey';
        Config::set('services.payment_gateway.server_key', $serverKey);

        $orderId = $this->payment->payment_code;
        $statusCode = '200';
        $tamperedAmount = '50000.00'; // Hanya 50rb padahal tagihan 500rb
        $signatureKey = hash('sha512', $orderId.$statusCode.$tamperedAmount.$serverKey);

        $response = $this->postJson(route('payment.webhook'), [
            'order_id' => $orderId,
            'status_code' => $statusCode,
            'gross_amount' => $tamperedAmount,
            'signature_key' => $signatureKey,
            'transaction_status' => 'settlement',
        ]);

        $response->assertStatus(400);
        $response->assertJson([
            'success' => false,
            'message' => 'Nominal transaksi tidak cocok dengan tagihan database.',
        ]);

        $this->payment->refresh();
        $this->assertEquals('Menunggu Pembayaran', $this->payment->status);
    }

    /** AC-10: Webhook duplicate tidak mengeksekusi stok atau status ganda (Idempotent) */
    public function test_duplicate_webhook_is_idempotent(): void
    {
        $serverKey = 'SB-Mid-server-testkey';
        Config::set('services.payment_gateway.server_key', $serverKey);

        $u = uniqid();

        $equipment = Peralatan::create([
            'kode' => 'EQ-P-'.$u,
            'nama' => 'Spektrofotometer Portabel',
            'kategori' => 'Instrumen',
            'jumlah' => 10,
            'stok_minimum' => 2,
            'status' => 'Tersedia',
            'harga_sewa_per_hari' => 100000,
            'biaya_deposit' => 300000,
        ]);

        $borrowing = Peminjaman::create([
            'kode_peminjaman' => 'PJM-P-'.$u,
            'customer_id' => $this->customer->id,
            'tanggal_mulai' => now(),
            'tanggal_selesai' => now()->addDays(3),
            'tujuan' => 'Riset',
            'biaya_deposit' => 300000,
            'status' => 'Disetujui',
        ]);

        $borrowing->borrowingDetails()->create([
            'equipment_id' => $equipment->id,
            'quantity' => 2,
        ]);

        $borrowPayment = Pembayaran::create([
            'kode_pembayaran' => 'PAY-BORROW-'.$u,
            'customer_id' => $this->customer->id,
            'id_peminjaman' => $borrowing->id,
            'tipe' => 'Deposit Peminjaman',
            'jumlah_bayar' => 300000,
            'metode_pembayaran' => 'Payment Gateway',
            'status' => 'Menunggu Pembayaran',
        ]);

        $orderId = $borrowPayment->payment_code;
        $statusCode = '200';
        $grossAmount = '300000.00';
        $signatureKey = hash('sha512', $orderId.$statusCode.$grossAmount.$serverKey);

        $payload = [
            'order_id' => $orderId,
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'signature_key' => $signatureKey,
            'transaction_status' => 'settlement',
            'transaction_id' => 'PG-MID-999',
            'payment_type' => 'bca_va',
        ];

        // Webhook #1
        $res1 = $this->postJson(route('payment.webhook'), $payload);
        $res1->assertStatus(200);

        $equipment->refresh();
        $this->assertEquals(8, $equipment->jumlah, 'Stock decreased from 10 to 8');

        // Webhook #2 (Duplicate Webhook)
        $res2 = $this->postJson(route('payment.webhook'), $payload);
        $res2->assertStatus(200);

        $equipment->refresh();
        $this->assertEquals(8, $equipment->jumlah, 'Stock must NOT decrease again (idempotent!)');
    }

    /** Test duplicate order_id from gateway is automatically retried with suffix */
    public function test_duplicate_order_id_from_gateway_is_automatically_retried_with_suffix(): void
    {
        Config::set('services.payment_gateway.server_key', 'SB-Mid-server-testkey');
        Config::set('services.payment_gateway.is_production', false);

        $attempt = 0;
        Http::fake(function ($request) use (&$attempt) {
            $attempt++;
            if ($attempt === 1) {
                return Http::response([
                    'error_messages' => ['transaction_details.order_id has already been taken'],
                ], 400);
            }

            return Http::response([
                'token' => 'snap-token-retry-success',
                'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v2/vtweb/snap-token-retry-success',
            ], 201);
        });

        $response = $this->actingAs($this->customer)->postJson(route('customer.payments.process', $this->payment));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'token' => 'snap-token-retry-success',
        ]);
        $this->assertEquals(2, $attempt, 'Must have retried with a new order ID');
    }

    /** Test webhook with retry suffix order_id correctly finds and updates payment */
    public function test_webhook_with_retry_suffix_order_id_correctly_finds_and_updates_payment(): void
    {
        $serverKey = 'SB-Mid-server-testkey';
        Config::set('services.payment_gateway.server_key', $serverKey);

        $orderIdWithSuffix = $this->payment->payment_code.'-1791234567';
        $statusCode = '200';
        $grossAmount = '500000.00';
        $signatureKey = hash('sha512', $orderIdWithSuffix.$statusCode.$grossAmount.$serverKey);

        $response = $this->postJson(route('payment.webhook'), [
            'order_id' => $orderIdWithSuffix,
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'signature_key' => $signatureKey,
            'transaction_status' => 'settlement',
            'transaction_id' => 'PG-RETRY-TRX-002',
            'payment_type' => 'qris',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $this->payment->refresh();
        $this->invoice->refresh();

        $this->assertEquals('Berhasil', $this->payment->status);
        $this->assertEquals('PG-RETRY-TRX-002', $this->payment->payment_reference);
        $this->assertEquals('Lunas', $this->invoice->status);
    }
}
