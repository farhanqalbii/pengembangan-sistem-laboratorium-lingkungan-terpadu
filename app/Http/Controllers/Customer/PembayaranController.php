<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Pembayaran;
use App\Services\PaymentGatewayService;
use App\Services\PdfGeneratorService;
use Illuminate\Http\Request;

class PembayaranController extends Controller
{
    public function index()
    {
        abort_unless(auth()->user() && auth()->user()->isCustomer(), 403, 'Akses ditolak: Hanya Customer yang berwenang melihat riwayat pembayaran customer.');

        $invoices = Invoice::where('customer_id', auth()->id())
            ->with(['payment.sample', 'payment.borrowing'])
            ->latest()
            ->paginate(10);

        return view('customer.pembayaran.index', compact('invoices'));
    }

    public function __construct(
        protected PaymentGatewayService $paymentGateway
    ) {}

    public function process(Request $request, Pembayaran $payment)
    {
        abort_unless(auth()->user() && auth()->user()->isCustomer(), 403, 'Akses ditolak.');

        if ($payment->customer_id !== auth()->id()) {
            abort(403, 'Akses ditolak. Anda tidak berhak memproses pembayaran ini.');
        }

        // Cegah pembayaran jika sudah berhasil lunas
        if ($payment->status === 'Berhasil') {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tagihan pembayaran ini sudah lunas sebelumnya.',
                ], 400);
            }

            return back()->with('info', 'Tagihan pembayaran ini sudah lunas sebelumnya.');
        }

        // Minta transaksi ke payment gateway resmi
        $gatewayResult = $this->paymentGateway->createTransaction($payment);

        if (! $gatewayResult['success']) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $gatewayResult['message'] ?? 'Payment gateway belum dikonfigurasi.',
                ], 422);
            }

            return back()->with('error', $gatewayResult['message'] ?? 'Payment gateway belum dikonfigurasi.');
        }

        // Simpan token pembayaran dari gateway jika tersedia
        if (! empty($gatewayResult['token'])) {
            $payment->update([
                'referensi_pembayaran' => $gatewayResult['token'],
                'status' => 'Menunggu Pembayaran',
            ]);
        }

        // Status pembayaran TETAP 'Menunggu Pembayaran' sampai webhook gateway diterima!
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'status' => 'Menunggu Pembayaran',
                'token' => $gatewayResult['token'] ?? null,
                'redirect_url' => $gatewayResult['redirect_url'] ?? null,
                'message' => 'Transaksi payment gateway berhasil dibuat. Mengarahkan ke halaman pembayaran...',
            ]);
        }

        if (! empty($gatewayResult['redirect_url'])) {
            return redirect()->away($gatewayResult['redirect_url']);
        }

        return back()->with('info', 'Transaksi pembayaran berhasil dibuat. Silakan selesaikan pembayaran di gateway.');
    }

    public function downloadInvoice(Invoice $invoice)
    {
        if ($invoice->customer_id !== auth()->id() && ! in_array(auth()->user()->role, ['admin', 'manager', 'staff'])) {
            abort(403);
        }

        $pdf = PdfGeneratorService::generateInvoicePdf($invoice);
        $filename = str_replace('/', '_', $invoice->invoice_number).'.pdf';

        return $pdf->download($filename);
    }

    public function checkStatus(Request $request, Pembayaran $payment)
    {
        abort_unless(auth()->user() && auth()->user()->isCustomer(), 403, 'Akses ditolak.');

        if ($payment->customer_id !== auth()->id()) {
            abort(403, 'Akses ditolak.');
        }

        if ($payment->status === 'Berhasil') {
            return response()->json([
                'success' => true,
                'status' => 'Berhasil',
                'message' => 'Pembayaran telah terverifikasi lunas.',
            ]);
        }

        $result = $this->paymentGateway->checkTransactionStatus($payment);

        return response()->json($result);
    }
}
