@extends('layouts.app')

@section('title', 'Detail & Riwayat Alat ' . $equipment->code . ' - Staff')
@section('page-title', 'Detail Instrumen, Kalibrasi & Servis')

@section('content')
<div class="space-y-6" x-data="{ tab: 'calibration', showLabelModal: false }" x-effect="document.body.classList.toggle('overflow-hidden', showLabelModal); document.body.classList.toggle('modal-open', showLabelModal)">
    <!-- Header Card -->
    <div class="bg-white rounded-2xl border border-slate-200 p-4 sm:p-5 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 sm:w-20 sm:h-20 bg-slate-50 border border-slate-200 rounded-xl flex items-center justify-center overflow-hidden flex-shrink-0 p-1">
                @if($equipment->photo_url)
                    <img src="{{ $equipment->photo_url }}" alt="{{ $equipment->name }}" class="max-h-full max-w-full object-contain">
                @else
                    <i class="fa-solid fa-microscope text-2xl text-slate-300"></i>
                @endif
            </div>
            <div>
                <div class="flex items-center space-x-3 mb-1">
                    <span class="font-mono font-bold text-base sm:text-lg text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-lg border border-emerald-200">{{ $equipment->code }}</span>
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $equipment->status_badge_class }}">{{ $equipment->status }}</span>
                </div>
                <h1 class="text-lg sm:text-xl font-bold text-slate-800">{{ $equipment->name }}</h1>
                <p class="text-xs text-slate-500 mt-1">Kategori: {{ $equipment->category }} | Merk: {{ $equipment->brand ?? 'N/A' }} | Lokasi: {{ $equipment->location }}</p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <a href="{{ route('staff.equipment.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-white border border-slate-300 hover:bg-slate-100 text-slate-700 font-semibold text-xs rounded-xl shadow-2xs transition-colors cursor-pointer">
                <i class="fa-solid fa-arrow-left text-[11px] text-slate-400"></i> Kembali
            </a>
            <button type="button" @click="showLabelModal = true" class="px-3.5 py-2 bg-gradient-to-r from-emerald-600 to-teal-700 hover:from-emerald-500 hover:to-teal-600 text-white font-bold text-xs rounded-xl shadow-md shadow-emerald-700/20 transition-all flex items-center gap-1.5 cursor-pointer">
                <i class="fa-solid fa-qrcode"></i> Cetak Stiker Label
            </button>
            <a href="{{ route('equipment.detail', $equipment->code) }}" target="_blank" class="px-3.5 py-2 bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs rounded-xl shadow transition-colors">
                <i class="fa-solid fa-arrow-up-right-from-square mr-1"></i> Pratinjau Halaman QR
            </a>
            <a href="{{ route('staff.equipment.edit', $equipment->id) }}" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs rounded-xl shadow-lg shadow-emerald-600/30 transition-colors">
                <i class="fa-solid fa-pen-to-square mr-1"></i> Edit Data Alat
            </a>
        </div>
    </div>

    <!-- QR & Barcode Physical Identification Card -->
    <div class="bg-white rounded-2xl border border-slate-200/90 p-5 sm:p-6 shadow-sm">
        <div class="flex flex-col lg:flex-row items-center justify-between gap-6">
            <div class="flex flex-col sm:flex-row items-center gap-5 text-center sm:text-left w-full lg:w-auto">
                <!-- QR Code Display Box -->
                <div class="relative w-36 h-36 bg-white p-2.5 rounded-2xl border-2 border-emerald-500/30 ring-4 ring-emerald-500/10 shadow-sm flex items-center justify-center flex-shrink-0">
                    <div id="qrcode-display" class="flex items-center justify-center"></div>
                </div>

                <!-- Details & Explanation -->
                <div class="space-y-1.5 flex-1">
                    <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2">
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            <i class="fa-solid fa-check-circle text-[10px]"></i> QR Code Terdaftar Resmi
                        </span>
                        <span class="text-xs text-slate-400 font-mono">Kode: {{ $equipment->code }}</span>
                    </div>
                    <h3 class="text-base font-bold text-slate-800">Label Digital Instrumen: {{ $equipment->name }}</h3>
                    <p class="text-xs text-slate-500 max-w-xl leading-relaxed">
                        QR Code dan Barcode ini berfungsi sebagai identitas fisik alat. Cetak label stiker ini lalu tempelkan pada bodi fisik instrumen laboratorium agar dapat diverifikasi secara instan dengan Scanner Kamera.
                    </p>
                    <!-- 1D Barcode Container -->
                    <div class="pt-1 flex items-center justify-center sm:justify-start">
                        <svg id="barcode-display" class="max-h-12"></svg>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row lg:flex-col gap-2.5 w-full lg:w-auto flex-shrink-0">
                <button type="button" @click="showLabelModal = true" class="flex-1 lg:flex-none inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-500 active:scale-[0.98] text-white font-bold text-xs rounded-xl shadow-md shadow-emerald-600/20 transition-all cursor-pointer">
                    <i class="fa-solid fa-print"></i>
                    <span>Cetak Stiker Label</span>
                </button>
                <button type="button" onclick="downloadQRCode('{{ $equipment->code }}')" class="flex-1 lg:flex-none inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs border border-slate-300 rounded-xl transition-all shadow-2xs cursor-pointer">
                    <i class="fa-solid fa-download text-emerald-600"></i>
                    <span>Download QR (.PNG)</span>
                </button>
                <a href="{{ route('equipment.scan') }}" target="_blank" class="flex-1 lg:flex-none inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-slate-900 hover:bg-slate-800 text-cyan-300 font-bold text-xs rounded-xl transition-all shadow-md cursor-pointer">
                    <i class="fa-solid fa-camera"></i>
                    <span>Tes Scanner Kamera</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200 shadow-sm">
            <span class="text-slate-400 text-xs font-bold uppercase tracking-wider block">Stok Alat</span>
            <h3 class="text-xl sm:text-2xl font-extrabold text-slate-800 mt-1">{{ $equipment->quantity }} unit</h3>
            <span class="text-[11px] text-slate-500">Min Stock: {{ $equipment->min_stock }} unit</span>
        </div>
        <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200 shadow-sm">
            <span class="text-slate-400 text-xs font-bold uppercase tracking-wider block">Tarif Sewa & Deposit</span>
            <h3 class="text-base sm:text-lg font-bold text-emerald-700 mt-1">Rp {{ number_format($equipment->rental_price_per_day, 0, ',', '.') }} / hari</h3>
            <span class="text-[11px] text-slate-500">Deposit: Rp {{ number_format($equipment->deposit_amount, 0, ',', '.') }}</span>
        </div>
        <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200 shadow-sm">
            <span class="text-slate-400 text-xs font-bold uppercase tracking-wider block">Kalibrasi Terakhir</span>
            <h3 class="text-sm sm:text-base font-bold text-indigo-700 mt-1">
                {{ $equipment->calibrations->first() ? $equipment->calibrations->first()->calibration_date->format('d F Y') : 'Belum Pernah' }}
            </h3>
            <span class="text-[11px] text-slate-500">
                Next: {{ $equipment->calibrations->first() && $equipment->calibrations->first()->next_calibration_date ? $equipment->calibrations->first()->next_calibration_date->format('d/m/Y') : '-' }}
            </span>
        </div>
    </div>

    <!-- Tabs Header -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4">
        <div class="flex border-b border-slate-200 space-x-6 text-xs font-bold overflow-x-auto whitespace-nowrap">
            <button @click="tab = 'calibration'" :class="tab === 'calibration' ? 'border-b-2 border-emerald-600 text-emerald-600 pb-3' : 'text-slate-500 pb-3 hover:text-slate-800'">
                <i class="fa-solid fa-sliders mr-1"></i> Catatan Kalibrasi ({{ $equipment->calibrations->count() }})
            </button>
            <button @click="tab = 'service'" :class="tab === 'service' ? 'border-b-2 border-emerald-600 text-emerald-600 pb-3' : 'text-slate-500 pb-3 hover:text-slate-800'">
                <i class="fa-solid fa-wrench mr-1"></i> Catatan Servis ({{ $equipment->serviceHistories->count() }})
            </button>
            <button @click="tab = 'stock'" :class="tab === 'stock' ? 'border-b-2 border-emerald-600 text-emerald-600 pb-3' : 'text-slate-500 pb-3 hover:text-slate-800'">
                <i class="fa-solid fa-boxes-stacked mr-1"></i> Audit Trail Stok ({{ $equipment->stockHistories->count() }})
            </button>
        </div>

        <!-- Tab 1: Kalibrasi -->
        <div x-show="tab === 'calibration'" class="mt-6 space-y-6">
            <!-- Form Catat Kalibrasi Baru -->
            <form action="{{ route('staff.equipment.calibration', $equipment->id) }}" method="POST" class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-4">
                @csrf
                <h4 class="font-bold text-slate-800 text-xs uppercase tracking-wider"><i class="fa-solid fa-plus-circle mr-1 text-emerald-600"></i> Catat Hasil Kalibrasi Baru</h4>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Tanggal Kalibrasi</label>
                        <input type="date" name="calibration_date" value="{{ date('Y-m-d') }}" required class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Jadwal Kalibrasi Berikutnya</label>
                        <input type="date" name="next_calibration_date" value="{{ date('Y-m-d', strtotime('+1 year')) }}" required class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Lembaga / Laboratorium Pengkalibrasi</label>
                        <input type="text" name="calibrated_by" placeholder="Contoh: Balai Besar Kalibrasi Kemendag..." class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2">
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Nomor Sertifikat Kalibrasi</label>
                        <input type="text" name="certificate_number" placeholder="Contoh: KAL/2026/09/102..." class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Status / Hasil</label>
                        <select name="result" class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2">
                            <option value="Lulus">Lulus (Akurat & Layak Pakai)</option>
                            <option value="Perlu Penyesuaian">Perlu Penyesuaian / Drift</option>
                            <option value="Gagal">Gagal / Rusak</option>
                        </select>
                    </div>
                </div>
                <div class="text-right">
                    <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs rounded-xl shadow-md transition-colors cursor-pointer">Simpan Record Kalibrasi</button>
                </div>
            </form>

            <!-- Table History -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs min-w-[600px]">
                    <thead class="bg-slate-100 border-b border-slate-200 text-slate-600 font-bold">
                        <tr>
                            <th class="p-3">Tgl Kalibrasi</th>
                            <th class="p-3">Status / Hasil</th>
                            <th class="p-3">Keterangan / Lembaga</th>
                            <th class="p-3">Tgl Kalibrasi Ulang</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($equipment->calibrations as $c)
                            <tr>
                                <td class="p-3 font-semibold">{{ $c->calibration_date ? $c->calibration_date->format('d/m/Y') : '-' }}</td>
                                <td class="p-3">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ str_contains(strtolower($c->status ?? ''), 'lulus') || str_contains(strtolower($c->status ?? ''), 'lolos') ? 'bg-emerald-100 text-emerald-800' : (str_contains(strtolower($c->status ?? ''), 'gagal') || str_contains(strtolower($c->status ?? ''), 'rusak') ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800') }}">
                                        {{ $c->status ?? $c->hasil ?? $c->result ?? '-' }}
                                    </span>
                                </td>
                                <td class="p-3 text-slate-600">{{ $c->catatan ?? '-' }}</td>
                                <td class="p-3 text-slate-600 font-medium">{{ $c->next_calibration_date ? $c->next_calibration_date->format('d/m/Y') : '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="p-4 text-center text-slate-400">Belum ada riwayat kalibrasi.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tab 2: Servis -->
        <div x-show="tab === 'service'" class="mt-6 space-y-6">
            <form action="{{ route('staff.equipment.service', $equipment->id) }}" method="POST" class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-4">
                @csrf
                <h4 class="font-bold text-slate-800 text-xs uppercase tracking-wider"><i class="fa-solid fa-plus-circle mr-1 text-emerald-600"></i> Catat Log Pemeliharaan / Perbaikan Servis</h4>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Tanggal Servis</label>
                        <input type="date" name="service_date" value="{{ date('Y-m-d') }}" required class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Status Servis</label>
                        <select name="status" class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2">
                            <option value="Selesai">Selesai</option>
                            <option value="Dalam Proses">Dalam Proses</option>
                            <option value="Menunggu Suku Cadang">Menunggu Suku Cadang</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Penyedia Jasa / Teknisi</label>
                        <input type="text" name="technician_name" placeholder="Teknisi Vendor / Internal..." class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Deskripsi Kerusakan / Tindakan Perbaikan</label>
                    <textarea name="description" rows="2" required placeholder="Jelaskan tindakan pemeliharaan yang dilakukan..." class="w-full bg-white border border-slate-300 rounded-lg p-2 text-xs"></textarea>
                </div>
                <div class="text-right">
                    <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs rounded-xl shadow-md transition-colors cursor-pointer">Simpan Record Servis</button>
                </div>
            </form>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs min-w-[600px]">
                    <thead class="bg-slate-100 border-b border-slate-200 text-slate-600 font-bold">
                        <tr>
                            <th class="p-3">Tgl Servis</th>
                            <th class="p-3">Deskripsi Perbaikan</th>
                            <th class="p-3">Teknisi</th>
                            <th class="p-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($equipment->serviceHistories as $s)
                            <tr>
                                <td class="p-3 font-semibold">{{ $s->service_date ? $s->service_date->format('d/m/Y') : '-' }}</td>
                                <td class="p-3">{{ $s->catatan ?? $s->description ?? '-' }}</td>
                                <td class="p-3">{{ $s->technician_name ?? $s->teknisi ?? '-' }}</td>
                                <td class="p-3"><span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800">{{ $s->status ?? 'Selesai' }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="p-4 text-center text-slate-400">Belum ada riwayat perbaikan/servis.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tab 3: Stok Audit -->
        <div x-show="tab === 'stock'" class="mt-6">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs min-w-[600px]">
                    <thead class="bg-slate-100 border-b border-slate-200 text-slate-600 font-bold">
                        <tr>
                            <th class="p-3">Tgl & Waktu</th>
                            <th class="p-3">Tipe</th>
                            <th class="p-3">Jumlah Change</th>
                            <th class="p-3">Stok Akhir</th>
                            <th class="p-3">Keterangan / Alasan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($equipment->stockHistories as $sh)
                            <tr>
                                <td class="p-3 text-slate-600">{{ $sh->created_at->format('d/m/Y H:i') }}</td>
                                <td class="p-3 font-bold uppercase text-[10px] {{ $sh->type === 'in' ? 'text-emerald-600' : 'text-rose-600' }}">{{ $sh->type }}</td>
                                <td class="p-3 font-bold">{{ $sh->quantity }} unit</td>
                                <td class="p-3 font-bold text-slate-800">{{ $sh->current_stock }} unit</td>
                                <td class="p-3 text-slate-500">{{ $sh->notes ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="p-4 text-center text-slate-400">Belum ada pergerakan stok.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Pratinjau & Cetak Stiker Label Fisik -->
    <template x-teleport="body">
        <div 
            x-show="showLabelModal" 
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-[99999] overflow-y-auto bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4 sm:p-6"
            style="display: none;"
        >
            <div 
                @click.away="showLabelModal = false"
                class="bg-white rounded-3xl max-w-xl w-full shadow-2xl border border-slate-200 overflow-hidden my-auto flex flex-col max-h-[92vh]"
            >
                <!-- Modal Header -->
                <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/80">
                    <div class="flex items-center gap-2.5">
                        <span class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm shadow-xs">
                            <i class="fa-solid fa-print"></i>
                        </span>
                        <div>
                            <h3 class="text-base font-bold text-slate-900">Cetak Label Stiker Instrumen</h3>
                            <p class="text-xs text-slate-500">Pratinjau label stiker fisik sebelum dicetak ke printer stiker / kertas.</p>
                        </div>
                    </div>
                    <button type="button" @click="showLabelModal = false" class="w-8 h-8 rounded-full bg-slate-200/70 hover:bg-slate-300 text-slate-600 hover:text-slate-900 flex items-center justify-center transition-colors cursor-pointer">
                        <i class="fa-solid fa-xmark text-sm"></i>
                    </button>
                </div>

                <!-- Modal Body: Realistic Sticker Preview -->
                <div class="p-6 overflow-y-auto space-y-5 bg-slate-100/60 flex flex-col items-center">
                    <!-- The Printable Sticker Wrapper -->
                    <div id="printable-sticker-wrapper" class="w-full max-w-md bg-white border-2 border-slate-800 rounded-xl p-4 shadow-md space-y-3 font-sans text-slate-900 relative">
                        <!-- Sticker Top Brand Bar -->
                        <div class="flex items-center justify-between border-b-2 border-slate-800 pb-2">
                            <div class="flex items-center gap-2">
                                <img src="{{ asset('images/logo_icon.png') }}" alt="" class="h-6 w-auto object-contain">
                                <div>
                                    <h4 class="font-black text-[11px] uppercase tracking-wider text-slate-900 leading-tight">UPTD LAB LINGKUNGAN TERPADU</h4>
                                    <span class="text-[9px] text-slate-600 block leading-tight">Sistem Inventori Instrumen Terakreditasi</span>
                                </div>
                            </div>
                            <span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-900 border border-emerald-300 font-extrabold text-[9px] uppercase tracking-wider">
                                Verified
                            </span>
                        </div>

                        <!-- Sticker Main Content: QR Code + Details -->
                        <div class="flex items-center gap-3.5 pt-1">
                            <!-- QR Code inside sticker -->
                            <div class="p-1.5 bg-white border border-slate-300 rounded-lg flex-shrink-0 shadow-2xs">
                                <div id="qrcode-sticker" class="flex items-center justify-center"></div>
                            </div>

                            <!-- Instrument Details -->
                            <div class="flex-1 space-y-1 text-left min-w-0">
                                <div class="text-[10px] font-mono font-black text-emerald-800 bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-200 inline-block">
                                    {{ $equipment->code }}
                                </div>
                                <h5 class="text-xs font-black text-slate-900 leading-tight truncate" title="{{ $equipment->name }}">
                                    {{ $equipment->name }}
                                </h5>
                                <div class="text-[10px] text-slate-600 space-y-0.5">
                                    <div class="truncate"><strong>Merk:</strong> {{ $equipment->brand ?: '-' }}</div>
                                    <div class="truncate"><strong>Lokasi:</strong> {{ $equipment->location }}</div>
                                    <div class="truncate text-emerald-700">
                                        <strong>Kalibrasi:</strong> 
                                        @php $nextCal = $equipment->calibrations->first()?->next_calibration_date; @endphp
                                        {{ $nextCal ? $nextCal->format('d/m/Y') : 'Valid' }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 1D Barcode Container inside sticker -->
                        <div class="pt-1.5 border-t border-slate-200 flex flex-col items-center justify-center">
                            <svg id="barcode-sticker" class="max-h-8 w-full"></svg>
                            <span class="text-[8px] text-slate-400 italic mt-0.5 text-center block">
                                Pindai QR ini untuk membuka rincian spesifikasi & sertifikat kalibrasi instrumen.
                            </span>
                        </div>
                    </div>

                    <div class="text-center text-xs text-slate-500 max-w-sm">
                        <p class="flex items-center justify-center gap-1.5 text-slate-600 font-medium">
                            <i class="fa-solid fa-lightbulb text-amber-500"></i>
                            <span>Tips: Gunakan kertas stiker label ukuran $\approx$ 80mm $\times$ 50mm untuk hasil cetak ideal.</span>
                        </p>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="p-4 border-t border-slate-100 flex items-center justify-end gap-3 bg-slate-50/80">
                    <button type="button" @click="showLabelModal = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800 bg-white border border-slate-200 hover:bg-slate-100 rounded-xl transition-all cursor-pointer">
                        Tutup
                    </button>
                    <button type="button" onclick="printSticker()" class="px-5 py-2.5 text-xs font-bold text-white bg-gradient-to-r from-emerald-600 to-teal-700 hover:from-emerald-500 hover:to-teal-600 rounded-xl shadow-md shadow-emerald-700/20 flex items-center gap-2 transition-all active:scale-95 cursor-pointer">
                        <i class="fa-solid fa-print"></i>
                        <span>Print Stiker Sekarang</span>
                    </button>
                </div>
            </div>
        </div>
    </template>
</div>
@endsection

@push('styles')
<style>
@media print {
    /* Hide everything in page except sticker */
    body * {
        visibility: hidden !important;
    }
    #printable-sticker-wrapper, #printable-sticker-wrapper * {
        visibility: visible !important;
    }
    #printable-sticker-wrapper {
        position: fixed !important;
        left: 50% !important;
        top: 20px !important;
        transform: translateX(-50%) !important;
        width: 85mm !important;
        margin: 0 !important;
        padding: 10px !important;
        border: 2px solid #000 !important;
        background: #ffffff !important;
        box-shadow: none !important;
    }
}
</style>
@endpush

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const qrTargetUrl = "{{ route('equipment.detail', $equipment->code) }}";
        const barcodeCode = "{{ $equipment->barcode ?: $equipment->code }}";

        // 1. Generate QR Code in Card Display
        const qrContainer = document.getElementById('qrcode-display');
        if (qrContainer) {
            try {
                new QRCode(qrContainer, {
                    text: qrTargetUrl,
                    width: 124,
                    height: 124,
                    colorDark: "#0e263e",
                    colorLight: "#ffffff",
                    correctLevel: QRCode.CorrectLevel.H
                });
            } catch (err) {
                console.warn("QRCode display init error:", err);
                qrContainer.innerHTML = '<img src="https://api.qrserver.com/v1/create-qr-code/?size=130x130&data=' + encodeURIComponent(qrTargetUrl) + '" alt="QR Code" class="w-full h-full object-contain">';
            }
        }

        // 2. Generate QR Code in Printable Sticker Modal
        const qrSticker = document.getElementById('qrcode-sticker');
        if (qrSticker) {
            try {
                new QRCode(qrSticker, {
                    text: qrTargetUrl,
                    width: 84,
                    height: 84,
                    colorDark: "#000000",
                    colorLight: "#ffffff",
                    correctLevel: QRCode.CorrectLevel.H
                });
            } catch (err) {
                console.warn("QRCode sticker init error:", err);
                qrSticker.innerHTML = '<img src="https://api.qrserver.com/v1/create-qr-code/?size=90x90&data=' + encodeURIComponent(qrTargetUrl) + '" alt="QR Code" class="w-full h-full object-contain">';
            }
        }

        // 3. Generate 1D Barcode with JsBarcode
        try {
            if (typeof JsBarcode !== 'undefined') {
                JsBarcode("#barcode-display", barcodeCode, {
                    format: "CODE128",
                    width: 1.5,
                    height: 34,
                    displayValue: true,
                    fontSize: 11,
                    textMargin: 2,
                    lineColor: "#1e293b"
                });
                JsBarcode("#barcode-sticker", barcodeCode, {
                    format: "CODE128",
                    width: 1.2,
                    height: 24,
                    displayValue: true,
                    fontSize: 10,
                    textMargin: 1,
                    lineColor: "#0f172a"
                });
            }
        } catch (e) {
            console.warn("JsBarcode init error:", e);
        }
    });

    function printSticker() {
        window.print();
    }

    function downloadQRCode(code) {
        const canvas = document.querySelector('#qrcode-display canvas');
        if (canvas) {
            const link = document.createElement('a');
            link.download = 'QR_' + code + '.png';
            link.href = canvas.toDataURL('image/png');
            link.click();
        } else {
            const img = document.querySelector('#qrcode-display img');
            if (img) {
                const link = document.createElement('a');
                link.download = 'QR_' + code + '.png';
                link.href = img.src;
                link.click();
            }
        }
    }
</script>
@endpush

