<?php

namespace App\Http\Middleware;

use App\Models\LogAktivitas;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // Flatten any comma-separated role arguments and trim whitespace
        $allowedRoles = [];
        foreach ($roles as $role) {
            foreach (explode(',', $role) as $r) {
                $trimmed = strtolower(trim($r));
                if ($trimmed !== '') {
                    $allowedRoles[] = $trimmed;
                }
            }
        }

        $userRole = strtolower(trim((string) ($user->role ?? '')));

        if (! in_array($userRole, $allowedRoles, true)) {
            try {
                LogAktivitas::record(
                    'Akses Ditolak',
                    'Keamanan',
                    "Percobaan akses tidak sah oleh {$user->name} ({$user->role}) ke rute [{$request->method()} {$request->path()}]. Peran yang dibutuhkan: ".implode(', ', $allowedRoles),
                    $user->id
                );
            } catch (\Throwable $e) {
                // Fail-safe: pastikan abort 403 tetap berjalan meskipun logging gagal
            }

            abort(403, 'Akses ditolak. Anda tidak memiliki hak akses ke halaman ini.');
        }

        return $next($request);
    }
}
