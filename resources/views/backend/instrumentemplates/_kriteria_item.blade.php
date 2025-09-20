{{-- Parameter: $kriterias (koleksi kriteria), $selectedIndikatorIds (array ID indikator yang sudah terpilih), $backend, $page --}}
<ul class="list-group">
    @foreach ($kriterias as $kriteria)
        <li class="list-group-item">
            {{-- Indikator expand/collapse untuk Kriteria --}}
            @if ($kriteria->children->isNotEmpty() || $kriteria->indikators->isNotEmpty())
                <i class="fas fa-chevron-right toggle-child me-2 text-muted" data-target="child-{{ $kriteria->id }}"></i>
            @else
                <i class="fas fa-minus me-2 text-secondary"></i> {{-- Placeholder jika tidak ada anak/indikator --}}
            @endif

            {{-- Icon folder + nama kriteria --}}
            <i class="fas fa-folder text-warning me-1"></i> <span class="fw-medium">{{ $kriteria->kode . ' ' . $kriteria->nama }}</span>

            <div id="child-{{ $kriteria->id }}" class="child ms-4 mt-2 d-none">
                <ul class="list-group list-group-flush">
                    {{-- 🔹 Daftar Indikator dengan Checkbox --}}
                    @if ($kriteria->indikators->isNotEmpty())
                        @foreach ($kriteria->indikators as $indikator)
                            <li class="list-group-item py-1">
                                <div class="form-check">
                                    <input name="indikators[]"
                                           value="{{ $indikator->id }}"
                                           class="form-check-input indikator-checkbox"
                                           type="checkbox"
                                           id="indikator-{{ $indikator->id }}"
                                           {{ in_array($indikator->id, $selectedIndikatorIds) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="indikator-{{ $indikator->id }}">
                                        <i class="fas fa-bullseye text-info me-1"></i> {{ $indikator->nama }}
                                    </label>
                                </div>
                            </li>
                        @endforeach
                    @endif

                    {{-- 🔹 Anak Kriteria (rekursif) --}}
                    @if ($kriteria->children->isNotEmpty())
                        {{-- Kirim $kriterias (children), $selectedIndikatorIds ke partial anak juga --}}
                        @include($backend . '.instrumentemplates._kriteria_item', [
                            'kriterias' => $kriteria->children,
                            'selectedIndikatorIds' => $selectedIndikatorIds,
                            'backend' => $backend
                        ])
                    @endif
                </ul>
            </div>
        </li>
    @endforeach
</ul>