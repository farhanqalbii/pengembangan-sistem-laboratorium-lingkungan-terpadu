<?php

namespace App\Console\Commands;

use App\Models\Kalibrasi;
use App\Models\LogAktivitas;
use App\Models\Notification;
use App\Models\Peminjaman;
use App\Models\Peralatan;
use App\Models\User;
use Illuminate\Console\Command;

class SendLaboratoryReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lab:send-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kirim notifikasi pengingat otomatis untuk pengembalian alat (PJM-F-005), jadwal kalibrasi (INV-F-004), dan stok minimum (INV-F-003)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Memulai pengiriman notifikasi pengingat sistem laboratorium...');

        $returnCount = 0;
        $calibrationCount = 0;
        $stockCount = 0;

        $staffAndAdmins = User::whereIn('role', ['staff', 'admin'])->get();

        // 1. PJM-F-005: Notifikasi Pengingat Pengembalian Alat (H-1 atau Hari H)
        $dueBorrowings = Peminjaman::where('status', 'Dipinjam')
            ->whereDate('tanggal_selesai', '<=', now()->addDay())
            ->whereDate('tanggal_selesai', '>=', now()->subDays(3))
            ->with(['customer', 'borrowingDetails.equipment'])
            ->get();

        foreach ($dueBorrowings as $borrowing) {
            $alreadyNotifiedToday = Notification::where('user_id', $borrowing->customer_id)
                ->where('title', 'like', "%{$borrowing->borrow_code}%")
                ->whereDate('created_at', today())
                ->exists();

            if (! $alreadyNotifiedToday) {
                Notification::create([
                    'user_id' => $borrowing->customer_id,
                    'title' => "Pengingat Pengembalian Alat: {$borrowing->borrow_code}",
                    'message' => "Peminjaman alat {$borrowing->borrow_code} memiliki batas pengembalian pada {$borrowing->end_date->format('d/m/Y')}. Mohon kembalikan tepat waktu untuk menghindari denda keterlambatan.",
                    'link' => '/customer/borrowings/'.($borrowing->kode_peminjaman ?? $borrowing->borrow_code),
                ]);
                $returnCount++;
            }
        }

        // 2. INV-F-004: Notifikasi Pengingat Jadwal Kalibrasi (H-30 hari)
        $upcomingCalibrations = Kalibrasi::whereDate('tanggal_kalibrasi_berikutnya', '<=', now()->addDays(30))
            ->whereDate('tanggal_kalibrasi_berikutnya', '>=', now())
            ->with('equipment')
            ->get();

        foreach ($upcomingCalibrations as $calib) {
            if (! $calib->equipment) {
                continue;
            }

            foreach ($staffAndAdmins as $officer) {
                $alreadyNotified = Notification::where('user_id', $officer->id)
                    ->where('title', 'like', "%{$calib->equipment->code}%")
                    ->whereDate('created_at', '>=', now()->subDays(7))
                    ->exists();

                if (! $alreadyNotified) {
                    Notification::create([
                        'user_id' => $officer->id,
                        'title' => "Pengingat Kalibrasi: {$calib->equipment->name} ({$calib->equipment->code})",
                        'message' => "Alat {$calib->equipment->name} ({$calib->equipment->code}) harus segera dikalibrasi ulang sebelum tanggal {$calib->next_calibration_date->format('d/m/Y')}.",
                        'link' => '/staff/equipment/'.$calib->equipment_id,
                    ]);
                    $calibrationCount++;
                }
            }
        }

        // 3. INV-F-003: Notifikasi Stok Minimum Alat
        $lowStockEquipments = Peralatan::where('status', '!=', 'Tidak Tersedia')
            ->whereRaw('jumlah <= stok_minimum')
            ->get();

        foreach ($lowStockEquipments as $eq) {
            foreach ($staffAndAdmins as $officer) {
                $alreadyNotified = Notification::where('user_id', $officer->id)
                    ->where('title', 'like', "%Stok Minimum: {$eq->code}%")
                    ->whereDate('created_at', today())
                    ->exists();

                if (! $alreadyNotified) {
                    Notification::create([
                        'user_id' => $officer->id,
                        'title' => "Peringatan Stok Minimum: {$eq->code}",
                        'message' => "Stok alat {$eq->name} tersisa {$eq->quantity} unit (Ambang batas minimum: {$eq->min_stock} unit).",
                        'link' => '/staff/equipment/'.$eq->id,
                    ]);
                    $stockCount++;
                }
            }
        }

        // Catat di log audit aktivitas
        LogAktivitas::record(
            'Cron Pengingat Otomatis',
            'Sistem',
            "Pengingat otomatis dieksekusi: {$returnCount} pengingat pengembalian, {$calibrationCount} pengingat kalibrasi, {$stockCount} peringatan stok minimum terkirim.",
            null
        );

        $this->info('Pengingat berhasil diproses!');
        $this->table(['Kategori Pengingat', 'Jumlah Notifikasi'], [
            ['Pengembalian Alat (PJM-F-005)', $returnCount],
            ['Jadwal Kalibrasi (INV-F-004)', $calibrationCount],
            ['Stok Minimum (INV-F-003)', $stockCount],
        ]);

        return Command::SUCCESS;
    }
}
