<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::rename('equipment', 'peralatan');
        Schema::rename('calibrations', 'kalibrasi');
        Schema::rename('service_histories', 'riwayat_servis');
        Schema::rename('stock_histories', 'riwayat_stok');
        Schema::rename('quality_standards', 'baku_mutu');
        Schema::rename('borrowings', 'peminjaman');
        Schema::rename('borrowing_details', 'detail_peminjaman');
        Schema::rename('payments', 'pembayaran');
        Schema::rename('certificates', 'sertifikat');
        Schema::rename('satisfactions', 'kepuasan_pelanggan');
        Schema::rename('activity_logs', 'log_aktivitas');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename('log_aktivitas', 'activity_logs');
        Schema::rename('kepuasan_pelanggan', 'satisfactions');
        Schema::rename('sertifikat', 'certificates');
        Schema::rename('pembayaran', 'payments');
        Schema::rename('detail_peminjaman', 'borrowing_details');
        Schema::rename('peminjaman', 'borrowings');
        Schema::rename('baku_mutu', 'quality_standards');
        Schema::rename('riwayat_stok', 'stock_histories');
        Schema::rename('riwayat_servis', 'service_histories');
        Schema::rename('kalibrasi', 'calibrations');
        Schema::rename('peralatan', 'equipment');
    }
};
