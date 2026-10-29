<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\LogAktivitas;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PasswordResetController extends Controller
{
    public function showLinkRequestForm(): View
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request): RedirectResponse
    {
        $request->validate([
            'email_or_phone' => ['required', 'string'],
        ], [
            'email_or_phone.required' => 'Silakan masukkan email atau nomor HP yang terdaftar.',
        ]);

        $input = trim($request->input('email_or_phone'));

        $user = User::where('email', $input)
            ->orWhere('phone', $input)
            ->first();

        if (! $user) {
            return back()->withErrors([
                'email_or_phone' => 'Akun dengan email atau nomor HP tersebut tidak ditemukan dalam sistem.',
            ])->withInput();
        }

        // Generate reset token
        $token = Str::random(64);

        // Store or update in password_reset_tokens
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'token' => Hash::make($token),
                'created_at' => Carbon::now(),
            ]
        );

        $resetUrl = route('password.reset', ['token' => $token, 'email' => $user->email]);

        $user->sendPasswordResetNotification($token);

        LogAktivitas::record(
            'Reset Password Request',
            'Auth',
            "Permintaan reset password untuk akun {$user->name} ({$user->email})",
            $user->id
        );

        return back()->with([
            'status' => 'Tautan pemulihan kata sandi telah berhasil dikirimkan ke alamat email '.$user->email.'. Silakan periksa kotak masuk atau spam email Anda.',
        ]);
    }

    public function showResetForm(Request $request, string $token): View|RedirectResponse
    {
        $email = $request->query('email');

        if (! $email) {
            $msg = 'Tautan pemulihan kata sandi tidak valid atau tidak lengkap. Silakan ajukan permohonan baru.';

            return redirect()->route('password.request')
                ->with('error', $msg)
                ->withErrors(['email' => $msg]);
        }

        $record = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->first();

        if (! $record) {
            $msg = 'Tautan pemulihan kata sandi tidak valid atau telah digunakan. Silakan ajukan permohonan baru.';

            return redirect()->route('password.request')
                ->with('error', $msg)
                ->withErrors(['email' => $msg]);
        }

        // Pastikan token cocok
        if (! Hash::check($token, $record->token)) {
            $msg = 'Tautan pemulihan kata sandi tidak valid atau telah diganti dengan permintaan baru. Silakan gunakan tautan terbaru atau ajukan kembali.';

            return redirect()->route('password.request')
                ->with('error', $msg)
                ->withErrors(['email' => $msg]);
        }

        // Periksa masa berlaku token (60 menit)
        if (Carbon::parse($record->created_at)->addMinutes(60)->isPast()) {
            // Hapus hanya record yang terbukti kedaluwarsa dan cocok ini
            DB::table('password_reset_tokens')
                ->where('email', $email)
                ->where('created_at', $record->created_at)
                ->delete();

            $msg = 'Tautan pemulihan kata sandi telah kedaluwarsa (masa berlaku 60 menit). Silakan ajukan permohonan baru.';

            return redirect()->route('password.request')
                ->with('error', $msg)
                ->withErrors(['email' => $msg]);
        }

        $user = User::where('email', $email)->first();
        if (! $user) {
            $msg = 'Akun pengguna tidak ditemukan. Silakan ajukan permohonan baru.';

            return redirect()->route('password.request')
                ->with('error', $msg)
                ->withErrors(['email' => $msg]);
        }

        return view('auth.reset-password', [
            'token' => $token,
            'email' => $email,
        ]);
    }

    public function reset(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'min:8', 'confirmed'],
        ], [
            'password.min' => 'Kata sandi baru minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        return DB::transaction(function () use ($request) {
            $record = DB::table('password_reset_tokens')
                ->where('email', $request->email)
                ->lockForUpdate()
                ->first();

            if (! $record) {
                return back()->withErrors(['email' => 'Permintaan reset kata sandi tidak valid atau telah kedaluwarsa.'])->withInput();
            }

            // Validasi kecocokan token terlebih dahulu agar tidak menghapus token baru pengguna
            if (! Hash::check($request->token, $record->token)) {
                return back()->withErrors(['email' => 'Token reset kata sandi tidak cocok.'])->withInput();
            }

            // Periksa kedaluwarsa token (60 menit)
            if (Carbon::parse($record->created_at)->addMinutes(60)->isPast()) {
                DB::table('password_reset_tokens')
                    ->where('email', $request->email)
                    ->where('created_at', $record->created_at)
                    ->delete();

                return back()->withErrors(['email' => 'Tautan reset kata sandi telah kedaluwarsa. Silakan ajukan kembali.'])->withInput();
            }

            $user = User::where('email', $request->email)->lockForUpdate()->first();
            if (! $user) {
                return back()->withErrors(['email' => 'Pengguna tidak ditemukan.'])->withInput();
            }

            $user->forceFill([
                'password' => Hash::make($request->password),
                'remember_token' => Str::random(60),
            ])->save();

            // Token sekali pakai: hapus setelah sukses diubah
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();

            LogAktivitas::record(
                'Reset Password Berhasil',
                'Auth',
                "Kata sandi akun {$user->name} ({$user->email}) berhasil diperbarui.",
                $user->id
            );

            return redirect()->route('login')->with('success', 'Kata sandi Anda berhasil diperbarui! Silakan masuk dengan kata sandi baru.');
        });
    }
}
