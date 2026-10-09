<?php

namespace App\Providers;

use App\Models\BakuMutu;
use App\Models\DetailPeminjaman;
use App\Models\Kalibrasi;
use App\Models\KepuasanPelanggan;
use App\Models\LogAktivitas;
use App\Models\Pembayaran;
use App\Models\Peminjaman;
use App\Models\Peralatan;
use App\Models\RiwayatServis;
use App\Models\RiwayatStok;
use App\Models\Sertifikat;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Class aliases for backward compatibility
        $aliases = [
            'App\Models\Borrowing' => Peminjaman::class,
            'App\Models\Equipment' => Peralatan::class,
            'App\Models\Payment' => Pembayaran::class,
            'App\Models\Certificate' => Sertifikat::class,
            'App\Models\QualityStandard' => BakuMutu::class,
            'App\Models\BorrowingDetail' => DetailPeminjaman::class,
            'App\Models\Calibration' => Kalibrasi::class,
            'App\Models\ServiceHistory' => RiwayatServis::class,
            'App\Models\StockHistory' => RiwayatStok::class,
            'App\Models\Satisfaction' => KepuasanPelanggan::class,
            'App\Models\ActivityLog' => LogAktivitas::class,
        ];

        foreach ($aliases as $alias => $class) {
            if (! class_exists($alias, false)) {
                class_alias($class, $alias);
            }
        }

        // Route model bindings
        Route::model('borrowing', Peminjaman::class);
        Route::model('equipment', Peralatan::class);
        Route::model('payment', Pembayaran::class);
        Route::model('certificate', Sertifikat::class);
    }
}
