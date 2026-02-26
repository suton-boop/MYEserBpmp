<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PermissionMiddleware
{
    public function handle(Request $request, Closure $next, string $permission)
    {
        $user = $request->user(); // sama dengan auth()->user()

        // kalau belum login → jangan 500
        if (!$user) {
            // pilih salah satu:
            // return redirect()->route('login');
            abort(401, 'Silakan login terlebih dahulu.');
        }

        // kalau tidak punya izin → 403
        if (!$user->hasPermission($permission)) {
            abort(403, 'Anda tidak punya akses.');
        }

        return $next($request);
    }
}
