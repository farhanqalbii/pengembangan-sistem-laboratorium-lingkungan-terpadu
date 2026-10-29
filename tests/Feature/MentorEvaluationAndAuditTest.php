<?php

namespace Tests\Feature;

use App\Models\DetailPeminjaman;
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

class MentorEvaluationAndAuditTest extends TestCase
{
    use DatabaseTransactions;

    protected User $customer;

    protected User $otherCustomer;

    protected User $staff;

    protected User $manager;

    protected User $admin;

    protected Peralatan $equipment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = User::factory()->create([
            'role' => 'customer',
            'name' => 'Budi Sanjaya Customer',
            'company_name' => 'PT Lingkungan Lestari',
        ]);

        $this->otherCustomer = User::factory()->create([
            'role' => 'customer',
            'name' => 'Siti Rahma Customer',
            'company_name' => 'CV Mandiri Sejahtera',
        ]);

        $this->staff = User::factory()->create(['role' => 'staff']);
        $this->manager = User::factory()->create(['role' => 'manager']);
        $this->admin = User::factory()->create(['role' => 'admin']);

        $this->equipment = Peralatan::create([
            'kode' => 'EQ-TEST-'.uniqid(),
            'nama' => 'Spectrophotometer UV-Vis 2026',
            'kategori' => 'Spektrometri',
            'merek' => 'Shimadzu',
            'spesifikasi' => 'UV-1900i',
            'tahun_pengadaan' => 2024,
            'jumlah' => 5,
            'lokasi_penyimpanan' => 'Lab Instrumentasi A',
            'stok_minimum' => 1,
            'status' => 'Tersedia',
            'harga_sewa_per_hari' => 100000,
            'biaya_deposit' => 200000,
        ]);
    }

    public function test_sample_detail_uses_sample_code_and_backward_compatible_with_id(): void
    {
        $sample = Sample::create([
            'sample_code' => 'SMP-TEST-'.uniqid(),
            'customer_id' => $this->customer->id,
            'sample_type' => 'Air Limbah Industri',
            'sampling_location' => 'Outlet IPAL Utama',
            'sampling_date' => now()->subDay(),
            'request_date' => now(),
            'status' => 'Diajukan',
        ]);

        // Route URL generated via model should use sample_code
        $urlGenerated = route('staff.samples.show', $sample);
        $this->assertStringContainsString($sample->sample_code, $urlGenerated);

        // Access via sample_code as Staff
        $responseByCode = $this->actingAs($this->staff)->get("/staff/samples/{$sample->sample_code}");
        $responseByCode->assertStatus(200);
        $responseByCode->assertSee($sample->sample_code);

        // Access via numeric ID as Staff is strictly rejected (404 - mentor correction #4)
        $responseById = $this->actingAs($this->staff)->get("/staff/samples/{$sample->id}");
        $responseById->assertStatus(404);

        // Non-existent sample returns 404
        $responseNotFound = $this->actingAs($this->staff)->get('/staff/samples/SMP-NONEXISTENT-99999');
        $responseNotFound->assertStatus(404);
    }

    public function test_borrowing_detail_uses_kode_peminjaman_and_rejects_numeric_id(): void
    {
        $borrowing = Peminjaman::create([
            'kode_peminjaman' => 'BRW-TEST-'.uniqid(),
            'customer_id' => $this->customer->id,
            'tanggal_mulai' => now(),
            'tanggal_selesai' => now()->addDays(3),
            'tujuan' => 'Monitoring Kualitas Udara Ambien',
            'status' => 'Disetujui',
            'biaya_deposit' => 200000,
        ]);

        DetailPeminjaman::create([
            'id_peminjaman' => $borrowing->id,
            'id_peralatan' => $this->equipment->id,
            'quantity' => 1,
            'rental_price_per_day' => 100000,
            'subtotal' => 300000,
        ]);

        // Route URL generated via model should use kode_peminjaman
        $urlGenerated = route('staff.borrowings.show', $borrowing);
        $this->assertStringContainsString($borrowing->kode_peminjaman, $urlGenerated);

        // Access via kode_peminjaman as Staff
        $responseByCode = $this->actingAs($this->staff)->get("/staff/borrowings/{$borrowing->kode_peminjaman}");
        $responseByCode->assertStatus(200);
        $responseByCode->assertSee($borrowing->kode_peminjaman);

        // Access via numeric ID as Staff is strictly rejected (404 - mentor correction #4)
        $responseById = $this->actingAs($this->staff)->get("/staff/borrowings/{$borrowing->id}");
        $responseById->assertStatus(404);

        // Access via customer borrowing route
        $responseCustomer = $this->actingAs($this->customer)->get("/customer/borrowings/{$borrowing->kode_peminjaman}");
        $responseCustomer->assertStatus(200);
        $responseCustomer->assertSee($borrowing->kode_peminjaman);

        // Non-existent borrowing returns 404
        $responseNotFound = $this->actingAs($this->staff)->get('/staff/borrowings/BRW-NONEXISTENT-99999');
        $responseNotFound->assertStatus(404);
    }

    public function test_customer_cannot_access_other_customers_sample_or_borrowing(): void
    {
        $sample = Sample::create([
            'sample_code' => 'SMP-TEST-'.uniqid(),
            'customer_id' => $this->customer->id,
            'sample_type' => 'Air Permukaan Sungai',
            'sampling_location' => 'Hulu Sungai',
            'sampling_date' => now()->subDay(),
            'request_date' => now(),
            'status' => 'Diajukan',
        ]);

        $borrowing = Peminjaman::create([
            'kode_peminjaman' => 'BRW-TEST-'.uniqid(),
            'customer_id' => $this->customer->id,
            'tanggal_mulai' => now(),
            'tanggal_selesai' => now()->addDays(2),
            'tujuan' => 'Riset Akademik',
            'status' => 'Diajukan',
            'biaya_deposit' => 200000,
        ]);

        // Other customer tries to view $sample -> 403 Forbidden
        $responseSample = $this->actingAs($this->otherCustomer)->get("/customer/samples/{$sample->sample_code}");
        $responseSample->assertStatus(403);

        // Other customer tries to view $borrowing -> 403 Forbidden
        $responseBorrowing = $this->actingAs($this->otherCustomer)->get("/customer/borrowings/{$borrowing->kode_peminjaman}");
        $responseBorrowing->assertStatus(403);
    }

    public function test_staff_borrowing_detail_is_not_empty_for_approved_status(): void
    {
        $borrowing = Peminjaman::create([
            'kode_peminjaman' => 'BRW-APPV-'.uniqid(),
            'customer_id' => $this->customer->id,
            'tanggal_mulai' => now(),
            'tanggal_selesai' => now()->addDays(4),
            'tujuan' => 'Pengujian Lapangan Emisi',
            'status' => 'Disetujui',
            'biaya_deposit' => 200000,
        ]);

        DetailPeminjaman::create([
            'id_peminjaman' => $borrowing->id,
            'id_peralatan' => $this->equipment->id,
            'quantity' => 2,
            'rental_price_per_day' => 100000,
            'subtotal' => 800000,
        ]);

        // Prior to fix, 'Disetujui' rendered an empty view because only Diajukan, Dipinjam, Dikembalikan were handled!
        $response = $this->actingAs($this->staff)->get(route('staff.borrowings.show', $borrowing));
        $response->assertStatus(200);

        // Must display customer information
        $response->assertSee($this->customer->name);
        $response->assertSee('PT Lingkungan Lestari');

        // Must display equipment information
        $response->assertSee($this->equipment->name);
        $response->assertSee('2 Unit');

        // Must display financial deposit summary
        $response->assertSee('Rp 200.000');

        // Must display status explanation banner
        $response->assertSee('Peminjaman Telah Disetujui');
    }

    public function test_staff_borrowing_detail_is_not_empty_for_rejected_status(): void
    {
        $borrowing = Peminjaman::create([
            'kode_peminjaman' => 'BRW-REJ-'.uniqid(),
            'customer_id' => $this->customer->id,
            'tanggal_mulai' => now(),
            'tanggal_selesai' => now()->addDays(2),
            'tujuan' => 'Kalibrasi Mandiri',
            'notes' => 'Instrumen tidak tersedia karena dalam jadwal maintenance lab.',
            'status' => 'Ditolak',
            'biaya_deposit' => 200000,
        ]);

        DetailPeminjaman::create([
            'id_peminjaman' => $borrowing->id,
            'id_peralatan' => $this->equipment->id,
            'quantity' => 1,
            'rental_price_per_day' => 100000,
            'subtotal' => 200000,
        ]);

        $response = $this->actingAs($this->staff)->get(route('staff.borrowings.show', $borrowing));
        $response->assertStatus(200);
        $response->assertSee('Pengajuan Peminjaman Telah Ditolak');
        $response->assertSee('dalam jadwal maintenance lab');
        $response->assertSee($this->equipment->name);
    }

    public function test_staff_borrowing_index_supports_search_by_code_and_customer(): void
    {
        $b1 = Peminjaman::create([
            'kode_peminjaman' => 'BRW-SRCH-1111',
            'customer_id' => $this->customer->id,
            'tanggal_mulai' => now(),
            'tanggal_selesai' => now()->addDays(3),
            'tujuan' => 'Sampling Sungai',
            'status' => 'Diajukan',
            'biaya_deposit' => 200000,
        ]);

        $b2 = Peminjaman::create([
            'kode_peminjaman' => 'BRW-SRCH-2222',
            'customer_id' => $this->otherCustomer->id,
            'tanggal_mulai' => now(),
            'tanggal_selesai' => now()->addDays(3),
            'tujuan' => 'Sampling Udara',
            'status' => 'Diajukan',
            'biaya_deposit' => 200000,
        ]);

        // Search by unique code
        $response1 = $this->actingAs($this->staff)->get('/staff/borrowings?search=1111');
        $response1->assertStatus(200);
        $response1->assertSee('BRW-SRCH-1111');
        $response1->assertDontSee('BRW-SRCH-2222');

        // Search by company name
        $response2 = $this->actingAs($this->staff)->get('/staff/borrowings?search=Mandiri+Sejahtera');
        $response2->assertStatus(200);
        $response2->assertSee('BRW-SRCH-2222');
        $response2->assertDontSee('BRW-SRCH-1111');
    }

    public function test_sample_cannot_be_received_without_verified_payment(): void
    {
        $parameter = Parameter::first() ?? Parameter::create([
            'name' => 'BOD5',
            'standard' => 'SNI 6989.72:2009',
            'unit' => 'mg/L',
            'price' => 150000,
            'category' => 'Fisika-Kimia',
        ]);

        $sample = Sample::create([
            'sample_code' => 'SMP-UNPAID-'.uniqid(),
            'customer_id' => $this->customer->id,
            'sample_type' => 'Air Bersih',
            'sampling_location' => 'Kran Kantor',
            'sampling_date' => now()->subDay(),
            'request_date' => now(),
            'status' => 'Diajukan',
        ]);

        SampleParameter::create([
            'sample_id' => $sample->id,
            'parameter_id' => $parameter->id,
        ]);

        // No payment made -> Receive must be rejected
        $responseNoPay = $this->actingAs($this->staff)->post(route('staff.samples.receive', $sample), [
            'received_date' => now()->toDateString(),
        ]);
        $responseNoPay->assertSessionHasErrors();
        $this->assertEquals('Diajukan', $sample->fresh()->status);

        // Pending payment made -> Receive must be rejected
        $payment = Pembayaran::create([
            'payment_code' => 'PAY-PENDING-'.uniqid(),
            'customer_id' => $this->customer->id,
            'sample_id' => $sample->id,
            'type' => 'Pengujian',
            'amount' => 150000,
            'payment_method' => 'Midtrans Snap',
            'status' => 'Menunggu Pembayaran',
        ]);

        $responsePending = $this->actingAs($this->staff)->post(route('staff.samples.receive', $sample), [
            'received_date' => now()->toDateString(),
        ]);
        $responsePending->assertSessionHasErrors();
        $this->assertEquals('Diajukan', $sample->fresh()->status);

        // Verified payment -> Receive must succeed
        $payment->update(['status' => 'Berhasil']);

        $responsePaid = $this->actingAs($this->staff)->post(route('staff.samples.receive', $sample), [
            'received_date' => now()->toDateString(),
        ]);
        $responsePaid->assertSessionHasNoErrors();
        $this->assertEquals('Diterima', $sample->fresh()->status);
    }

    public function test_manager_approval_generates_official_coa_with_digital_signature(): void
    {
        $parameter = Parameter::first() ?? Parameter::create([
            'name' => 'COD',
            'standard' => 'SNI 6989.73:2009',
            'unit' => 'mg/L',
            'price' => 175000,
            'category' => 'Kimia',
        ]);

        $sample = Sample::create([
            'sample_code' => 'SMP-COA-'.uniqid(),
            'customer_id' => $this->customer->id,
            'sample_type' => 'Air Danau',
            'sampling_location' => 'Titik 1',
            'sampling_date' => now()->subDays(2),
            'request_date' => now()->subDays(2),
            'status' => 'Menunggu Verifikasi',
        ]);

        SampleParameter::create([
            'sample_id' => $sample->id,
            'parameter_id' => $parameter->id,
            'analyst_id' => $this->staff->id,
            'result' => '25.5',
            'unit' => 'mg/L',
            'compliance_status' => 'Memenuhi',
        ]);

        $responseApprove = $this->actingAs($this->manager)->post(route('manager.verifications.approve', $sample));
        $responseApprove->assertSessionHasNoErrors();

        // Sample status must transition to Selesai
        $this->assertEquals('Selesai', $sample->fresh()->status);

        // Certificate must exist
        $certificate = Sertifikat::where('sample_id', $sample->id)->first();
        $this->assertNotNull($certificate);
        $this->assertNotEmpty($certificate->certificate_number);
        $this->assertNotEmpty($certificate->nomor_sertifikat);
        $this->assertNotEmpty($certificate->hash_tanda_tangan);
        $this->assertEquals($this->manager->id, $certificate->verified_by);

        // Customer can download COA
        $responseDownload = $this->actingAs($this->customer)->get(route('customer.certificates.download', $certificate));
        $responseDownload->assertStatus(200);
        $this->assertEquals('application/pdf', $responseDownload->headers->get('Content-Type'));
    }
}
