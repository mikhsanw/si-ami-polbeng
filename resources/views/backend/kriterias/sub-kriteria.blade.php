@foreach ($items as $sub)
    <!-- Sub-Kriteria -->
    <div class="mb-4">
        <div class="d-flex align-items-center collapsible ps-{{ $level * 5 }} py-2 toggle collapsed"
            data-bs-toggle="collapse" data-bs-target="#sub_{{ $sub->id }}" aria-expanded="false">
            <div class="btn btn-sm btn-icon mw-20px btn-active-color-primary me-3">
                <i class="ki-duotone ki-minus-square toggle-on text-primary fs-2">
                    <span class="path1"></span><span class="path2"></span>
                </i>
                <i class="ki-duotone ki-plus-square toggle-off fs-2">
                    <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                </i>
            </div>
            <div class="text-gray-700 fw-bold fs-6">{{ $sub->kode }} - {{ $sub->nama }}</div>
        </div>

        <div id="sub_{{ $sub->id }}" class="collapse ps-{{ ($level + 1) * 5 }}">

            {{-- Rekursif jika masih ada child --}}
            @if ($sub->children->isNotEmpty())
                @include($backend.'.kriterias.sub-kriteria', ['items' => $sub->children, 'level' => $level + 1, 'page' => $page])
            @else
            <!-- Indikator -->
            @forelse ($sub->indikators as $indikator)
                <div class="d-flex align-items-center mb-2">
                    <span class="bullet me-3"></span>
                    <div class="text-gray-600 fs-6">{{ $indikator->nama }}</div>
                </div>
            @empty
                <div class="text-muted ps-5 mb-2">Belum ada indikator.</div>
            @endforelse

            <!-- Tombol Tambah Indikator -->
            <button class="btn btn-sm btn-light-success mt-3"
                data-title="Tambah Indikator"
                data-action="create-child"
                data-id="{{ $sub->id }}"
                data-url="{{ url($page->url.'/create-indikator/'.$sub->id) }}">
                <i class="ki-outline ki-plus-square fs-4"></i> Tambah Indikator
            </button>

            <!-- Tombol Aksi Sub-Kriteria -->
            <div class="mt-3">
                <button class="btn btn-sm btn-light-primary"
                    data-title="Tambah Sub-Kriteria"
                    data-action="create-child"
                    data-id="{{ $sub->id }}"
                    data-url="{{ url($page->url.'/create-child/'.$sub->id) }}">
                    <i class="ki-outline ki-plus-square fs-4"></i> Tambah Sub-Kriteria
                </button>

                <button class="btn btn-sm btn-light-warning"
                    data-title="Edit Sub-Kriteria"
                    data-action="edit"
                    data-id="{{ $sub->id }}"
                    data-url="{{ url($page->url.'/edit/'.$sub->id) }}">
                    <i class="ki-outline ki-pencil fs-4"></i> Edit
                </button>

                <button class="btn btn-sm btn-light-danger"
                    data-title="Hapus Sub-Kriteria"
                    data-action="delete"
                    data-id="{{ $sub->id }}"
                    data-url="{{ url($page->url) }}">
                    <i class="ki-outline ki-trash fs-4"></i> Hapus
                </button>
            </div>
            @endif

        </div>
    </div>
@endforeach
