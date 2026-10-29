<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LogAktivitas;
use App\Models\Pembayaran;
use App\Models\Peminjaman;
use App\Models\Peralatan;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        abort_unless(auth()->user() && auth()->user()->isAdmin(), 403, 'Akses ditolak: Dashboard hanya dapat diakses oleh Administrator.');

        $totalUsers = User::count();
        $pendingBorrowingsCount = Peminjaman::where('status', 'Diajukan')->count();
        $totalIncome = Pembayaran::where('status', 'Berhasil')->sum('jumlah_bayar');
        $pendingRefundsCount = Peminjaman::where('biaya_pengembalian', '>', 0)
            ->where(function ($q) {
                $q->whereIn('status_pengembalian', ['Diprofilkan', 'Pending', 'Belum']);
            })
            ->count();
        $recentActivityLogs = LogAktivitas::with('user')->latest()->take(8)->get();

        $totalEquipmentCount = Peralatan::sum('jumlah');
        $lowStockEquipment = Peralatan::whereRaw('jumlah <= stok_minimum')->get();
        $borrowedEquipmentCount = Peminjaman::where('status', 'Dipinjam')->count();

        $pendingBorrowings = Peminjaman::where('status', 'Diajukan')
            ->with(['customer', 'borrowingDetails.equipment'])
            ->latest()
            ->get();

        $recentTransactions = Pembayaran::with(['customer', 'invoice'])
            ->latest()
            ->take(6)
            ->get();

        $totalRevenue = $totalIncome;
        $totalFines = Peminjaman::sum('biaya_denda');
        $totalRefunds = Peminjaman::where('status_pengembalian', 'Refunded')->sum('biaya_pengembalian');

        $activityLogs = $recentActivityLogs;

        return view('admin.dashboard', compact(
            'totalUsers',
            'pendingBorrowingsCount',
            'totalIncome',
            'pendingRefundsCount',
            'recentActivityLogs',
            'totalEquipmentCount',
            'lowStockEquipment',
            'borrowedEquipmentCount',
            'pendingBorrowings',
            'recentTransactions',
            'totalRevenue',
            'totalFines',
            'totalRefunds',
            'activityLogs'
        ));
    }
}
