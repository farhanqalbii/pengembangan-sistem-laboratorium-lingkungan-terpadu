<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\LogAktivitas;
use App\Models\Notification;
use App\Models\Pembayaran;
use App\Models\Peralatan;
use App\Models\Sample;
use App\Models\SampleParameter;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SampleController extends Controller
{
    protected function authorizeStaff(): void
    {
        abort_unless(auth()->user() && auth()->user()->isStaff(), 403, 'Akses ditolak: Hanya Staf Analis yang berwenang mengelola sampel.');
    }

    public function index(Request $request)
    {
        $this->authorizeStaff();

        $query = Sample::with(['customer', 'sampleParameters.parameter', 'certificate', 'payments']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('sample_code', 'like', "%{$search}%")
                    ->orWhere('sample_type', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($cq) use ($search) {
                        $cq->where('name', 'like', "%{$search}%")
                            ->orWhere('company_name', 'like', "%{$search}%");
                    });
            });
        }

        $samples = $query->latest()->paginate(10)->withQueryString();

        return view('staff.samples.index', compact('samples'));
    }

    public function show(Sample $sample)
    {
        $this->authorizeStaff();

        $sample->load(['customer', 'sampleParameters.parameter.qualityStandard', 'sampleParameters.equipment', 'sampleParameters.analyst', 'certificate', 'payments.invoice']);

        $assignedAnalystIds = $sample->sampleParameters->pluck('analyst_id')->filter()->all();
        $analysts = User::whereIn('role', ['staff', 'admin'])
            ->orWhereIn('id', $assignedAnalystIds)
            ->get();

        $assignedEquipmentIds = $sample->sampleParameters->pluck('id_peralatan')->filter()->all();
        $equipmentList = Peralatan::where('status', 'Tersedia')
            ->orWhereIn('id', $assignedEquipmentIds)
            ->get();

        return view('staff.samples.show', compact('sample', 'analysts', 'equipmentList'));
    }

    public function receive(Request $request, Sample $sample)
    {
        $this->authorizeStaff();

        if ($sample->status !== 'Diajukan') {
            return back()->withErrors(['error' => "Sampel tidak dapat diterima karena saat ini berstatus '{$sample->status}' (hanya sampel 'Diajukan' yang dapat diterima)."]);
        }

        // Aturan Bisnis: Pembayaran harus terverifikasi lunas sebelum staf dapat menerima sampel
        $totalPrice = $sample->calculateTotalPrice();
        if ($totalPrice > 0) {
            $latestPayment = $sample->payments()->latest()->first();

            if (! $latestPayment) {
                return back()->withErrors(['error' => 'Persetujuan sampel ditolak: Bukti tagihan pembayaran tidak ditemukan dalam sistem.']);
            }

            if ($latestPayment->status === 'Menunggu Pembayaran') {
                return back()->withErrors(['error' => 'Persetujuan sampel ditolak: Pembayaran pengujian masih berstatus pending / belum diselesaikan oleh customer.']);
            }

            if (in_array($latestPayment->status, ['Gagal', 'Kadaluarsa'])) {
                return back()->withErrors(['error' => "Persetujuan sampel ditolak: Pembayaran pengujian berstatus '{$latestPayment->status}'. Customer harus melakukan pembayaran ulang."]);
            }

            if ($latestPayment->status !== 'Berhasil') {
                return back()->withErrors(['error' => 'Persetujuan sampel ditolak: Pembayaran pengujian belum terkonfirmasi berhasil.']);
            }
        }

        $validated = $request->validate([
            'received_date' => ['required', 'date'],
        ]);

        try {
            DB::transaction(function () use ($sample, $validated, $totalPrice) {
                // Lock row sampel untuk mencegah race condition saat persetujuan simultan
                $lockedSample = Sample::where('id', $sample->id)->lockForUpdate()->firstOrFail();

                if ($lockedSample->status !== 'Diajukan') {
                    throw new \RuntimeException("Sampel sudah diproses sebelumnya (Status saat ini: {$lockedSample->status}).");
                }

                if ($totalPrice > 0) {
                    $hasVerifiedPayment = $lockedSample->payments()->where('status', 'Berhasil')->exists();
                    if (! $hasVerifiedPayment) {
                        throw new \RuntimeException('Persetujuan sampel ditolak: Pembayaran pengujian belum terverifikasi berhasil dalam database.');
                    }
                }

                $lockedSample->update([
                    'received_date' => $validated['received_date'],
                    'status' => 'Diterima',
                ]);

                LogAktivitas::record('Penerimaan Sampel', 'Sampel', 'Staf '.auth()->user()->name." memverifikasi penerimaan sampel {$lockedSample->sample_code}.", auth()->id());

                Notification::create([
                    'user_id' => $lockedSample->customer_id,
                    'title' => 'Sampel Telah Diterima',
                    'message' => "Sampel {$lockedSample->sample_code} telah disetujui & diterima oleh staf laboratorium dan siap diproses ke tahap pengujian.",
                    'link' => '/customer/samples/'.$lockedSample->sample_code,
                ]);
            });
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return back()->with('success', "Sampel {$sample->sample_code} berhasil diterima dan didaftarkan.");
    }

    public function assign(Request $request, Sample $sample)
    {
        $this->authorizeStaff();

        if (! in_array($sample->status, ['Diterima', 'Diproses', 'Diuji', 'Revisi'])) {
            return back()->withInput()->withErrors(['error' => "Alokasi analis/alat hanya dapat dilakukan untuk sampel berstatus 'Diterima', 'Diproses', 'Diuji', atau 'Revisi' (saat ini: {$sample->status})."]);
        }

        $messages = [
            'assignments.required' => 'Data alokasi parameter tidak boleh kosong.',
            'assignments.array' => 'Format alokasi parameter tidak valid.',
            'assignments.min' => 'Minimal satu parameter harus dialokasikan.',
            'assignments.*.sample_parameter_id.required' => 'Parameter sampel wajib dipilih.',
            'assignments.*.sample_parameter_id.integer' => 'ID parameter sampel harus berupa angka.',
            'assignments.*.sample_parameter_id.exists' => 'Parameter sampel tidak valid atau tidak sesuai dengan sampel ini.',
            'assignments.*.analyst_id.required' => 'Analis penanggung jawab wajib dipilih untuk setiap parameter.',
            'assignments.*.analyst_id.integer' => 'ID analis penanggung jawab harus berupa angka.',
            'assignments.*.analyst_id.exists' => 'Analis penanggung jawab yang dipilih tidak valid atau tidak terdaftar.',
            'assignments.*.equipment_id.required' => 'Instrumen/alat pengujian wajib dipilih untuk setiap parameter.',
            'assignments.*.equipment_id.integer' => 'ID instrumen/alat harus berupa angka.',
            'assignments.*.equipment_id.exists' => 'Instrumen/alat pengujian yang dipilih tidak valid atau tidak terdaftar.',
        ];

        $validated = $request->validate([
            'assignments' => ['required', 'array', 'min:1'],
            'assignments.*.sample_parameter_id' => [
                'required',
                'integer',
                Rule::exists('sample_parameters', 'id')->where(function ($q) use ($sample) {
                    $q->where('sample_id', $sample->id);
                }),
            ],
            'assignments.*.analyst_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where(function ($q) {
                    $q->whereIn('role', ['staff', 'admin']);
                }),
            ],
            'assignments.*.equipment_id' => [
                'required',
                'integer',
                'exists:peralatan,id',
            ],
        ], $messages);

        // Pastikan seluruh parameter pada sampel ini dialokasikan tanpa ada yang terlewat
        // Cast ke integer agar perbandingan tipe data konsisten antara database (int) dan request HTTP (string)
        $sampleParameterIds = $sample->sampleParameters()->pluck('id')->map(fn ($id) => (int) $id)->sort()->values()->all();
        $submittedParameterIds = collect($validated['assignments'])->pluck('sample_parameter_id')->map(fn ($id) => (int) $id)->unique()->sort()->values()->all();

        if (count($sampleParameterIds) !== count($submittedParameterIds) || array_diff($sampleParameterIds, $submittedParameterIds) !== []) {
            return back()->withInput()->withErrors(['assignments' => 'Semua parameter uji sampel wajib dialokasikan analis dan alatnya secara lengkap.']);
        }

        DB::transaction(function () use ($sample, $validated) {
            foreach ($validated['assignments'] as $item) {
                $sp = SampleParameter::where('id', $item['sample_parameter_id'])
                    ->where('sample_id', $sample->id)
                    ->firstOrFail();

                // Pastikan test_method selalu terisi dari Parameter.test_method jika belum terisi
                $testMethod = $sp->test_method ?: ($sp->parameter ? $sp->parameter->test_method : null);

                $sp->update([
                    'analyst_id' => (int) $item['analyst_id'],
                    'id_peralatan' => (int) $item['equipment_id'],
                    'test_method' => $testMethod,
                ]);
            }

            // Alur status: Setelah alokasi parameter/alat selesai, sampel masuk ke tahap Diproses / Diuji
            $nextStatus = $sample->status === 'Revisi' ? 'Diproses' : 'Diuji';
            $sample->update(['status' => $nextStatus]);

            LogAktivitas::record('Penugasan Pengujian', 'Sampel', "Staf menentukan parameter, alat, dan analis untuk sampel {$sample->sample_code} (Status: {$nextStatus}).", auth()->id());

            // Notify Customer bahwa sampel masuk tahap pengujian
            Notification::create([
                'user_id' => $sample->customer_id,
                'title' => 'Sampel Sedang Diuji',
                'message' => "Sampel {$sample->sample_code} telah dialokasikan ke analis dan instrumen laboratorium untuk proses pengujian.",
                'link' => '/customer/samples/'.$sample->sample_code,
            ]);
        });

        return redirect()->route('staff.samples.show', $sample)->with('success', 'Penugasan analis dan pengalokasian alat berhasil disimpan.');
    }

    public function editResults(Sample $sample)
    {
        $this->authorizeStaff();

        $sample->load(['sampleParameters.parameter.qualityStandard', 'sampleParameters.equipment', 'sampleParameters.analyst']);
        $equipmentList = Peralatan::where('status', 'Tersedia')->get();

        return view('staff.samples.input-results', compact('sample', 'equipmentList'));
    }

    public function storeResults(Request $request, Sample $sample)
    {
        $this->authorizeStaff();

        if (! in_array($sample->status, ['Diproses', 'Diuji', 'Revisi'])) {
            return back()->withErrors(['error' => "Hasil pengujian hanya dapat disimpan jika sampel berstatus 'Diproses', 'Diuji', atau 'Revisi' (saat ini: {$sample->status})."]);
        }

        $validated = $request->validate([
            'results' => ['required', 'array'],
            'results.*.sample_parameter_id' => ['required', 'exists:sample_parameters,id'],
            'results.*.test_value' => ['required', 'string'],
            'results.*.notes' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($sample, $validated) {
            foreach ($validated['results'] as $res) {
                $sp = SampleParameter::with('parameter.qualityStandard')->find($res['sample_parameter_id']);
                if ($sp && $sp->sample_id === $sample->id) {
                    $numVal = is_numeric($res['test_value']) ? (float) $res['test_value'] : null;

                    $compliance = 'Pending';
                    if ($sp->parameter && $sp->parameter->qualityStandard) {
                        $compliance = $sp->parameter->qualityStandard->checkCompliance($numVal);
                    } else {
                        $compliance = 'Memenuhi';
                    }

                    $sp->update([
                        'test_value' => $res['test_value'],
                        'unit' => $sp->parameter ? $sp->parameter->unit : null,
                        'compliance_status' => $compliance,
                        'status' => 'Submitted',
                        'notes' => $res['notes'] ?? null,
                        'analyst_id' => auth()->id(),
                    ]);
                }
            }

            $sample->update(['status' => 'Menunggu Verifikasi']);

            LogAktivitas::record('Input Hasil Uji', 'Hasil Uji', "Staf/Analis menginput hasil pengujian sampel {$sample->sample_code}. Menunggu verifikasi Manajer Lab.", auth()->id());

            // Notify Manager
            $managers = User::where('role', 'manager')->get();
            foreach ($managers as $mgr) {
                Notification::create([
                    'user_id' => $mgr->id,
                    'title' => 'Hasil Uji Menunggu Verifikasi',
                    'message' => "Staf telah menginput hasil uji untuk sampel {$sample->sample_code}. Silakan lakukan verifikasi.",
                    'link' => '/manager/dashboard',
                ]);
            }

            // Notify Customer bahwa hasil uji telah selesai diinput
            Notification::create([
                'user_id' => $sample->customer_id,
                'title' => 'Hasil Uji Selesai Diinput',
                'message' => "Hasil uji untuk sampel {$sample->sample_code} telah selesai diinput dan sedang menunggu verifikasi Manajer Lab.",
                'link' => '/customer/samples/'.$sample->sample_code,
            ]);
        });

        return redirect()->route('staff.samples.show', $sample)
            ->with('success', "Hasil pengujian sampel {$sample->sample_code} berhasil disimpan dan diteruskan ke Manajer Lab untuk verifikasi.");
    }
}
