<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
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
    public function index(Request $request)
    {
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
        if ($sample->status !== 'Diajukan') {
            return back()->withErrors(['error' => "Sampel tidak dapat diterima karena saat ini berstatus '{$sample->status}' (hanya sampel 'Diajukan' yang dapat diterima)."]);
        }

        $validated = $request->validate([
            'received_date' => ['required', 'date'],
        ]);

        DB::transaction(function () use ($sample, $validated) {
            $sample->update([
                'received_date' => $validated['received_date'],
                'status' => 'Diterima',
            ]);

            // Generate invoice for testing payment if not created
            $totalPrice = $sample->calculateTotalPrice();
            if ($totalPrice > 0 && ! $sample->payments()->exists()) {
                $payCode = 'PAY-'.date('Ymd').'-'.sprintf('%04d', Pembayaran::whereDate('created_at', today())->count() + 1);
                $payment = Pembayaran::create([
                    'payment_code' => $payCode,
                    'customer_id' => $sample->customer_id,
                    'sample_id' => $sample->id,
                    'type' => 'Pengujian',
                    'amount' => $totalPrice,
                    'payment_method' => 'Transfer Bank',
                    'status' => 'Menunggu Pembayaran',
                ]);

                $invCode = 'INV/'.date('Y/m').'/'.sprintf('%04d', Invoice::whereYear('created_at', date('Y'))->count() + 1);
                Invoice::create([
                    'invoice_number' => $invCode,
                    'customer_id' => $sample->customer_id,
                    'payment_id' => $payment->id,
                    'subtotal' => $totalPrice,
                    'deposit' => 0,
                    'fine' => 0,
                    'total' => $totalPrice,
                    'status' => 'Belum Lunas',
                ]);
            }

            LogAktivitas::record('Penerimaan Sampel', 'Sampel', 'Staf '.auth()->user()->name." memverifikasi penerimaan sampel {$sample->sample_code}.", auth()->id());

            Notification::create([
                'user_id' => $sample->customer_id,
                'title' => 'Sampel Telah Diterima',
                'message' => "Sampel {$sample->sample_code} telah diterima oleh laboratorium dan siap diproses.",
                'link' => '/customer/samples/'.$sample->id,
            ]);
        });

        return back()->with('success', "Sampel {$sample->sample_code} berhasil diterima dan didaftarkan.");
    }

    public function assign(Request $request, Sample $sample)
    {
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
                'link' => '/customer/samples/'.$sample->id,
            ]);
        });

        return redirect()->route('staff.samples.show', $sample->id)->with('success', 'Penugasan analis dan pengalokasian alat berhasil disimpan.');
    }

    public function editResults(Sample $sample)
    {
        $sample->load(['sampleParameters.parameter.qualityStandard', 'sampleParameters.equipment', 'sampleParameters.analyst']);
        $equipmentList = Peralatan::where('status', 'Tersedia')->get();

        return view('staff.samples.input-results', compact('sample', 'equipmentList'));
    }

    public function storeResults(Request $request, Sample $sample)
    {
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
                'link' => '/customer/samples/'.$sample->id,
            ]);
        });

        return redirect()->route('staff.samples.show', $sample)
            ->with('success', "Hasil pengujian sampel {$sample->sample_code} berhasil disimpan dan diteruskan ke Manajer Lab untuk verifikasi.");
    }
}
