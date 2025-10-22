<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminAuthenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Cek apakah user sudah login di guard 'web'
        if (!Auth::guard('web')->check()) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu');
        }

        // 2. Cek apakah user yang login memiliki role 'admin'
        // if (!Auth::user()->isAdmin()) {
        //     // Jika bukan admin, logout (opsional) atau redirect ke home
        //     // Auth::guard('web')->logout();
        //     return redirect()->route('home')->with('error', 'Anda tidak memiliki hak akses admin.');
        // }

        // 3. Jika dia admin, share nama ke view dan lanjutkan
        view()->share('admin_name', Auth::user()->name);

        return $next($request);
    }
}