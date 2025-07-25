@if(count($mn->children) > 0)
<div data-kt-menu-trigger="click" class="menu-item menu-accordion {{ $mn->active($mn->code) }}">
    <!--begin:Menu link-->
    <span class="menu-link">
        @if($mn->icon)
        <span class="menu-icon">
            <i class="{{ $mn->icon }}"></i>
        </span>
        @else
        <span class="menu-bullet">
            <span class="bullet bullet-dot"></span>
        </span>
        @endif
        <span class="menu-title">{{ $mn->title }}</span>
        <span class="menu-arrow"></span>
    </span>
    <div class="menu-sub menu-sub-accordion">
        @foreach($mn->children as $sub)
            @if($sub->show && $sub->active)
                @canany([$page->code.' list'])
                    @include('layouts.backend.parsial.sidebar-menu.submenu', ['mn'=>$sub])
                @endcanany
            @endif
        @endforeach
    </div>
</div>
@else
<div class="menu-item">
    <a class="menu-link {{ $mn->active($mn->code) }}" href="{{url(config('master.app.url.backend').'/'.$mn->url)}}">
        @if($mn->icon)
        <span class="menu-icon">
            <i class="{{ $mn->icon }}"></i>
        </span>
        @else
        <span class="menu-bullet">
            <span class="bullet bullet-dot"></span>
        </span>
        @endif
        <span class="menu-title">{{ $mn->title }}</span>
    </a>
</div>
@endif