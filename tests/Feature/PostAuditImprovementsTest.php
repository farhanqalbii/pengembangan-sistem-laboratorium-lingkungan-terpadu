<?php

namespace Tests\Feature;

use App\Models\DetailPeminjaman;
use App\Models\LogAktivitas;
use App\Models\Notification;
use App\Models\Parameter;
use App\Models\Pembayaran;
use App\Models\Peminjaman;
use App\Models\Peralatan;
use App\Models\RiwayatStok;
use App\Models\Sample;
use App\Models\SampleParameter;
use App\Models\Sertifikat;
use App\Models\User;
use App\Services\PaymentGatewayService;
use App\Services\SimpleXlsxService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PostAuditImprovementsTest extends TestCase
{
    use DatabaseTransactions;

    protected User $customer;

    protected User $staff;

    protected User $admin;

    protected User $manager;

    protected Peralatan $equipment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = User::factory()->create(['role' => 'customer']);
        $this->staff = User::factory()->create(['role' => 'staff']);
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->manager = User::factory()->create(['role' => 'manager']);

        $this->equipment = Peralatan::create([
            'kode' => 'EQ-AUDIT-'.rand(1000, 9999),
            'nama' => 'pH Meter Digital Akurat',
            'kategori' => 'Elektrometri',
            'merek' => 'Hanna Instruments',
            'spesifikasi' => 'HI-98107 pH range 0-14',
            'tahun_pengadaan' => 2024,
            'jumlah' => 10,
            'lokasi_penyimpanan' => 'Lemari A-1',
            'stok_minimum' => 2,
            'status' => 'Tersedia',
            'harga_sewa_per_hari' => 50000,
            'biaya_deposit' => 100000,
        ]);
    }

    /**
     * Helper to create an active borrowing in Dipinjam status
     */
    protected function createActiveBorrowing(int $qty = 2, string $status = 'Dipinjam'): Peminjaman
    {
        $borrowing = Peminjaman::create([
            'borrow_code' => 'BRW-TEST-'.rand(1000, 9999),
            'customer_id' => $this->customer->id,
            'start_date' => now()->subDays(3)->toDateString(),
            'end_date' => now()->addDays(2)->toDateString(),
            'purpose' => 'Pengujian Lapangan',
            'status' => $status,
            'deposit_amount' => $this->equipment->deposit_amount * $qty,
        ]);

        DetailPeminjaman::create([
            'borrowing_id' => $borrowing->id,
            'equipment_id' => $this->equipment->id,
            'quantity' => $qty,
        ]);

        return $borrowing;
    }

    // ==========================================
    // PRIORITAS 2 — PENGEMBALIAN PEMINJAMAN ALAT
    // ==========================================

    public function test_borrowing_return_normal_restores_stock_and_sets_status_dikembalikan(): void
    {
        $borrowing = $this->createActiveBorrowing(2, 'Dipinjam');
        $initialStock = $this->equipment->fresh()->quantity; // 10

        $detail = $borrowing->borrowingDetails->first();
        $response = $this->actingAs($this->staff)->post(route('staff.borrowings.return', $borrowing), [
            'actual_return_date' => now()->toDateString(),
            'conditions' => [
                [
                    'detail_id' => $detail->id,
                    'condition_return' => 'Baik',
                    'notes' => 'Alat bersih dan berfungsi',
                ],
            ],
            'fine_amount' => 0,
        ]);

        $response->assertRedirect(route('staff.borrowings.show', $borrowing));
        $borrowing->refresh();
        $this->assertEquals('Dikembalikan', $borrowing->status);
        $this->assertEquals($initialStock + 2, $this->equipment->fresh()->quantity);
    }

    public function test_borrowing_return_repeated_request_is_rejected_and_stock_not_double_restored(): void
    {
        $borrowing = $this->createActiveBorrowing(2, 'Dipinjam');
        $detail = $borrowing->borrowingDetails->first();

        // 1st return succeeds
        $this->actingAs($this->staff)->post(route('staff.borrowings.return', $borrowing), [
            'actual_return_date' => now()->toDateString(),
            'conditions' => [
                [
                    'detail_id' => $detail->id,
                    'condition_return' => 'Baik',
                    'notes' => 'Alat bersih',
                ],
            ],
            'fine_amount' => 0,
        ]);

        $stockAfterFirstReturn = $this->equipment->fresh()->quantity;
        $borrowing->refresh();
        $this->assertEquals('Dikembalikan', $borrowing->status);

        // 2nd return must be rejected and must NOT increment stock again
        $repeatResponse = $this->actingAs($this->staff)->post(route('staff.borrowings.return', $borrowing), [
            'actual_return_date' => now()->toDateString(),
            'conditions' => [
                [
                    'detail_id' => $detail->id,
                    'condition_return' => 'Baik',
                    'notes' => 'Mencoba submit ulang',
                ],
            ],
            'fine_amount' => 0,
        ]);

        $repeatResponse->assertSessionHas('error');
        $this->assertEquals($stockAfterFirstReturn, $this->equipment->fresh()->quantity, 'Stock should NOT be incremented on duplicate return');
    }

    public function test_borrowing_return_fails_when_status_is_invalid(): void
    {
        $borrowing = $this->createActiveBorrowing(2, 'Diajukan');
        $stockBefore = $this->equipment->fresh()->quantity;
        $detail = $borrowing->borrowingDetails->first();

        $response = $this->actingAs($this->staff)->post(route('staff.borrowings.return', $borrowing), [
            'actual_return_date' => now()->toDateString(),
            'conditions' => [
                [
                    'detail_id' => $detail->id,
                    'condition_return' => 'Baik',
                ],
            ],
        ]);

        $response->assertSessionHas('error');
        $borrowing->refresh();
        $this->assertEquals('Diajukan', $borrowing->status);
        $this->assertEquals($stockBefore, $this->equipment->fresh()->quantity);
    }

    // ==========================================
    // PRIORITAS 3 — PERSETUJUAN DAN STOK PEMINJAMAN
    // ==========================================

    public function test_borrowing_approval_only_allowed_from_diajukan(): void
    {
        $borrowing = $this->createActiveBorrowing(2, 'Disetujui');

        $response = $this->actingAs($this->staff)->post(route('staff.borrowings.approve', $borrowing));
        $response->assertSessionHas('error');
    }

    public function test_borrowing_duplicate_approval_does_not_create_duplicate_payment_or_invoice(): void
    {
        $borrowing = $this->createActiveBorrowing(2, 'Diajukan');

        // First approval
        $res1 = $this->actingAs($this->staff)->post(route('staff.borrowings.approve', $borrowing));
        $res1->assertSessionHas('success');
        $borrowing->refresh();
        $this->assertEquals('Disetujui', $borrowing->status);

        $initialPaymentsCount = Pembayaran::where('id_peminjaman', $borrowing->id)->count();
        $this->assertEquals(1, $initialPaymentsCount);

        // Repeated approval attempt
        $res2 = $this->actingAs($this->staff)->post(route('staff.borrowings.approve', $borrowing));
        $res2->assertSessionHas('error');

        $finalPaymentsCount = Pembayaran::where('id_peminjaman', $borrowing->id)->count();
        $this->assertEquals(1, $finalPaymentsCount, 'Repeated approval must not create duplicate payment rows');
    }

    public function test_borrowing_approval_fails_if_equipment_stock_insufficient(): void
    {
        // Deplete equipment stock
        $this->equipment->update(['jumlah' => 0]);

        $borrowing = $this->createActiveBorrowing(2, 'Diajukan');

        $response = $this->actingAs($this->staff)->post(route('staff.borrowings.approve', $borrowing));
        $response->assertSessionHas('error');

        $borrowing->refresh();
        $this->assertEquals('Diajukan', $borrowing->status);
    }

    public function test_borrowing_deposit_settlement_does_not_make_stock_negative_if_depleted(): void
    {
        // Equipment only has 1 unit available
        $this->equipment->update(['jumlah' => 1]);

        $borrowing = $this->createActiveBorrowing(2, 'Disetujui');
        $payment = Pembayaran::create([
            'kode_pembayaran' => 'PAY-DEP-TEST-'.rand(1000, 9999),
            'customer_id' => $this->customer->id,
            'id_peminjaman' => $borrowing->id,
            'tipe' => 'Deposit Peminjaman',
            'jumlah_bayar' => 200000,
            'status' => 'Menunggu Pembayaran',
        ]);

        // Attempt gateway settlement when stock (1) is less than required (2)
        $service = app(PaymentGatewayService::class);
        $service->handleSuccessfulPayment($payment, 'GW-TRX-DEP-'.rand(1000, 9999), 'bank_transfer');

        $borrowing->refresh();
        $this->equipment->refresh();

        // Payment itself is confirmed received, but borrowing MUST NOT be activated to Dipinjam with negative stock
        $this->assertEquals('Berhasil', $payment->fresh()->status);
        $this->assertNotEquals('Dipinjam', $borrowing->status);
        $this->assertGreaterThanOrEqual(0, $this->equipment->quantity, 'Stock must not become negative');
        $this->assertStringContainsString('Kendala Stok', (string) $borrowing->catatan);
    }

    // ==========================================
    // PRIORITAS 4 — PERSETUJUAN DAN PENERBITAN COA
    // ==========================================

    protected function createSampleForVerification(): Sample
    {
        $parameter = Parameter::first() ?? Parameter::create([
            'name' => 'BOD Test '.rand(100, 999),
            'category' => 'Kimia',
            'unit' => 'mg/L',
            'test_method' => 'SNI',
            'price' => 100000,
        ]);

        $sample = Sample::create([
            'sample_code' => 'SMP-TEST-'.rand(1000, 9999),
            'customer_id' => $this->customer->id,
            'sample_type' => 'Air Bersih',
            'sampling_location' => 'Sumber Mata Air',
            'sampling_date' => now()->toDateString(),
            'request_date' => now()->toDateString(),
            'status' => 'Menunggu Verifikasi',
        ]);

        SampleParameter::create([
            'sample_id' => $sample->id,
            'parameter_id' => $parameter->id,
            'analysis_status' => 'Selesai Analisis',
            'result_value' => '4.2',
        ]);

        return $sample;
    }

    public function test_manager_verifikasi_approve_valid_sample_generates_certificate(): void
    {
        $sample = $this->createSampleForVerification();

        $response = $this->actingAs($this->manager)->post(route('manager.verifications.approve', $sample));
        $response->assertRedirect(route('manager.verifications.show', $sample));

        $sample->refresh();
        $this->assertEquals('Selesai', $sample->status);
        $this->assertNotNull($sample->certificate);
    }

    public function test_manager_verifikasi_approve_fails_on_invalid_status(): void
    {
        $sample = $this->createSampleForVerification();
        $sample->update(['status' => 'Diajukan']);

        $response = $this->actingAs($this->manager)->post(route('manager.verifications.approve', $sample));
        $response->assertSessionHasErrors('error');

        $this->assertNull(Sertifikat::where('sample_id', $sample->id)->first());
    }

    public function test_manager_verifikasi_duplicate_approval_does_not_create_duplicate_certificate(): void
    {
        $sample = $this->createSampleForVerification();

        // 1st approval
        $this->actingAs($this->manager)->post(route('manager.verifications.approve', $sample));
        $sample->refresh();
        $this->assertEquals('Selesai', $sample->status);
        $certCountAfterFirst = Sertifikat::where('sample_id', $sample->id)->count();
        $this->assertEquals(1, $certCountAfterFirst);

        // 2nd approval attempt
        $res2 = $this->actingAs($this->manager)->post(route('manager.verifications.approve', $sample));
        $res2->assertSessionHasErrors('error');

        $certCountAfterSecond = Sertifikat::where('sample_id', $sample->id)->count();
        $this->assertEquals(1, $certCountAfterSecond, 'Certificate must not be created twice for the same sample');
    }

    // ==========================================
    // PRIORITAS 5 — IDEMPOTENSI WEBHOOK PEMBAYARAN
    // ==========================================

    public function test_webhook_repeated_failed_payment_is_idempotent_without_duplicate_notifications(): void
    {
        $sample = $this->createSampleForVerification();
        $payment = Pembayaran::create([
            'kode_pembayaran' => 'PAY-FAIL-'.rand(1000, 9999),
            'customer_id' => $this->customer->id,
            'sample_id' => $sample->id,
            'tipe' => 'Pengujian Sampel',
            'jumlah_bayar' => 100000,
            'status' => 'Menunggu Pembayaran',
        ]);

        $service = app(PaymentGatewayService::class);

        // 1st failure call
        $service->handleFailedPayment($payment, 'Gagal');
        $notifCount1 = Notification::where('user_id', $this->customer->id)
            ->where('title', 'like', '%Gagal%')
            ->count();
        $logCount1 = LogAktivitas::where('deskripsi', 'like', "%{$payment->kode_pembayaran}%")
            ->count();

        $this->assertEquals('Gagal', $payment->fresh()->status);
        $this->assertEquals(1, $notifCount1);
        $this->assertEquals(1, $logCount1);

        // 2nd failure call (identical failure retry)
        $service->handleFailedPayment($payment, 'Gagal');
        $notifCount2 = Notification::where('user_id', $this->customer->id)
            ->where('title', 'like', '%Gagal%')
            ->count();
        $logCount2 = LogAktivitas::where('deskripsi', 'like', "%{$payment->kode_pembayaran}%")
            ->count();

        $this->assertEquals(1, $notifCount2, 'Failed payment notification must be idempotent');
        $this->assertEquals(1, $logCount2, 'Failed payment activity log must be idempotent');
    }

    public function test_webhook_credit_card_capture_with_challenge_status_is_not_settled(): void
    {
        $serverKey = 'SB-Mid-server-testkey12345';
        config(['services.payment_gateway.server_key' => $serverKey]);
        app()->forgetInstance(PaymentGatewayService::class);

        $payment = Pembayaran::create([
            'kode_pembayaran' => 'PAY-CC-'.rand(1000, 9999),
            'customer_id' => $this->customer->id,
            'tipe' => 'Pengujian Sampel',
            'jumlah_bayar' => 150000,
            'status' => 'Menunggu Pembayaran',
        ]);

        $orderId = $payment->kode_pembayaran;
        $statusCode = '200';
        $grossAmount = '150000.00';
        $signature = hash('sha512', $orderId.$statusCode.$grossAmount.$serverKey);

        $payload = [
            'order_id' => $orderId,
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'signature_key' => $signature,
            'transaction_status' => 'capture',
            'fraud_status' => 'challenge', // Under FDS review
            'payment_type' => 'credit_card',
            'transaction_id' => 'CC-CHALLENGE-TRX-'.rand(1000, 9999),
        ];

        $response = $this->postJson(route('payment.webhook'), $payload);
        $response->assertStatus(200);

        // Payment status must remain Menunggu Pembayaran because it is challenged
        $this->assertEquals('Menunggu Pembayaran', $payment->fresh()->status);
    }

    public function test_webhook_credit_card_capture_with_accept_status_is_settled(): void
    {
        $serverKey = 'SB-Mid-server-testkey12345';
        config(['services.payment_gateway.server_key' => $serverKey]);
        app()->forgetInstance(PaymentGatewayService::class);

        $payment = Pembayaran::create([
            'kode_pembayaran' => 'PAY-CC-OK-'.rand(1000, 9999),
            'customer_id' => $this->customer->id,
            'tipe' => 'Pengujian Sampel',
            'jumlah_bayar' => 150000,
            'status' => 'Menunggu Pembayaran',
        ]);

        $orderId = $payment->kode_pembayaran;
        $statusCode = '200';
        $grossAmount = '150000.00';
        $signature = hash('sha512', $orderId.$statusCode.$grossAmount.$serverKey);

        $payload = [
            'order_id' => $orderId,
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'signature_key' => $signature,
            'transaction_status' => 'capture',
            'fraud_status' => 'accept', // Clean
            'payment_type' => 'credit_card',
            'transaction_id' => 'CC-ACCEPT-TRX-'.rand(1000, 9999),
        ];

        $response = $this->postJson(route('payment.webhook'), $payload);
        $response->assertStatus(200);

        $this->assertEquals('Berhasil', $payment->fresh()->status);
    }

    // ==========================================
    // PRIORITAS 6 — PERBAIKAN KECIL TAMBAHAN
    // ==========================================

    public function test_simple_xlsx_service_generates_valid_spreadsheet_without_eval(): void
    {
        $headers = ['Kode', 'Nama Alat', 'Jumlah', 'Status'];
        $rows = [
            ['EQ-001', 'Spektrofotometer UV-Vis', 3, 'Tersedia'],
            ['EQ-002', 'pH Meter Digital', 5, 'Tersedia'],
        ];

        $binary = SimpleXlsxService::generateRawContent($headers, $rows);

        $this->assertNotEmpty($binary);
        // Valid zip format starts with PK\x03\x04
        $this->assertStringStartsWith("PK\x03\x04", $binary, 'XLSX archive must start with valid ZIP magic bytes');
    }

    public function test_notification_mark_as_read_via_patch_route(): void
    {
        $notification = Notification::create([
            'user_id' => $this->customer->id,
            'title' => 'Tes Notifikasi Patch',
            'message' => 'Pesan pengujian metode PATCH',
            'link' => null,
            'read_at' => null,
        ]);

        $response = $this->actingAs($this->customer)->patch(route('notifications.read', $notification));
        $response->assertRedirect();

        $this->assertNotNull($notification->fresh()->read_at);
    }

    // ==========================================
    // WEEK 2 — ALOKASI STOK, PERPANJANGAN, INVENTORI, & PENGINGAT
    // ==========================================

    public function test_borrowing_request_fails_if_stock_exceeded_or_equipment_unavailable(): void
    {
        // 1. Melebihi stok yang ada
        $response = $this->actingAs($this->customer)->post(route('customer.borrowings.store'), [
            'equipment_id' => $this->equipment->id,
            'quantity' => 15, // stok fisik saat ini 10
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(3)->toDateString(),
            'purpose' => 'Penelitian Dosen',
        ]);
        $response->assertSessionHasErrors('quantity');

        // 2. Status alat bukan Tersedia (misal Dalam Servis)
        $this->equipment->update(['status' => 'Dalam Servis']);
        $responseUnavailable = $this->actingAs($this->customer)->post(route('customer.borrowings.store'), [
            'equipment_id' => $this->equipment->id,
            'quantity' => 2,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(3)->toDateString(),
            'purpose' => 'Penelitian Dosen',
        ]);
        $responseUnavailable->assertSessionHasErrors('equipment_id');
    }

    public function test_two_borrowings_competing_for_stock_prevent_over_allocation(): void
    {
        // Set stok fisik alat menjadi 5 unit
        $this->equipment->update(['jumlah' => 5, 'status' => 'Tersedia']);

        $customer2 = User::factory()->create(['role' => 'customer']);

        // Peminjaman 1 mengajukan 3 unit
        $borrowing1 = Peminjaman::create([
            'borrow_code' => 'BRW-COMPETE-1-'.rand(1000, 9999),
            'customer_id' => $this->customer->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(3)->toDateString(),
            'purpose' => 'Pengujian 1',
            'status' => 'Diajukan',
            'deposit_amount' => 300000,
        ]);
        DetailPeminjaman::create([
            'borrowing_id' => $borrowing1->id,
            'equipment_id' => $this->equipment->id,
            'quantity' => 3,
        ]);

        // Peminjaman 2 mengajukan 3 unit
        $borrowing2 = Peminjaman::create([
            'borrow_code' => 'BRW-COMPETE-2-'.rand(1000, 9999),
            'customer_id' => $customer2->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(3)->toDateString(),
            'purpose' => 'Pengujian 2',
            'status' => 'Diajukan',
            'deposit_amount' => 300000,
        ]);
        DetailPeminjaman::create([
            'borrowing_id' => $borrowing2->id,
            'equipment_id' => $this->equipment->id,
            'quantity' => 3,
        ]);

        // Staf menyetujui peminjaman 1 -> status menjadi Disetujui (reservasi aktif 3 unit)
        $approve1 = $this->actingAs($this->staff)->post(route('staff.borrowings.approve', $borrowing1));
        $approve1->assertSessionHas('success');
        $this->assertEquals('Disetujui', $borrowing1->fresh()->status);

        // Stok riil yang tersedia sekarang adalah 5 - 3 = 2 unit
        $this->assertEquals(2, $this->equipment->fresh()->available_stock);

        // Staf mencoba menyetujui peminjaman 2 -> harus DITOLAK karena stok tersedia (2) < dibutuhkan (3)
        $approve2 = $this->actingAs($this->staff)->post(route('staff.borrowings.approve', $borrowing2));
        $approve2->assertSessionHas('error');
        $this->assertEquals('Diajukan', $borrowing2->fresh()->status, 'Peminjaman 2 harus tetap Diajukan karena alokasi stok terbatas');
    }

    public function test_extension_validation_disallows_invalid_statuses_and_duplicate_pending(): void
    {
        // 1. Customer tidak bisa meminta perpanjangan jika status bukan Dipinjam (misal masih Diajukan)
        $borrowing = $this->createActiveBorrowing(2, 'Diajukan');
        $response1 = $this->actingAs($this->customer)->post(route('customer.borrowings.extend', $borrowing), [
            'extended_end_date' => now()->addDays(5)->toDateString(),
        ]);
        $response1->assertSessionHasErrors('status');

        // 2. Ubah ke Dipinjam -> permohonan perpanjangan pertama berhasil
        $borrowing->update(['status' => 'Dipinjam']);
        $response2 = $this->actingAs($this->customer)->post(route('customer.borrowings.extend', $borrowing), [
            'extended_end_date' => now()->addDays(5)->toDateString(),
        ]);
        $response2->assertSessionHas('success');
        $borrowing->refresh();
        $this->assertTrue((bool) $borrowing->perpanjangan_diajukan);
        $this->assertEquals('Pending', $borrowing->status_perpanjangan);

        // 3. Permohonan perpanjangan berulang ketika masih Pending ditolak
        $response3 = $this->actingAs($this->customer)->post(route('customer.borrowings.extend', $borrowing), [
            'extended_end_date' => now()->addDays(7)->toDateString(),
        ]);
        $response3->assertSessionHasErrors('status_perpanjangan');

        // 4. Persetujuan admin gagal jika status bukan Dipinjam (misal sudah Dikembalikan sebelumnya)
        $borrowing->update(['status' => 'Dikembalikan']);
        $originalEndDate = $borrowing->tanggal_selesai;
        $adminApprove = $this->actingAs($this->admin)->post(route('admin.borrowings.approve-extension', $borrowing));
        $adminApprove->assertSessionHasErrors(['status']);
        $borrowing->refresh();
        $this->assertEquals($originalEndDate->toDateString(), $borrowing->tanggal_selesai->toDateString(), 'Tanggal pengembalian tidak boleh berubah pada persetujuan tidak sah');
        $this->assertNotEquals('Disetujui', $borrowing->status_perpanjangan);
    }

    public function test_inventory_atomic_transaction_rolls_back_on_failure(): void
    {
        $initialStock = $this->equipment->jumlah; // 10
        $initialHistoryCount = RiwayatStok::where('id_peralatan', $this->equipment->id)->count();

        // Simulasikan kegagalan transaksi inventori
        try {
            DB::transaction(function () {
                $lockedEq = Peralatan::where('id', $this->equipment->id)->lockForUpdate()->first();
                $lockedEq->update(['jumlah' => 25]);

                // Simulasikan error sistem saat pembuatan mutasi
                throw new \RuntimeException('Simulated failure during inventory mutation');
            });
        } catch (\RuntimeException $e) {
            // Expected
        }

        $this->assertEquals($initialStock, $this->equipment->fresh()->jumlah, 'Stok alat harus rollback ke nilai semula jika mutasi gagal');
        $this->assertEquals($initialHistoryCount, RiwayatStok::where('id_peralatan', $this->equipment->id)->count(), 'Riwayat stok tidak boleh tersimpan saat transaksi di-rollback');
    }

    public function test_send_laboratory_reminders_is_idempotent_and_does_not_duplicate_notifications(): void
    {
        // Siapkan peminjaman yang jatuh tempo pengembalian besok
        $dueBorrowing = Peminjaman::create([
            'borrow_code' => 'BRW-REMIND-'.rand(1000, 9999),
            'customer_id' => $this->customer->id,
            'start_date' => now()->subDays(5)->toDateString(),
            'end_date' => now()->addDay()->toDateString(),
            'purpose' => 'Uji Idempoten Pengingat',
            'status' => 'Dipinjam',
            'deposit_amount' => 100000,
        ]);
        DetailPeminjaman::create([
            'borrowing_id' => $dueBorrowing->id,
            'equipment_id' => $this->equipment->id,
            'quantity' => 1,
        ]);

        $initialNotificationCount = Notification::where('user_id', $this->customer->id)->count();

        // Eksekusi scheduler pertama kali
        $this->artisan('lab:send-reminders')->assertSuccessful();

        $countAfterFirstRun = Notification::where('user_id', $this->customer->id)->count();
        $this->assertGreaterThan($initialNotificationCount, $countAfterFirstRun, 'Notifikasi harus terkirim pada eksekusi pertama');

        // Eksekusi scheduler kedua kali pada hari yang sama
        $this->artisan('lab:send-reminders')->assertSuccessful();

        $countAfterSecondRun = Notification::where('user_id', $this->customer->id)->count();
        $this->assertEquals($countAfterFirstRun, $countAfterSecondRun, 'Eksekusi ulang di hari yang sama tidak boleh menggandakan notifikasi');
    }

    public function test_equipment_cannot_be_deleted_if_active_borrowing_exists(): void
    {
        $borrowing = $this->createActiveBorrowing(1, 'Dipinjam');

        $response = $this->actingAs($this->staff)->delete(route('staff.equipment.destroy', $this->equipment));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('peralatan', ['id' => $this->equipment->id]);
    }

    public function test_staff_peminjaman_show_renders_approval_card_for_diajukan_status(): void
    {
        $borrowing = $this->createActiveBorrowing(1, 'Diajukan');

        $response = $this->actingAs($this->staff)->get(route('staff.borrowings.show', $borrowing));
        $response->assertStatus(200);
        $response->assertSee('Verifikasi Kesiapan Fisik Alat');
        $response->assertSee(route('staff.borrowings.approve', $borrowing->id));
        $response->assertSee(route('staff.borrowings.reject', $borrowing->id));
    }

    public function test_admin_peminjaman_show_renders_extension_approval_form(): void
    {
        $borrowing = $this->createActiveBorrowing(1, 'Dipinjam');
        $borrowing->update([
            'perpanjangan_diajukan' => true,
            'status_perpanjangan' => 'Pending',
            'tanggal_perpanjangan' => now()->addDays(7)->toDateString(),
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.borrowings.show', $borrowing));
        $response->assertStatus(200);
        $response->assertSee(route('admin.borrowings.approve-extension', $borrowing->id));
        $response->assertSee('Permintaan Perpanjangan Jadwal Pengembalian Alat');
    }

    public function test_return_with_fine_within_deposit_does_not_create_unpaid_invoice(): void
    {
        // Deposit 200.000, Denda 50.000 (tercover penuh oleh deposit)
        $borrowing = $this->createActiveBorrowing(2, 'Dipinjam');
        $borrowing->update(['deposit_amount' => 200000]);
        $detail = $borrowing->borrowingDetails->first();

        $response = $this->actingAs($this->staff)->post(route('staff.borrowings.return', $borrowing), [
            'actual_return_date' => now()->toDateString(),
            'conditions' => [
                [
                    'detail_id' => $detail->id,
                    'condition_return' => 'Baik',
                    'notes' => 'Alat bersih',
                ],
            ],
            'fine_amount' => 50000,
        ]);
        $response->assertRedirect();

        $borrowing->refresh();
        $this->assertEquals(150000, $borrowing->refund_amount, 'Refund harus 200.000 - 50.000 = 150.000');
        $this->assertEquals(50000, $borrowing->fine_amount);

        // Tidak boleh membuat invoice denda baru karena sudah terpotong langsung dari deposit
        $unpaidFinePayment = Pembayaran::where('id_peminjaman', $borrowing->id)
            ->where('tipe', 'Denda Peminjaman')
            ->where('status', 'Menunggu Pembayaran')
            ->first();
        $this->assertNull($unpaidFinePayment, 'Tidak boleh ada tagihan denda berulang ketika sudah terpotong deposit');
    }

    public function test_return_with_damaged_condition_records_riwayat_servis_and_riwayat_stok(): void
    {
        $borrowing = $this->createActiveBorrowing(1, 'Dipinjam');
        $detail = $borrowing->borrowingDetails->first();

        $response = $this->actingAs($this->staff)->post(route('staff.borrowings.return', $borrowing), [
            'actual_return_date' => now()->toDateString(),
            'conditions' => [
                [
                    'detail_id' => $detail->id,
                    'condition_return' => 'Rusak Berat',
                    'notes' => 'Lensa retak dan sensor tidak membaca',
                ],
            ],
            'fine_amount' => 0,
        ]);
        $response->assertRedirect();

        // Riwayat Servis otomatis tercatat
        $this->assertDatabaseHas('riwayat_servis', [
            'id_peralatan' => $this->equipment->id,
            'status' => 'Perlu Perbaikan',
        ]);

        // Riwayat Stok mencatat Rusak/Afkir
        $this->assertDatabaseHas('riwayat_stok', [
            'id_peralatan' => $this->equipment->id,
            'tipe' => 'Rusak/Afkir',
        ]);
    }
}
