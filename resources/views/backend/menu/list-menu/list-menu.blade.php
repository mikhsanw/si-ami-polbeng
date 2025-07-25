<ol class="dd-list">
    @foreach($menu as $mn)
        <li class="dd-item dd3-item" data-id="{{$mn->id}}">
            <div class="dd-handle dd3-handle"></div>
            <div class="dd3-content" title="{{$mn->title ?? ''}}">
                @if($mn->icon) <span class="{{$mn->icon}}"></span> @endif {{$mn->title}}
                    <div class="btn-group btn-group-sm float-end">
                        <button type="button" class="btn-action btn btn-link btn-color-warning btn-active-color-primary me-2 btn-sm" style="padding:calc(.55rem + -14px) calc(1rem + 1px)" title="Ubah data menu" data-title="Edit" data-action="edit" data-url="{{ config('master.app.url.backend').'/'.$page->url }}" data-id="{{ $mn->id }}">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button type="button" class="btn-action btn btn-link btn-color-danger btn-active-color-primary btn-sm" style="padding:calc(.55rem + -14px) calc(1rem + 1px)" title="Hapus menu" data-title="Delete" data-action="delete" data-url="{{ config('master.app.url.backend').'/'.$page->url }}" data-id="{{ $mn->id }}">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
            </div>
            @include($backend.'.menu.list-menu.list-menu', ['menu'=>$mn->children])
        </li>
    @endforeach
</ol>
