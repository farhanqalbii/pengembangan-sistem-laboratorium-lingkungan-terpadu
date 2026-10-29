@extends('layouts.app')

@section('title', 'Manajemen Pengguna - Admin')
@section('page-title', 'Manajemen Pengguna & Role Access Control')

@section('content')
<div class="space-y-6" x-data="{ showModal: false, editUser: null, showDeleteModal: false, deleteUrl: '', deleteName: '' }" x-effect="document.body.classList.toggle('overflow-hidden', showModal || showDeleteModal); document.body.classList.toggle('modal-open', showModal || showDeleteModal)">
    <!-- Action Bar -->
    <div class="relative z-20 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
        <form action="{{ route('admin.users.index') }}" method="GET" class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama/email/perusahaan..." class="bg-slate-50 border border-slate-300 text-xs rounded-xl px-4 py-2 text-slate-800 w-full sm:w-64 focus:outline-none focus:border-emerald-500">
            <x-custom-select 
                name="role" 
                :options="[
                    '' => '-- Semua Role --',
                    'admin' => 'Admin',
                    'manager' => 'Manajer Lab',
                    'staff' => 'Staff / Analis',
                    'customer' => 'Customer'
                ]"
                :selected="request('role')"
                auto-submit
                container-class="w-full sm:w-48"
                button-class="py-2"
                aria-label="Filter berdasarkan Role Pengguna"
            />
            <button type="submit" class="px-4 py-2 bg-slate-800 text-white rounded-xl text-xs font-semibold hover:bg-slate-700">Filter</button>
        </form>

        <button @click="editUser = null; showModal = true" class="w-full sm:w-auto px-5 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold rounded-xl shadow-lg shadow-emerald-600/30 flex items-center justify-center">
            <i class="fa-solid fa-user-plus mr-2"></i> Tambah User Baru
        </button>
    </div>

    <!-- Users Table -->
    <div class="relative z-10 bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs min-w-[700px]">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider">
                    <tr>
                        <th class="px-4 sm:px-5 py-3">Nama Lengkap</th>
                        <th class="px-4 sm:px-5 py-3">Email</th>
                        <th class="px-4 sm:px-5 py-3">Role</th>
                        <th class="px-4 sm:px-5 py-3">Perusahaan / No HP</th>
                        <th class="px-4 sm:px-5 py-3">Tgl Terdaftar</th>
                        <th class="px-4 sm:px-5 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($users as $u)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-4 sm:px-5 py-3">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 rounded-full overflow-hidden bg-slate-100 border border-slate-200 flex items-center justify-center font-bold text-emerald-700 text-xs flex-shrink-0 shadow-sm">
                                        @if($u->avatar_url)
                                            <img src="{{ $u->avatar_url }}" alt="{{ $u->name }}" class="w-full h-full object-cover">
                                        @else
                                            {{ strtoupper(substr($u->name, 0, 2)) }}
                                        @endif
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-800">{{ $u->name }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 sm:px-5 py-3 text-slate-600">
                                {{ $u->email }}
                            </td>
                            <td class="px-4 sm:px-5 py-3">
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold capitalize
                                    {{ $u->role === 'admin' ? 'bg-purple-100 text-purple-800' : '' }}
                                    {{ $u->role === 'manager' ? 'bg-indigo-100 text-indigo-800' : '' }}
                                    {{ $u->role === 'staff' ? 'bg-blue-100 text-blue-800' : '' }}
                                    {{ $u->role === 'customer' ? 'bg-emerald-100 text-emerald-800' : '' }}
                                ">
                                    {{ $u->role }}
                                </span>
                            </td>
                            <td class="px-4 sm:px-5 py-3 text-slate-700">
                                <div>{{ $u->company_name ?? '-' }}</div>
                                <div class="text-[11px] text-slate-400">{{ $u->phone ?? '-' }}</div>
                            </td>
                            <td class="px-4 sm:px-5 py-3 text-slate-500">
                                {{ $u->created_at->format('d/m/Y') }}
                            </td>
                            <td class="px-4 sm:px-5 py-3 text-center space-x-1.5 whitespace-nowrap">
                                <button @click="editUser = {{ json_encode($u) }}; showModal = true" class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-lg text-xs">
                                    Edit
                                </button>
                                @if(auth()->id() !== $u->id)
                                    <button type="button" 
                                            @click="deleteUrl = '{{ route('admin.users.destroy', $u->id) }}'; deleteName = '{{ addslashes($u->name) }}'; showDeleteModal = true" 
                                            class="px-2.5 py-1.5 bg-rose-100 hover:bg-rose-200 text-rose-800 font-semibold rounded-lg text-xs transition-colors cursor-pointer">
                                        Hapus
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-12 text-slate-400">Tidak ada user ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($users->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50">
                {{ $users->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Form Create / Edit User -->
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
                  class="bg-white rounded-2xl max-w-md w-full p-4 sm:p-6 shadow-2xl space-y-4 my-auto relative max-h-[92vh] overflow-y-auto">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="font-bold text-slate-800 text-base" x-text="editUser ? 'Edit User: ' + editUser.name : 'Tambah Pengguna Baru'"></h3>
                    <button @click="showModal = false" class="text-slate-400 hover:text-slate-700 cursor-pointer"><i class="fa-solid fa-xmark text-lg"></i></button>
                </div>

                <form x-bind:action="editUser ? '/admin/users/' + editUser.id : '{{ route('admin.users.store') }}'" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <template x-if="editUser">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    <!-- Foto Profil -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Foto Profil <span class="text-slate-400 font-normal">(Opsional)</span></label>
                        <div class="flex items-center gap-3">
                            <div class="w-11 h-11 rounded-full overflow-hidden bg-slate-100 border border-slate-300 flex items-center justify-center font-bold text-emerald-700 text-xs flex-shrink-0 shadow-sm">
                                <template x-if="editUser && editUser.avatar_url">
                                    <img :src="editUser.avatar_url" class="w-full h-full object-cover">
                                </template>
                                <template x-if="!editUser || !editUser.avatar_url">
                                    <span x-text="editUser ? editUser.name.substring(0, 2).toUpperCase() : 'US'"></span>
                                </template>
                            </div>
                            <div class="flex-1">
                                <input type="file" name="avatar" accept="image/png, image/jpeg, image/jpg, image/webp" class="text-xs text-slate-600 file:mr-2 file:py-1 file:px-2.5 file:rounded-xl file:border-0 file:text-[11px] file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 cursor-pointer">
                                <template x-if="editUser && editUser.avatar_url">
                                    <label class="inline-flex items-center gap-1.5 mt-1 text-[11px] text-rose-600 hover:text-rose-700 cursor-pointer">
                                        <input type="checkbox" name="remove_avatar" value="1" class="rounded border-rose-300 text-rose-600 focus:ring-rose-500">
                                        <span>Hapus foto saat ini</span>
                                    </label>
                                </template>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama Lengkap</label>
                        <input type="text" name="name" :value="editUser ? editUser.name : ''" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-800">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Alamat Email</label>
                        <input type="email" name="email" :value="editUser ? editUser.email : ''" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-800">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Role Hak Akses</label>
                        <x-custom-select 
                            name="role" 
                            id="modal_user_role"
                            :options="[
                                'customer' => 'Customer',
                                'staff' => 'Staff / Analis Lab',
                                'manager' => 'Manajer Lab',
                                'admin' => 'Administrator'
                            ]"
                            :selected="old('role', 'customer')"
                            alpine-model="editUser ? editUser.role : 'customer'"
                            required
                            aria-label="Pilih Role Hak Akses Pengguna"
                        />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">No WhatsApp/HP</label>
                            <input type="text" name="phone" :value="editUser ? editUser.phone : ''" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-800">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Perusahaan</label>
                            <input type="text" name="company_name" :value="editUser ? editUser.company_name : ''" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-800">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Kata Sandi <span class="text-slate-400 font-normal" x-show="editUser">(Kosongkan jika tidak diubah)</span></label>
                        <input type="password" name="password" :required="!editUser" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-800">
                    </div>

                    <div class="flex flex-col-reverse sm:flex-row justify-end gap-2 sm:space-x-3 pt-2">
                        <button type="button" @click="showModal = false" class="w-full sm:w-auto px-4 py-2 bg-slate-100 text-slate-700 text-xs font-semibold rounded-xl text-center">Batal</button>
                        <button type="submit" class="w-full sm:w-auto px-5 py-2 bg-emerald-600 text-white text-xs font-bold rounded-xl shadow-lg shadow-emerald-600/30 text-center">Simpan User</button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    <!-- Modal Konfirmasi Hapus User -->
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
                class="bg-white rounded-3xl max-w-sm w-full p-5 sm:p-7 shadow-2xl border border-slate-200 text-center relative overflow-hidden my-auto max-h-[92vh] overflow-y-auto"
            >
                <!-- Decorative Accent Background -->
                <div class="absolute -top-10 -right-10 w-24 h-24 bg-rose-50 rounded-full pointer-events-none"></div>

                <!-- Icon Header -->
                <div class="w-14 h-14 rounded-2xl bg-rose-50 text-rose-600 border border-rose-100 flex items-center justify-center text-2xl mx-auto mb-4 shadow-sm">
                    <i class="fa-solid fa-user-xmark"></i>
                </div>

                <!-- Title & Subtitle -->
                <h3 class="text-lg font-bold text-slate-800">Konfirmasi Hapus</h3>
                <p class="text-xs text-slate-500 mt-2 leading-relaxed">
                    Apakah Anda yakin ingin menghapus akun pengguna <strong class="text-slate-800" x-text="deleteName"></strong>? Data akses akun ini akan dihapus dari sistem.
                </p>

                <!-- Buttons Action -->
                <div class="mt-6 flex flex-col-reverse sm:flex-row items-center gap-2.5 sm:gap-3">
                    <button 
                        type="button" 
                        @click="showDeleteModal = false" 
                        class="w-full sm:flex-1 py-2.5 px-4 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-xl transition-colors cursor-pointer text-center"
                    >
                        Batal
                    </button>
                    <form :action="deleteUrl" method="POST" class="w-full sm:flex-1 m-0">
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
