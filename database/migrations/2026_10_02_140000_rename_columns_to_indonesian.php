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
        // 1. detail_peminjaman
        Schema::table('detail_peminjaman', function (Blueprint $table) {
            $table->renameColumn('borrowing_id', 'id_peminjaman');
            $table->renameColumn('equipment_id', 'id_peralatan');
            $table->renameColumn('quantity', 'jumlah');
            $table->renameColumn('condition_return', 'kondisi_kembali');
            $table->renameColumn('condition_notes', 'catatan_kondisi');
        });

        // 2. peminjaman
        Schema::table('peminjaman', function (Blueprint $table) {
            $table->renameColumn('borrow_code', 'kode_peminjaman');
            $table->renameColumn('start_date', 'tanggal_mulai');
            $table->renameColumn('end_date', 'tanggal_selesai');
            $table->renameColumn('actual_return_date', 'tanggal_pengembalian');
            $table->renameColumn('purpose', 'tujuan');
            $table->renameColumn('notes', 'catatan');
            $table->renameColumn('extension_requested', 'perpanjangan_diajukan');
            $table->renameColumn('extended_end_date', 'tanggal_perpanjangan');
            $table->renameColumn('extension_status', 'status_perpanjangan');
            $table->renameColumn('deposit_amount', 'biaya_deposit');
            $table->renameColumn('fine_amount', 'biaya_denda');
            $table->renameColumn('refund_amount', 'biaya_pengembalian');
            $table->renameColumn('refund_status', 'status_pengembalian');
        });

        // 3. peralatan
        Schema::table('peralatan', function (Blueprint $table) {
            $table->renameColumn('code', 'kode');
            $table->renameColumn('name', 'nama');
            $table->renameColumn('category', 'kategori');
            $table->renameColumn('brand', 'merk');
            $table->renameColumn('specification', 'spesifikasi');
            $table->renameColumn('purchase_year', 'tahun_pembelian');
            $table->renameColumn('quantity', 'jumlah');
            $table->renameColumn('location', 'lokasi');
            $table->renameColumn('min_stock', 'stok_minimum');
            $table->renameColumn('rental_price_per_day', 'harga_sewa_per_hari');
            $table->renameColumn('deposit_amount', 'biaya_deposit');
        });

        // 4. kalibrasi
        Schema::table('kalibrasi', function (Blueprint $table) {
            $table->renameColumn('equipment_id', 'id_peralatan');
            $table->renameColumn('calibration_date', 'tanggal_kalibrasi');
            $table->renameColumn('next_calibration_date', 'tanggal_kalibrasi_berikutnya');
            $table->renameColumn('result', 'hasil');
            $table->renameColumn('notes', 'catatan');
        });

        // 5. riwayat_servis
        Schema::table('riwayat_servis', function (Blueprint $table) {
            $table->renameColumn('equipment_id', 'id_peralatan');
            $table->renameColumn('service_date', 'tanggal_servis');
            $table->renameColumn('notes', 'catatan');
            $table->renameColumn('technician', 'teknisi');
        });

        // 6. riwayat_stok
        Schema::table('riwayat_stok', function (Blueprint $table) {
            $table->renameColumn('equipment_id', 'id_peralatan');
            $table->renameColumn('type', 'tipe');
            $table->renameColumn('qty_before', 'jumlah_sebelum');
            $table->renameColumn('qty_change', 'jumlah_perubahan');
            $table->renameColumn('qty_after', 'jumlah_setelah');
            $table->renameColumn('notes', 'catatan');
        });

        // 7. baku_mutu
        Schema::table('baku_mutu', function (Blueprint $table) {
            $table->renameColumn('min_value', 'nilai_minimum');
            $table->renameColumn('max_value', 'nilai_maksimum');
            $table->renameColumn('regulation_source', 'sumber_regulasi');
        });

        // 8. pembayaran
        Schema::table('pembayaran', function (Blueprint $table) {
            $table->renameColumn('payment_code', 'kode_pembayaran');
            $table->renameColumn('borrowing_id', 'id_peminjaman');
            $table->renameColumn('type', 'tipe');
            $table->renameColumn('amount', 'jumlah_bayar');
            $table->renameColumn('payment_method', 'metode_pembayaran');
            $table->renameColumn('payment_reference', 'referensi_pembayaran');
            $table->renameColumn('paid_at', 'tanggal_bayar');
        });

        // 9. sertifikat
        Schema::table('sertifikat', function (Blueprint $table) {
            $table->renameColumn('certificate_number', 'nomor_sertifikat');
            $table->renameColumn('verified_at', 'tanggal_verifikasi');
            $table->renameColumn('issued_at', 'tanggal_terbit');
            $table->renameColumn('digital_signature_hash', 'hash_tanda_tangan');
        });

        // 10. kepuasan_pelanggan
        Schema::table('kepuasan_pelanggan', function (Blueprint $table) {
            $table->renameColumn('service_type', 'tipe_layanan');
            $table->renameColumn('service_id', 'id_layanan');
            $table->renameColumn('feedback', 'saran_masukan');
        });

        // 11. log_aktivitas
        Schema::table('log_aktivitas', function (Blueprint $table) {
            $table->renameColumn('activity', 'aktivitas');
            $table->renameColumn('module', 'modul');
            $table->renameColumn('description', 'deskripsi');
        });

        // 12. sample_parameters
        Schema::table('sample_parameters', function (Blueprint $table) {
            $table->renameColumn('equipment_id', 'id_peralatan');
        });

        // 13. invoices
        Schema::table('invoices', function (Blueprint $table) {
            $table->renameColumn('payment_id', 'id_pembayaran');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->renameColumn('id_pembayaran', 'payment_id');
        });

        Schema::table('sample_parameters', function (Blueprint $table) {
            $table->renameColumn('id_peralatan', 'equipment_id');
        });

        Schema::table('log_aktivitas', function (Blueprint $table) {
            $table->renameColumn('aktivitas', 'activity');
            $table->renameColumn('modul', 'module');
            $table->renameColumn('deskripsi', 'description');
        });

        Schema::table('kepuasan_pelanggan', function (Blueprint $table) {
            $table->renameColumn('tipe_layanan', 'service_type');
            $table->renameColumn('id_layanan', 'service_id');
            $table->renameColumn('saran_masukan', 'feedback');
        });

        Schema::table('sertifikat', function (Blueprint $table) {
            $table->renameColumn('nomor_sertifikat', 'certificate_number');
            $table->renameColumn('tanggal_verifikasi', 'verified_at');
            $table->renameColumn('tanggal_terbit', 'issued_at');
            $table->renameColumn('hash_tanda_tangan', 'digital_signature_hash');
        });

        Schema::table('pembayaran', function (Blueprint $table) {
            $table->renameColumn('kode_pembayaran', 'payment_code');
            $table->renameColumn('id_peminjaman', 'borrowing_id');
            $table->renameColumn('tipe', 'type');
            $table->renameColumn('jumlah_bayar', 'amount');
            $table->renameColumn('metode_pembayaran', 'payment_method');
            $table->renameColumn('referensi_pembayaran', 'payment_reference');
            $table->renameColumn('tanggal_bayar', 'paid_at');
        });

        Schema::table('baku_mutu', function (Blueprint $table) {
            $table->renameColumn('nilai_minimum', 'min_value');
            $table->renameColumn('nilai_maksimum', 'max_value');
            $table->renameColumn('sumber_regulasi', 'regulation_source');
        });

        Schema::table('riwayat_stok', function (Blueprint $table) {
            $table->renameColumn('id_peralatan', 'equipment_id');
            $table->renameColumn('tipe', 'type');
            $table->renameColumn('jumlah_sebelum', 'qty_before');
            $table->renameColumn('jumlah_perubahan', 'qty_change');
            $table->renameColumn('jumlah_setelah', 'qty_after');
            $table->renameColumn('catatan', 'notes');
        });

        Schema::table('riwayat_servis', function (Blueprint $table) {
            $table->renameColumn('id_peralatan', 'equipment_id');
            $table->renameColumn('tanggal_servis', 'service_date');
            $table->renameColumn('catatan', 'notes');
            $table->renameColumn('teknisi', 'technician');
        });

        Schema::table('kalibrasi', function (Blueprint $table) {
            $table->renameColumn('id_peralatan', 'equipment_id');
            $table->renameColumn('tanggal_kalibrasi', 'calibration_date');
            $table->renameColumn('tanggal_kalibrasi_berikutnya', 'next_calibration_date');
            $table->renameColumn('hasil', 'result');
            $table->renameColumn('catatan', 'notes');
        });

        Schema::table('peralatan', function (Blueprint $table) {
            $table->renameColumn('kode', 'code');
            $table->renameColumn('nama', 'name');
            $table->renameColumn('kategori', 'category');
            $table->renameColumn('merk', 'brand');
            $table->renameColumn('spesifikasi', 'specification');
            $table->renameColumn('tahun_pembelian', 'purchase_year');
            $table->renameColumn('jumlah', 'quantity');
            $table->renameColumn('lokasi', 'location');
            $table->renameColumn('stok_minimum', 'min_stock');
            $table->renameColumn('harga_sewa_per_hari', 'rental_price_per_day');
            $table->renameColumn('biaya_deposit', 'deposit_amount');
        });

        Schema::table('peminjaman', function (Blueprint $table) {
            $table->renameColumn('kode_peminjaman', 'borrow_code');
            $table->renameColumn('tanggal_mulai', 'start_date');
            $table->renameColumn('tanggal_selesai', 'end_date');
            $table->renameColumn('tanggal_pengembalian', 'actual_return_date');
            $table->renameColumn('tujuan', 'purpose');
            $table->renameColumn('catatan', 'notes');
            $table->renameColumn('perpanjangan_diajukan', 'extension_requested');
            $table->renameColumn('tanggal_perpanjangan', 'extended_end_date');
            $table->renameColumn('status_perpanjangan', 'extension_status');
            $table->renameColumn('biaya_deposit', 'deposit_amount');
            $table->renameColumn('biaya_denda', 'fine_amount');
            $table->renameColumn('biaya_pengembalian', 'refund_amount');
            $table->renameColumn('status_pengembalian', 'refund_status');
        });

        Schema::table('detail_peminjaman', function (Blueprint $table) {
            $table->renameColumn('id_peminjaman', 'borrowing_id');
            $table->renameColumn('id_peralatan', 'equipment_id');
            $table->renameColumn('jumlah', 'quantity');
            $table->renameColumn('kondisi_kembali', 'condition_return');
            $table->renameColumn('catatan_kondisi', 'condition_notes');
        });
    }
};
