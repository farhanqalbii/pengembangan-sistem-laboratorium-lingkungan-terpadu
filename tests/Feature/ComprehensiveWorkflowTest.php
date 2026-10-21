<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\Notification;
use App\Models\Parameter;
use App\Models\Pembayaran;
use App\Models\Peminjaman;
use App\Models\Peralatan;
use App\Models\Sample;
use App\Models\Sertifikat;
use App\Models\User;
use App\Services\PaymentGatewayService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ComprehensiveWorkflowTest extends TestCase
{
    use DatabaseTransactions;

    protected User $admin;

    protected User $manager;

    protected User $staff;

    protected User $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::where('role', 'admin')->first();
        $this->manager = User::where('role', 'manager')->first();
        $this->staff = User::where('role', 'staff')->first();
        $this->customer = User::where('role', 'customer')->first();
    }

    public function test_full_sample_testing_lifecycle(): void
    {
        // 1. Customer submits a new sample request
        $param = Parameter::first();
        $this->assertNotNull($param, 'Parameter must exist.');

        $sampleData = [
            'sample_type' => 'Air Limbah Uji Otomatis',
            'sampling_location' => 'Kawasan Industri Rungkut',
            'sampling_date' => now()->toDateString(),
            'parameters' => [$param->id],
            'notes' => 'Pengujian komprehensif',
        ];

        $response = $this->actingAs($this->customer)->post(route('customer.samples.store'), $sampleData);
        $response->assertRedirect();

        $sample = Sample::where('sample_type', 'Air Limbah Uji Otomatis')->latest()->first();
        $this->assertNotNull($sample);
        $this->assertEquals('Diajukan', $sample->status);
        $this->assertDatabaseHas('sample_parameters', [
            'sample_id' => $sample->id,
            'parameter_id' => $param->id,
        ]);

        // Customer can view the sample page
        $resView = $this->actingAs($this->customer)->get(route('customer.samples.show', $sample));
        $resView->assertStatus(200);

        // Settle sample payment before staff can receive
        $payment = $sample->payments()->latest()->first();
        if ($payment) {
            app(PaymentGatewayService::class)->handleSuccessfulPayment($payment, 'TRX-SAMPLE-'.rand(), 'bank_transfer');
        }

        // 2. Staff receives the sample
        $receiveRes = $this->actingAs($this->staff)->post(route('staff.samples.receive', $sample), [
            'received_date' => now()->toDateString(),
        ]);
        $receiveRes->assertRedirect();
        $sample->refresh();
        $this->assertEquals('Diterima', $sample->status);

        // 3. Staff assigns analyst and equipment
        $sampleParam = $sample->sampleParameters->first();
        $equip = Peralatan::where('status', 'Tersedia')->first();

        $assignRes = $this->actingAs($this->staff)->post(route('staff.samples.assign', $sample), [
            'assignments' => [
                [
                    'sample_parameter_id' => $sampleParam->id,
                    'analyst_id' => $this->staff->id,
                    'equipment_id' => $equip ? $equip->id : null,
                ],
            ],
        ]);
        $assignRes->assertRedirect();
        $sample->refresh();
        $this->assertEquals('Diuji', $sample->status);

        // 4. Staff inputs test results
        $resultsRes = $this->actingAs($this->staff)->post(route('staff.samples.store-results', $sample), [
            'results' => [
                [
                    'sample_parameter_id' => $sampleParam->id,
                    'test_value' => '7.2',
                    'notes' => 'Hasil uji sesuai prosedur',
                ],
            ],
        ]);
        $resultsRes->assertRedirect(route('staff.samples.show', $sample));
        $sample->refresh();
        $this->assertEquals('Menunggu Verifikasi', $sample->status);

        // 5. Manager rejects for revision
        $rejectRes = $this->actingAs($this->manager)->post(route('manager.verifications.reject', $sample), [
            'rejection_reason' => 'Periksa ulang kalibrasi sensor pH sebelum konfirmasi.',
        ]);
        $rejectRes->assertRedirect(route('manager.verifications.show', $sample));
        $sample->refresh();
        $this->assertEquals('Revisi', $sample->status);
        $this->assertEquals('Periksa ulang kalibrasi sensor pH sebelum konfirmasi.', $sample->rejection_reason);

        // 6. Staff re-submits results
        $reStoreRes = $this->actingAs($this->staff)->post(route('staff.samples.store-results', $sample), [
            'results' => [
                [
                    'sample_parameter_id' => $sampleParam->id,
                    'test_value' => '7.15',
                    'notes' => 'Telah dikalibrasi ulang.',
                ],
            ],
        ]);
        $reStoreRes->assertRedirect(route('staff.samples.show', $sample));
        $sample->refresh();
        $this->assertEquals('Menunggu Verifikasi', $sample->status);

        // 7. Manager approves and COA is issued
        $approveRes = $this->actingAs($this->manager)->post(route('manager.verifications.approve', $sample));
        $approveRes->assertRedirect(route('manager.verifications.show', $sample));
        $sample->refresh();
        $this->assertEquals('Selesai', $sample->status);

        // Verify Certificate created
        $cert = Sertifikat::where('sample_id', $sample->id)->first();
        $this->assertNotNull($cert);
        $this->assertNotEmpty($cert->nomor_sertifikat);

        // 8. Customer can view and download certificate
        $certViewRes = $this->actingAs($this->customer)->get(route('customer.certificates.show', $cert));
        $certViewRes->assertStatus(200);

        $downloadRes = $this->actingAs($this->customer)->get(route('customer.certificates.download', $cert));
        $downloadRes->assertStatus(200);
        $this->assertEquals('application/pdf', $downloadRes->headers->get('Content-Type'));

        // 9. Customer submits satisfaction feedback
        $satRes = $this->actingAs($this->customer)->post(route('customer.satisfaction.store'), [
            'service_type' => 'Pengujian',
            'service_id' => $sample->id,
            'rating' => 5,
            'feedback' => 'Pelayanan laboratorium sangat cepat dan profesional!',
        ]);
        $satRes->assertRedirect(route('customer.dashboard'));

        $this->assertDatabaseHas('kepuasan_pelanggan', [
            'customer_id' => $this->customer->id,
            'tipe_layanan' => 'Pengujian',
            'id_layanan' => $sample->id,
            'rating' => 5,
        ]);
    }

    public function test_full_equipment_and_borrowing_lifecycle(): void
    {
        // 1. Staff creates new equipment
        $randomCode = 'EQ-TEST-'.rand(1000, 9999);
        $equipData = [
            'code' => $randomCode,
            'name' => 'Spectrophotometer UV-Vis Test',
            'category' => 'Spektrofotometer',
            'brand' => 'Shimadzu',
            'specification' => 'UV-1800 190-1100 nm',
            'purchase_year' => 2024,
            'quantity' => 5,
            'location' => 'Lab Kimia Analitik',
            'min_stock' => 1,
            'status' => 'Tersedia',
            'rental_price_per_day' => 150000,
            'deposit_amount' => 500000,
        ];

        $createEqRes = $this->actingAs($this->staff)->post(route('staff.equipment.store'), $equipData);
        $createEqRes->assertRedirect();

        $equipment = Peralatan::where('kode', $randomCode)->first();
        $this->assertNotNull($equipment);
        $this->assertEquals(5, $equipment->jumlah);

        // Verify initial stock history
        $this->assertDatabaseHas('riwayat_stok', [
            'id_peralatan' => $equipment->id,
            'tipe' => 'Stok Awal',
            'jumlah_setelah' => 5,
        ]);

        // 2. Staff records calibration & service
        $calibRes = $this->actingAs($this->staff)->post(route('staff.equipment.calibration', $equipment), [
            'calibration_date' => now()->toDateString(),
            'next_calibration_date' => now()->addMonths(6)->toDateString(),
            'result' => 'Sesuai Standar ISO/IEC 17025',
            'notes' => 'Akurasi panjang gelombang terverifikasi baik',
        ]);
        // 2b. Staff records calibration using web form fields (calibrated_by, certificate_number, status)
        $calibFormRes = $this->actingAs($this->staff)->post(route('staff.equipment.calibration', $equipment), [
            'calibration_date' => now()->toDateString(),
            'next_calibration_date' => now()->addYear()->toDateString(),
            'status' => 'Lulus',
            'calibrated_by' => 'Balai Besar Kalibrasi Kemendag',
            'certificate_number' => 'KAL/2026/09/102',
        ]);
        $calibFormRes->assertRedirect();
        $this->assertDatabaseHas('kalibrasi', [
            'id_peralatan' => $equipment->id,
            'hasil' => 'Lulus',
        ]);

        $serviceRes = $this->actingAs($this->staff)->post(route('staff.equipment.service', $equipment), [
            'service_date' => now()->toDateString(),
            'status' => 'Pembersihan Optik Selesai',
            'technician' => 'Teknisi PT Shimadzu',
            'notes' => 'Perawatan rutin berkala',
        ]);
        $serviceRes->assertRedirect();
        $this->assertDatabaseHas('riwayat_servis', [
            'id_peralatan' => $equipment->id,
            'teknisi' => 'Teknisi PT Shimadzu',
        ]);

        // 3. Customer submits a borrowing request
        $borrowData = [
            'equipment_id' => $equipment->id,
            'quantity' => 2,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(5)->toDateString(),
            'purpose' => 'Penelitian Kualitas Air Baku',
            'notes' => 'Diperlukan untuk sampling lapangan',
        ];

        $borrowRes = $this->actingAs($this->customer)->post(route('customer.borrowings.store'), $borrowData);
        $borrowRes->assertRedirect();

        $borrowing = Peminjaman::where('customer_id', $this->customer->id)->latest()->first();
        $this->assertNotNull($borrowing);
        $this->assertEquals('Diajukan', $borrowing->status);

        // 4. Admin approves borrowing
        $adminApproveRes = $this->actingAs($this->admin)->post(route('admin.borrowings.approve', $borrowing));
        $adminApproveRes->assertRedirect();
        $borrowing->refresh();
        $this->assertEquals('Disetujui', $borrowing->status);

        // Deposit payment generated
        $payment = Pembayaran::where('id_peminjaman', $borrowing->id)->first();
        $this->assertNotNull($payment);

        // 5. Customer processes deposit payment
        $payRes = $this->actingAs($this->customer)->post(route('customer.payments.process', $payment), [
            'payment_method' => 'Transfer Bank BCA',
        ]);
        $payRes->assertRedirect();

        // Simulate gateway settlement via PaymentGatewayService
        app(PaymentGatewayService::class)->handleSuccessfulPayment($payment, 'TRX-SETTLE-TEST-'.rand(), 'bank_transfer');

        $borrowing->refresh();
        $equipment->refresh();
        $this->assertEquals('Dipinjam', $borrowing->status);
        $this->assertEquals(3, $equipment->jumlah, 'Equipment stock should decrease from 5 to 3');

        // 6. Customer requests extension
        $extRes = $this->actingAs($this->customer)->post(route('customer.borrowings.extend', $borrowing), [
            'extended_end_date' => now()->addDays(8)->toDateString(),
        ]);
        $extRes->assertRedirect();
        $borrowing->refresh();
        $this->assertTrue((bool) $borrowing->perpanjangan_diajukan);

        // 7. Admin approves extension
        $adminExtRes = $this->actingAs($this->admin)->post(route('admin.borrowings.approve-extension', $borrowing));
        $adminExtRes->assertRedirect();
        $borrowing->refresh();
        $this->assertEquals('Disetujui', $borrowing->status_perpanjangan);

        // 8. Staff processes return
        $detail = $borrowing->borrowingDetails->first();
        $returnRes = $this->actingAs($this->staff)->post(route('staff.borrowings.return', $borrowing), [
            'actual_return_date' => now()->addDays(8)->toDateString(),
            'conditions' => [
                [
                    'detail_id' => $detail->id,
                    'condition_return' => 'Baik',
                    'notes' => 'Kondisi optik dan bodi lengkap normal',
                ],
            ],
            'fine_amount' => 0,
        ]);
        $returnRes->assertRedirect();

        $borrowing->refresh();
        $equipment->refresh();
        $this->assertEquals('Dikembalikan', $borrowing->status);
        $this->assertEquals(5, $equipment->jumlah, 'Equipment stock should be restored to 5');
        $this->assertGreaterThan(0, $borrowing->biaya_pengembalian);

        // 9. Admin processes deposit refund in Finance
        $refundRes = $this->actingAs($this->admin)->post(route('admin.finance.refund', $borrowing));
        $refundRes->assertRedirect();

        $borrowing->refresh();
        $this->assertEquals('Refunded', $borrowing->status_pengembalian);
    }

    public function test_parameter_crud_lifecycle(): void
    {
        // 1. Admin creates a new parameter with Baku Mutu
        $paramName = 'Fluorida Bebas '.rand(100, 999);
        $paramData = [
            'name' => $paramName,
            'category' => 'Anorganik',
            'unit' => 'mg/L',
            'test_method' => 'SNI 06-6989.29-2005',
            'price' => 75000,
            'min_value' => 0,
            'max_value' => 1.5,
            'regulation_source' => 'Permenkes No 2 Tahun 2023',
        ];

        $storeRes = $this->actingAs($this->admin)->post(route('admin.parameters.store'), $paramData);
        $storeRes->assertRedirect(route('admin.parameters.index'));

        $param = Parameter::where('name', $paramName)->first();
        $this->assertNotNull($param);
        $this->assertEquals($paramName, $param->name);
        $this->assertNotNull($param->qualityStandard);
        $this->assertEquals(1.5, $param->qualityStandard->nilai_maksimum);

        // 2. Admin updates the parameter
        $updateRes = $this->actingAs($this->admin)->put(route('admin.parameters.update', $param), [
            'name' => 'Fluorida Bebas (F-)',
            'category' => 'Anorganik',
            'unit' => 'mg/L',
            'test_method' => 'SNI 06-6989.29-2005 Revisi',
            'price' => 85000,
            'min_value' => 0.1,
            'max_value' => 1.5,
            'regulation_source' => 'Permenkes No 2 Tahun 2023',
        ]);
        $updateRes->assertRedirect(route('admin.parameters.index'));

        $param->refresh();
        $this->assertEquals('Fluorida Bebas (F-)', $param->name);
        $this->assertEquals(85000, (float) $param->price);

        // 3. Admin deletes the parameter
        $deleteRes = $this->actingAs($this->admin)->delete(route('admin.parameters.destroy', $param));
        $deleteRes->assertRedirect(route('admin.parameters.index'));

        $this->assertNull(Parameter::find($param->id));
        $this->assertDatabaseMissing('baku_mutu', ['parameter_id' => $param->id]);
    }

    public function test_user_management_crud(): void
    {
        $testEmail = 'staff.test.'.rand(1000, 9999).'@labenv.id';
        $userData = [
            'name' => 'Staf Laboratorium Testing',
            'email' => $testEmail,
            'password' => 'Password123!',
            'role' => 'staff',
            'phone' => '081298765432',
            'address' => 'Jl. Kalibrasi No. 12',
            'status' => 'active',
        ];

        // 1. Create user
        $storeRes = $this->actingAs($this->admin)->post(route('admin.users.store'), $userData);
        $storeRes->assertRedirect(route('admin.users.index'));

        $newUser = User::where('email', $testEmail)->first();
        $this->assertNotNull($newUser);
        $this->assertEquals('Staf Laboratorium Testing', $newUser->name);

        // 2. Update user
        $updateRes = $this->actingAs($this->admin)->put(route('admin.users.update', $newUser), [
            'name' => 'Staf Senior Lab Testing',
            'email' => $testEmail,
            'role' => 'staff',
            'phone' => '081298765432',
            'status' => 'active',
        ]);
        $updateRes->assertRedirect(route('admin.users.index'));

        $newUser->refresh();
        $this->assertEquals('Staf Senior Lab Testing', $newUser->name);

        // 3. Delete user
        $deleteRes = $this->actingAs($this->admin)->delete(route('admin.users.destroy', $newUser));
        $deleteRes->assertRedirect(route('admin.users.index'));

        $this->assertNull(User::find($newUser->id));
    }

    public function test_notifications_and_invoice_download(): void
    {
        // 1. Notification mark as read & mark all
        $notif = Notification::create([
            'user_id' => $this->customer->id,
            'title' => 'Uji Notifikasi',
            'message' => 'Pesan pengujian sistem notifikasi',
            'link' => '/customer/dashboard',
        ]);

        $readRes = $this->actingAs($this->customer)->get(route('notifications.read', $notif));
        $readRes->assertRedirect();
        $notif->refresh();
        $this->assertNotNull($notif->read_at);

        $readAllRes = $this->actingAs($this->customer)->post(route('notifications.read-all'));
        $readAllRes->assertRedirect();

        // 2. Invoice PDF download
        $invoice = Invoice::where('customer_id', $this->customer->id)->first();
        if ($invoice) {
            $invRes = $this->actingAs($this->customer)->get(route('customer.invoices.download', $invoice));
            $invRes->assertStatus(200);
            $this->assertEquals('application/pdf', $invRes->headers->get('Content-Type'));
        }
    }
}
