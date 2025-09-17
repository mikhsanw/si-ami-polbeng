<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRoutePermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        $user = auth()->user();

        // Super Admin bebas semua
        if ($user->hasRole('Super Admin')) {
            return $next($request);
        }

        $routeName = $request->route()->getName();
        $uri       = $request->route()->uri();   // contoh: "admin/hasilaudits/audit-kriteria/{id}"

        // skip home
        if ($routeName === 'home') {
            return $next($request);
        }

        // kalau ada routeName → mapping ke permission
        if ($routeName) {
            $permission = $this->mapRouteToPermission($routeName);
            if ($user->can($permission)) {
                return $next($request);
            }
        }

        // kalau TIDAK ada routeName → fallback ke prefix resource
        // ambil segmen setelah "admin"
        $segments = $request->segments(); 
        // contoh: ["admin", "hasilaudits", "audit-kriteria", "uuid"]
        $resource = $segments[1] ?? null;  

        if ($resource && $user->can($resource.' list')) {
            return $next($request);
        }

        abort(403, 'Unauthorized');
    }

    protected function mapRouteToPermission($routeName)
    {
        $map = [
        'index'   => 'list',
        'create'  => 'create',
        'store'   => 'create',
        'edit'    => 'edit',
        'update'  => 'edit',
        'destroy' => 'delete',
        'show'    => 'list',
        ];

        $parts = explode('.', $routeName);

        // Jika routeName tidak punya titik, anggap action = index
        $resource = $parts[0];
        $action   = $parts[1] ?? 'index';  

        $permissionAction = $map[$action] ?? $action;

        return $resource.' '.$permissionAction;
    }

}