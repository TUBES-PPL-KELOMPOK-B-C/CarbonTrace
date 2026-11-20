<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PastikanAdministrator314
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !$user->is_active) {
            abort(403, 'Akses ditolak.');
        }

        // Periksa apakah pengguna memiliki peran administrator
        $isAdmin = false;

        if (method_exists($user, 'hasRole') && $user->hasRole('administrator')) {
            $isAdmin = true;
        } elseif (isset($user->role) && in_array(strtolower($user->role->name), ['administrator', 'admin'])) {
            $isAdmin = true;
        } elseif ((int) $user->role_id === 1) {
            $isAdmin = true;
        }

        if (!$isAdmin) {
            abort(403, 'Akses ditolak. Halaman ini hanya untuk administrator.');
        }

        return $next($request);
    }
}
