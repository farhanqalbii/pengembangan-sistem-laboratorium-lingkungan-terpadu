<?php

namespace Database\Seeders;

use App\Models\Borrowing;
use App\Models\BorrowingDetail;
use App\Models\Calibration;
use App\Models\Certificate;
use App\Models\Equipment;
use App\Models\Invoice;
use App\Models\LogAktivitas;
use App\Models\Notification;
use App\Models\Parameter;
use App\Models\Payment;
use App\Models\QualityStandard;
use App\Models\Sample;
use App\Models\SampleParameter;
use App\Models\Satisfaction;
use App\Models\ServiceHistory;
use App\Models\StockHistory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Users
        $admin = User::create([
            'name' => 'Administrator Utama',
            'email' => 'admin@lab.test',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'phone' => '081234567890',
            'company_name' => 'UPTD Lab Lingkungan Terpadu',
            'address' => 'Jl. Sains & Teknologi No. 45, Jakarta',
        ]);

        $manager = User::create([
            'name' => 'Dr. Hendra Gunawan, M.Si',
            'email' => 'manager@lab.test',
            'password' => Hash::make('password'),
            'role' => 'manager',
            'phone' => '081234567891',
            'company_name' => 'UPTD Lab Lingkungan Terpadu',
            'address' => 'Jl. Sains & Teknologi No. 45, Jakarta',
        ]);

        $staff = User::create([
            'name' => 'Siti Rahmawati, S.Si',
            'email' => 'staff@lab.test',
            'password' => Hash::make('password'),
            'role' => 'staff',
            'phone' => '081234567892',
            'company_name' => 'UPTD Lab Lingkungan Terpadu',
            'address' => 'Jl. Sains & Teknologi No. 45, Jakarta',
        ]);

        $customer = User::create([
            'name' => 'PT Hijau Lestari Indonesia',
            'email' => 'customer@lab.test',
            'password' => Hash::make('password'),
            'role' => 'customer',
            'phone' => '081987654321',
            'company_name' => 'PT Hijau Lestari Indonesia',
            'address' => 'Kawasan Industri EcoPark Blok C-12, Bekasi',
        ]);

        $customer2 = User::create([
            'name' => 'Budi Santoso (Perorangan)',
            'email' => 'budi@customer.test',
            'password' => Hash::make('password'),
            'role' => 'customer',
            'phone' => '081765432109',
            'company_name' => 'CV Tirta Bening',
            'address' => 'Jl. Raya Merdeka No. 88, Tangerang',
        ]);

        // 2. Equipment
        $eq1 = Equipment::create([
            'code' => 'EQP-SPE-001',
            'name' => 'Spektrofotometer UV-Vis High Precision',
            'category' => 'Spektrofotometri',
            'brand' => 'Thermo Scientific',
            'specification' => 'Wavelength 190-1100 nm, Dual Beam Optics',
            'purchase_year' => 2023,
            'quantity' => 3,
            'location' => 'Lab Analisis Kimia 1',
            'qr_code' => 'QR-EQP-SPE-001',
            'barcode' => '8991001002001',
            'min_stock' => 1,
            'status' => 'Tersedia',
            'rental_price_per_day' => 350000,
            'deposit_amount' => 1000000,
        ]);

        $eq2 = Equipment::create([
            'code' => 'EQP-PHM-002',
            'name' => 'Digital Portable pH Meter Waterproof',
            'category' => 'Pengukuran Fisika',
            'brand' => 'Hanna Instruments',
            'specification' => 'Range pH 0.00-14.00, Resolution 0.01 pH, Auto Cal',
            'purchase_year' => 2024,
            'quantity' => 5,
            'location' => 'Lab Lapangan & Fisika',
            'qr_code' => 'QR-EQP-PHM-002',
            'barcode' => '8991001002002',
            'min_stock' => 2,
            'status' => 'Tersedia',
            'rental_price_per_day' => 150000,
            'deposit_amount' => 500000,
        ]);

        $eq3 = Equipment::create([
            'code' => 'EQP-AAS-003',
            'name' => 'Atomic Absorption Spectrophotometer (AAS)',
            'category' => 'Spektrofotometri',
            'brand' => 'Shimadzu AA-7000',
            'specification' => 'High-sensitivity trace metal detection (Pb, Cd, Hg, Cu)',
            'purchase_year' => 2022,
            'quantity' => 2,
            'location' => 'Lab Logam Berat',
            'qr_code' => 'QR-EQP-AAS-003',
            'barcode' => '8991001002003',
            'min_stock' => 1,
            'status' => 'Dalam Kalibrasi',
            'rental_price_per_day' => 750000,
            'deposit_amount' => 2500000,
        ]);

        $eq4 = Equipment::create([
            'code' => 'EQP-INC-004',
            'name' => 'Incubator BOD Refrigerated',
            'category' => 'Mikrobiologi & BOD',
            'brand' => 'Memmert ICP 260',
            'specification' => 'Temp Range 0°C to +70°C, 256 Liters capacity',
            'purchase_year' => 2023,
            'quantity' => 2,
            'location' => 'Lab Mikrobiologi',
            'qr_code' => 'QR-EQP-INC-004',
            'barcode' => '8991001002004',
            'min_stock' => 1,
            'status' => 'Tersedia',
            'rental_price_per_day' => 250000,
            'deposit_amount' => 800000,
        ]);

        // Calibrations & Services
        Calibration::create([
            'equipment_id' => $eq1->id,
            'calibration_date' => now()->subMonths(3),
            'next_calibration_date' => now()->addMonths(9),
            'result' => 'Lolos - Akurat (Ketidakpastian < 0.5%)',
            'notes' => 'Kalibrasi rutin oleh Balai Metrologi Industri',
        ]);

        Calibration::create([
            'equipment_id' => $eq3->id,
            'calibration_date' => now()->subDays(5),
            'next_calibration_date' => now()->addYear(),
            'result' => 'Dalam Proses Re-kalibrasi Lampu Katoda',
            'notes' => 'Pemeriksaan rutin sensitivitas elemen Pb & Cd',
        ]);

        ServiceHistory::create([
            'equipment_id' => $eq2->id,
            'service_date' => now()->subMonths(1),
            'notes' => 'Penggantian elektroda glass probe pH dan baterai lithium',
            'status' => 'Selesai',
            'technician' => 'Teknisi Hanna Service Center',
        ]);

        StockHistory::create([
            'equipment_id' => $eq1->id,
            'type' => 'Stok Awal',
            'qty_before' => 0,
            'qty_change' => 3,
            'qty_after' => 3,
            'user_id' => $admin->id,
            'notes' => 'Pengadaan unit baru APBD 2023',
        ]);

        // 3. Parameters & Quality Standards
        $p1 = Parameter::create([
            'name' => 'pH (Derajat Keasaman)',
            'category' => 'Fisika & Kimia Dasar',
            'unit' => '-',
            'test_method' => 'SNI 06-6989.11-2004',
            'price' => 50000,
        ]);

        QualityStandard::create([
            'parameter_id' => $p1->id,
            'min_value' => 6.0,
            'max_value' => 9.0,
            'unit' => '-',
            'regulation_source' => 'Permen LHK No. 68 Tahun 2016 (Baku Mutu Air Limbah Domestik)',
            'status' => 'Aktif',
        ]);

        $p2 = Parameter::create([
            'name' => 'BOD (Biological Oxygen Demand)',
            'category' => 'Kimia Organik',
            'unit' => 'mg/L',
            'test_method' => 'SNI 6989.72:2009',
            'price' => 120000,
        ]);

        QualityStandard::create([
            'parameter_id' => $p2->id,
            'min_value' => null,
            'max_value' => 30.0,
            'unit' => 'mg/L',
            'regulation_source' => 'Permen LHK No. 68 Tahun 2016',
            'status' => 'Aktif',
        ]);

        $p3 = Parameter::create([
            'name' => 'COD (Chemical Oxygen Demand)',
            'category' => 'Kimia Organik',
            'unit' => 'mg/L',
            'test_method' => 'SNI 06-6989.2-2004',
            'price' => 150000,
        ]);

        QualityStandard::create([
            'parameter_id' => $p3->id,
            'min_value' => null,
            'max_value' => 100.0,
            'unit' => 'mg/L',
            'regulation_source' => 'Permen LHK No. 68 Tahun 2016',
            'status' => 'Aktif',
        ]);

        $p4 = Parameter::create([
            'name' => 'TSS (Total Suspended Solids)',
            'category' => 'Fisika',
            'unit' => 'mg/L',
            'test_method' => 'SNI 06-6989.3-2004',
            'price' => 75000,
        ]);

        QualityStandard::create([
            'parameter_id' => $p4->id,
            'min_value' => null,
            'max_value' => 30.0,
            'unit' => 'mg/L',
            'regulation_source' => 'Permen LHK No. 68 Tahun 2016',
            'status' => 'Aktif',
        ]);

        $p5 = Parameter::create([
            'name' => 'Timbal (Pb)',
            'category' => 'Logam Berat',
            'unit' => 'mg/L',
            'test_method' => 'SNI 6989.8:2009 (AAS)',
            'price' => 180000,
        ]);

        QualityStandard::create([
            'parameter_id' => $p5->id,
            'min_value' => null,
            'max_value' => 0.1,
            'unit' => 'mg/L',
            'regulation_source' => 'PP No. 22 Tahun 2021 Lampiran VI',
            'status' => 'Aktif',
        ]);

        // 4. Sample Requests & Workflow Demo Data

        // Sample 1: Completed with COA
        $s1 = Sample::create([
            'sample_code' => 'SMP-20260901-0001',
            'customer_id' => $customer->id,
            'sample_type' => 'Air Limbah Domestik Outlet',
            'sampling_location' => 'Outlet WWTP PT Hijau Lestari - Cikarang',
            'sampling_date' => now()->subDays(5),
            'request_date' => now()->subDays(5),
            'received_date' => now()->subDays(4),
            'notes' => 'Pengujian rutin bulanan compliance lingkungan',
            'status' => 'Selesai',
        ]);

        SampleParameter::create([
            'sample_id' => $s1->id,
            'parameter_id' => $p1->id,
            'test_method' => $p1->test_method,
            'equipment_id' => $eq2->id,
            'analyst_id' => $staff->id,
            'test_value' => '7.4',
            'unit' => '-',
            'quality_standard_summary' => '6.0 - 9.0',
            'compliance_status' => 'Memenuhi',
            'status' => 'Submitted',
        ]);

        SampleParameter::create([
            'sample_id' => $s1->id,
            'parameter_id' => $p2->id,
            'test_method' => $p2->test_method,
            'equipment_id' => $eq4->id,
            'analyst_id' => $staff->id,
            'test_value' => '18.5',
            'unit' => 'mg/L',
            'quality_standard_summary' => '≤ 30.0 mg/L',
            'compliance_status' => 'Memenuhi',
            'status' => 'Submitted',
        ]);

        SampleParameter::create([
            'sample_id' => $s1->id,
            'parameter_id' => $p3->id,
            'test_method' => $p3->test_method,
            'equipment_id' => $eq1->id,
            'analyst_id' => $staff->id,
            'test_value' => '62.0',
            'unit' => 'mg/L',
            'quality_standard_summary' => '≤ 100.0 mg/L',
            'compliance_status' => 'Memenuhi',
            'status' => 'Submitted',
        ]);

        Certificate::create([
            'certificate_number' => 'COA/2026/09/0001',
            'sample_id' => $s1->id,
            'customer_id' => $customer->id,
            'pdf_path' => null,
            'verified_by' => $manager->id,
            'verified_at' => now()->subDays(1),
            'issued_at' => now()->subDays(1),
            'digital_signature_hash' => hash('sha256', 'COA-0001-'.now()->timestamp),
        ]);

        $pay1 = Payment::create([
            'payment_code' => 'PAY-20260901-0001',
            'customer_id' => $customer->id,
            'sample_id' => $s1->id,
            'type' => 'Pengujian',
            'amount' => 320000,
            'payment_method' => 'Transfer Bank BCA',
            'status' => 'Berhasil',
            'payment_reference' => 'TRX-BCA-982104921',
            'paid_at' => now()->subDays(4),
        ]);

        Invoice::create([
            'invoice_number' => 'INV/2026/09/0001',
            'customer_id' => $customer->id,
            'payment_id' => $pay1->id,
            'subtotal' => 320000,
            'deposit' => 0,
            'fine' => 0,
            'total' => 320000,
            'status' => 'Lunas',
        ]);

        Satisfaction::create([
            'customer_id' => $customer->id,
            'service_type' => 'Pengujian',
            'service_id' => $s1->id,
            'rating' => 5,
            'feedback' => 'Hasil pengujian sangat akurat dan terbit tepat waktu. Pelayanan staf sangat professional!',
        ]);

        // Sample 2: Waiting for Verification (Pending Manager approval)
        $s2 = Sample::create([
            'sample_code' => 'SMP-20260901-0002',
            'customer_id' => $customer2->id,
            'sample_type' => 'Air Sungai Intake',
            'sampling_location' => 'Sungai Cisadane Hulu',
            'sampling_date' => now()->subDays(2),
            'request_date' => now()->subDays(2),
            'received_date' => now()->subDays(2),
            'notes' => 'Pemeriksaan kualitas baku air minum',
            'status' => 'Menunggu Verifikasi',
        ]);

        SampleParameter::create([
            'sample_id' => $s2->id,
            'parameter_id' => $p1->id,
            'test_method' => $p1->test_method,
            'equipment_id' => $eq2->id,
            'analyst_id' => $staff->id,
            'test_value' => '6.8',
            'unit' => '-',
            'quality_standard_summary' => '6.0 - 9.0',
            'compliance_status' => 'Memenuhi',
            'status' => 'Submitted',
        ]);

        SampleParameter::create([
            'sample_id' => $s2->id,
            'parameter_id' => $p5->id,
            'test_method' => $p5->test_method,
            'equipment_id' => $eq3->id,
            'analyst_id' => $staff->id,
            'test_value' => '0.15',
            'unit' => 'mg/L',
            'quality_standard_summary' => '≤ 0.1 mg/L',
            'compliance_status' => 'Tidak Memenuhi',
            'status' => 'Submitted',
            'notes' => 'Kadar Timbal melebihi ambang batas baku mutu!',
        ]);

        // Sample 3: Newly submitted by customer
        Sample::create([
            'sample_code' => 'SMP-20260901-0003',
            'customer_id' => $customer->id,
            'sample_type' => 'Air Sumur Bor Industri',
            'sampling_location' => 'Sumur Dalam Unit 2 PT Hijau Lestari',
            'sampling_date' => now()->subHours(5),
            'request_date' => now()->subHours(5),
            'received_date' => null,
            'notes' => 'Uji kelayakan konsumsi air karyawan',
            'status' => 'Diajukan',
        ]);

        // 5. Equipment Borrowing Demo Data
        $brw1 = Borrowing::create([
            'borrow_code' => 'BRW-20260901-0001',
            'customer_id' => $customer->id,
            'start_date' => now()->subDays(3)->toDateString(),
            'end_date' => now()->addDays(2)->toDateString(),
            'actual_return_date' => null,
            'purpose' => 'Sampling air limbah berkala di 5 lokasi cabang Bekasi',
            'notes' => 'Perlu membawa 2 unit pH Meter portable',
            'status' => 'Dipinjam',
            'deposit_amount' => 1000000,
            'fine_amount' => 0,
            'refund_amount' => 0,
            'refund_status' => 'Belum',
        ]);

        BorrowingDetail::create([
            'borrowing_id' => $brw1->id,
            'equipment_id' => $eq2->id,
            'quantity' => 2,
        ]);

        $pay2 = Payment::create([
            'payment_code' => 'PAY-20260901-0002',
            'customer_id' => $customer->id,
            'borrowing_id' => $brw1->id,
            'type' => 'Deposit Peminjaman',
            'amount' => 1000000,
            'payment_method' => 'QRIS Direct Settlement',
            'status' => 'Berhasil',
            'payment_reference' => 'QRIS-DEPOSIT-88391',
            'paid_at' => now()->subDays(3),
        ]);

        Invoice::create([
            'invoice_number' => 'INV/2026/09/0002',
            'customer_id' => $customer->id,
            'payment_id' => $pay2->id,
            'subtotal' => 600000, // 2 units x 150.000 x 2 days
            'deposit' => 1000000,
            'fine' => 0,
            'total' => 1600000,
            'status' => 'Lunas',
        ]);

        // Activity Logs
        LogAktivitas::record('Login', 'Auth', 'User Admin berhasil login ke sistem', $admin->id);
        LogAktivitas::record('Register Sample', 'Sampel', 'Pendaftaran sampel baru SMP-20260901-0001', $staff->id);
        LogAktivitas::record('Verify Sample Results', 'Verifikasi', 'Manajer menyetujui hasil uji SMP-20260901-0001 dan menerbitkan COA/2026/09/0001', $manager->id);

        // Notifications
        Notification::create([
            'user_id' => $customer->id,
            'title' => 'Sertifikat Hasil Uji Terbit!',
            'message' => 'COA untuk sampel SMP-20260901-0001 telah terbit dan dapat diunduh.',
            'link' => '/customer/samples/'.$s1->id,
        ]);

        Notification::create([
            'user_id' => $manager->id,
            'title' => 'Sampel Menunggu Verifikasi',
            'message' => 'Sampel SMP-20260901-0002 menunggu peninjauan dan verifikasi hasil uji.',
            'link' => '/manager/verifications/'.$s2->id,
        ]);
    }
}
