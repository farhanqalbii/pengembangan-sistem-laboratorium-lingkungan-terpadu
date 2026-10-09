<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Equipment Table
        Schema::create('equipment', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('category');
            $table->string('brand')->nullable();
            $table->text('specification')->nullable();
            $table->integer('purchase_year')->nullable();
            $table->integer('quantity')->default(1);
            $table->string('location')->nullable();
            $table->string('qr_code')->nullable();
            $table->string('barcode')->nullable();
            $table->integer('min_stock')->default(1);
            $table->enum('status', ['Tersedia', 'Dipinjam', 'Dalam Servis', 'Dalam Kalibrasi', 'Tidak Tersedia'])->default('Tersedia');
            $table->decimal('rental_price_per_day', 12, 2)->default(0);
            $table->decimal('deposit_amount', 12, 2)->default(0);
            $table->timestamps();
        });

        // 2. Calibrations Table
        Schema::create('calibrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipment_id')->constrained('equipment')->cascadeOnDelete();
            $table->date('calibration_date');
            $table->date('next_calibration_date');
            $table->string('result');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 3. Service Histories Table
        Schema::create('service_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipment_id')->constrained('equipment')->cascadeOnDelete();
            $table->date('service_date');
            $table->text('notes')->nullable();
            $table->string('status')->default('Selesai');
            $table->string('technician')->nullable();
            $table->timestamps();
        });

        // 4. Stock Histories Table
        Schema::create('stock_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipment_id')->constrained('equipment')->cascadeOnDelete();
            $table->string('type'); // Stok Awal, Penambahan, Pengurangan, Update, Peminjaman, Pengembalian, Kerusakan
            $table->integer('qty_before');
            $table->integer('qty_change');
            $table->integer('qty_after');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('notes')->nullable();
            $table->timestamps();
        });

        // 5. Parameters Table
        Schema::create('parameters', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category');
            $table->string('unit');
            $table->string('test_method');
            $table->decimal('price', 12, 2)->default(0);
            $table->timestamps();
        });

        // 6. Quality Standards Table
        Schema::create('quality_standards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parameter_id')->constrained('parameters')->cascadeOnDelete();
            $table->decimal('min_value', 10, 4)->nullable();
            $table->decimal('max_value', 10, 4)->nullable();
            $table->string('unit');
            $table->string('regulation_source');
            $table->string('status')->default('Aktif');
            $table->timestamps();
        });

        // 7. Samples Table
        Schema::create('samples', function (Blueprint $table) {
            $table->id();
            $table->string('sample_code')->unique();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->string('sample_type');
            $table->string('sampling_location');
            $table->dateTime('sampling_date')->nullable();
            $table->dateTime('request_date');
            $table->dateTime('received_date')->nullable();
            $table->text('notes')->nullable();
            $table->string('status')->default('Diajukan'); // Diajukan, Diterima, Diproses, Diuji, Menunggu Verifikasi, Revisi, Diverifikasi, Selesai
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
        });

        // 8. Sample Parameters Table
        Schema::create('sample_parameters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sample_id')->constrained('samples')->cascadeOnDelete();
            $table->foreignId('parameter_id')->constrained('parameters')->cascadeOnDelete();
            $table->string('test_method')->nullable();
            $table->foreignId('equipment_id')->nullable()->constrained('equipment')->nullOnDelete();
            $table->foreignId('analyst_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('test_value')->nullable();
            $table->string('unit')->nullable();
            $table->string('quality_standard_summary')->nullable();
            $table->string('compliance_status')->default('Pending'); // Memenuhi, Tidak Memenuhi, Pending
            $table->string('status')->default('Draft'); // Draft, Submitted, Revised
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 9. Certificates Table
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->string('certificate_number')->unique();
            $table->foreignId('sample_id')->constrained('samples')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->string('pdf_path')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('verified_at')->nullable();
            $table->dateTime('issued_at')->nullable();
            $table->string('digital_signature_hash')->nullable();
            $table->timestamps();
        });

        // 10. Borrowings Table
        Schema::create('borrowings', function (Blueprint $table) {
            $table->id();
            $table->string('borrow_code')->unique();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->date('actual_return_date')->nullable();
            $table->text('purpose');
            $table->text('notes')->nullable();
            $table->string('status')->default('Diajukan'); // Diajukan, Disetujui, Ditolak, Dipinjam, Dikembalikan, Dibatalkan
            $table->boolean('extension_requested')->default(false);
            $table->date('extended_end_date')->nullable();
            $table->string('extension_status')->nullable(); // Pending, Disetujui, Ditolak
            $table->decimal('deposit_amount', 12, 2)->default(0);
            $table->decimal('fine_amount', 12, 2)->default(0);
            $table->decimal('refund_amount', 12, 2)->default(0);
            $table->string('refund_status')->default('Belum'); // Belum, Diprofilkan, Refunded
            $table->timestamps();
        });

        // 11. Borrowing Details Table
        Schema::create('borrowing_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('borrowing_id')->constrained('borrowings')->cascadeOnDelete();
            $table->foreignId('equipment_id')->constrained('equipment')->cascadeOnDelete();
            $table->integer('quantity')->default(1);
            $table->string('condition_return')->nullable(); // Baik, Rusak Ringan, Rusak Berat, Hilang
            $table->text('condition_notes')->nullable();
            $table->timestamps();
        });

        // 12. Payments Table
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_code')->unique();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('borrowing_id')->nullable()->constrained('borrowings')->nullOnDelete();
            $table->foreignId('sample_id')->nullable()->constrained('samples')->nullOnDelete();
            $table->string('type'); // Pengujian, Deposit Peminjaman, Denda Peminjaman
            $table->decimal('amount', 12, 2);
            $table->string('payment_method')->default('Transfer Bank');
            $table->string('status')->default('Menunggu Pembayaran'); // Menunggu Pembayaran, Berhasil, Gagal, Kadaluarsa, Refund Diproses, Refunded
            $table->string('payment_reference')->nullable();
            $table->dateTime('paid_at')->nullable();
            $table->timestamps();
        });

        // 13. Invoices Table
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('payment_id')->constrained('payments')->cascadeOnDelete();
            $table->decimal('subtotal', 12, 2);
            $table->decimal('deposit', 12, 2)->default(0);
            $table->decimal('fine', 12, 2)->default(0);
            $table->decimal('total', 12, 2);
            $table->string('status')->default('Belum Lunas'); // Belum Lunas, Lunas, Dibatalkan
            $table->string('pdf_path')->nullable();
            $table->timestamps();
        });

        // 14. Satisfactions Table
        Schema::create('satisfactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->string('service_type'); // Pengujian, Peminjaman Alat
            $table->unsignedBigInteger('service_id');
            $table->integer('rating'); // 1 - 5
            $table->text('feedback')->nullable();
            $table->timestamps();
        });

        // 15. Notifications Table
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('message');
            $table->string('link')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        // 16. Activity Logs Table
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('activity');
            $table->string('module');
            $table->text('description');
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('satisfactions');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('borrowing_details');
        Schema::dropIfExists('borrowings');
        Schema::dropIfExists('certificates');
        Schema::dropIfExists('sample_parameters');
        Schema::dropIfExists('samples');
        Schema::dropIfExists('quality_standards');
        Schema::dropIfExists('parameters');
        Schema::dropIfExists('stock_histories');
        Schema::dropIfExists('service_histories');
        Schema::dropIfExists('calibrations');
        Schema::dropIfExists('equipment');
    }
};
