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
use App\Models\User;
use App\Services\PdfGeneratorService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class InvoicePdfGenerationTest extends TestCase
{
    use DatabaseTransactions;

    protected User $customer;

    protected User $otherCustomer;

    protected User $staff;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = User::factory()->create([
            'role' => 'customer',
            'name' => 'Budi Santoso',
            'company_name' => 'PT Sumber Tirta Alami',
            'email' => 'budi@tirtaalami.co.id',
            'phone' => '081234567890',
            'address' => 'Jl. Industri No. 12, Cikarang',
        ]);

        $this->otherCustomer = User::factory()->create([
            'role' => 'customer',
            'name' => 'Ahmad Rivaldi',
            'company_name' => null,
            'email' => 'ahmad@example.com',
        ]);

        $this->staff = User::factory()->create([
            'role' => 'staff',
            'name' => 'Siti Analis',
        ]);

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'name' => 'Admin Lab',
        ]);
    }

    public function test_sample_invoice_pdf_download_unpaid(): void
    {
        $param = Parameter::create([
            'category' => 'Air Bersih',
            'name' => 'Derajat Keasaman (pH)',
            'unit' => 'pH Unit',
            'price' => 50000,
            'test_method' => 'SNI 06-6989.11-2004',
        ]);

        $sample = Sample::create([
            'sample_code' => 'SMP-20261021-0001',
            'customer_id' => $this->customer->id,
            'sample_type' => 'Air Bersih Sanitasi',
            'sampling_location' => 'Sumur Bor Kawasan',
            'sampling_date' => now(),
            'request_date' => now(),
            'status' => 'Diajukan',
        ]);

        SampleParameter::create([
            'sample_id' => $sample->id,
            'parameter_id' => $param->id,
            'test_method' => 'SNI 06-6989.11-2004',
        ]);

        $payment = Pembayaran::create([
            'payment_code' => 'PAY-20261021-0001',
            'customer_id' => $this->customer->id,
            'sample_id' => $sample->id,
            'type' => 'Pengujian',
            'amount' => 50000,
            'payment_method' => 'Transfer Bank',
            'status' => 'Menunggu Pembayaran',
        ]);

        $invoice = Invoice::create([
            'invoice_number' => 'INV/2026/10/0001',
            'customer_id' => $this->customer->id,
            'payment_id' => $payment->id,
            'subtotal' => 50000,
            'deposit' => 0,
            'fine' => 0,
            'total' => 50000,
            'status' => 'Belum Lunas',
        ]);

        $response = $this->actingAs($this->customer)->get(route('customer.invoices.download', $invoice));

        $response->assertStatus(200);
        $this->assertEquals('application/pdf', $response->headers->get('content-type'));
        $this->assertStringContainsString('INV_2026_10_0001.pdf', $response->headers->get('content-disposition'));
    }

    public function test_sample_invoice_pdf_download_paid_with_midtrans_data(): void
    {
        $param = Parameter::create([
            'category' => 'Air Limbah',
            'name' => 'BOD (Biochemical Oxygen Demand)',
            'unit' => 'mg/L',
            'price' => 125000,
            'test_method' => 'SNI 6989.72:2009',
        ]);

        $sample = Sample::create([
            'sample_code' => 'SMP-20261021-0002',
            'customer_id' => $this->customer->id,
            'sample_type' => 'Air Limbah Industri',
            'sampling_location' => 'Outlet IPAL Utama',
            'sampling_date' => now(),
            'request_date' => now(),
            'status' => 'Diterima',
        ]);

        SampleParameter::create([
            'sample_id' => $sample->id,
            'parameter_id' => $param->id,
            'test_method' => 'SNI 6989.72:2009',
        ]);

        $payment = Pembayaran::create([
            'payment_code' => 'PAY-20261021-0002',
            'customer_id' => $this->customer->id,
            'sample_id' => $sample->id,
            'type' => 'Pengujian',
            'amount' => 125000,
            'payment_method' => 'qris',
            'status' => 'Berhasil',
            'payment_reference' => 'MIDTRANS-SETTLEMENT-987654321',
            'paid_at' => now(),
        ]);

        $invoice = Invoice::create([
            'invoice_number' => 'INV/2026/10/0002',
            'customer_id' => $this->customer->id,
            'payment_id' => $payment->id,
            'subtotal' => 125000,
            'deposit' => 0,
            'fine' => 0,
            'total' => 125000,
            'status' => 'Lunas',
        ]);

        $response = $this->actingAs($this->customer)->get(route('customer.invoices.download', $invoice));

        $response->assertStatus(200);
        $this->assertEquals('application/pdf', $response->headers->get('content-type'));
    }

    public function test_borrowing_deposit_invoice_pdf_download(): void
    {
        $equipment = Peralatan::create([
            'kode' => 'EQP-PHM-099',
            'nama' => 'Digital Portable pH Meter Waterproof',
            'kategori' => 'Portabel',
            'merk' => 'Hanna Instruments',
            'tahun_pembelian' => 2023,
            'jumlah' => 5,
            'harga_sewa_per_hari' => 100000,
            'biaya_deposit' => 500000,
            'status' => 'Tersedia',
        ]);

        $borrowing = Peminjaman::create([
            'borrow_code' => 'BRW-20261021-001',
            'customer_id' => $this->customer->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(3)->toDateString(),
            'purpose' => 'Uji Lapangan Analisis Air Danau',
            'status' => 'Disetujui',
            'deposit_amount' => 500000,
        ]);

        DetailPeminjaman::create([
            'borrowing_id' => $borrowing->id,
            'equipment_id' => $equipment->id,
            'quantity' => 1,
        ]);

        $payment = Pembayaran::create([
            'payment_code' => 'PAY-20261021-0003',
            'customer_id' => $this->customer->id,
            'borrowing_id' => $borrowing->id,
            'type' => 'Deposit Peminjaman',
            'amount' => 500000,
            'payment_method' => 'bca_va',
            'status' => 'Berhasil',
            'payment_reference' => 'BCA-VA-123456789012',
            'paid_at' => now(),
        ]);

        $invoice = Invoice::create([
            'invoice_number' => 'INV/2026/10/0003',
            'customer_id' => $this->customer->id,
            'payment_id' => $payment->id,
            'subtotal' => 500000,
            'deposit' => 500000,
            'fine' => 0,
            'total' => 500000,
            'status' => 'Lunas',
        ]);

        $response = $this->actingAs($this->customer)->get(route('customer.invoices.download', $invoice));

        $response->assertStatus(200);
        $this->assertEquals('application/pdf', $response->headers->get('content-type'));
    }

    public function test_borrowing_fine_invoice_pdf_download(): void
    {
        $borrowing = Peminjaman::create([
            'borrow_code' => 'BRW-20261021-002',
            'customer_id' => $this->customer->id,
            'start_date' => now()->subDays(5)->toDateString(),
            'end_date' => now()->subDays(2)->toDateString(),
            'actual_return_date' => now()->toDateString(),
            'purpose' => 'Uji Lapangan Selesai',
            'status' => 'Dikembalikan',
            'deposit_amount' => 500000,
            'fine_amount' => 150000,
            'notes' => 'Terlambat 2 hari dari jadwal pengembalian.',
        ]);

        $payment = Pembayaran::create([
            'payment_code' => 'PAY-20261021-0004',
            'customer_id' => $this->customer->id,
            'borrowing_id' => $borrowing->id,
            'type' => 'Denda Peminjaman',
            'amount' => 150000,
            'payment_method' => 'Transfer Bank',
            'status' => 'Menunggu Pembayaran',
        ]);

        $invoice = Invoice::create([
            'invoice_number' => 'INV/2026/10/0004',
            'customer_id' => $this->customer->id,
            'payment_id' => $payment->id,
            'subtotal' => 0,
            'deposit' => 0,
            'fine' => 150000,
            'total' => 150000,
            'status' => 'Belum Lunas',
        ]);

        $response = $this->actingAs($this->customer)->get(route('customer.invoices.download', $invoice));

        $response->assertStatus(200);
        $this->assertEquals('application/pdf', $response->headers->get('content-type'));
    }

    public function test_terbilang_helper_accuracy(): void
    {
        $this->assertEquals('Nol Rupiah', PdfGeneratorService::terbilang(0));
        $this->assertEquals('Lima Puluh Ribu Rupiah', PdfGeneratorService::terbilang(50000));
        $this->assertEquals('Seratus Lima Puluh Ribu Rupiah', PdfGeneratorService::terbilang(150000));
        $this->assertEquals('Satu Juta Dua Ratus Lima Puluh Ribu Rupiah', PdfGeneratorService::terbilang(1250000));
        $this->assertEquals('Dua Puluh Lima Juta Rupiah', PdfGeneratorService::terbilang(25000000));
    }

    public function test_payment_method_formatter(): void
    {
        $this->assertEquals('QRIS (GoPay / OVO / Dana / LinkAja)', PdfGeneratorService::formatPaymentMethod('qris'));
        $this->assertEquals('GoPay / GoPay Later', PdfGeneratorService::formatPaymentMethod('gopay'));
        $this->assertEquals('BCA Virtual Account', PdfGeneratorService::formatPaymentMethod('bca_va'));
        $this->assertEquals('Mandiri Bill Payment', PdfGeneratorService::formatPaymentMethod('echannel'));
        $this->assertEquals('Transfer Bank / Virtual Account (Midtrans)', PdfGeneratorService::formatPaymentMethod('bank_transfer'));
        $this->assertEquals('Menunggu Pembayaran', PdfGeneratorService::formatPaymentMethod(null));
    }

    public function test_unauthorized_customer_cannot_download_other_customer_invoice(): void
    {
        $payment = Pembayaran::create([
            'payment_code' => 'PAY-OTHER-001',
            'customer_id' => $this->customer->id,
            'type' => 'Pengujian',
            'amount' => 50000,
            'status' => 'Menunggu Pembayaran',
        ]);

        $invoice = Invoice::create([
            'invoice_number' => 'INV/OTHER/0001',
            'customer_id' => $this->customer->id,
            'payment_id' => $payment->id,
            'subtotal' => 50000,
            'deposit' => 0,
            'fine' => 0,
            'total' => 50000,
            'status' => 'Belum Lunas',
        ]);

        $response = $this->actingAs($this->otherCustomer)->get(route('customer.invoices.download', $invoice));

        $response->assertStatus(403);
    }

    public function test_staff_and_admin_can_download_customer_invoice(): void
    {
        $payment = Pembayaran::create([
            'payment_code' => 'PAY-AUTH-001',
            'customer_id' => $this->customer->id,
            'type' => 'Pengujian',
            'amount' => 75000,
            'status' => 'Berhasil',
        ]);

        $invoice = Invoice::create([
            'invoice_number' => 'INV/AUTH/0001',
            'customer_id' => $this->customer->id,
            'payment_id' => $payment->id,
            'subtotal' => 75000,
            'deposit' => 0,
            'fine' => 0,
            'total' => 75000,
            'status' => 'Lunas',
        ]);

        $staffRes = $this->actingAs($this->staff)->get(route('customer.invoices.download', $invoice));
        $staffRes->assertStatus(200);

        $adminRes = $this->actingAs($this->admin)->get(route('customer.invoices.download', $invoice));
        $adminRes->assertStatus(200);
    }
}
