<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

class Backend
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth()->check()) {
            $user = Auth()->user();

            $page = \App\Models\Menu::whereCode(explode(".",Route::currentRouteName())[0])->first();
            View::share([
                'user' => $user,
                'menus' => \App\Models\Menu::whereActive(true)->whereNull('parent_id')->orderBy('sort','asc')->get(),
                'page' => $page,
                'backend' => config('master.app.view.backend'),
                'template' => config('master.app.web.template'),
                // 'prefix' => config('master.app.url.backend'),
                'helper' => app('App\Helpers\Helper'),
            ]);

            $user->update([
                'last_login_at' => now(),
            ]);
            
            if(!is_null($page)) {
                if ($page->maintenance) {
                    return redirect()->route('maintenance');
                }
                if ($page->coming_soon) {
                    return redirect()->route('coming-soon');
                }
            }

        }
        return $next($request);
    }
}
