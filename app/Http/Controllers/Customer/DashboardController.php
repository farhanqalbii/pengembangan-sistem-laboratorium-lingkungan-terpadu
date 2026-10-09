<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Notification;
use App\Models\Peminjaman;
use App\Models\Sample;
use App\Models\Sertifikat;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $activeSamples = Sample::where('customer_id', $user->id)
            ->whereNotIn('status', ['Selesai'])
            ->latest()
            ->get();

        $activeBorrowings = Peminjaman::where('customer_id', $user->id)
            ->whereIn('status', ['Diajukan', 'Disetujui', 'Dipinjam'])
            ->with('borrowingDetails.equipment')
            ->latest()
            ->get();

        $latestCertificates = Sertifikat::where('customer_id', $user->id)
            ->with('sample')
            ->latest()
            ->take(5)
            ->get();

        $unpaidInvoices = Invoice::where('customer_id', $user->id)
            ->where('status', 'Belum Lunas')
            ->with('payment')
            ->latest()
            ->get();

        $notifications = Notification::where('user_id', $user->id)
            ->latest()
            ->take(6)
            ->get();

        return view('customer.dashboard', compact(
            'activeSamples',
            'activeBorrowings',
            'latestCertificates',
            'unpaidInvoices',
            'notifications'
        ));
    }
}
