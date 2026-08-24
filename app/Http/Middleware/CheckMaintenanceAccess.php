<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckMaintenanceAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        
        if (!$user) {
            abort(403);
        }

        // Cek jika operator Produksi atau leader di departemen tertentu mencoba akses
        $isProduksiOperator = $user->role === 'operator' && $user->departemen === 'Produksi';
        $isUnrelatedLeader = $user->role === 'leader' && in_array($user->departemen, ['Produksi', 'Information Technology', 'Manager Plant']);
        
        if ($isProduksiOperator || $isUnrelatedLeader) {
            abort(403, 'Akses ditolak. Departemen Anda tidak memiliki wewenang untuk modul ini.');
        }

        return $next($request);
    }
}
