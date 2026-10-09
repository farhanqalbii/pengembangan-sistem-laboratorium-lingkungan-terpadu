@extends('layouts.app')

@section('title', 'Master Parameter Uji & Baku Mutu - Admin')
@section('page-title', 'Master Parameter Uji & Regulasi Baku Mutu')

@section('content')
<div class="space-y-6" x-data="{ showModal: false, editParam: null, showDeleteModal: false, deleteUrl: '', deleteName: '' }" x-effect="document.body.classList.toggle('overflow-hidden', showModal || showDeleteModal); document.body.classList.toggle('modal-open', showModal || showDeleteModal)">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
        <h2 class="text-sm font-bold text-slate-800">Daftar Parameter Pengujian Laboratorium & Tarif</h2>
        <button @click="editParam = null; showModal = true" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold rounded-xl shadow-lg shadow-emerald-600/30 flex items-center justify-center">
            <i class="fa-solid fa-plus-circle mr-2"></i> Tambah Parameter Baru
        </button>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider">
                    <tr>
                        <th class="px-4 sm:px-5 py-3">Nama Parameter</th>
                        <th class="px-4 sm:px-5 py-3">Kategori</th>
                        <th class="px-4 sm:px-5 py-3">Metode Uji Standar</th>
                        <th class="px-4 sm:px-5 py-3">Satuan</th>
                        <th class="px-4 sm:px-5 py-3">Tarif Uji (Rp)</th>
                        <th class="px-4 sm:px-5 py-3">Regulasi Baku Mutu</th>
                        <th class="px-4 sm:px-5 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($parameters as $p)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-4 sm:px-5 py-3 font-bold text-slate-800">
                                {{ $p->name }}
                            </td>
                            <td class="px-4 sm:px-5 py-3 font-semibold text-emerald-700">
                                {{ $p->category }}
                            </td>
                            <td class="px-4 sm:px-5 py-3 text-slate-600 font-mono text-[11px]">
                                {{ $p->test_method }}
                            </td>
                            <td class="px-4 sm:px-5 py-3 text-slate-600">
                                {{ $p->unit }}
                            </td>
                            <td class="px-4 sm:px-5 py-3 font-bold text-slate-800">
                                Rp {{ number_format($p->price, 0, ',', '.') }}
                            </td>
                            <td class="px-4 sm:px-5 py-3 text-slate-700 text-[11px]">
                                {{ $p->qualityStandard->regulation_name ?? '-' }} ({{ $p->qualityStandard->summary ?? 'N/A' }})
                            </td>
                            <td class="px-4 sm:px-5 py-3 text-center space-x-1.5 whitespace-nowrap">
                                <button @click="editParam = {{ json_encode($p) }}; showModal = true" class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-lg text-xs">
                                    Edit
                                </button>
                                <button type="button" 
                                        @click="deleteUrl = '{{ route('admin.parameters.destroy', $p->id) }}'; deleteName = '{{ addslashes($p->name) }}'; showDeleteModal = true" 
                                        class="px-2.5 py-1.5 bg-rose-100 hover:bg-rose-200 text-rose-800 font-semibold rounded-lg text-xs transition-colors cursor-pointer">
                                    Hapus
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-12 text-slate-400">Belum ada parameter uji.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Form Create / Edit Parameter -->
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
             class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[99999] flex items-center justify-center p-4 overflow-y-auto"
             style="z-index: 99999;">
            <div @click.outside="showModal = false" 
                 x-show="showModal"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-95 translate-y-2"
                 class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4 my-auto relative">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="font-bold text-slate-800 text-base" x-text="editParam ? 'Edit Parameter: ' + editParam.name : 'Tambah Parameter Uji'"></h3>
                    <button @click="showModal = false" class="text-slate-400 hover:text-slate-700 cursor-pointer"><i class="fa-solid fa-xmark text-lg"></i></button>
                </div>

                <form x-bind:action="editParam ? '/admin/parameters/' + editParam.id : '{{ route('admin.parameters.store') }}'" method="POST" class="space-y-4">
                    @csrf
                    <template x-if="editParam">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama Parameter</label>
                        <input type="text" name="name" :value="editParam ? editParam.name : ''" placeholder="Contoh: pH, BOD5, COD, Lead (Pb)..." required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-800">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Kategori</label>
                            <input type="text" name="category" :value="editParam ? editParam.category : ''" placeholder="Contoh: Air Minum, Udara..." required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-800">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Satuan</label>
                            <input type="text" name="unit" :value="editParam ? editParam.unit : ''" placeholder="mg/L, %..." required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-800">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Metode Uji Standar (SNI/USEPA)</label>
                        <input type="text" name="test_method" :value="editParam ? editParam.test_method : ''" placeholder="Contoh: SNI 6989.11:2019..." required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-800">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tarif Pengujian (Rp)</label>
                        <input type="number" name="price" min="0" step="5000" :value="editParam ? editParam.price : 75000" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs font-bold text-slate-800">
                    </div>

                    <div class="flex justify-end space-x-3 pt-2">
                        <button type="button" @click="showModal = false" class="px-4 py-2 bg-slate-100 text-slate-700 text-xs font-semibold rounded-xl">Batal</button>
                        <button type="submit" class="px-5 py-2 bg-emerald-600 text-white text-xs font-bold rounded-xl shadow-lg shadow-emerald-600/30">Simpan Parameter</button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    <!-- Modal Konfirmasi Hapus Parameter -->
    <template x-teleport="body">
        <div 
            x-show="showDeleteModal" 
            x-cloak
            @keydown.escape.window="showDeleteModal = false"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[99999] flex items-center justify-center p-4 overflow-y-auto"
            style="z-index: 99999;"
        >
            <div 
                @click.outside="showDeleteModal = false"
                x-show="showDeleteModal"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-95 translate-y-3"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="opacity-0 scale-95 translate-y-3"
                class="bg-white rounded-3xl max-w-sm w-full p-6 sm:p-7 shadow-2xl border border-slate-200 text-center relative overflow-hidden my-auto"
            >
                <!-- Decorative Accent Background -->
                <div class="absolute -top-10 -right-10 w-24 h-24 bg-rose-50 rounded-full pointer-events-none"></div>

                <!-- Icon Header -->
                <div class="w-14 h-14 rounded-2xl bg-rose-50 text-rose-600 border border-rose-100 flex items-center justify-center text-2xl mx-auto mb-4 shadow-sm">
                    <i class="fa-solid fa-trash-can"></i>
                </div>

                <!-- Title & Subtitle -->
                <h3 class="text-lg font-bold text-slate-800">Konfirmasi Hapus</h3>
                <p class="text-xs text-slate-500 mt-2 leading-relaxed">
                    Apakah Anda yakin ingin menghapus parameter uji <strong class="text-slate-800" x-text="deleteName"></strong>? Data yang dihapus tidak dapat dipulihkan.
                </p>

                <!-- Buttons Action -->
                <div class="mt-6 flex items-center gap-3">
                    <button 
                        type="button" 
                        @click="showDeleteModal = false" 
                        class="flex-1 py-2.5 px-4 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-xl transition-colors cursor-pointer"
                    >
                        Batal
                    </button>
                    <form :action="deleteUrl" method="POST" class="flex-1 m-0">
                        @csrf
                        @method('DELETE')
                        <button 
                            type="submit" 
                            class="w-full py-2.5 px-4 bg-rose-600 hover:bg-rose-500 text-white font-bold text-xs rounded-xl shadow-lg shadow-rose-600/30 flex items-center justify-center gap-1.5 transition-all cursor-pointer hover:shadow-rose-600/40"
                        >
                            <i class="fa-solid fa-trash-can text-[11px]"></i> Ya, Hapus
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </template>
</div>
@endsection
