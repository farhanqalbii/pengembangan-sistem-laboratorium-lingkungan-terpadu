<?php

namespace Tests\Feature;

use App\Models\DetailPeminjaman;
use App\Models\Invoice;
use App\Models\KepuasanPelanggan;
use App\Models\Parameter;
use App\Models\Pembayaran;
use App\Models\Peminjaman;
use App\Models\Peralatan;
use App\Models\Sample;
use App\Models\SampleParameter;
use App\Models\Sertifikat;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class Week3ImplementationTest extends TestCase
{
    use DatabaseTransactions;

    protected User $customer;

    protected User $otherCustomer;

    protected User $staff;

    protected User $admin;

    protected User $manager;

    protected Parameter $parameter;

    protected Peralatan $equipment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = User::factory()->create(['role' => 'customer', 'name' => 'Customer Week 3']);
        $this->otherCustomer = User::factory()->create(['role' => 'customer', 'name' => 'Other Customer']);
        $this->staff = User::factory()->create(['role' => 'staff', 'name' => 'Staff Week 3']);
        $this->admin = User::factory()->create(['role' => 'admin', 'name' => 'Admin Week 3']);
        $this->manager = User::factory()->create(['role' => 'manager', 'name' => 'Manager Week 3']);

        $this->parameter = Parameter::first() ?? Parameter::create([
            'name' => 'Derajat Keasaman (pH)',
            'unit' => 'pH Unit',
            'test_method' => 'SNI 06-6989.11-2004',
            'price' => 75000,
        ]);

        $this->equipment = Peralatan::create([
            'kode' => 'EQ-W3-'.rand(1000, 9999),
            'nama' => 'pH Meter Lab Week 3',
            'kategori' => 'Elektrometri',
            'merek' => 'Horiba',
            'spesifikasi' => 'Precision 0.01 pH',
            'tahun_pengadaan' => 2024,
            'jumlah' => 5,
            'lokasi_penyimpanan' => 'Ruang Instrumen',
            'stok_minimum' => 1,
            'status' => 'Tersedia',
            'harga_sewa_per_hari' => 100000,
            'biaya_deposit' => 250000,
        ]);
    }

    /**
     * SRT-F-001: Verifikasi Hasil Uji & Alur Revisi
     */
    public function test_manager_verifikasi_reject_and_revision_flow_srt_f_001(): void
    {
        $sample = Sample::create([
            'sample_code' => 'SMP-TEST-REV-'.rand(1000, 9999),
            'customer_id' => $this->customer->id,
            'sample_type' => 'Air Sungai',
            'sampling_location' => 'Sungai Citarum',
            'sampling_date' => now()->toDateString(),
            'request_date' => now(),
            'status' => 'Menunggu Verifikasi',
        ]);

        $sp = SampleParameter::create([
            'sample_id' => $sample->id,
            'parameter_id' => $this->parameter->id,
            'test_method' => $this->parameter->test_method,
            'test_value' => '9.5',
            'compliance_status' => 'Pending',
            'status' => 'Submitted',
            'analyst_id' => $this->staff->id,
            'id_peralatan' => $this->equipment->id,
        ]);

        // 1. Manager rejects and requests revision
        $rejectionReason = 'Nilai pH 9.5 terlalu basa untuk titik hulu sungai, harap kalibrasi elektroda dan uji ulang.';
        $rejectResponse = $this->actingAs($this->manager)
            ->post(route('manager.verifications.reject', $sample), [
                'rejection_reason' => $rejectionReason,
            ]);

        $rejectResponse->assertRedirect(route('manager.verifications.show', $sample));
        $sample->refresh();
        $this->assertEquals('Revisi', $sample->status);
        $this->assertEquals($rejectionReason, $sample->rejection_reason);

        // 2. Staff views input-results page, should see revision notes
        $viewResponse = $this->actingAs($this->staff)->get(route('staff.samples.input-results', $sample));
        $viewResponse->assertStatus(200);
        $viewResponse->assertSee('Catatan Permintaan Revisi dari Manajer Lab');
        $viewResponse->assertSee($rejectionReason);

        // 3. Staff re-submits corrected test results
        $storeResponse = $this->actingAs($this->staff)
            ->post(route('staff.samples.store-results', $sample), [
                'results' => [
                    [
                        'sample_parameter_id' => $sp->id,
                        'test_value' => '7.2',
                        'notes' => 'Pengujian ulang pasca kalibrasi elektroda pH.',
                    ],
                ],
            ]);

        $storeResponse->assertRedirect(route('staff.samples.show', $sample));
        $sample->refresh();
        $sp->refresh();

        $this->assertEquals('Menunggu Verifikasi', $sample->status);
        $this->assertEquals('7.2', $sp->test_value);

        // 4. Manager review page shows previous revision notes for comparison
        $managerView = $this->actingAs($this->manager)->get(route('manager.verifications.show', $sample));
        $managerView->assertStatus(200);
        $managerView->assertSee('Catatan Permintaan Revisi Sebelumnya');
        $managerView->assertSee($rejectionReason);
    }

    /**
     * SRT-F-002: Terbitkan Sertifikat (COA) dengan Tanda Tangan Digital SHA-256
     */
    public function test_manager_verifikasi_approve_generates_coa_and_digital_signature_srt_f_002(): void
    {
        $sample = Sample::create([
            'sample_code' => 'SMP-TEST-COA-'.rand(1000, 9999),
            'customer_id' => $this->customer->id,
            'sample_type' => 'Air Limbah Industri',
            'sampling_location' => 'Outlet IPAL Pabrik A',
            'sampling_date' => now()->toDateString(),
            'request_date' => now(),
            'status' => 'Menunggu Verifikasi',
        ]);

        SampleParameter::create([
            'sample_id' => $sample->id,
            'parameter_id' => $this->parameter->id,
            'test_method' => $this->parameter->test_method,
            'test_value' => '7.1',
            'compliance_status' => 'Memenuhi',
            'status' => 'Submitted',
            'analyst_id' => $this->staff->id,
            'id_peralatan' => $this->equipment->id,
        ]);

        $approveResponse = $this->actingAs($this->manager)
            ->post(route('manager.verifications.approve', $sample));

        $approveResponse->assertRedirect(route('manager.verifications.show', $sample));
        $sample->refresh();

        $this->assertEquals('Selesai', $sample->status);
        $this->assertNotNull($sample->certificate);
        $this->assertStringStartsWith('COA/', $sample->certificate->certificate_number);
        $this->assertEquals(64, strlen($sample->certificate->digital_signature_hash));

        // Idempotency: cannot approve again
        $secondApprove = $this->actingAs($this->manager)
            ->post(route('manager.verifications.approve', $sample));
        $secondApprove->assertSessionHasErrors(['error']);
    }

    /**
     * SRT-F-003 & SRT-F-004: Riwayat Sertifikat Customer & Unduh PDF & Verifikasi Publik
     */
    public function test_customer_certificates_view_download_and_public_verification_srt_f_003_srt_f_004(): void
    {
        $sample = Sample::create([
            'sample_code' => 'SMP-TEST-CERT-'.rand(1000, 9999),
            'customer_id' => $this->customer->id,
            'sample_type' => 'Air Minum',
            'sampling_location' => 'Kran Konsumsi',
            'sampling_date' => now()->toDateString(),
            'request_date' => now(),
            'status' => 'Selesai',
        ]);

        $coaNumber = 'COA/'.date('Y/m').'/'.rand(5000, 9999);
        $hash = hash('sha256', $sample->sample_code.$coaNumber.now()->timestamp);

        $certificate = Sertifikat::create([
            'nomor_sertifikat' => $coaNumber,
            'certificate_number' => $coaNumber,
            'sample_id' => $sample->id,
            'customer_id' => $this->customer->id,
            'verified_by' => $this->manager->id,
            'tanggal_verifikasi' => now(),
            'tanggal_terbit' => now(),
            'hash_tanda_tangan' => $hash,
        ]);

        // 1. Customer lists certificates (SRT-F-004)
        $listRes = $this->actingAs($this->customer)->get(route('customer.certificates.index'));
        $listRes->assertStatus(200);
        $listRes->assertSee($coaNumber);

        // 2. Customer views certificate detail
        $showRes = $this->actingAs($this->customer)->get(route('customer.certificates.show', $certificate));
        $showRes->assertStatus(200);
        $showRes->assertSee($coaNumber);

        // Other customer cannot view
        $forbiddenRes = $this->actingAs($this->otherCustomer)->get(route('customer.certificates.show', $certificate));
        $forbiddenRes->assertStatus(403);

        // 3. Customer downloads PDF (SRT-F-003)
        $downloadRes = $this->actingAs($this->customer)->get(route('customer.certificates.download', $certificate));
        $downloadRes->assertStatus(200);
        $this->assertEquals('application/pdf', $downloadRes->headers->get('content-type'));

        // 4. Public verification via QR/hash
        $verifyRes = $this->get(route('certificate.verify', ['hash' => $hash]));
        $verifyRes->assertStatus(200);
        $verifyRes->assertSee($coaNumber);
        $verifyRes->assertSee('Dokumen Terverifikasi');
    }

    /**
     * BYR-F-001 & BYR-F-004: Pembuatan Tagihan Sampel & Unduh Invoice
     */
    public function test_sample_creation_generates_invoice_and_customer_can_download_byr_f_001_byr_f_004(): void
    {
        $response = $this->actingAs($this->customer)->post(route('customer.samples.store'), [
            'sample_type' => 'Air Bersih Sanitasi',
            'sampling_location' => 'Sumur Gali Warga',
            'sampling_date' => now()->toDateString(),
            'parameters' => [$this->parameter->id],
            'notes' => 'Uji rutin',
        ]);

        $response->assertRedirect();

        $sample = Sample::where('customer_id', $this->customer->id)->latest()->first();
        $this->assertNotNull($sample);

        $payment = Pembayaran::where('sample_id', $sample->id)->first();
        $this->assertNotNull($payment);
        $this->assertEquals((float) $this->parameter->price, (float) $payment->amount);
        $this->assertEquals('Menunggu Pembayaran', $payment->status);

        $invoice = $payment->invoice;
        $this->assertNotNull($invoice);
        $this->assertEquals('Belum Lunas', $invoice->status);

        // Download invoice (BYR-F-004)
        $invDownload = $this->actingAs($this->customer)->get(route('customer.invoices.download', $invoice));
        $invDownload->assertStatus(200);
        $this->assertEquals('application/pdf', $invDownload->headers->get('content-type'));
    }

    /**
     * BYR-F-003: Kelola Refund Deposit oleh Admin & Pencegahan Duplikasi (Idempotensi)
     */
    public function test_admin_deposit_refund_processing_and_double_refund_prevention_byr_f_003(): void
    {
        $borrowing = Peminjaman::create([
            'borrow_code' => 'BRW-REF-'.rand(1000, 9999),
            'customer_id' => $this->customer->id,
            'start_date' => now()->subDays(5)->toDateString(),
            'end_date' => now()->subDays(1)->toDateString(),
            'actual_return_date' => now()->toDateString(),
            'purpose' => 'Pengukuran Kualitas Udara',
            'status' => 'Dikembalikan',
            'deposit_amount' => 250000,
            'fine_amount' => 50000,
            'refund_amount' => 200000,
            'refund_status' => 'Diprofilkan',
        ]);

        DetailPeminjaman::create([
            'borrowing_id' => $borrowing->id,
            'equipment_id' => $this->equipment->id,
            'quantity' => 1,
            'condition_return' => 'Baik',
        ]);

        // 1. Admin processes refund
        $refundRes = $this->actingAs($this->admin)->post(route('admin.finance.refund', $borrowing));
        $refundRes->assertRedirect();
        $refundRes->assertSessionHas('success');

        $borrowing->refresh();
        $this->assertEquals('Refunded', $borrowing->status_pengembalian);

        $refundPaymentCount = Pembayaran::where('id_peminjaman', $borrowing->id)
            ->where('tipe', 'Refund Deposit Peminjaman')
            ->count();
        $this->assertEquals(1, $refundPaymentCount);

        // 2. Admin attempts duplicate refund on same borrowing
        $duplicateRes = $this->actingAs($this->admin)->post(route('admin.finance.refund', $borrowing));
        $duplicateRes->assertSessionHasErrors(['error']);

        // Assert payment record count remains exactly 1 (no duplicate money created)
        $this->assertEquals(1, Pembayaran::where('id_peminjaman', $borrowing->id)
            ->where('tipe', 'Refund Deposit Peminjaman')
            ->count());
    }

    /**
     * FDB-F-001: Form Kepuasan Pelanggan (Hanya untuk Layanan yang Selesai)
     */
    public function test_customer_satisfaction_validation_and_submission_fdb_f_001(): void
    {
        // Sample not completed yet
        $incompleteSample = Sample::create([
            'sample_code' => 'SMP-INCOMP-'.rand(1000, 9999),
            'customer_id' => $this->customer->id,
            'sample_type' => 'Air Limbah',
            'sampling_location' => 'Bak Kontrol',
            'sampling_date' => now()->toDateString(),
            'request_date' => now(),
            'status' => 'Diuji',
        ]);

        // Attempting to submit satisfaction for incomplete sample should fail
        $failRes = $this->actingAs($this->customer)->post(route('customer.satisfaction.store'), [
            'service_type' => 'Pengujian',
            'service_id' => $incompleteSample->id,
            'rating' => 5,
            'feedback' => 'Bagus sekali',
        ]);

        $failRes->assertRedirect(route('customer.dashboard'));
        $failRes->assertSessionHas('error');

        // Sample completed
        $completedSample = Sample::create([
            'sample_code' => 'SMP-COMP-'.rand(1000, 9999),
            'customer_id' => $this->customer->id,
            'sample_type' => 'Air Bersih',
            'sampling_location' => 'Tandon Air',
            'sampling_date' => now()->toDateString(),
            'request_date' => now(),
            'status' => 'Selesai',
        ]);

        $successRes = $this->actingAs($this->customer)->post(route('customer.satisfaction.store'), [
            'service_type' => 'Pengujian',
            'service_id' => $completedSample->id,
            'rating' => 5,
            'feedback' => 'Hasil uji sangat cepat dan akurat!',
        ]);

        $successRes->assertRedirect(route('customer.dashboard'));
        $successRes->assertSessionHas('success');

        $feedback = KepuasanPelanggan::where('customer_id', $this->customer->id)
            ->where('id_layanan', $completedSample->id)
            ->first();

        $this->assertNotNull($feedback);
        $this->assertEquals(5, $feedback->rating);
        $this->assertEquals('Hasil uji sangat cepat dan akurat!', $feedback->saran_masukan);
    }

    /**
     * FDB-F-002: Rekap Hasil Evaluasi Kepuasan Pelanggan untuk Manajer Lab & Admin
     */
    public function test_satisfaction_report_access_for_both_manager_and_admin_fdb_f_002(): void
    {
        // Seed feedback
        KepuasanPelanggan::create([
            'customer_id' => $this->customer->id,
            'tipe_layanan' => 'Pengujian',
            'id_layanan' => 1,
            'rating' => 5,
            'saran_masukan' => 'Pelayanan sangat memuaskan.',
        ]);

        // 1. Admin access
        $adminRes = $this->actingAs($this->admin)->get(route('admin.reports.satisfaction'));
        $adminRes->assertStatus(200);
        $adminRes->assertSee('Laporan Indeks Kepuasan Pelanggan');
        $adminRes->assertSee('Pelayanan sangat memuaskan.');

        // 2. Manager Lab access (SRS FDB-F-002 requirement)
        $managerRes = $this->actingAs($this->manager)->get(route('manager.reports.satisfaction'));
        $managerRes->assertStatus(200);
        $managerRes->assertSee('Laporan Indeks Kepuasan Pelanggan');
        $managerRes->assertSee('Pelayanan sangat memuaskan.');

        // 3. Customer must be denied (RBAC check)
        $customerDenied = $this->actingAs($this->customer)->get(route('manager.reports.satisfaction'));
        $customerDenied->assertStatus(403);

        // 4. Export excel and pdf work for manager
        $excelRes = $this->actingAs($this->manager)->get(route('manager.reports.satisfaction', ['export' => 'excel']));
        $excelRes->assertStatus(200);
        $this->assertStringContainsString('spreadsheet', $excelRes->headers->get('content-type'));

        $pdfRes = $this->actingAs($this->manager)->get(route('manager.reports.satisfaction', ['export' => 'pdf']));
        $pdfRes->assertStatus(200);
        $this->assertEquals('application/pdf', $pdfRes->headers->get('content-type'));
    }
}
