<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PumpAccessMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $accessLevel = 'read'): Response
    {
        $user = auth()->user();
        
        // Admin memiliki akses penuh
        if ($user->isAdmin()) {
            return $next($request);
        }
        
        // Untuk petugas, cek akses ke pump house spesifik
        $pumpHouseId = $request->route('pumpHouse');
        
        if ($pumpHouseId) {
            // Jika ada parameter pump house, cek akses spesifik
            if (!$user->hasAccessToPumpHouse($pumpHouseId, $accessLevel)) {
                abort(403, 'Anda tidak memiliki akses ke rumah pompa ini.');
            }
        } else {
            // Jika tidak ada parameter pump house (halaman index), izinkan petugas masuk
            // Controller akan menangani filtering data sesuai akses
            if (!$user->isPetugas()) {
                abort(403, 'Anda tidak memiliki akses ke sistem ini.');
            }
        }
        
        return $next($request);
    }
} 
 