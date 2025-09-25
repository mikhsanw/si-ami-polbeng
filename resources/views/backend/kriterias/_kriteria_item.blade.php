<div class="accordion-item">
    <h2 class="accordion-header" id="header_{{ $item->id }}">
        <button class="accordion-button fs-4 fw-semibold @if (!$loop->first && $loop->count > 1) collapsed @endif"
            type="button" data-bs-toggle="collapse" data-bs-target="#body_{{ $item->id }}"
            aria-expanded="{{ $loop->first ? 'true' : 'false' }}" aria-controls="body_{{ $item->id }}">
            @if ($item->indikators->isEmpty() && empty($item->parent))
                <i class="ki-outline ki-abstract-14 fs-2 me-3"></i>
            @endif
            {{-- Menggunakan nama_kriteria sesuai model sebelumnya --}}
            {{ $item->kode . ' ' . $item->nama }}
        </button>

    </h2>

    <div id="body_{{ $item->id }}" class="accordion-collapse collapse @if ($loop->first) show @endif"
        aria-labelledby="header_{{ $item->id }}" data-bs-parent="{{ $parent_id }}">

        <div class="accordion-body">

            @if ($item->children->isNotEmpty())
                <div class="accordion" id="accordion_child_{{ $item->id }}">
                    @foreach ($item->children as $child)
                        @include($backend . '.kriterias._kriteria_item', [
                            'item' => $child,
                            'parent_id' => '#accordion_child_' . $item->id,
                            'loop' => $loop,
                        ])
                    @endforeach
                </div>
            @else
                <div class="ps-5 text-muted">
                    @if ($item->indikators->isEmpty())
                        <p class="text-danger">Tidak ada indikator yang ditambahkan.</p>
                        <button class="btn btn-action btn-sm btn-light-success" data-title="Isi Indikator"
                            data-action="create-child" data-id="{{ $item->id }}"
                            data-url="{{ route($page->code . '.create-indikator', $item->id) }}">
                            <i class="ki-outline ki-plus-square fs-3"></i>
                            Isi Indikator
                        </button>
                    @else
                        <ul class="list-group list-group-flush">
                            @foreach ($item->indikators as $indikator)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center gap-2">
                                        <a href="#" class="btn-action" data-title="Edit Indikator"
                                            data-action="edit-child" data-id="{{ $item->id }}"
                                            data-url="{{ route($page->code . '.edit-indikator', $indikator->id) }}">
                                            {{ $indikator->nama }}
                                            <i class="ki-outline ki-pencil rounded-pill"></i>
                                        </a>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-primary rounded-pill text-white">
                                            {{ $indikator->rubrikPenilaians->count() }}
                                        </span>
                                        <button class="btn btn-action btn-sm btn-light-danger"
                                            data-title="Hapus Indikator" data-action="delete-indikator"
                                            data-id="{{ $indikator->id }}"
                                            data-url="{{ route($page->code . '.delete-indikator', $indikator->id) }}">
                                            <i class="ki-outline ki-trash fs-3"></i>
                                        </button>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                        <button class="btn btn-action btn-sm btn-light-success mt-3" data-title="Isi Indikator"
                            data-action="create-indikator" data-id="{{ $item->id }}"
                            data-url="{{ route($page->code . '.create-indikator', $item->id) }}">
                            <i class="ki-outline ki-plus-square fs-3"></i>
                            Tambah Indikator
                        </button>
                    @endif
                </div>

            @endif

            <div class="mt-4 pt-4 border-top">
                @can($page->code . ' create')
                    <button class="btn btn-action btn-sm btn-light-primary" data-title="Tambah" data-action="create-child"
                        data-id="{{ $item->id }}" data-url="{{ route($page->code . '.create-child', $item->id) }}">
                        <i class="ki-outline ki-plus-square fs-3"></i>
                        Tambah Sub-Kriteria
                    </button>
                @endcan

                <button class="btn btn-sm btn-light-danger btn-action" data-id="{{ $item->id }}"
                    data-url="{{ config('master.app.url.backend') . '/' . $page->url }}" data-title="Hapus Kriteria"
                    data-action="delete">
                    <i class="ki-outline ki-trash fs-3"></i>
                    Hapus Kriteria
                </button>
                <button class="btn btn-sm btn-light-warning btn-action" data-id="{{ $item->id }}"
                    data-url="{{ config('master.app.url.backend') . '/' . $page->url }}" data-title="Edit Kriteria"
                    data-action="edit">

                    <i class="ki-outline ki-pencil fs-3"></i>
                    Edit Kriteria
                </button>
            </div>
        </div>
    </div>
</div>
