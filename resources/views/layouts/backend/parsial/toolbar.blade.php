<div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
    <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
        <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
        <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">{{$page->title??''}}</h1>
            <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                <i class="{{$page->icon??''}}"></i> 
                <li class="breadcrumb-item text-muted">
                    <a href="{{url('/')}}" class="text-muted text-hover-primary">Home</a>
                </li>
                @include('layouts.backend.parsial.extra.breadcrumb', ['menus'=>$menus])
            </ul>
        </div>
        
    </div>
</div>