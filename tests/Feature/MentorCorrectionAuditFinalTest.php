<?php

namespace Tests\Feature;

use App\Models\DetailPeminjaman;
use App\Models\Invoice;
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

class MentorCorrectionAuditFinalTest extends TestCase
{
    use DatabaseTransactions;

    protected User $customer;

    protected User $otherCustomer;

    protected User $staff;

    protected User $manager;

    protected User $admin;

    protected Peralatan $equipment;

    protected Sample $sample;

    protected Peminjaman $borrowing;

    protected Sertifikat $certificate;

    protected Invoice $invoice;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = User::factory()->create([
            'name' => 'Budi Customer',
            'email' => 'budi.customer.'.rand(1000, 9999).'@test.com',
            'role' => 'customer',
        ]);

        $this->otherCustomer = User::factory()->create([
            'name' => 'Siti Customer',
            'email' => 'siti.customer.'.rand(1000, 9999).'@test.com',
            'role' => 'customer',
        ]);

        $this->staff = User::factory()->create([
            'name' => 'Staf Analis Lab',
            'email' => 'staff.analis.'.rand(1000, 9999).'@test.com',
            'role' => 'staff',
        ]);

        $this->manager = User::factory()->create([
            'name' => 'Manajer Teknis Lab',
            'email' => 'manager.lab.'.rand(1000, 9999).'@test.com',
            'role' => 'manager',
        ]);

        $this->admin = User::factory()->create([
            'name' => 'Admin Utama',
            'email' => 'admin.lims.'.rand(1000, 9999).'@test.com',
            'role' => 'admin',
        ]);

        $this->equipment = Peralatan::create([
            'kode' => 'EQP-TST-'.rand(100, 999),
            'nama' => 'Spectrophotometer Audit Test',
            'kategori' => 'Spektrometri',
            'merk' => 'Shimadzu',
            'spesifikasi' => 'UV-1800 Dual Beam',
            'tahun_pembelian' => 2024,
            'jumlah' => 5,
            'lokasi' => 'Lab Instrumentasi',
            'stok_minimum' => 1,
            'status' => 'Tersedia',
            'harga_sewa_per_hari' => 150000,
            'biaya_deposit' => 300000,
        ]);

        $parameter = Parameter::firstOrCreate(
            ['name' => 'Biochemical Oxygen Demand (BOD)'],
            [
                'category' => 'Kimia Organik',
                'unit' => 'mg/L',
                'test_method' => 'SNI 6989.72:2009',
                'price' => 85000,
            ]
        );

        $this->sample = Sample::create([
            'sample_code' => 'SMP-AUD-'.rand(1000, 9999),
            'customer_id' => $this->customer->id,
            'sample_type' => 'Air Sungai',
            'sampling_location' => 'Sungai Citarum Hulu',
            'sampling_date' => now()->subDays(2),
            'request_date' => now(),
            'status' => 'Menunggu Verifikasi',
        ]);

        SampleParameter::create([
            'sample_id' => $this->sample->id,
            'parameter_id' => $parameter->id,
            'status' => 'Selesai',
            'result' => '4.2',
            'analyst_id' => $this->staff->id,
            'analysis_date' => now()->subDay(),
        ]);

        $this->borrowing = Peminjaman::create([
            'kode_peminjaman' => 'BRW-AUD-'.rand(1000, 9999),
            'customer_id' => $this->customer->id,
            'tanggal_mulai' => now()->addDay(),
            'tanggal_selesai' => now()->addDays(3),
            'status' => 'Diajukan',
            'tujuan' => 'Penelitian Kualitas Air',
        ]);

        DetailPeminjaman::create([
            'id_peminjaman' => $this->borrowing->id,
            'id_peralatan' => $this->equipment->id,
            'jumlah' => 1,
            'harga_sewa_per_hari' => $this->equipment->harga_sewa_per_hari,
            'subtotal' => $this->equipment->harga_sewa_per_hari * 2,
        ]);

        $this->certificate = Sertifikat::create([
            'nomor_sertifikat' => 'COA/2026/10/'.rand(1000, 9999),
            'sample_id' => $this->sample->id,
            'customer_id' => $this->customer->id,
            'verified_by' => $this->manager->id,
            'tanggal_verifikasi' => now(),
            'tanggal_terbit' => now(),
            'hash_tanda_tangan' => 'hmac_sha256_mock_hash_audit',
        ]);

        $payment = Pembayaran::create([
            'kode_pembayaran' => 'PAY-AUD-'.rand(1000, 9999),
            'sample_id' => $this->sample->id,
            'customer_id' => $this->customer->id,
            'amount' => 85000,
            'type' => 'Pengujian',
            'status' => 'Berhasil',
            'paid_at' => now(),
        ]);

