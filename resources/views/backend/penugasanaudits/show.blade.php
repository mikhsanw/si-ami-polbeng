<div class="panel">
    <div class="panel-body">
        {{-- BAGIAN 1: INFORMASI UMUM (TETAP SAMA) --}}
        <div class="mb-4 p-3 bg-body-tertiary rounded">
            <div class="row">
                <div class="col-md-6">
                    <strong>Unit:</strong> {{ $auditPeriode->unit->nama }}<br>
                    <strong>Periode:</strong> {{ $auditPeriode->tahun_akademik }}
                </div>
                <div class="col-md-6">
                    <strong>Standar:</strong> {{ $auditPeriode->instrumenTemplate->nama }}<br>
                    <strong>Lembaga:</strong> {{ $auditPeriode->instrumenTemplate->lembagaAkreditasi->singkatan }}
                </div>
            </div>
        </div>

        {{-- BAGIAN 2: DETAIL INDIKATOR (TETAP SAMA) --}}
        <div class="mb-3">
            <label class="form-label fw-bold fs-5">Indikator Penilaian:</label>
            <p class="fs-6">{{ $data->nama }}</p>
        </div>
        <hr>

        {{-- =============================================== --}}
        {{-- BAGIAN 3: HASIL PENILAIAN (DIUBAH JADI VIEW) --}}
        {{-- =============================================== --}}
        @if ($data->tipe === 'LKPS')
            {{-- TAMPILAN LIHAT UNTUK LKPS --}}
            <div>
                <h5 class="fw-bold mb-3">Data Kinerja (LKPS) yang Diinput:</h5>
                <table class="table table-sm table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Deskripsi Data</th>
                            <th>Nilai yang Diinput</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($data->indikatorInputs as $field)
                            <tr>
                                <td>{{ $field->label_input }}</td>
                                <td>
                                    <strong>{{ optional($data->hasilAuditForPeriode($auditPeriode->id)->dataAuditInputForInput($field->id))->nilai_variable ?? '-' }}</strong>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="text-muted text-center">Tidak ada data input yang
                                    didefinisikan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="mt-3">
                    <h6 class="fw-bold">Skor Hasil Kalkulasi Sistem:</h6>
                    <p class="fs-4 text-primary fw-bold">
                        {{ optional($data->hasilAuditForPeriode($auditPeriode->id))->skor_auditee ?? 'Belum terhitung' }}
                    </p>
                </div>
            </div>
        @endif

        {{-- TAMPILAN LIHAT UNTUK LED --}}
        <div style="{{ $data->tipe === 'LED' ? '' : 'display:none;' }}">
            <h5 class="fw-bold mb-3">Hasil Evaluasi Diri (Skor Auditee):</h5>
            @forelse ($data->rubrikPenilaians->sortByDesc('skor') as $rubrik)
                <div class="form-check mb-3">
                    <input class="form-check-input" disabled type="radio" name="skor_auditee"
                        value="{{ $rubrik->skor }}" id="skor_{{ $rubrik->skor }}"
                        {{ (int) optional($data->hasilAuditForPeriode($auditPeriode->id))->skor_auditee === (int) $rubrik->skor ? 'checked' : '' }}>

                    <label class="" for="skor_{{ $rubrik->skor }}">
                        <strong class="text-primary">Skor {{ $rubrik->skor }}:</strong> {{ $rubrik->deskripsi }}
                    </label>
                </div>
            @empty
                <p class="text-muted">Rubrik penilaian untuk indikator ini belum didefinisikan.</p>
            @endforelse
        </div>

        {{-- BAGIAN 4: DOKUMEN BUKTI (DIUBAH JADI VIEW) --}}
        <div class="mt-4">
            <h5 class="fw-bold mb-3">Dokumen Bukti Terlampir</h5>
            @if ($data->hasilAuditForPeriode($auditPeriode->id)->files->isNotEmpty())
                <div class="list-group">
                    @foreach ($data->hasilAuditForPeriode($auditPeriode->id)->files as $key => $file)
                        <a href="{{ asset($file->link_public_stream) }}"
                            class="list-group-item list-group-item-action d-flex text-primary align-items-center"
                            target="_blank">
                            <i class="fas fa-file-alt fa-fw me-3 text-primary"></i>
                            Bukti Penilaian {{ $key + 1 }}
                            <i class="fas fa-external-link-alt ms-auto"></i>
                        </a>
                    @endforeach
                </div>
            @else
                <p class="text-muted">Tidak ada dokumen bukti yang dilampirkan.</p>
            @endif
        </div>
    </div>
</div>
<style>
    .modal-lg {
        max-width: 1000px !important;
    }
</style>
<script>
    $('.submit-data').hide();
    $('.modal-title').html('<i class="fa fa-search"></i> Detail Data {!! $page->title !!}');
</script>
