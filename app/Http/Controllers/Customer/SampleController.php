<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\LogAktivitas;
use App\Models\Notification;
use App\Models\Parameter;
use App\Models\Pembayaran;
use App\Models\Sample;
use App\Models\SampleParameter;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SampleController extends Controller
{
    public function index(Request $request)
    {
        $query = Sample::where('customer_id', auth()->id())
            ->with(['sampleParameters.parameter', 'certificate']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('sample_code', 'like', "%{$search}%")
                    ->orWhere('sample_type', 'like', "%{$search}%")
                    ->orWhere('sampling_location', 'like', "%{$search}%");
            });
        }

        $samples = $query->latest()->paginate(10)->withQueryString();

        return view('customer.samples.index', compact('samples'));
    }

    public function create()
    {
        $parameters = Parameter::with('qualityStandard')->get()->groupBy('category');

        return view('customer.samples.create', compact('parameters'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sample_type' => ['required', 'string', 'max:255'],
            'sampling_location' => ['required', 'string', 'max:255'],
            'sampling_date' => ['required', 'date'],
            'parameters' => ['required', 'array', 'min:1'],
            'parameters.*' => ['exists:parameters,id'],
            'notes' => ['nullable', 'string'],
        ]);

        $sample = null;
        $sampleCode = '';

        DB::transaction(function () use ($validated, &$sample, &$sampleCode) {
            $sampleCode = 'SMP-'.date('Ymd').'-'.sprintf('%04d', Sample::whereDate('created_at', today())->count() + 1);

            $sample = Sample::create([
                'sample_code' => $sampleCode,
                'customer_id' => auth()->id(),
                'sample_type' => $validated['sample_type'],
                'sampling_location' => $validated['sampling_location'],
                'sampling_date' => $validated['sampling_date'],
                'request_date' => now(),
                'notes' => $validated['notes'] ?? null,
                'status' => 'Diajukan',
            ]);

            foreach ($validated['parameters'] as $paramId) {
                $param = Parameter::with('qualityStandard')->find($paramId);
                SampleParameter::create([
                    'sample_id' => $sample->id,
                    'parameter_id' => $param->id,
                    'test_method' => $param->test_method,
                    'quality_standard_summary' => $param->qualityStandard ? $param->qualityStandard->summary : 'N/A',
                    'compliance_status' => 'Pending',
                    'status' => 'Draft',
                ]);
            }

            // Generate invoice & payment immediately
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

            LogAktivitas::record('Pengajuan Sampel', 'Sampel', 'Customer '.auth()->user()->name." mengajukan sampel baru: {$sampleCode}", auth()->id());

            // Notify Customer (Konfirmasi Pengajuan)
            Notification::create([
                'user_id' => auth()->id(),
                'title' => 'Pengajuan Sampel Berhasil',
                'message' => "Sampel {$sampleCode} ({$sample->sample_type}) telah diajukan dan sedang menunggu verifikasi penerimaan oleh staf laboratorium.",
                'link' => '/customer/samples/'.$sample->id,
            ]);

            // Notify Staff & Admin
            $staffUsers = User::whereIn('role', ['staff', 'admin'])->get();
            foreach ($staffUsers as $staff) {
                Notification::create([
                    'user_id' => $staff->id,
                    'title' => 'Pengajuan Sampel Baru',
                    'message' => "Pengajuan sampel {$sampleCode} dari ".auth()->user()->name,
                    'link' => '/staff/samples/'.$sample->id,
                ]);
            }
        });

        return redirect()->route('customer.samples.show', $sample);
    }

    public function show(Sample $sample)
    {
        if ($sample->customer_id !== auth()->id()) {
            abort(403, 'Anda tidak diizinkan melihat sampel ini.');
        }

        // Ensure payment & invoice exist if sample has parameters with prices
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

        $sample->load(['sampleParameters.parameter.qualityStandard', 'sampleParameters.analyst', 'certificate', 'payments.invoice']);

        return view('customer.samples.show', compact('sample'));
    }
}