        $this->invoice = Invoice::create([
            'invoice_number' => 'INV/2026/10/'.rand(1000, 9999),
            'customer_id' => $this->customer->id,
            'id_pembayaran' => $payment->id,
            'subtotal' => 85000,
            'deposit' => 0,
            'fine' => 0,
            'total' => 85000,
            'status' => 'Lunas',
        ]);
    }

    /**
     * 1. Koreksi Mentor #1: Ikon dekoratif yang redundan telah dikurangi di Landing, Login, dan Register,
     *    sedangkan kontrol fungsional penting tetap dipertahankan.
     */
    public function test_mentor_correction_one_decorative_icons_reduced_functional_preserved(): void
    {
        // Landing Page
        $resHome = $this->get(route('home'));
        $resHome->assertStatus(200);
        // Ikon fungsional tetap ada
        $resHome->assertSee('fa-qrcode'); // QR Scanner
        $resHome->assertSee('fa-search'); // Search Status
        $resHome->assertSee('fa-spinner'); // Loading indicator
        // Ikon redundan sudah dihilangkan
        $resHome->assertDontSee('fa-barcode'); // Redundant barcode icon inside search input removed

        // Login Page
        $resLogin = $this->get(route('login'));
        $resLogin->assertStatus(200);
        // Ikon fungsional dipertahankan
        $resLogin->assertSee('fa-eye'); // Password toggle
        $resLogin->assertSee('fa-spinner'); // Form submit loading
        // Ikon dekoratif berulang dihilangkan
        $resLogin->assertDontSee('fa-flask-vial');
        $resLogin->assertDontSee('fa-file-shield');

        // Register Page
        $resRegister = $this->get(route('register'));
        $resRegister->assertStatus(200);
        $resRegister->assertSee('fa-eye');
        $resRegister->assertSee('fa-spinner');
        $resRegister->assertDontSee('fa-flask-vial');
        $resRegister->assertDontSee('fa-file-shield');
    }

    /**
     * 2. Koreksi Mentor #4: Peralatan menggunakan identifier publik 'kode', bukan ID numerik.
     *    ID numerik pada URL detail ditolak dengan 404 Not Found.
     */
    public function test_equipment_public_identifier_access_and_numeric_id_rejection(): void
    {
        // Akses via kode publik berhasil (Staff)
        $resStaffPublic = $this->actingAs($this->staff)->get('/staff/equipment/'.$this->equipment->kode);
        $resStaffPublic->assertStatus(200);
        $resStaffPublic->assertSee($this->equipment->nama);

        // Akses via ID numerik ditolak 404 (Staff)
        $resStaffNumeric = $this->actingAs($this->staff)->get('/staff/equipment/'.$this->equipment->id);
        $resStaffNumeric->assertStatus(404);

        // Akses via kode publik berhasil (Admin)
        $resAdminPublic = $this->actingAs($this->admin)->get('/admin/equipment/'.$this->equipment->kode);
        $resAdminPublic->assertStatus(200);
        $resAdminPublic->assertSee($this->equipment->nama);

        // Akses via ID numerik ditolak 404 (Admin)
        $resAdminNumeric = $this->actingAs($this->admin)->get('/admin/equipment/'.$this->equipment->id);
        $resAdminNumeric->assertStatus(404);
    }

    /**
     * 3. Koreksi Mentor #4: Sertifikat COA menggunakan identifier publik bisnis, bukan ID numerik.
     */
    public function test_certificate_public_identifier_access_and_numeric_id_rejection(): void
    {
        // Akses detail via nomor_sertifikat berhasil untuk pemilik
        $resCertPublic = $this->actingAs($this->customer)->get('/customer/certificates/'.$this->certificate->nomor_sertifikat);
        $resCertPublic->assertStatus(200);
        $resCertPublic->assertSee($this->certificate->nomor_sertifikat);

        // Akses detail via ID numerik ditolak 404
        $resCertNumeric = $this->actingAs($this->customer)->get('/customer/certificates/'.$this->certificate->id);
        $resCertNumeric->assertStatus(404);

        // Download COA via nomor_sertifikat berhasil
        $resDownloadPublic = $this->actingAs($this->customer)->get('/customer/certificates/'.$this->certificate->nomor_sertifikat.'/download');
        $resDownloadPublic->assertStatus(200);

        // Download COA via ID numerik ditolak 404
        $resDownloadNumeric = $this->actingAs($this->customer)->get('/customer/certificates/'.$this->certificate->id.'/download');
        $resDownloadNumeric->assertStatus(404);

        // Customer lain ditolak 403 saat mencoba mengakses sertifikat bukan miliknya
        $resForbidden = $this->actingAs($this->otherCustomer)->get('/customer/certificates/'.$this->certificate->nomor_sertifikat);
        $resForbidden->assertStatus(403);
    }

    /**
     * 4. Koreksi Mentor #4: Invoice menggunakan identifier publik nomor invoice, bukan ID numerik.
     */
    public function test_invoice_public_identifier_access_and_numeric_id_rejection(): void
    {
        // Download invoice via nomor invoice publik berhasil
        $resInvPublic = $this->actingAs($this->customer)->get('/customer/invoices/'.$this->invoice->invoice_number.'/download');
        $resInvPublic->assertStatus(200);

        // Download invoice via ID numerik ditolak 404
        $resInvNumeric = $this->actingAs($this->customer)->get('/customer/invoices/'.$this->invoice->id.'/download');
        $resInvNumeric->assertStatus(404);

        // Customer lain ditolak 403
        $resForbidden = $this->actingAs($this->otherCustomer)->get('/customer/invoices/'.$this->invoice->invoice_number.'/download');
        $resForbidden->assertStatus(403);
    }

    /**
     * 5. Koreksi Mentor #4: Sampel dan Peminjaman konsisten menggunakan identifier publik bisnis.
     */
    public function test_sample_and_borrowing_public_identifier_and_numeric_rejection(): void
    {
        // Sampel Staff
        $this->actingAs($this->staff)->get('/staff/samples/'.$this->sample->sample_code)->assertStatus(200);
        $this->actingAs($this->staff)->get('/staff/samples/'.$this->sample->id)->assertStatus(404);

        // Peminjaman Staff
        $this->actingAs($this->staff)->get('/staff/borrowings/'.$this->borrowing->kode_peminjaman)->assertStatus(200);
        $this->actingAs($this->staff)->get('/staff/borrowings/'.$this->borrowing->id)->assertStatus(404);

        // Peminjaman Customer
        $this->actingAs($this->customer)->get('/customer/borrowings/'.$this->borrowing->kode_peminjaman)->assertStatus(200);
        $this->actingAs($this->customer)->get('/customer/borrowings/'.$this->borrowing->id)->assertStatus(404);
    }

    /**
     * 6. Verifikasi Regresi RBAC: Matriks Akses 4x4 Dashboard dan Perlindungan Verifikasi Teknis.
     */
    public function test_rbac_strict_matrix_and_technical_verification_protection(): void
    {
        // Customer: hanya boleh customer.dashboard
        $this->actingAs($this->customer)->get(route('customer.dashboard'))->assertStatus(200);
        $this->actingAs($this->customer)->get(route('staff.dashboard'))->assertStatus(403);
        $this->actingAs($this->customer)->get(route('manager.dashboard'))->assertStatus(403);
        $this->actingAs($this->customer)->get(route('admin.dashboard'))->assertStatus(403);

        // Staff: hanya boleh staff.dashboard
        $this->actingAs($this->staff)->get(route('staff.dashboard'))->assertStatus(200);
        $this->actingAs($this->staff)->get(route('customer.dashboard'))->assertStatus(403);
        $this->actingAs($this->staff)->get(route('manager.dashboard'))->assertStatus(403);
        $this->actingAs($this->staff)->get(route('admin.dashboard'))->assertStatus(403);

        // Manager: hanya boleh manager.dashboard
        $this->actingAs($this->manager)->get(route('manager.dashboard'))->assertStatus(200);
        $this->actingAs($this->manager)->get(route('customer.dashboard'))->assertStatus(403);
        $this->actingAs($this->manager)->get(route('staff.dashboard'))->assertStatus(403);
        $this->actingAs($this->manager)->get(route('admin.dashboard'))->assertStatus(403);

        // Admin: hanya boleh admin.dashboard
        $this->actingAs($this->admin)->get(route('admin.dashboard'))->assertStatus(200);
        $this->actingAs($this->admin)->get(route('customer.dashboard'))->assertStatus(403);
        $this->actingAs($this->admin)->get(route('staff.dashboard'))->assertStatus(403);
        $this->actingAs($this->admin)->get(route('manager.dashboard'))->assertStatus(403);

        $sampleToApprove = Sample::create([
            'sample_code' => 'SMP-APP-'.rand(1000, 9999),
            'customer_id' => $this->customer->id,
            'sample_type' => 'Air Sungai',
            'sampling_location' => 'Sungai Citarum',
            'sampling_date' => now()->subDays(2),
            'request_date' => now(),
            'status' => 'Menunggu Verifikasi',
        ]);

        // Perlindungan Verifikasi Teknis: Staff dan Admin TIDAK BISA menjalankan verifikasi Manajer Lab!
        $this->actingAs($this->staff)->post(route('manager.verifications.approve', $sampleToApprove))->assertStatus(403);
        $this->actingAs($this->admin)->post(route('manager.verifications.approve', $sampleToApprove))->assertStatus(403);

        // Manajer yang sah BISA menyetujui verifikasi
        $resManagerApprove = $this->actingAs($this->manager)->post(route('manager.verifications.approve', $sampleToApprove));
        $resManagerApprove->assertRedirect(route('manager.verifications.show', $sampleToApprove));
    }
}
