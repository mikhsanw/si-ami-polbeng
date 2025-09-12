<li class="list-group-item">
    <i class="fas fa-folder-open text-warning"></i> {{ $item->kode . ' ' . $item->nama }}
    @if ($item->children->isEmpty())
        <div class="form-check form-check-inline float-end">
            <input name="kriteria[]" value="{{ $item->id }}" class="form-check-input kriteria-checkbox" type="checkbox"
                data-id="{{ $item->id }}" data-label="{{ $item->kode . ' ' . $item->nama }}"
                id="kriteria-{{ $item->id }}"
                {{ isset($item) && $item->templateKriterias->contains(fn($k) => $k->kriteria_id == $item->id) ? 'checked' : '' }}>
        </div>
    @endif
    @if ($item->children->isNotEmpty())
        <div class="child">
            <ul class="list-group">
                @foreach ($item->children as $child)
                    @include($backend . '.' . $page->code . '._kriteria_item', [
                        'item' => $child,
                    ])
                @endforeach
            </ul>
        </div>
    @endif
</li>
