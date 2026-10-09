<?php

namespace App\Http\Controllers;

use App\Models\LogAktivitas;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(): View
    {
        $user = auth()->user();

        return view('profile.edit', compact('user'));
    }

    public function update(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => ['required', 'string', 'max:20'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:1000'],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ]);

        if ($request->hasFile('avatar')) {
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }
            $path = $request->file('avatar')->store('avatars', 'public');
            $validated['avatar'] = $path;
        } elseif ($request->boolean('remove_avatar')) {
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }
            $validated['avatar'] = null;
        }

        $user->update($validated);

        LogAktivitas::record('Update Profil', 'Profil', "User {$user->name} memperbarui informasi profil akun.", $user->id);

        return back()->with('success', 'Informasi profil berhasil diperbarui.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'confirmed', Password::min(8)],
        ], [
            'current_password.required' => 'Kata sandi saat ini wajib diisi.',
            'current_password.current_password' => 'Kata sandi saat ini yang Anda masukkan salah.',
            'password.required' => 'Kata sandi baru wajib diisi.',
            'password.min' => 'Kata sandi baru minimal harus 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi baru tidak cocok.',
        ]);

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        LogAktivitas::record('Ubah Kata Sandi', 'Profil', "User {$user->name} berhasil mengubah kata sandi akun.", $user->id);

        return back()->with('success', 'Kata sandi berhasil diperbarui.');
    }
}
