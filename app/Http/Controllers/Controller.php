<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Str;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests, ValidatesRequests;

    public string $view;

    public string $code;

    public string $model;

    public string $url;

    public object $help;

    public function __construct()
    {
        $menu = app('App\Helpers\Helper')->menu();
        $this->help = app('App\Helpers\Helper');
        $this->code = $menu->code ?? 'home';
        $this->model = $menu->model ?? 'home';
        $this->url = config('master.app.url.backend').'/'.($menu->url ?? 'home');
        $this->view = config('master.app.view.backend').'.'.$this->code;
    }

    public function javaScript(Request $request, $layout, $page, $file)
    {
        $data = json_decode($request->getContent(), true) ?? [];
        $data['url'] = $page;
        $layout = config('master.app.view.'.$layout);
        $viewPath = "$layout.$page.".Str::before($file, '.js');

        return response()->view($viewPath, $data)->header('Content-Type', 'application/javascript');
    }
}
