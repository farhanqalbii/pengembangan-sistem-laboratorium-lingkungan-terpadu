<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LogAktivitas;
use Illuminate\Http\Request;

class LogAktivitasController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(auth()->user() && auth()->user()->isAdmin(), 403, 'Akses ditolak: Log aktivitas hanya dapat diakses oleh Administrator.');

        $query = LogAktivitas::with('user');

        if ($request->filled('module')) {
            $query->where('modul', $request->module);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('aktivitas', 'like', "%{$search}%")
                    ->orWhere('deskripsi', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $logs = $query->latest()->paginate(15)->withQueryString();
        $modules = LogAktivitas::distinct('modul')->pluck('modul');

        return view('admin.log-aktivitas.index', compact('logs', 'modules'));
    }
}
