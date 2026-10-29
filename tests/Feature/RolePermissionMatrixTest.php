<?php

namespace Tests\Feature;

use App\Models\BakuMutu;
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

class RolePermissionMatrixTest extends TestCase
{
    use DatabaseTransactions;

    protected User $customerA;

    protected User $customerB;

    protected User $staff;

    protected User $manager;

    protected User $admin;

    protected Peralatan $equipment;

    protected Parameter $parameter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customerA = User::factory()->create([
            'name' => 'Customer Alpha',
            'email' => 'customer.alpha.'.rand(1000, 9999).'@test.com',
            'role' => 'customer',
        ]);

        $this->customerB = User::factory()->create([
            'name' => 'Customer Beta',
            'email' => 'customer.beta.'.rand(1000, 9999).'@test.com',
            'role' => 'customer',
        ]);

        $this->staff = User::factory()->create([
            'name' => 'Staff Analis',
            'email' => 'staff.analis.'.rand(1000, 9999).'@test.com',
            'role' => 'staff',
        ]);

        $this->manager = User::factory()->create([
            'name' => 'Manager Lab',
            'email' => 'manager.lab.'.rand(1000, 9999).'@test.com',
            'role' => 'manager',
        ]);

        $this->admin = User::factory()->create([
            'name' => 'Administrator',
            'email' => 'admin.lims.'.rand(1000, 9999).'@test.com',
            'role' => 'admin',
        ]);

        $this->equipment = Peralatan::create([
            'kode' => 'EQ-MTX-'.rand(1000, 9999),
            'nama' => 'Spectrophotometer UV-Vis Matrix',
            'kategori' => 'Spektrometri',
            'merek' => 'Shimadzu',
            'spesifikasi' => 'UV-1800',
            'tahun_pengadaan' => 2024,
            'jumlah' => 5,
            'lokasi_penyimpanan' => 'Lab Instrumentasi',
            'stok_minimum' => 1,
            'status' => 'Tersedia',
            'harga_sewa_per_hari' => 150000,
            'biaya_deposit' => 300000,
        ]);

        $this->parameter = Parameter::create([
            'name' => 'BOD-Matrix-'.rand(100, 999),
            'category' => 'Air Limbah',
            'unit' => 'mg/L',
            'test_method' => 'SNI 6989.72:2009',
            'price' => 75000,
        ]);

