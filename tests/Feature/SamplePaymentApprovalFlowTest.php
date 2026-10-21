<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\Notification;
use App\Models\Parameter;
use App\Models\Pembayaran;
use App\Models\Sample;
use App\Models\User;
use App\Services\PaymentGatewayService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SamplePaymentApprovalFlowTest extends TestCase
{
    use DatabaseTransactions;

    protected User $customer;

    protected User $staff;

    protected User $otherCustomer;

    protected Parameter $parameter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = User::factory()->create(['role' => 'customer']);
        $this->staff = User::factory()->create(['role' => 'staff']);
        $this->otherCustomer = User::factory()->create(['role' => 'customer']);

        $this->parameter = Parameter::first() ?? Parameter::create([
            'name' => 'BOD (Biochemical Oxygen Demand)',
            'category' => 'Kimia',
            'unit' => 'mg/L',
            'test_method' => 'SNI 6989.72:2009',
            'price' => 120000,
        ]);
    }

    /**
     * 1. Customer mengajukan sampel dan invoice dibuat sesuai mekanisme yang ada.
     */
    public function test_customer_submits_sample_and_invoice_is_created_with_pending_payment(): void
    {
        $response = $this->actingAs($this->customer)->post(route('customer.samples.store'), [
            'sample_type' => 'Air Sungai',
            'sampling_location' => 'Titik Hulu Sungai',
            'sampling_date' => now()->toDateString(),
            'parameters' => [$this->parameter->id],
            'notes' => 'Pengujian rutin kualitas air baku',
        ]);

        $sample = Sample::where('customer_id', $this->customer->id)->latest()->first();
        $this->assertNotNull($sample);
        $response->assertRedirect(route('customer.samples.show', $sample));

        // Sample status: Diajukan
        $this->assertEquals('Diajukan', $sample->status);

        // Payment status: Menunggu Pembayaran
        $payment = $sample->payments()->latest()->first();
        $this->assertNotNull($payment);
        $this->assertEquals('Menunggu Pembayaran', $payment->status);
        $this->assertEquals($this->parameter->price, (float) $payment->amount);

        // Invoice status: Belum Lunas
        $invoice = Invoice::where('id_pembayaran', $payment->id)->first();
        $this->assertNotNull($invoice);
        $this->assertEquals('Belum Lunas', $invoice->status);
        $this->assertEquals($this->parameter->price, (float) $invoice->total);
    }

    /**
     * 2. Staff mencoba menyetujui sampel dengan pembayaran pending -> ditolak.
     */
    public function test_staff_cannot_receive_sample_when_payment_is_pending(): void
    {
        // Customer creates sample with pending payment
        $this->actingAs($this->customer)->post(route('customer.samples.store'), [
            'sample_type' => 'Air Tanah',
            'sampling_location' => 'Sumur Pantau 1',
            'sampling_date' => now()->toDateString(),
            'parameters' => [$this->parameter->id],
        ]);

        $sample = Sample::where('customer_id', $this->customer->id)->latest()->first();
        $this->assertNotNull($sample);

        // Staff attempts to receive sample while payment is pending
        $receiveResponse = $this->actingAs($this->staff)->post(route('staff.samples.receive', $sample), [
            'received_date' => now()->toDateString(),
        ]);

        $receiveResponse->assertSessionHasErrors('error');
        $sample->refresh();
        $this->assertEquals('Diajukan', $sample->status, 'Sample status must remain Diajukan when payment is pending');
    }

    /**
     * 3. Staff mencoba menyetujui sampel dengan pembayaran failed -> ditolak.
     */
    public function test_staff_cannot_receive_sample_when_payment_is_failed(): void
    {
        $this->actingAs($this->customer)->post(route('customer.samples.store'), [
            'sample_type' => 'Air Limbah',
            'sampling_location' => 'Outlet IPAL',
            'sampling_date' => now()->toDateString(),
            'parameters' => [$this->parameter->id],
        ]);

        $sample = Sample::where('customer_id', $this->customer->id)->latest()->first();
        $payment = $sample->payments()->latest()->first();

        // Simulate payment failed from gateway
        app(PaymentGatewayService::class)->handleFailedPayment($payment, 'Gagal');
        $this->assertEquals('Gagal', $payment->fresh()->status);

        // Staff attempts to receive sample
        $receiveResponse = $this->actingAs($this->staff)->post(route('staff.samples.receive', $sample), [
            'received_date' => now()->toDateString(),
        ]);

        $receiveResponse->assertSessionHasErrors('error');
        $sample->refresh();
        $this->assertEquals('Diajukan', $sample->status, 'Sample status must remain Diajukan when payment failed');
    }

    /**
     * 4. Staff menyetujui sampel setelah pembayaran paid dan terverifikasi -> berhasil jika validasi lainnya terpenuhi.
     */
    public function test_staff_can_receive_sample_after_payment_is_verified_paid(): void
    {
        $this->actingAs($this->customer)->post(route('customer.samples.store'), [
            'sample_type' => 'Air Minum Isi Ulang',
            'sampling_location' => 'Depot Air Bersih',
            'sampling_date' => now()->toDateString(),
            'parameters' => [$this->parameter->id],
        ]);

        $sample = Sample::where('customer_id', $this->customer->id)->latest()->first();
        $payment = $sample->payments()->latest()->first();

        // Verified payment settlement
        app(PaymentGatewayService::class)->handleSuccessfulPayment($payment, 'TRX-VERIFIED-'.rand(), 'qris');
        $this->assertEquals('Berhasil', $payment->fresh()->status);
        $this->assertEquals('Lunas', $payment->fresh()->invoice->status);

        // Staff accepts/receives sample
        $receiveResponse = $this->actingAs($this->staff)->post(route('staff.samples.receive', $sample), [
            'received_date' => now()->toDateString(),
        ]);

        $receiveResponse->assertSessionHas('success');
        $sample->refresh();
        $this->assertEquals('Diterima', $sample->status);
        $this->assertNotNull($sample->received_date);
    }

    /**
     * 5. Pembayaran sukses tidak otomatis mengubah status sampel menjadi Diterima.
     */
    public function test_successful_payment_does_not_automatically_mark_sample_as_received(): void
    {
        $this->actingAs($this->customer)->post(route('customer.samples.store'), [
            'sample_type' => 'Air Danau',
            'sampling_location' => 'Danau Toba Dermaga',
            'sampling_date' => now()->toDateString(),
            'parameters' => [$this->parameter->id],
        ]);

        $sample = Sample::where('customer_id', $this->customer->id)->latest()->first();
        $payment = $sample->payments()->latest()->first();

        // Customer completes payment
        app(PaymentGatewayService::class)->handleSuccessfulPayment($payment, 'TRX-SETTLED-'.rand(), 'bank_transfer');

        $payment->refresh();
        $sample->refresh();

        $this->assertEquals('Berhasil', $payment->status);
        $this->assertEquals('Diajukan', $sample->status, 'Sample status MUST stay Diajukan until Staff explicitly inspects and receives it');
    }

    /**
     * 6. Customer atau role yang tidak berwenang tidak dapat menyetujui sampel.
     */
    public function test_unauthorized_roles_cannot_receive_sample(): void
    {
        $this->actingAs($this->customer)->post(route('customer.samples.store'), [
            'sample_type' => 'Air Sumur Gali',
            'sampling_location' => 'Pemukiman Warga',
            'sampling_date' => now()->toDateString(),
            'parameters' => [$this->parameter->id],
        ]);

        $sample = Sample::where('customer_id', $this->customer->id)->latest()->first();
        $payment = $sample->payments()->latest()->first();
        app(PaymentGatewayService::class)->handleSuccessfulPayment($payment, 'TRX-AUTH-'.rand(), 'bank_transfer');

        // Customer attempts to receive sample
        $custResponse = $this->actingAs($this->customer)->post(route('staff.samples.receive', $sample), [
            'received_date' => now()->toDateString(),
        ]);
        $custResponse->assertStatus(403);

        // Guest attempts to receive sample
        auth()->logout();
        $guestResponse = $this->post(route('staff.samples.receive', $sample), [
            'received_date' => now()->toDateString(),
        ]);
        $guestResponse->assertRedirect(route('login'));
    }

    /**
     * 7. Request palsu ke halaman sukses payment gateway tidak membuat pembayaran menjadi paid.
     */
    public function test_fake_success_url_parameters_does_not_mark_payment_as_paid(): void
    {
        $this->actingAs($this->customer)->post(route('customer.samples.store'), [
            'sample_type' => 'Air Limbah Domestik',
            'sampling_location' => 'Outlet Apartemen',
            'sampling_date' => now()->toDateString(),
            'parameters' => [$this->parameter->id],
        ]);

        $sample = Sample::where('customer_id', $this->customer->id)->latest()->first();
        $payment = $sample->payments()->latest()->first();

        // Customer navigates to URL with fake success params
        $res = $this->actingAs($this->customer)->get(route('customer.samples.show', [
            'sample' => $sample,
            'status' => 'success',
            'transaction_status' => 'settlement',
            'order_id' => $payment->payment_code,
        ]));

        $res->assertStatus(200);
        $this->assertEquals('Menunggu Pembayaran', $payment->fresh()->status, 'Payment must NOT be marked paid from fake query params');
    }

    /**
     * 8. Webhook yang sama diproses berulang kali tanpa efek duplikat (Idempotency & Concurrency).
     */
    public function test_duplicate_payment_settlement_is_idempotent(): void
    {
        $this->actingAs($this->customer)->post(route('customer.samples.store'), [
            'sample_type' => 'Air Kolam',
            'sampling_location' => 'Kolam Retensi',
            'sampling_date' => now()->toDateString(),
            'parameters' => [$this->parameter->id],
        ]);

        $sample = Sample::where('customer_id', $this->customer->id)->latest()->first();
        $payment = $sample->payments()->latest()->first();

        $service = app(PaymentGatewayService::class);

        // Record initial notification count
        $notifCountBefore = Notification::count();

        // First execution (webhook pertama tiba)
        $service->handleSuccessfulPayment($payment, 'TRX-IDEMP-01', 'qris');
        $this->assertEquals('Berhasil', $payment->fresh()->status);
        $paidAtFirst = $payment->fresh()->paid_at;

        $notifCountAfterFirst = Notification::count();
        $this->assertGreaterThan($notifCountBefore, $notifCountAfterFirst, 'Notifikasi pembayaran harus terbuat pada eksekusi pertama');

        // Second execution: simultaneous / concurrent simulation with stale pre-commit instance
        // Instance dibuat seolah-olah dibaca dari database sebelum eksekusi pertama selesai commit (status masih 'Menunggu Pembayaran')
        $stalePayment = $payment->replicate();
        $stalePayment->id = $payment->id;
        $stalePayment->exists = true;
        $stalePayment->status = 'Menunggu Pembayaran';

        $service->handleSuccessfulPayment($stalePayment, 'TRX-IDEMP-01', 'qris');

        // Pastikan status tetap 'Berhasil' dan paid_at tidak berubah
        $this->assertEquals('Berhasil', $payment->fresh()->status);
        $this->assertEquals($paidAtFirst->toIso8601String(), $payment->fresh()->paid_at->toIso8601String());

        // Pastikan tidak ada notifikasi duplikat yang terkirim
        $this->assertEquals($notifCountAfterFirst, Notification::count(), 'Tidak boleh ada notifikasi duplikat meskipun instance model belum di-refresh');

        // Ensure exactly 1 invoice remains
        $this->assertEquals(1, Invoice::where('id_pembayaran', $payment->id)->count());
    }

    /**
     * 9. Sampel dengan biaya 0 (bebas biaya) dapat diterima staf tanpa pembayaran.
     */
    public function test_zero_cost_sample_can_be_received_without_payment(): void
    {
        $freeParam = Parameter::create([
            'name' => 'Suhu Udara Lapangan',
            'category' => 'Fisika',
            'unit' => '°C',
            'test_method' => 'In-situ',
            'price' => 0,
        ]);

        $this->actingAs($this->customer)->post(route('customer.samples.store'), [
            'sample_type' => 'Udara Ambien',
            'sampling_location' => 'Halaman Parkir',
            'sampling_date' => now()->toDateString(),
            'parameters' => [$freeParam->id],
        ]);

        $sample = Sample::where('customer_id', $this->customer->id)->latest()->first();
        $this->assertEquals(0, $sample->calculateTotalPrice());

        $receiveRes = $this->actingAs($this->staff)->post(route('staff.samples.receive', $sample), [
            'received_date' => now()->toDateString(),
        ]);

        $receiveRes->assertSessionHas('success');
        $this->assertEquals('Diterima', $sample->fresh()->status);
    }
}
