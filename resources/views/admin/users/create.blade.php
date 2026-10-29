@extends('layouts.app')

@section('title', 'Tambah Pengguna Baru - Admin')
@section('page-title', 'Tambah Pengguna Baru')

@section('content')
<div class="max-w-2xl mx-auto bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
    <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-4">
        @csrf

        <div>
            <label class="block text-xs font-bold text-slate-700 mb-1">Nama Lengkap</label>
            <input type="text" name="name" value="{{ old('name') }}" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-800">
            @error('name') <span class="text-rose-500 text-[11px]">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-xs font-bold text-slate-700 mb-1">Alamat Email</label>
            <input type="email" name="email" value="{{ old('email') }}" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-800">
            @error('email') <span class="text-rose-500 text-[11px]">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-xs font-bold text-slate-700 mb-1">Role Hak Akses</label>
            <x-custom-select 
                name="role" 
                :options="[
                    'customer' => 'Customer',
                    'staff' => 'Staff / Analis Lab',
                    'manager' => 'Manajer Lab',
                    'admin' => 'Administrator'
                ]"
                :selected="old('role', 'customer')"
                required
                aria-label="Pilih Role Hak Akses Pengguna"
            />
            @error('role') <span class="text-rose-500 text-[11px]">{{ $message }}</span> @enderror
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">No WhatsApp/HP</label>
                <input type="text" name="phone" value="{{ old('phone') }}" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-800">
                @error('phone') <span class="text-rose-500 text-[11px]">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Perusahaan</label>
                <input type="text" name="company_name" value="{{ old('company_name') }}" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-800">
                @error('company_name') <span class="text-rose-500 text-[11px]">{{ $message }}</span> @enderror
            </div>
        </div>

        <div>
            <label class="block text-xs font-bold text-slate-700 mb-1">Alamat</label>
            <textarea name="address" rows="2" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-800">{{ old('address') }}</textarea>
            @error('address') <span class="text-rose-500 text-[11px]">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-xs font-bold text-slate-700 mb-1">Kata Sandi</label>
            <input type="password" name="password" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-800">
            @error('password') <span class="text-rose-500 text-[11px]">{{ $message }}</span> @enderror
        </div>

        <div class="flex justify-end space-x-3 pt-4 border-t border-slate-100">
            <a href="{{ route('admin.users.index') }}" class="px-4 py-2 bg-slate-100 text-slate-700 text-xs font-semibold rounded-xl">Batal</a>
            <button type="submit" class="px-5 py-2 bg-emerald-600 text-white text-xs font-bold rounded-xl shadow-lg shadow-emerald-600/30">Simpan User</button>
        </div>
    </form>
</div>
@endsection