        BakuMutu::create([
            'parameter_id' => $this->parameter->id,
            'min_value' => 0,
            'max_value' => 50,
            'unit' => 'mg/L',
            'regulation_source' => 'Permen LHK No. 68/2016',
            'status' => 'Aktif',
        ]);
    }

    /**
     * Helper to create sample
     */
    protected function createSample(User $customer, string $status = 'Menunggu Verifikasi'): Sample
    {
        $sample = Sample::create([
            'sample_code' => 'SMP-MTX-'.rand(10000, 99999),
            'customer_id' => $customer->id,
            'sample_type' => 'Air Sungai',
            'sampling_location' => 'Sungai Citarum',
            'sampling_date' => now()->toDateString(),
            'request_date' => now(),
            'status' => $status,
        ]);

        SampleParameter::create([
            'sample_id' => $sample->id,
            'parameter_id' => $this->parameter->id,
            'id_peralatan' => $this->equipment->id,
            'analyst_id' => $this->staff->id,
            'test_method' => 'SNI 6989.72:2009',
            'test_value' => '25.5',
            'compliance_status' => 'Memenuhi',
            'status' => 'Submitted',
        ]);

        return $sample;
    }

    /**
     * Helper to create borrowing
     */
    protected function createBorrowing(User $customer, string $status = 'Diajukan'): Peminjaman
    {
        $borrowing = Peminjaman::create([
            'borrow_code' => 'BRW-MTX-'.rand(10000, 99999),
            'customer_id' => $customer->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(3)->toDateString(),
            'purpose' => 'Penelitian Baku Mutu',
            'status' => $status,
            'deposit_amount' => 300000,
        ]);

        DetailPeminjaman::create([
            'borrowing_id' => $borrowing->id,
            'equipment_id' => $this->equipment->id,
            'quantity' => 1,
        ]);

        return $borrowing;
    }

    // =========================================================================
    // 1. DASHBOARD ACCESS MATRIX (4 ROLES)
    // =========================================================================

    public function test_customer_can_only_access_customer_dashboard(): void
    {
        $this->actingAs($this->customerA)->get(route('customer.dashboard'))->assertStatus(200);
        $this->actingAs($this->customerA)->get(route('staff.dashboard'))->assertStatus(403);
        $this->actingAs($this->customerA)->get(route('manager.dashboard'))->assertStatus(403);
        $this->actingAs($this->customerA)->get(route('admin.dashboard'))->assertStatus(403);
    }

    public function test_staff_can_only_access_staff_dashboard(): void
    {
        $this->actingAs($this->staff)->get(route('staff.dashboard'))->assertStatus(200);
        $this->actingAs($this->staff)->get(route('customer.dashboard'))->assertStatus(403);
        $this->actingAs($this->staff)->get(route('manager.dashboard'))->assertStatus(403);
        $this->actingAs($this->staff)->get(route('admin.dashboard'))->assertStatus(403);
    }

    public function test_manager_can_only_access_manager_dashboard(): void
    {
        $this->actingAs($this->manager)->get(route('manager.dashboard'))->assertStatus(200);
        $this->actingAs($this->manager)->get(route('customer.dashboard'))->assertStatus(403);
        $this->actingAs($this->manager)->get(route('staff.dashboard'))->assertStatus(403);
        $this->actingAs($this->manager)->get(route('admin.dashboard'))->assertStatus(403);
    }

    public function test_admin_can_only_access_admin_dashboard(): void
    {
        $this->actingAs($this->admin)->get(route('admin.dashboard'))->assertStatus(200);
        $this->actingAs($this->admin)->get(route('staff.dashboard'))->assertStatus(403);
        $this->actingAs($this->admin)->get(route('customer.dashboard'))->assertStatus(403);
        $this->actingAs($this->admin)->get(route('manager.dashboard'))->assertStatus(403);
    }

    /**
     * Test wajib verifikasi bugfix kritis:
     * Mereproduksi kondisi pembuktian RBAC browser nyata (Admin -> /staff/dashboard -> 403 Forbidden).
     */
    public function test_bugfix_admin_is_strictly_forbidden_from_staff_and_manager_dashboards_reproduction(): void
    {
        // 1. Login sebagai Admin & request ke /staff/dashboard -> 403 Forbidden
        $responseAdminStaff = $this->actingAs($this->admin)->get('/staff/dashboard');
        $responseAdminStaff->assertStatus(403);

        // 2. Request Admin ke /manager/dashboard -> 403 Forbidden
        $responseAdminManager = $this->actingAs($this->admin)->get('/manager/dashboard');
        $responseAdminManager->assertStatus(403);

        // 3. Request Admin ke /customer/dashboard -> 403 Forbidden
        $responseAdminCustomer = $this->actingAs($this->admin)->get('/customer/dashboard');
        $responseAdminCustomer->assertStatus(403);

        // 4. Request Admin ke /admin/dashboard -> 200 OK
        $responseAdminOwn = $this->actingAs($this->admin)->get('/admin/dashboard');
        $responseAdminOwn->assertStatus(200);

        // 5. Login sebagai Staff: bisa buka /staff/dashboard (200), ditolak di dashboard lain (403)
        $this->actingAs($this->staff)->get('/staff/dashboard')->assertStatus(200);
        $this->actingAs($this->staff)->get('/manager/dashboard')->assertStatus(403);
        $this->actingAs($this->staff)->get('/admin/dashboard')->assertStatus(403);
        $this->actingAs($this->staff)->get('/customer/dashboard')->assertStatus(403);

        // 6. Login sebagai Manager: bisa buka /manager/dashboard (200), ditolak di dashboard lain (403)
        $this->actingAs($this->manager)->get('/manager/dashboard')->assertStatus(200);
        $this->actingAs($this->manager)->get('/staff/dashboard')->assertStatus(403);
        $this->actingAs($this->manager)->get('/admin/dashboard')->assertStatus(403);
        $this->actingAs($this->manager)->get('/customer/dashboard')->assertStatus(403);

        // 7. Login sebagai Customer: hanya bisa buka /customer/dashboard (200), ditolak di dashboard lain (403)
        $this->actingAs($this->customerA)->get('/customer/dashboard')->assertStatus(200);
        $this->actingAs($this->customerA)->get('/staff/dashboard')->assertStatus(403);
        $this->actingAs($this->customerA)->get('/manager/dashboard')->assertStatus(403);
        $this->actingAs($this->customerA)->get('/admin/dashboard')->assertStatus(403);
    }

    // =========================================================================
    // 2. MANAGER LAB TECHNICAL VERIFICATIONS (AUT-08 & SRS)
    // =========================================================================

    public function test_manager_can_access_verifications_list_and_details(): void
    {
        $sample = $this->createSample($this->customerA, 'Menunggu Verifikasi');

        $this->actingAs($this->manager)->get(route('manager.verifications.index'))->assertStatus(200);
        $this->actingAs($this->manager)->get(route('manager.verifications.show', $sample))->assertStatus(200);
    }

    public function test_staff_is_forbidden_from_manager_verifications_module(): void
    {
        $sample = $this->createSample($this->customerA, 'Menunggu Verifikasi');

        // Staff access to Manager Verifications must return 403
        $this->actingAs($this->staff)->get(route('manager.verifications.index'))->assertStatus(403);
        $this->actingAs($this->staff)->get(route('manager.verifications.show', $sample))->assertStatus(403);
        $this->actingAs($this->staff)->post(route('manager.verifications.approve', $sample))->assertStatus(403);
        $this->actingAs($this->staff)->post(route('manager.verifications.reject', $sample), [
            'rejection_reason' => 'Unauthorized rejection attempt',
        ])->assertStatus(403);

        // Verify database state was NOT modified
        $this->assertEquals('Menunggu Verifikasi', $sample->fresh()->status);
        $this->assertFalse(Sertifikat::where('sample_id', $sample->id)->exists());
    }

    public function test_admin_is_forbidden_from_manager_verifications_and_coa_issuance(): void
    {
        $sample = $this->createSample($this->customerA, 'Menunggu Verifikasi');

        // Admin access to technical verifications must return 403
        $this->actingAs($this->admin)->get(route('manager.verifications.index'))->assertStatus(403);
        $this->actingAs($this->admin)->get(route('manager.verifications.show', $sample))->assertStatus(403);
        $this->actingAs($this->admin)->post(route('manager.verifications.approve', $sample))->assertStatus(403);
        $this->actingAs($this->admin)->post(route('manager.verifications.reject', $sample), [
            'rejection_reason' => 'Admin technical rejection attempt',
        ])->assertStatus(403);

        // Verify database state remains intact
        $this->assertEquals('Menunggu Verifikasi', $sample->fresh()->status);
        $this->assertFalse(Sertifikat::where('sample_id', $sample->id)->exists());
    }

    public function test_customer_is_forbidden_from_manager_verifications(): void
    {
        $sample = $this->createSample($this->customerA, 'Menunggu Verifikasi');

        $this->actingAs($this->customerA)->get(route('manager.verifications.index'))->assertStatus(403);
        $this->actingAs($this->customerA)->get(route('manager.verifications.show', $sample))->assertStatus(403);
        $this->actingAs($this->customerA)->post(route('manager.verifications.approve', $sample))->assertStatus(403);
    }

    public function test_manager_can_approve_sample_and_issue_valid_coa(): void
    {
        $sample = $this->createSample($this->customerA, 'Menunggu Verifikasi');

        $response = $this->actingAs($this->manager)->post(route('manager.verifications.approve', $sample));
        $response->assertRedirect(route('manager.verifications.show', $sample));
        $response->assertSessionHas('success');

        $this->assertEquals('Selesai', $sample->fresh()->status);
        $this->assertTrue(Sertifikat::where('sample_id', $sample->id)->exists());
    }

    public function test_manager_can_request_revision_on_sample_results(): void
    {
        $sample = $this->createSample($this->customerA, 'Menunggu Verifikasi');

        $response = $this->actingAs($this->manager)->post(route('manager.verifications.reject', $sample), [
            'rejection_reason' => 'Periksa ulang kalibrasi sensor pH dan ulangi titrasi.',
        ]);

        $response->assertRedirect(route('manager.verifications.show', $sample));
        $this->assertEquals('Revisi', $sample->fresh()->status);
        $this->assertEquals('Periksa ulang kalibrasi sensor pH dan ulangi titrasi.', $sample->fresh()->rejection_reason);
    }

    // =========================================================================
    // 3. STAFF OPERATIONS (SAMPLES & EQUIPMENT)
    // =========================================================================

    public function test_staff_can_access_staff_sample_operations_while_admin_is_forbidden(): void
    {
        $sample = $this->createSample($this->customerA, 'Diproses');

        $this->actingAs($this->staff)->get(route('staff.samples.index'))->assertStatus(200);
        $this->actingAs($this->staff)->get(route('staff.samples.show', $sample))->assertStatus(200);
        $this->actingAs($this->staff)->get(route('staff.samples.input-results', $sample))->assertStatus(200);

        // Admin is forbidden from staff operational sample management
        $this->actingAs($this->admin)->get(route('staff.samples.index'))->assertStatus(403);
        $this->actingAs($this->admin)->get(route('staff.samples.show', $sample))->assertStatus(403);
        $this->actingAs($this->admin)->get(route('staff.samples.input-results', $sample))->assertStatus(403);
    }

    public function test_manager_and_customer_are_forbidden_from_staff_sample_operations(): void
    {
        $sample = $this->createSample($this->customerA, 'Diproses');

        $this->actingAs($this->manager)->get(route('staff.samples.index'))->assertStatus(403);
        $this->actingAs($this->manager)->get(route('staff.samples.show', $sample))->assertStatus(403);
        $this->actingAs($this->manager)->get(route('staff.samples.input-results', $sample))->assertStatus(403);

        $this->actingAs($this->customerA)->get(route('staff.samples.index'))->assertStatus(403);
        $this->actingAs($this->customerA)->get(route('staff.samples.show', $sample))->assertStatus(403);
        $this->actingAs($this->customerA)->get(route('staff.samples.input-results', $sample))->assertStatus(403);
    }

    public function test_unauthorized_roles_cannot_store_test_results_or_change_sample_status(): void
    {
        $sample = $this->createSample($this->customerA, 'Diproses');
        $sp = $sample->sampleParameters->first();

        // Customer attempts to submit test result
        $resCust = $this->actingAs($this->customerA)->post(route('staff.samples.store-results', $sample), [
            'results' => [
                [
                    'sample_parameter_id' => $sp->id,
                    'test_value' => '999',
                ],
            ],
        ]);
        $resCust->assertStatus(403);

        // Manager attempts to submit operational test result
        $resMgr = $this->actingAs($this->manager)->post(route('staff.samples.store-results', $sample), [
            'results' => [
                [
                    'sample_parameter_id' => $sp->id,
                    'test_value' => '999',
                ],
            ],
        ]);
        $resMgr->assertStatus(403);

        // Sample status remains unchanged
        $this->assertEquals('Diproses', $sample->fresh()->status);
        $this->assertNotEquals('999', $sp->fresh()->test_value);
    }

    // =========================================================================
    // 4. ADMIN EXCLUSIVE MODULES (USERS, FINANCE, SETTINGS, LOGS, REPORTS)
    // =========================================================================

    public function test_admin_can_access_exclusive_admin_modules(): void
    {
        $this->actingAs($this->admin)->get(route('admin.users.index'))->assertStatus(200);
        $this->actingAs($this->admin)->get(route('admin.users.create'))->assertStatus(200);
        $this->actingAs($this->admin)->get(route('admin.finance.index'))->assertStatus(200);
        $this->actingAs($this->admin)->get(route('admin.parameters.index'))->assertStatus(200);
        $this->actingAs($this->admin)->get(route('admin.parameters.create'))->assertStatus(200);
        $this->actingAs($this->admin)->get(route('admin.activity-logs.index'))->assertStatus(200);
        $this->actingAs($this->admin)->get(route('admin.reports.samples'))->assertStatus(200);
        $this->actingAs($this->admin)->get(route('admin.reports.inventory'))->assertStatus(200);
        $this->actingAs($this->admin)->get(route('admin.reports.borrowings'))->assertStatus(200);
        $this->actingAs($this->admin)->get(route('admin.reports.financial'))->assertStatus(200);
    }

    public function test_staff_manager_customer_are_forbidden_from_admin_modules(): void
    {
        $unauthorizedRoles = [$this->staff, $this->manager, $this->customerA];

        $adminRoutes = [
            'admin.users.index',
            'admin.users.create',
            'admin.finance.index',
            'admin.parameters.index',
            'admin.activity-logs.index',
            'admin.reports.samples',
            'admin.reports.inventory',
            'admin.reports.borrowings',
            'admin.reports.financial',
        ];

        foreach ($unauthorizedRoles as $user) {
            foreach ($adminRoutes as $route) {
                $response = $this->actingAs($user)->get(route($route));
                $response->assertStatus(403, "User {$user->role} must get 403 on {$route}");
            }
        }
    }

    public function test_unauthorized_roles_cannot_create_users_or_parameters(): void
    {
        $unauthorizedRoles = [$this->staff, $this->manager, $this->customerA];

        foreach ($unauthorizedRoles as $user) {
            $userCountBefore = User::count();
            $paramCountBefore = Parameter::count();

            // Attempt user creation
            $resUser = $this->actingAs($user)->post(route('admin.users.store'), [
                'name' => 'Injected User',
                'email' => 'injected.'.rand(1000, 9999).'@test.com',
                'password' => 'password123',
                'role' => 'admin',
                'phone' => '08123456789',
            ]);
            $resUser->assertStatus(403);
            $this->assertEquals($userCountBefore, User::count());

            // Attempt parameter creation
            $resParam = $this->actingAs($user)->post(route('admin.parameters.store'), [
                'name' => 'Injected Param',
                'category' => 'Air Limbah',
                'unit' => 'mg/L',
                'test_method' => 'SNI Fake',
                'price' => 50000,
                'regulation_source' => 'Fake Regulation',
            ]);
            $resParam->assertStatus(403);
            $this->assertEquals($paramCountBefore, Parameter::count());
        }
    }

    // =========================================================================
    // 5. SATISFACTION REPORT ACCESS (SRS FDB-F-002: ADMIN & MANAGER)
    // =========================================================================

    public function test_satisfaction_report_accessible_by_admin_and_manager_per_srs(): void
    {
        // SRS FDB-F-002 explicitly grants satisfaction recap to Admin and Manager Lab
        $this->actingAs($this->admin)->get(route('admin.reports.satisfaction'))->assertStatus(200);
        $this->actingAs($this->manager)->get(route('manager.reports.satisfaction'))->assertStatus(200);

        // Staff and Customer are forbidden
        $this->actingAs($this->staff)->get(route('admin.reports.satisfaction'))->assertStatus(403);
        $this->actingAs($this->staff)->get(route('manager.reports.satisfaction'))->assertStatus(403);
        $this->actingAs($this->customerA)->get(route('admin.reports.satisfaction'))->assertStatus(403);
        $this->actingAs($this->customerA)->get(route('manager.reports.satisfaction'))->assertStatus(403);
    }

    // =========================================================================
    // 6. CUSTOMER DATA OWNERSHIP ISOLATION (CUSTOMER A VS CUSTOMER B)
    // =========================================================================

    public function test_customer_cannot_access_other_customers_sample(): void
    {
        $sampleA = $this->createSample($this->customerA, 'Diajukan');

        // Customer A can view own sample
        $this->actingAs($this->customerA)->get(route('customer.samples.show', $sampleA))->assertStatus(200);

        // Customer B is rejected with 403 when trying to access Customer A's sample
        $this->actingAs($this->customerB)->get(route('customer.samples.show', $sampleA))->assertStatus(403);
    }

    public function test_customer_cannot_access_or_extend_other_customers_borrowing(): void
    {
        $borrowingA = $this->createBorrowing($this->customerA, 'Dipinjam');

        // Customer A can view own borrowing
        $this->actingAs($this->customerA)->get(route('customer.borrowings.show', $borrowingA))->assertStatus(200);

        // Customer B gets 403 on view
        $this->actingAs($this->customerB)->get(route('customer.borrowings.show', $borrowingA))->assertStatus(403);

        // Customer B gets 403 on extend
        $resExtend = $this->actingAs($this->customerB)->post(route('customer.borrowings.extend', $borrowingA), [
            'extended_end_date' => now()->addDays(7)->toDateString(),
        ]);
        $resExtend->assertStatus(403);

        // Borrowing state remains untouched
        $this->assertFalse((bool) $borrowingA->fresh()->extension_requested);
    }

    public function test_customer_cannot_access_other_customers_invoice_or_payment(): void
    {
        $sampleA = $this->createSample($this->customerA, 'Selesai');

        $paymentA = Pembayaran::create([
            'payment_code' => 'PAY-OWN-'.rand(1000, 9999),
            'customer_id' => $this->customerA->id,
            'sample_id' => $sampleA->id,
            'type' => 'Pengujian',
            'amount' => 75000,
            'payment_method' => 'Transfer Bank',
            'status' => 'Menunggu Pembayaran',
        ]);

        $invoiceA = Invoice::create([
            'invoice_number' => 'INV-OWN-'.rand(1000, 9999),
            'customer_id' => $this->customerA->id,
            'payment_id' => $paymentA->id,
            'subtotal' => 75000,
            'deposit' => 0,
            'fine' => 0,
            'total' => 75000,
            'status' => 'Belum Lunas',
        ]);

        // Customer A can download own invoice
        $this->actingAs($this->customerA)->get(route('customer.invoices.download', $invoiceA))->assertStatus(200);

        // Customer B is rejected with 403
        $this->actingAs($this->customerB)->get(route('customer.invoices.download', $invoiceA))->assertStatus(403);

        // Customer B cannot trigger process payment on Customer A's payment
        $resProcess = $this->actingAs($this->customerB)->post(route('customer.payments.process', $paymentA));
        $resProcess->assertStatus(403);
    }

    public function test_customer_cannot_access_other_customers_certificate(): void
    {
        $sampleA = $this->createSample($this->customerA, 'Selesai');

        $certA = Sertifikat::create([
            'nomor_sertifikat' => 'COA/TEST/'.rand(1000, 9999),
            'certificate_number' => 'COA/TEST/'.rand(1000, 9999),
            'sample_id' => $sampleA->id,
            'customer_id' => $this->customerA->id,
            'verified_by' => $this->manager->id,
            'tanggal_verifikasi' => now(),
            'tanggal_terbit' => now(),
            'hash_tanda_tangan' => 'signature_hash_'.rand(1000, 9999),
        ]);

        // Customer A can view and download
        $this->actingAs($this->customerA)->get(route('customer.certificates.show', $certA))->assertStatus(200);
        $this->actingAs($this->customerA)->get(route('customer.certificates.download', $certA))->assertStatus(200);

        // Customer B is rejected with 403
        $this->actingAs($this->customerB)->get(route('customer.certificates.show', $certA))->assertStatus(403);
        $this->actingAs($this->customerB)->get(route('customer.certificates.download', $certA))->assertStatus(403);
    }

    // =========================================================================
    // 7. PUBLIC CODE ROUTING INTEGRITY (MENTOR CORRECTION)
    // =========================================================================

    public function test_routes_use_public_codes_and_reject_numeric_id_spoofing(): void
    {
        $sample = $this->createSample($this->customerA, 'Diajukan');
        $borrowing = $this->createBorrowing($this->customerA, 'Diajukan');

        // Customer views sample with public code
        $this->actingAs($this->customerA)->get('/customer/samples/'.$sample->sample_code)->assertStatus(200);

        // Staff views sample with public code
        $this->actingAs($this->staff)->get('/staff/samples/'.$sample->sample_code)->assertStatus(200);

        // Customer views borrowing with public code
        $this->actingAs($this->customerA)->get('/customer/borrowings/'.$borrowing->borrow_code)->assertStatus(200);

        // Numeric ID fallback is NOT accepted (must return 404 since route key name is public code)
        $this->actingAs($this->staff)->get('/staff/samples/'.$sample->id)->assertStatus(404);
        $this->actingAs($this->customerA)->get('/customer/samples/'.$sample->id)->assertStatus(404);
        $this->actingAs($this->customerA)->get('/customer/borrowings/'.$borrowing->id)->assertStatus(404);
    }
}
