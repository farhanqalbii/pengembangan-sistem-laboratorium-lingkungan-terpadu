<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\LogAktivitas;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }

        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            $user = Auth::user();

            LogAktivitas::record('Login', 'Auth', "User {$user->name} ({$user->role}) berhasil login.", $user->id);

            return $this->redirectBasedOnRole($user);
        }

        return back()->withErrors([
            'email' => 'Email atau password tidak cocok.',
        ])->onlyInput('email');
    }

    public function showRegister()
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }

        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'phone' => ['required', 'string', 'max:20'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'customer',
            'phone' => $validated['phone'],
            'company_name' => $validated['company_name'] ?? null,
            'address' => $validated['address'] ?? null,
        ]);

        Auth::login($user);
        LogAktivitas::record('Register', 'Auth', "Pendaftaran customer baru: {$user->name}", $user->id);

        return redirect()->route('customer.dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        if (Auth::check()) {
            LogAktivitas::record('Logout', 'Auth', 'User '.Auth::user()->name.' logout.', Auth::id());
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'Anda telah berhasil keluar dari sistem.');
    }

    protected function redirectBasedOnRole(User $user): RedirectResponse
    {
        return match ($user->role) {
            'admin' => redirect()->route('admin.dashboard'),
            'manager' => redirect()->route('manager.dashboard'),
            'staff' => redirect()->route('staff.dashboard'),
            'customer' => redirect()->route('customer.dashboard'),
            default => redirect()->route('login'),
        };
    }
}
