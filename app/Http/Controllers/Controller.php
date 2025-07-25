<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Route;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests, ValidatesRequests;
    public string $view, $code, $model, $url;
    public object $help;

    function __construct()
    {
        $this->help = app('App\Helpers\Helper');
        $this->code = app('App\Helpers\Helper')->menu()->code ?? 'home';
        $this->model = app('App\Helpers\Helper')->menu()->model ?? 'home';
        $this->url = config('master.app.url.backend') . '/' . (app('App\Helpers\Helper')->menu()->url ?? 'home');
        $this->view = config('master.app.view.backend') . '.' . $this->code;
    }

    public function javaScript(Request $request, $layout,$page,$file)
    {
        $data = json_decode($request->getContent(), true) ?? [];
        $data['url'] = $page;
        $layout = config('master.app.view.' . $layout);
        $viewPath = "$layout.$page." . Str::before($file, '.js');
        return response()->view($viewPath, $data)->header('Content-Type', 'application/javascript');
    }
}
