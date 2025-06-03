<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPumpHouseAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $accessLevel = 'read'): Response
    {
        $user = $request->user();
        
        if (!$user) {
            return redirect()->route('login');
        }

        // Admin memiliki akses ke semua
        if ($user->isAdmin()) {
            return $next($request);
        }

        // Ambil pump_house_id dari route parameter dengan berbagai kemungkinan nama
        $pumpHouseId = $request->route('pumpHouse') ?? 
                      $request->route('pump_house') ?? 
                      $request->route('id') ??
                      $request->input('pump_house_id');

        // Jika tidak ada pump_house_id, cek apakah user memiliki akses ke minimal satu pump house
        if (!$pumpHouseId) {
            $accessibleIds = $user->getAccessiblePumpHouseIds();
            if (empty($accessibleIds)) {
                abort(403, 'Anda tidak memiliki akses ke rumah pompa manapun.');
            }
            return $next($request);
        }

        // Cek akses spesifik ke pump house
        if (!$user->hasAccessToPumpHouse($pumpHouseId, $accessLevel)) {
            abort(403, 'Anda tidak memiliki akses ke rumah pompa ini.');
        }

        return $next($request);
    }
} 
 