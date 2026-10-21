@extends('layouts.app')

@section('title', 'Katalog & Pengajuan Peminjaman Alat - LIMS 2026')
@section('page-title', 'Katalog & Formulir Peminjaman Alat')

@section('content')
<div class="space-y-6">
    <!-- Filter & Search Bar -->
    <form action="{{ route('customer.borrowings.create') }}" method="GET" class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-wrap gap-3 items-center">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama alat/merk/tipe..." class="bg-slate-50 border border-slate-300 text-xs rounded-xl px-4 py-2 text-slate-800 w-full sm:w-64 focus:outline-none focus:border-emerald-500">
        <select name="category" onchange="this.form.submit()" class="bg-slate-50 border border-slate-300 text-xs rounded-xl px-3 py-2 text-slate-800 w-full sm:w-auto focus:outline-none focus:border-emerald-500">
            <option value="">-- Semua Kategori --</option>
            @foreach($categories as $cat)
                <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
            @endforeach
        </select>
        <button type="submit" class="px-4 py-2 bg-slate-800 text-white rounded-xl text-xs font-semibold hover:bg-slate-700 w-full sm:w-auto">Cari</button>
    </form>

    <!-- Available Equipment Catalog Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5" x-data="{ selectedEquipment: null, showModal: false, submitting: false }" x-init="window.addEventListener('pageshow', () => submitting = false)" x-effect="document.body.classList.toggle('overflow-hidden', showModal); document.body.classList.toggle('modal-open', showModal)">
        @forelse($equipmentList as $eq)
            <div class="bg-white rounded-2xl border border-slate-200 p-4 sm:p-5 shadow-sm hover:shadow-md transition-all flex flex-col justify-between">
                <div>
                    <!-- Equipment Photo -->
                    <div class="w-full h-28 bg-slate-50/80 rounded-xl mb-3 flex items-center justify-center overflow-hidden border border-slate-100 p-2">
                        @if($eq->photo_url)
                            <img src="{{ $eq->photo_url }}" alt="{{ $eq->name }}" class="max-h-full max-w-full object-contain mx-auto" loading="lazy">
                        @else
                            <div class="flex flex-col items-center justify-center text-slate-300">
                                <i class="fa-solid fa-microscope text-3xl mb-1 text-slate-300"></i>
                                <span class="text-[10px] font-semibold text-slate-400">Instrumen Lab</span>
                            </div>
                        @endif
                    </div>

                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[11px] font-bold text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-md">{{ $eq->category }}</span>
                        <span class="text-xs font-bold text-slate-600">Stok Tersedia: {{ $eq->available_stock }} unit</span>
                    </div>

                    <h3 class="font-bold text-slate-800 text-sm mb-1">{{ $eq->name }}</h3>
                    <p class="text-xs text-slate-500 mb-3">Merk: {{ $eq->brand ?? 'N/A' }} | Lokasi: {{ $eq->location }}</p>

                    <div class="bg-slate-50 p-3 rounded-xl space-y-1 text-xs mb-4 border border-slate-100">
                        <div class="flex justify-between">
                            <span class="text-slate-400">Harga Sewa:</span>
                            <span class="font-bold text-emerald-700">Rp {{ number_format($eq->rental_price_per_day, 0, ',', '.') }} / hari</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">Deposit Jaminan:</span>
                            <span class="font-semibold text-slate-700">Rp {{ number_format($eq->deposit_amount, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>

                <button @click="selectedEquipment = {{ json_encode($eq) }}; showModal = true" class="w-full py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs rounded-xl shadow-md transition-all">
                    Pilih & Pinjam Alat Ini
                </button>
            </div>
        @empty
            <div class="col-span-full text-center py-12 text-slate-400 bg-white rounded-2xl border border-slate-200">
                Tidak ada instrumen/alat laboratorium yang sesuai kriteria.
            </div>
        @endforelse

        <!-- Modal Booking Form -->
        <template x-teleport="body">
            <div x-show="showModal" 
                 x-cloak
                 @keydown.escape.window="showModal = false"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-[99999] flex items-center justify-center p-4 overflow-y-auto"
                 style="z-index: 99999;">
                <div @click.outside="showModal = false" 
                     x-show="showModal"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                     x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                     x-transition:leave-end="opacity-0 scale-95 translate-y-2"
                      class="bg-white rounded-3xl max-w-lg w-full p-4 sm:p-6 shadow-2xl space-y-4 my-auto relative border border-slate-200">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <h3 class="font-bold text-slate-800 text-base">Pengajuan Peminjaman Alat</h3>
                        <button @click="showModal = false" class="text-slate-400 hover:text-slate-700 cursor-pointer"><i class="fa-solid fa-xmark text-lg"></i></button>
                    </div>

                    <div x-if="selectedEquipment" class="bg-emerald-50 p-3 rounded-xl text-xs text-emerald-900 border border-emerald-200">
                        <p class="font-bold text-sm" x-text="selectedEquipment.name"></p>
                        <p x-text="'Kode: ' + selectedEquipment.code + ' | Deposit/unit: Rp ' + selectedEquipment.deposit_amount.toLocaleString('id-ID')"></p>
                    </div>

                    <form action="{{ route('customer.borrowings.store') }}" method="POST" @submit="if ($el.checkValidity()) submitting = true" class="space-y-4">
                        @csrf
                        <input type="hidden" name="equipment_id" :value="selectedEquipment?.id">

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Jumlah Unit</label>
                                <input type="number" name="quantity" min="1" :max="selectedEquipment?.available_stock ?? selectedEquipment?.quantity" value="1" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-800">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Mulai Pinjam</label>
                                <input type="date" name="start_date" min="{{ date('Y-m-d') }}" value="{{ date('Y-m-d') }}" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-800">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Rencana Pengembalian</label>
                            <input type="date" name="end_date" min="{{ date('Y-m-d', strtotime('+1 day')) }}" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-800">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Tujuan & Lokasi Peminjaman</label>
                            <textarea name="purpose" rows="2" placeholder="Sebutkan kegiatan sampling/lapangan yang dilakukan..." required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-800"></textarea>
                        </div>

                        <div class="flex flex-col sm:flex-row justify-end gap-2.5 pt-2">
                            <button type="button" @click="showModal = false; submitting = false" :disabled="submitting" class="w-full sm:w-auto px-4 py-2 bg-slate-100 text-slate-700 text-xs font-semibold rounded-xl cursor-pointer disabled:opacity-50 text-center">Batal</button>
                            <button type="submit" 
                                :disabled="submitting"
                                :class="{ 'opacity-75 pointer-events-none': submitting }"
                                class="w-full sm:w-auto px-5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold rounded-xl shadow-lg shadow-emerald-600/30 flex items-center justify-center gap-1.5 cursor-pointer disabled:cursor-not-allowed">
                                <i class="fa-solid fa-spinner fa-spin" x-show="submitting" x-cloak></i>
                                <span x-text="submitting ? 'Mengirim Pengajuan...' : 'Kirim Pengajuan'">Kirim Pengajuan</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </template>
    </div>
</div>
@endsection
