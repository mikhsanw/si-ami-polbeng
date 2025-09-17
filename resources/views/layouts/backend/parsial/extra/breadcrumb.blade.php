@foreach($menus as $mn)
    @if($mn->active($mn->code) != '')
        <li class="breadcrumb-item">
            <span class="bullet bg-gray-500 w-5px h-2px"></span>
        </li>
        <li class="breadcrumb-item text-muted">
            @if(count($mn->children) > 0)
            {{$mn->title}}
            @else
            <a href="{{url(config('master.app.url.backend').'/'.$mn->url)}}" class="text-muted text-hover-primary">{{$mn->title}}</a></li>
            @endif
    @endif
    @include('layouts.backend.parsial.extra.breadcrumb', ['menus'=>$mn->children])
@endforeach
