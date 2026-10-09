<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\LogAktivitas;
use App\Models\Notification;
use App\Models\Sample;
use App\Models\Sertifikat;
use App\Models\User;
use Illuminate\Http\Request;

class VerifikasiController extends Controller
{
    public function index(Request $request)
    {
        $currentStatus = $request->get('status', 'Menunggu Verifikasi');

        $query = Sample::query()
            ->with(['customer', 'sampleParameters.parameter', 'certificate']);

        if ($currentStatus !== 'all') {
            $query->where('status', $currentStatus);
        } else {
            $query->whereIn('status', ['Menunggu Verifikasi', 'Revisi', 'Diverifikasi', 'Selesai']);
        }

        $samples = $query->latest()->paginate(10)->withQueryString();

        $countPending = Sample::where('status', 'Menunggu Verifikasi')->count();
        $countRevision = Sample::where('status', 'Revisi')->count();
        $countDone = Sample::where('status', 'Selesai')->count();
        $countAll = Sample::whereIn('status', ['Menunggu Verifikasi', 'Revisi', 'Diverifikasi', 'Selesai'])->count();

        return view('manager.verifikasi.index', compact(
            'samples',
            'currentStatus',
            'countPending',
            'countRevision',
            'countDone',
            'countAll'
        ));
    }

    public function show(Sample $sample)
    {
        $sample->load(['customer', 'sampleParameters.parameter.qualityStandard', 'sampleParameters.equipment', 'sampleParameters.analyst', 'certificate']);

        return view('manager.verifikasi.show', compact('sample'));
    }

    public function approve(Sample $sample)
    {
        if ($sample->status !== 'Menunggu Verifikasi') {
            return back()->withErrors(['error' => 'Sampel ini tidak dalam status Menunggu Verifikasi.']);
        }

        $certificate = null;
        $coaNumber = '';

        \Illuminate\Support\Facades\DB::transaction(function () use ($sample, &$certificate, &$coaNumber) {
            // Transisi ke status Diverifikasi lalu Selesai secara sah
            $sample->update([
                'status' => 'Diverifikasi',
                'rejection_reason' => null,
            ]);

            // Generate Certificate of Analysis (COA)
            $coaNumber = 'COA/'.date('Y/m').'/'.sprintf('%04d', Sertifikat::whereYear('created_at', date('Y'))->count() + 1);
            $issuedAt = now();
            $signatureHash = Sertifikat::generateSignature($sample, $coaNumber, auth()->id(), (string) $issuedAt->timestamp);

            $certificate = Sertifikat::create([
                'nomor_sertifikat' => $coaNumber,
                'certificate_number' => $coaNumber,
                'sample_id' => $sample->id,
                'customer_id' => $sample->customer_id,
                'verified_by' => auth()->id(),
                'tanggal_verifikasi' => $issuedAt,
                'tanggal_terbit' => $issuedAt,
                'hash_tanda_tangan' => $signatureHash,
            ]);

            // Status akhir sampel menjadi Selesai setelah COA resmi terbit
            $sample->update(['status' => 'Selesai']);

            LogAktivitas::record('Verifikasi & Terbit COA', 'Verifikasi', 'Manajer Lab ('.auth()->user()->name.") menyetujui verifikasi teknis sampel {$sample->sample_code} dan menerbitkan Sertifikat Hasil Uji {$coaNumber}.", auth()->id());

            // Notify Customer
            Notification::create([
                'user_id' => $sample->customer_id,
                'title' => 'Sertifikat Hasil Uji (COA) Terbit!',
                'message' => "Sertifikat Hasil Uji {$coaNumber} untuk sampel {$sample->sample_code} telah diterbitkan dan siap diunduh.",
                'link' => '/customer/certificates/'.$certificate->id,
            ]);
        });

        return redirect()->route('manager.verifications.show', $sample)
            ->with('success', "Verifikasi teknis berhasil disetujui! Certificate of Analysis ({$coaNumber}) telah diterbitkan dengan Tanda Tangan Digital sah.");
    }

    public function reject(Request $request, Sample $sample)
    {
        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:1000'],
        ]);

        $sample->update([
            'status' => 'Revisi',
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        LogAktivitas::record('Permintaan Revisi Hasil', 'Verifikasi', "Manajer Lab meminta revisi hasil uji sampel {$sample->sample_code}: {$validated['rejection_reason']}", auth()->id());

        // Notify Staff
        $staffUsers = User::whereIn('role', ['staff', 'admin'])->get();
        foreach ($staffUsers as $staff) {
            Notification::create([
                'user_id' => $staff->id,
                'title' => 'Revisi Hasil Uji Sampel',
                'message' => "Manajer Lab meminta revisi hasil uji sampel {$sample->sample_code}: {$validated['rejection_reason']}",
                'link' => '/staff/samples/'.$sample->id,
            ]);
        }

        return redirect()->route('manager.verifications.show', $sample)
            ->with('warning', "Status sampel {$sample->sample_code} diubah menjadi 'Revisi'. Catatan revisi telah disampaikan ke staf analis.");
    }
}
