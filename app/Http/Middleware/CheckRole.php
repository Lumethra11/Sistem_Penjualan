<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        // Jika pengguna belum login, atau role-nya tidak sesuai, tendang!
        if (!auth()->check() || auth()->user()->role !== $role) {
            
            // Kalau dia kasir iseng masuk ke admin, balikin ke kasir
            if (auth()->user() && auth()->user()->role === 'kasir') {
                return redirect()->route('kasir.transaksi')->with('error', 'Akses ditolak!');
            }
            
            // Fallback default jika tidak punya akses
            abort(403, 'Unauthorized.');
        }

        return $next($request);
    }
}