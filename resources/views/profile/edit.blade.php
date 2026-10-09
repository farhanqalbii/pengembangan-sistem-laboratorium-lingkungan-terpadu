@extends('layouts.app')

@section('title', 'Profil Pengguna - Sistem Laboratorium Lingkungan Terpadu 2026')
@section('page-title', 'Profil & Pengaturan Akun')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">
<style>
    /* Styling agar kotak pemotong (crop box) berbentuk lingkaran persis seperti foto profil */
    .cropper-view-box,
    .cropper-face {
        border-radius: 50%;
    }
    .cropper-view-box {
        outline: 2px solid #10b981;
        outline-color: rgba(16, 185, 129, 0.85);
    }
    .cropper-point {
        background-color: #10b981;
    }
</style>
@endpush

@section('content')
<div class="space-y-6 max-w-6xl mx-auto">
    <!-- User Header Hero Card -->
    <div class="bg-gradient-to-r from-slate-900 via-[#103a5e] to-emerald-700 rounded-2xl p-5 sm:p-6 text-white shadow-xl relative overflow-hidden border border-slate-700/40">
        <div class="relative z-10 flex flex-col sm:flex-row items-center sm:items-start gap-4 sm:gap-5">
            <!-- Large Avatar -->
            <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl overflow-hidden bg-white/10 border-2 border-white/20 backdrop-blur-md flex items-center justify-center text-2xl sm:text-3xl font-extrabold text-cyan-300 shadow-2xl flex-shrink-0 relative">
                @if($user->avatar_url)
                    <img id="hero-avatar-img" src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
                    <span id="hero-avatar-fallback" class="hidden text-2xl sm:text-3xl font-extrabold text-cyan-300">{{ strtoupper(substr($user->name, 0, 2)) }}</span>
                @else
                    <span id="hero-avatar-fallback" class="text-2xl sm:text-3xl font-extrabold text-cyan-300">{{ strtoupper(substr($user->name, 0, 2)) }}</span>
                    <img id="hero-avatar-img" src="" alt="{{ $user->name }}" class="w-full h-full object-cover hidden">
                @endif
            </div>

            <!-- Profile Info & Meta -->
            <div class="flex-1 text-center sm:text-left">
                <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2.5 mb-1">
                    <h1 class="text-xl sm:text-2xl font-extrabold tracking-tight">{{ $user->name }}</h1>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold capitalize shadow-sm tracking-wide
                        {{ $user->role === 'admin' ? 'bg-purple-500/20 text-purple-200 border border-purple-400/30' : '' }}
                        {{ $user->role === 'manager' ? 'bg-indigo-500/20 text-indigo-200 border border-indigo-400/30' : '' }}
                        {{ $user->role === 'staff' ? 'bg-blue-500/20 text-blue-200 border border-blue-400/30' : '' }}
                        {{ $user->role === 'customer' ? 'bg-emerald-500/20 text-emerald-200 border border-emerald-400/30' : '' }}
                    ">
                        <i class="fa-solid fa-shield-halved mr-1.5 text-[10px]"></i> Role: {{ $user->role }}
                    </span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-cyan-500/20 text-cyan-200 border border-cyan-400/30">
                        <i class="fa-solid fa-circle-check mr-1.5 text-[9px] text-cyan-300"></i> Akun Aktif
                    </span>
                </div>

                <p class="text-slate-200 text-xs sm:text-sm flex flex-wrap items-center justify-center sm:justify-start gap-x-4 gap-y-1 mt-1.5">
                    <span class="inline-flex items-center gap-1.5">
                        <i class="fa-regular fa-envelope text-cyan-300"></i> {{ $user->email }}
                    </span>
                    @if($user->phone)
                        <span class="inline-flex items-center gap-1.5">
                            <i class="fa-brands fa-whatsapp text-emerald-300"></i> {{ $user->phone }}
                        </span>
                    @endif
                    @if($user->company_name)
                        <span class="inline-flex items-center gap-1.5">
                            <i class="fa-regular fa-building text-cyan-200"></i> {{ $user->company_name }}
                        </span>
                    @endif
                </p>

                <p class="text-[11px] text-slate-300/80 mt-2 flex items-center justify-center sm:justify-start gap-1.5">
                    <i class="fa-regular fa-calendar-check text-cyan-300"></i> Terdaftar sejak: {{ $user->created_at ? $user->created_at->translatedFormat('d F Y') : '-' }} &bull; ID Pengguna: #USR-{{ str_pad($user->id, 4, '0', STR_PAD_LEFT) }}
                </p>
            </div>
        </div>

        <div class="absolute right-0 top-0 bottom-0 opacity-10 pointer-events-none hidden md:flex items-center pr-12 text-cyan-200">
            <i class="fa-solid fa-id-card-clip text-[180px]"></i>
        </div>
    </div>

    <!-- Forms Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 sm:gap-6">
        <!-- Edit Profile Form (2 Columns on Desktop) -->
        <div class="lg:col-span-2 bg-white rounded-2xl p-5 sm:p-6 border border-slate-200 shadow-sm relative">
            <div class="border-b border-slate-100 pb-4 mb-6 flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                        <i class="fa-solid fa-user-pen text-emerald-600"></i> Informasi Data Diri
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">Perbarui informasi foto, kontak, dan profil akun Anda.</p>
                </div>
                <span class="text-xs text-slate-400 font-medium hidden sm:inline">Data Pribadi</span>
            </div>

            <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PUT')

                <!-- Photo Profile Upload Card -->
                <div class="bg-gradient-to-r from-slate-50 to-slate-100/70 border border-slate-200 rounded-2xl p-4 sm:p-5 flex flex-col sm:flex-row items-center gap-5">
                    <!-- Preview Avatar Box -->
                    <div class="relative w-20 h-20 rounded-2xl overflow-hidden bg-slate-200 border-2 border-emerald-500/40 shadow-inner flex items-center justify-center flex-shrink-0">
                        @if($user->avatar_url)
                            <img id="avatar-preview" src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
                            <span id="avatar-preview-fallback" class="hidden text-2xl font-bold text-slate-600">{{ strtoupper(substr($user->name, 0, 2)) }}</span>
                        @else
                            <img id="avatar-preview" src="" alt="{{ $user->name }}" class="w-full h-full object-cover hidden">
                            <span id="avatar-preview-fallback" class="text-2xl font-bold text-slate-600">{{ strtoupper(substr($user->name, 0, 2)) }}</span>
                        @endif
                    </div>

                    <div class="flex-1 text-center sm:text-left space-y-2 w-full">
                        <div>
                            <label class="block text-xs font-bold text-slate-800 mb-0.5 flex items-center justify-center sm:justify-start gap-1.5">
                                <i class="fa-solid fa-image text-emerald-600 text-xs"></i> Foto Profil Akun
                            </label>
                            <p class="text-[11px] text-slate-500">Mendukung format JPG, JPEG, PNG, atau WEBP. Anda dapat mengatur dan memotong posisi foto.</p>
                        </div>

                        <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2.5 pt-1">
                            <label class="cursor-pointer inline-flex items-center gap-2 px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl shadow-sm transition-all active:scale-95">
                                <i class="fa-solid fa-camera"></i>
                                <span>Pilih Foto Baru</span>
                                <input type="file" name="avatar" id="avatar-input" accept="image/png, image/jpeg, image/jpg, image/webp" class="hidden" onchange="previewAvatarImage(this)">
                            </label>

                            <button type="button" id="btn-adjust-crop" onclick="reopenCropModal()" class="hidden inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-300 rounded-xl text-xs font-medium transition-colors">
                                <i class="fa-solid fa-crop-simple text-[11px] text-emerald-600"></i>
                                <span>Atur Ulang Posisi</span>
                            </button>

                            @if($user->avatar)
                                <label class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 rounded-xl text-xs font-medium cursor-pointer transition-colors">
                                    <input type="checkbox" name="remove_avatar" value="1" id="remove-avatar-checkbox" class="rounded border-rose-300 text-rose-600 focus:ring-rose-500" onchange="handleRemoveAvatar(this)">
                                    <i class="fa-solid fa-trash-can text-[11px]"></i>
                                    <span>Hapus Foto</span>
                                </label>
                            @endif
                            <span id="file-chosen-name" class="text-[11px] text-slate-500 italic max-w-xs truncate block sm:inline"></span>
                        </div>
                        @error('avatar')
                            <p class="text-rose-600 text-[11px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5 flex items-center gap-1.5">
                            <i class="fa-regular fa-user text-emerald-600 text-xs"></i> Nama Lengkap / PIC
                        </label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:bg-white focus:outline-none focus:border-emerald-600 focus:ring-2 focus:ring-emerald-500/20 transition-all">
                        @error('name')
                            <p class="text-rose-600 text-[11px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5 flex items-center gap-1.5">
                            <i class="fa-regular fa-envelope text-emerald-600 text-xs"></i> Alamat Email
                        </label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:bg-white focus:outline-none focus:border-emerald-600 focus:ring-2 focus:ring-emerald-500/20 transition-all">
                        @error('email')
                            <p class="text-rose-600 text-[11px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5 flex items-center gap-1.5">
                            <i class="fa-brands fa-whatsapp text-emerald-600 text-xs"></i> Nomor WhatsApp / Telepon
                        </label>
                        <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" required placeholder="Contoh: 08123456789"
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:bg-white focus:outline-none focus:border-emerald-600 focus:ring-2 focus:ring-emerald-500/20 transition-all">
                        @error('phone')
                            <p class="text-rose-600 text-[11px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5 flex items-center justify-between">
                            <span class="flex items-center gap-1.5">
                                <i class="fa-regular fa-building text-emerald-600 text-xs"></i> Nama Perusahaan / Instansi
                            </span>
                            <span class="text-[10px] text-slate-400 font-normal px-2 py-0.5 rounded bg-slate-100 border border-slate-200">Opsional</span>
                        </label>
                        <input type="text" name="company_name" value="{{ old('company_name', $user->company_name) }}" placeholder="PT / Instansi / Universitas"
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:bg-white focus:outline-none focus:border-emerald-600 focus:ring-2 focus:ring-emerald-500/20 transition-all">
                        @error('company_name')
                            <p class="text-rose-600 text-[11px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5 flex items-center gap-1.5">
                        <i class="fa-solid fa-location-dot text-emerald-600 text-xs"></i> Alamat Lengkap / Domisili
                    </label>
                    <textarea name="address" rows="3" placeholder="Alamat jalan, gedung, kawasan industri, kota..."
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:bg-white focus:outline-none focus:border-emerald-600 focus:ring-2 focus:ring-emerald-500/20 transition-all">{{ old('address', $user->address) }}</textarea>
                    @error('address')
                        <p class="text-rose-600 text-[11px] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end">
                    <button type="submit" 
                        class="px-6 py-2.5 bg-gradient-to-r from-emerald-600 to-teal-700 hover:from-emerald-500 hover:to-teal-600 active:scale-[0.99] text-white text-xs font-bold rounded-xl shadow-lg shadow-emerald-700/25 transition-all flex items-center gap-2">
                        <i class="fa-solid fa-floppy-disk"></i>
                        <span>Simpan Perubahan Profil</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Change Password & Security Card (1 Column on Desktop) -->
        <div class="space-y-5">
            <!-- Change Password Card -->
            <div class="bg-white rounded-2xl p-5 sm:p-6 border border-slate-200 shadow-sm relative">
                <div class="border-b border-slate-100 pb-3 mb-4">
                    <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                        <i class="fa-solid fa-shield-halved text-emerald-600"></i> Ganti Kata Sandi
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">Pastikan akun Anda menggunakan kata sandi yang kuat.</p>
                </div>

                <form action="{{ route('profile.password') }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5 flex items-center gap-1.5">
                            <i class="fa-solid fa-key text-slate-400 text-xs"></i> Kata Sandi Saat Ini
                        </label>
                        <div class="relative">
                            <input type="password" id="current_password" name="current_password" required placeholder="Masukkan kata sandi lama"
                                class="w-full bg-slate-50 border @error('current_password') border-rose-400 bg-rose-50/20 @else border-slate-200 @enderror rounded-xl pl-4 pr-11 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:bg-white focus:outline-none focus:border-emerald-600 focus:ring-2 focus:ring-emerald-500/20 transition-all">
                            <button type="button" onclick="togglePasswordVisibility('current_password', this)" title="Tampilkan / Sembunyikan"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors focus:outline-none p-1.5 cursor-pointer">
                                <i class="fa-solid fa-eye text-sm"></i>
                            </button>
                        </div>
                        @error('current_password')
                            <p class="text-rose-600 text-xs font-medium mt-1.5 flex items-center gap-1.5">
                                <i class="fa-solid fa-circle-exclamation text-xs"></i>
                                <span>{{ $message }}</span>
                            </p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5 flex items-center gap-1.5">
                            <i class="fa-solid fa-lock text-slate-400 text-xs"></i> Kata Sandi Baru
                        </label>
                        <div class="relative">
                            <input type="password" id="new_password" name="password" required placeholder="Minimal 8 karakter"
                                class="w-full bg-slate-50 border @error('password') border-rose-400 bg-rose-50/20 @else border-slate-200 @enderror rounded-xl pl-4 pr-11 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:bg-white focus:outline-none focus:border-emerald-600 focus:ring-2 focus:ring-emerald-500/20 transition-all">
                            <button type="button" onclick="togglePasswordVisibility('new_password', this)" title="Tampilkan / Sembunyikan"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors focus:outline-none p-1.5 cursor-pointer">
                                <i class="fa-solid fa-eye text-sm"></i>
                            </button>
                        </div>
                        @error('password')
                            <p class="text-rose-600 text-xs font-medium mt-1.5 flex items-center gap-1.5">
                                <i class="fa-solid fa-circle-exclamation text-xs"></i>
                                <span>{{ $message }}</span>
                            </p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5 flex items-center gap-1.5">
                            <i class="fa-solid fa-lock text-slate-400 text-xs"></i> Konfirmasi Kata Sandi Baru
                        </label>
                        <div class="relative">
                            <input type="password" id="new_password_confirmation" name="password_confirmation" required placeholder="Ulangi kata sandi baru"
                                class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-4 pr-11 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:bg-white focus:outline-none focus:border-emerald-600 focus:ring-2 focus:ring-emerald-500/20 transition-all">
                            <button type="button" onclick="togglePasswordVisibility('new_password_confirmation', this)" title="Tampilkan / Sembunyikan"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors focus:outline-none p-1.5 cursor-pointer">
                                <i class="fa-solid fa-eye text-sm"></i>
                            </button>
                        </div>
                    </div>

                    <div class="pt-2">
                        <button type="submit" 
                            class="w-full py-2.5 bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold rounded-xl shadow-md transition-all flex items-center justify-center gap-2">
                            <i class="fa-solid fa-key text-cyan-300"></i>
                            <span>Perbarui Kata Sandi</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Role Access & System Notice Card -->
            <div class="bg-gradient-to-br from-slate-50 to-teal-50/40 rounded-3xl p-5 border border-slate-200 text-xs space-y-3">
                <div class="flex items-center gap-2 font-bold text-slate-800">
                    <i class="fa-solid fa-circle-info text-emerald-600"></i>
                    <span>Informasi Otoritas Akun</span>
                </div>
                <p class="text-slate-600 leading-relaxed text-[11px]">
                    Akun Anda saat ini memiliki hak akses sebagai <strong class="capitalize text-slate-800">{{ $user->role }}</strong> dalam Sistem Laboratorium Lingkungan Terpadu 2026.
                </p>
                <div class="pt-2 border-t border-slate-200/60 text-[11px] text-slate-500">
                    Perubahan alamat email atau kata sandi akan dicatat pada Log Audit Sistem demi keamanan.
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Image Crop Modal -->
<div id="cropModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm hidden" style="z-index: 9999;">
    <div class="bg-white rounded-3xl max-w-xl w-full overflow-hidden shadow-2xl border border-slate-200 flex flex-col max-h-[92vh]">
        <!-- Modal Header -->
        <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50">
            <div>
                <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-crop-simple text-emerald-600"></i>
                    <span>Sesuaikan & Potong Foto Profil</span>
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">Geser, perbesar, atau putar foto agar posisinya pas di dalam lingkaran.</p>
            </div>
            <button type="button" onclick="closeCropModal()" class="w-8 h-8 rounded-full bg-slate-200/70 hover:bg-slate-300 text-slate-600 hover:text-slate-900 flex items-center justify-center transition-colors">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>

        <!-- Modal Body -->
        <div class="p-5 overflow-y-auto space-y-4">
            <!-- Cropper Canvas Container -->
            <div class="w-full h-72 sm:h-80 bg-slate-950 rounded-2xl overflow-hidden flex items-center justify-center relative shadow-inner">
                <img id="cropper-image" src="" alt="Crop preview" class="max-w-full max-h-full block">
            </div>

            <!-- Controls Toolbar -->
            <div class="flex flex-wrap items-center justify-between gap-2 pt-1">
                <div class="flex items-center gap-1.5 bg-slate-100 p-1.5 rounded-xl border border-slate-200">
                    <button type="button" onclick="cropZoom(0.1)" title="Perbesar (Zoom In)" class="w-8 h-8 rounded-lg hover:bg-white text-slate-700 hover:text-emerald-700 flex items-center justify-center transition-all shadow-sm">
                        <i class="fa-solid fa-magnifying-glass-plus text-xs"></i>
                    </button>
                    <button type="button" onclick="cropZoom(-0.1)" title="Perkecil (Zoom Out)" class="w-8 h-8 rounded-lg hover:bg-white text-slate-700 hover:text-emerald-700 flex items-center justify-center transition-all shadow-sm">
                        <i class="fa-solid fa-magnifying-glass-minus text-xs"></i>
                    </button>
                    <div class="w-px h-5 bg-slate-300 mx-0.5"></div>
                    <button type="button" onclick="cropRotate(-90)" title="Putar Kiri 90°" class="w-8 h-8 rounded-lg hover:bg-white text-slate-700 hover:text-emerald-700 flex items-center justify-center transition-all shadow-sm">
                        <i class="fa-solid fa-rotate-left text-xs"></i>
                    </button>
                    <button type="button" onclick="cropRotate(90)" title="Putar Kanan 90°" class="w-8 h-8 rounded-lg hover:bg-white text-slate-700 hover:text-emerald-700 flex items-center justify-center transition-all shadow-sm">
                        <i class="fa-solid fa-rotate-right text-xs"></i>
                    </button>
                    <div class="w-px h-5 bg-slate-300 mx-0.5"></div>
                    <button type="button" onclick="cropReset()" title="Reset Posisi" class="px-2.5 h-8 rounded-lg hover:bg-white text-slate-700 hover:text-emerald-700 text-xs font-semibold flex items-center gap-1 transition-all shadow-sm">
                        <i class="fa-solid fa-arrows-rotate text-[11px]"></i>
                        <span>Reset</span>
                    </button>
                </div>

                <div class="text-[11px] text-slate-500 italic hidden sm:flex items-center gap-1">
                    <i class="fa-regular fa-hand-pointer text-emerald-600"></i>
                    <span>Geser/drag langsung foto dengan kursor</span>
                </div>
            </div>
        </div>

        <!-- Modal Footer -->
        <div class="p-4 border-t border-slate-100 flex items-center justify-end gap-3 bg-slate-50">
            <button type="button" onclick="closeCropModal()" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800 bg-white border border-slate-200 hover:bg-slate-100 rounded-xl transition-all">
                Batal
            </button>
            <button type="button" onclick="applyCrop()" class="px-5 py-2 text-xs font-bold text-white bg-gradient-to-r from-emerald-600 to-teal-700 hover:from-emerald-500 hover:to-teal-600 rounded-xl shadow-md shadow-emerald-700/20 flex items-center gap-1.5 transition-all active:scale-95">
                <i class="fa-solid fa-check"></i>
                <span>Gunakan Hasil Potong</span>
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
<script>
    let currentCropper = null;
    let originalFileName = 'avatar.jpg';
    let lastRawImageDataUrl = null;

    function previewAvatarImage(input) {
        const file = input.files[0];
        if (!file) return;

        originalFileName = file.name;
        const reader = new FileReader();
        reader.onload = function(e) {
            lastRawImageDataUrl = e.target.result;
            openCropModal(lastRawImageDataUrl);
        };
        reader.readAsDataURL(file);
    }

    function openCropModal(imageSrc) {
        const modal = document.getElementById('cropModal');
        const image = document.getElementById('cropper-image');
        image.src = imageSrc;
        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden', 'modal-open');

        if (currentCropper) {
            currentCropper.destroy();
            currentCropper = null;
        }

        // Initialize Cropper once modal & image are ready
        setTimeout(() => {
            currentCropper = new Cropper(image, {
                aspectRatio: 1,
                viewMode: 1,
                dragMode: 'move',
                autoCropArea: 0.85,
                restore: false,
                guides: true,
                center: true,
                highlight: false,
                cropBoxMovable: true,
                cropBoxResizable: true,
                toggleDragModeOnDblclick: false,
            });
        }, 150);
    }

    function reopenCropModal() {
        if (lastRawImageDataUrl) {
            openCropModal(lastRawImageDataUrl);
        }
    }

    function closeCropModal() {
        const modal = document.getElementById('cropModal');
        modal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden', 'modal-open');
        if (currentCropper) {
            currentCropper.destroy();
            currentCropper = null;
        }
    }

    function cropZoom(ratio) {
        if (currentCropper) currentCropper.zoom(ratio);
    }

    function cropRotate(deg) {
        if (currentCropper) currentCropper.rotate(deg);
    }

    function cropReset() {
        if (currentCropper) currentCropper.reset();
    }

    function applyCrop() {
        if (!currentCropper) return;

        const canvas = currentCropper.getCroppedCanvas({
            width: 512,
            height: 512,
            imageSmoothingEnabled: true,
            imageSmoothingQuality: 'high',
        });

        canvas.toBlob(function(blob) {
            const fileName = originalFileName.replace(/\.[^/.]+$/, "") + ".jpg";
            const file = new File([blob], fileName, { type: 'image/jpeg', lastModified: Date.now() });

            // Assign cropped file to input using DataTransfer
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            const avatarInput = document.getElementById('avatar-input');
            if (avatarInput) {
                avatarInput.files = dataTransfer.files;
            }

            const croppedUrl = canvas.toDataURL('image/jpeg', 0.92);

            const preview = document.getElementById('avatar-preview');
            const fallback = document.getElementById('avatar-preview-fallback');
            const heroImg = document.getElementById('hero-avatar-img');
            const heroFallback = document.getElementById('hero-avatar-fallback');
            const nameLabel = document.getElementById('file-chosen-name');
            const removeCheck = document.getElementById('remove-avatar-checkbox');
            const adjustBtn = document.getElementById('btn-adjust-crop');

            if (removeCheck) removeCheck.checked = false;
            if (nameLabel) nameLabel.textContent = originalFileName + ' (sudah diatur posisinya)';
            if (adjustBtn) adjustBtn.classList.remove('hidden');

            if (preview) {
                preview.src = croppedUrl;
                preview.classList.remove('hidden');
            }
            if (fallback) fallback.classList.add('hidden');
            if (heroImg) {
                heroImg.src = croppedUrl;
                heroImg.classList.remove('hidden');
            }
            if (heroFallback) heroFallback.classList.add('hidden');

            closeCropModal();
        }, 'image/jpeg', 0.92);
    }

    function handleRemoveAvatar(checkbox) {
        const preview = document.getElementById('avatar-preview');
        const fallback = document.getElementById('avatar-preview-fallback');
        const heroImg = document.getElementById('hero-avatar-img');
        const heroFallback = document.getElementById('hero-avatar-fallback');
        const fileInput = document.getElementById('avatar-input');
        const nameLabel = document.getElementById('file-chosen-name');
        const adjustBtn = document.getElementById('btn-adjust-crop');

        if (adjustBtn) adjustBtn.classList.add('hidden');

        if (checkbox.checked) {
            if (fileInput) fileInput.value = '';
            if (nameLabel) nameLabel.textContent = '(Foto akan dihapus setelah klik Simpan)';
            if (preview) preview.classList.add('hidden');
            if (fallback) fallback.classList.remove('hidden');
            if (heroImg) heroImg.classList.add('hidden');
            if (heroFallback) heroFallback.classList.remove('hidden');
        } else {
            if (nameLabel) nameLabel.textContent = '';
            @if($user->avatar_url)
                if (preview) {
                    preview.src = "{{ $user->avatar_url }}";
                    preview.classList.remove('hidden');
                }
                if (fallback) fallback.classList.add('hidden');
                if (heroImg) {
                    heroImg.src = "{{ $user->avatar_url }}";
                    heroImg.classList.remove('hidden');
                }
                if (heroFallback) heroFallback.classList.add('hidden');
            @else
                if (preview) preview.classList.add('hidden');
                if (fallback) fallback.classList.remove('hidden');
                if (heroImg) heroImg.classList.add('hidden');
                if (heroFallback) heroFallback.classList.remove('hidden');
            @endif
        }
    }

    function togglePasswordVisibility(inputId, btn) {
        const input = document.getElementById(inputId);
        if (!input) return;
        const icon = btn.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            if (icon) {
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash', 'text-emerald-600');
            }
        } else {
            input.type = 'password';
            if (icon) {
                icon.classList.remove('fa-eye-slash', 'text-emerald-600');
                icon.classList.add('fa-eye');
            }
        }
    }
</script>
@endpush
@endsection
